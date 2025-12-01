<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailVerificationCode;
use App\Mail\MfaVerificationCodeMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MfaService
{
    /**
     * Generate a 6-digit verification code.
     */
    protected function generateCode(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check if user has exceeded code generation rate limit.
     */
    public function hasExceededGenerationLimit(User $user): bool
    {
        $cacheKey = "mfa_generation_limit_{$user->id}";
        $attempts = Cache::get($cacheKey, 0);
        
        return $attempts >= config('mfa.max_generation_attempts', 3);
    }

    /**
     * Increment code generation attempt counter.
     */
    protected function incrementGenerationAttempts(User $user): void
    {
        $cacheKey = "mfa_generation_limit_{$user->id}";
        $attempts = Cache::get($cacheKey, 0);
        $ttl = config('mfa.generation_rate_limit_window', 900); // 15 minutes
        
        Cache::put($cacheKey, $attempts + 1, $ttl);
    }

    /**
     * Check if user has exceeded verification attempt rate limit.
     */
    public function hasExceededVerificationLimit(User $user): bool
    {
        $cacheKey = "mfa_verification_limit_{$user->id}";
        $attempts = Cache::get($cacheKey, 0);
        
        return $attempts >= config('mfa.max_verification_attempts', 5);
    }

    /**
     * Increment verification attempt counter.
     */
    public function incrementVerificationAttempts(User $user): void
    {
        $cacheKey = "mfa_verification_limit_{$user->id}";
        $attempts = Cache::get($cacheKey, 0);
        $ttl = config('mfa.verification_rate_limit_window', 900); // 15 minutes
        
        Cache::put($cacheKey, $attempts + 1, $ttl);
    }

    /**
     * Reset verification attempt counter.
     */
    public function resetVerificationAttempts(User $user): void
    {
        $cacheKey = "mfa_verification_limit_{$user->id}";
        Cache::forget($cacheKey);
    }

    /**
     * Generate and send a new verification code.
     */
    public function generateAndSendCode(User $user, ?string $ipAddress = null): ?EmailVerificationCode
    {
        // Check rate limit
        if ($this->hasExceededGenerationLimit($user)) {
            return null;
        }

        // Invalidate any existing valid codes for this user
        EmailVerificationCode::forUser($user->id)
            ->valid()
            ->update(['used_at' => now()]);

        // Generate new code
        $plainCode = $this->generateCode();
        $expiresAt = Carbon::now()->addMinutes(config('mfa.code_expiration_minutes', 15));

        // Create verification code record
        $verificationCode = EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => Hash::make($plainCode),
            'expires_at' => $expiresAt,
            'ip_address' => $ipAddress,
        ]);

        // Send email
        try {
            Mail::to($user->email)->send(new MfaVerificationCodeMail($user, $plainCode, $expiresAt));
        } catch (\Exception $e) {
            Log::error("Failed to send MFA code to user {$user->id}: {$e->getMessage()}");
            // Delete the code if email fails
            $verificationCode->delete();
            return null;
        }

        // Increment rate limit counter
        $this->incrementGenerationAttempts($user);

        return $verificationCode;
    }

    /**
     * Verify a code for a user.
     */
    public function verifyCode(User $user, string $code): array
    {
        // Check rate limit
        if ($this->hasExceededVerificationLimit($user)) {
            return [
                'success' => false,
                'message' => 'Too many verification attempts. Please try again later.',
                'error' => 'rate_limit_exceeded'
            ];
        }

        // Get the most recent valid code
        $verificationCode = EmailVerificationCode::forUser($user->id)
            ->valid()
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$verificationCode) {
            $this->incrementVerificationAttempts($user);
            return [
                'success' => false,
                'message' => 'No valid verification code found. Please request a new code.',
                'error' => 'no_valid_code'
            ];
        }

        // Check if code has expired
        if ($verificationCode->isExpired()) {
            $this->incrementVerificationAttempts($user);
            return [
                'success' => false,
                'message' => 'Verification code has expired. Please request a new code.',
                'error' => 'code_expired'
            ];
        }

        // Verify the code
        if (!$verificationCode->verifyCode($code)) {
            $verificationCode->incrementAttempts();
            $this->incrementVerificationAttempts($user);
            
            $remainingAttempts = config('mfa.max_verification_attempts', 5) - Cache::get("mfa_verification_limit_{$user->id}", 0);
            
            return [
                'success' => false,
                'message' => "Invalid verification code. {$remainingAttempts} attempts remaining.",
                'error' => 'invalid_code',
                'remaining_attempts' => $remainingAttempts
            ];
        }

        // Code is valid - mark as used
        $verificationCode->markAsUsed();
        $this->resetVerificationAttempts($user);
        return [
            'success' => true,
            'message' => 'Verification successful',
        ];
    }

    /**
     * Get remaining time for a user's current code.
     */
    public function getRemainingTime(User $user): ?int
    {
        $verificationCode = EmailVerificationCode::forUser($user->id)
            ->valid()
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$verificationCode) {
            return null;
        }

        return max(0, $verificationCode->expires_at->diffInSeconds(now()));
    }

    /**
     * Clean up expired codes.
     */
    public function cleanupExpiredCodes(): int
    {
        $deleted = EmailVerificationCode::expired()
            ->where('created_at', '<', now()->subDays(config('mfa.cleanup_after_days', 7)))
            ->delete();
        return $deleted;
    }

    /**
     * Check if MFA is enabled globally.
     */
    public function isMfaEnabled(): bool
    {
        return config('mfa.enabled', true);
    }

    /**
     * Check if MFA is required for a specific user.
     */
    public function isRequiredForUser(User $user): bool
    {
        // If MFA is disabled globally, it's not required
        if (!$this->isMfaEnabled()) {
            return false;
        }

        // Check if user has MFA enabled (if you add a column to users table)
        // return $user->mfa_enabled ?? true;
        
        // For now, require MFA for all users when globally enabled
        return true;
    }
}
