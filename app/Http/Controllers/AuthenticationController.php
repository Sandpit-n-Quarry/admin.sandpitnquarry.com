<?php

namespace App\Http\Controllers;

use App\Services\MfaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    protected MfaService $mfaService;

    public function __construct(MfaService $mfaService)
    {
        $this->mfaService = $mfaService;
    }
    
    public function forgotPassword()
    {
        return view('authentication/forgotPassword');
    }

    public function signIn()
    {
        return view('authentication/signin');
    }

    public function postLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }
        
        // Check if email has sandpitnquarry.com domain
        $email = $request->email;
        $domain = substr(strrchr($email, "@"), 1);
        
        if ($domain !== 'sandpitnquarry.com') {
            return redirect()->back()
                ->withErrors(['email' => 'Only @sandpitnquarry.com email addresses are allowed'])
                ->withInput($request->except('password'));
        }
        
        $credentials = $request->only('email', 'password');
        
        // Validate credentials without logging in
        if (Auth::validate($credentials)) {
            // Credentials are valid - get the user
            $user = \App\Models\User::where('email', $request->email)->first();
            
            // IMPORTANT: Ensure user is NOT already logged in
            if (Auth::check()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            
            // Check if MFA is enabled and required for this user
            if ($this->mfaService->isMfaEnabled() && $this->mfaService->isRequiredForUser($user)) {
                
                // Generate and send MFA code
                $verificationCode = $this->mfaService->generateAndSendCode($user, $request->ip());
                
                if (!$verificationCode) {
                    return redirect()->back()
                        ->withErrors(['email' => 'Failed to send verification code. Please try again or contact support.'])
                        ->withInput($request->except('password'));
                }
                
                // Store user ID in session for MFA verification
                $request->session()->put('mfa_user_id', $user->id);
                $request->session()->put('mfa_remember', $request->has('remember'));
                
                
                // Redirect to MFA verification page
                return redirect()->route('mfa.show');
            }
            
            // MFA not required - proceed with normal login
            if (Auth::attempt($credentials, $request->has('remember'))) {
                return redirect()->intended('/');
            }
        }
        
        // Authentication failed
        return redirect()->back()
            ->withErrors(['email' => 'Invalid credentials'])
            ->withInput($request->except('password'));
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('signin');
    }
}
