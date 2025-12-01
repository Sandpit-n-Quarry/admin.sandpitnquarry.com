<?php

namespace App\Http\Controllers;

use App\Services\MfaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MfaController extends Controller
{
    protected MfaService $mfaService;

    public function __construct(MfaService $mfaService)
    {
        $this->mfaService = $mfaService;
    }

    /**
     * Show the MFA verification form.
     */
    public function show(Request $request)
    {
        // Check if user has pending MFA verification
        if (!$request->session()->has('mfa_user_id')) {
            return redirect()->route('signin')->withErrors(['error' => 'No pending authentication found.']);
        }

        $userId = $request->session()->get('mfa_user_id');
        $user = \App\Models\User::find($userId);

        if (!$user) {
            $request->session()->forget('mfa_user_id');
            return redirect()->route('signin')->withErrors(['error' => 'User not found.']);
        }

        // Get remaining time for current code
        $verificationCode = \App\Models\EmailVerificationCode::forUser($user->id)
            ->valid()
            ->orderBy('created_at', 'desc')
            ->first();
        
        // If no valid code exists, generate a new one
        if (!$verificationCode) {
            $verificationCode = $this->mfaService->generateAndSendCode($user, $request->ip());
            
            if (!$verificationCode) {
                return redirect()->route('signin')->withErrors([
                    'error' => 'Failed to generate verification code. Please try logging in again.'
                ]);
            }
        }

        
        $remainingTime = max(0, $verificationCode->expires_at->diffInSeconds(now()));
        $expiresAt = $verificationCode->expires_at;
        

        return view('authentication.mfa-verify', [
            'email' => $user->email,
            'remainingTime' => $remainingTime,
            'expiresAt' => $expiresAt,
            'canResend' => !$this->mfaService->hasExceededGenerationLimit($user),
        ]);
    }

    /**
     * Verify the MFA code.
     */
    public function verify(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6|regex:/^[0-9]+$/',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if user has pending MFA verification
        if (!$request->session()->has('mfa_user_id')) {
            return redirect()->route('signin')->withErrors(['error' => 'No pending authentication found.']);
        }

        $userId = $request->session()->get('mfa_user_id');
        $user = \App\Models\User::find($userId);

        if (!$user) {
            $request->session()->forget('mfa_user_id');
            return redirect()->route('signin')->withErrors(['error' => 'User not found.']);
        }

        // Verify the code
        $result = $this->mfaService->verifyCode($user, $request->code);

        if (!$result['success']) {
            // If rate limit exceeded, clear session and redirect to login
            if ($result['error'] === 'rate_limit_exceeded') {
                $request->session()->forget('mfa_user_id');
                return redirect()->route('signin')->withErrors(['error' => $result['message']]);
            }

            return redirect()->back()->withErrors(['code' => $result['message']]);
        }

        // MFA successful - complete the authentication
        Auth::login($user, $request->session()->get('mfa_remember', false));
        
        // Clear MFA session data
        $request->session()->forget(['mfa_user_id', 'mfa_remember']);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    /**
     * Resend the verification code.
     */
    public function resend(Request $request)
    {
        // Check if user has pending MFA verification
        if (!$request->session()->has('mfa_user_id')) {
            return redirect()->route('signin')->withErrors(['error' => 'No pending authentication found.']);
        }

        $userId = $request->session()->get('mfa_user_id');
        $user = \App\Models\User::find($userId);

        if (!$user) {
            $request->session()->forget('mfa_user_id');
            return redirect()->route('signin')->withErrors(['error' => 'User not found.']);
        }

        // Check rate limit
        if ($this->mfaService->hasExceededGenerationLimit($user)) {
            return redirect()->back()->withErrors([
                'error' => 'Too many code requests. Please wait 15 minutes and try again.'
            ]);
        }

        // Generate and send new code
        $verificationCode = $this->mfaService->generateAndSendCode($user, $request->ip());

        if (!$verificationCode) {
            return redirect()->back()->withErrors([
                'error' => 'Failed to send verification code. Please try again or contact support.'
            ]);
        }

        return redirect()->back()->with('success', 'A new verification code has been sent to your email.');
    }

    /**
     * Cancel MFA verification and return to login.
     */
    public function cancel(Request $request)
    {
        $request->session()->forget(['mfa_user_id', 'mfa_remember']);
        
        return redirect()->route('signin')->with('info', 'Login cancelled. Please try again.');
    }
}
