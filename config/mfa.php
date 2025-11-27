<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MFA Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable Multi-Factor Authentication globally.
    | When disabled, users will be able to login without MFA verification.
    |
    */
    'enabled' => env('MFA_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Code Expiration Time
    |--------------------------------------------------------------------------
    |
    | The number of minutes a verification code remains valid after generation.
    | Recommended: 10-15 minutes for security balance.
    |
    */
    'code_expiration_minutes' => (int) env('MFA_CODE_EXPIRATION_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | Code Length
    |--------------------------------------------------------------------------
    |
    | The length of the verification code. Default is 6 digits.
    | Changing this requires updating the email template and validation.
    |
    */
    'code_length' => 6,

    /*
    |--------------------------------------------------------------------------
    | Maximum Verification Attempts
    |--------------------------------------------------------------------------
    |
    | The maximum number of times a user can attempt to verify a code
    | before being required to request a new code or re-login.
    |
    */
    'max_verification_attempts' => (int) env('MFA_MAX_VERIFICATION_ATTEMPTS', 5),

    /*
    |--------------------------------------------------------------------------
    | Verification Rate Limit Window
    |--------------------------------------------------------------------------
    |
    | Time window (in seconds) for verification rate limiting.
    | After exceeding max_verification_attempts within this window,
    | user must wait before trying again. Default: 900 seconds (15 minutes)
    |
    */
    'verification_rate_limit_window' => (int) env('MFA_VERIFICATION_RATE_LIMIT_WINDOW', 900),

    /*
    |--------------------------------------------------------------------------
    | Maximum Code Generation Attempts
    |--------------------------------------------------------------------------
    |
    | The maximum number of times a user can request a new verification code
    | within the generation rate limit window.
    |
    */
    'max_generation_attempts' => (int) env('MFA_MAX_GENERATION_ATTEMPTS', 3),

    /*
    |--------------------------------------------------------------------------
    | Generation Rate Limit Window
    |--------------------------------------------------------------------------
    |
    | Time window (in seconds) for code generation rate limiting.
    | After exceeding max_generation_attempts within this window,
    | user must wait before requesting a new code.
    | Default: 900 seconds (15 minutes)
    |
    */
    'generation_rate_limit_window' => (int) env('MFA_GENERATION_RATE_LIMIT_WINDOW', 900),

    /*
    |--------------------------------------------------------------------------
    | Cleanup After Days
    |--------------------------------------------------------------------------
    |
    | Number of days after which expired verification codes should be
    | permanently deleted from the database during cleanup operations.
    |
    */
    'cleanup_after_days' => (int) env('MFA_CLEANUP_AFTER_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Remember Device Duration
    |--------------------------------------------------------------------------
    |
    | Number of days a device can be "remembered" before requiring MFA again.
    | Set to 0 to disable remember device functionality.
    | Note: This feature requires additional implementation.
    |
    */
    'remember_device_days' => env('MFA_REMEMBER_DEVICE_DAYS', 0),

    /*
    |--------------------------------------------------------------------------
    | Email Configuration
    |--------------------------------------------------------------------------
    |
    | Email settings specific to MFA notifications.
    |
    */
    'email' => [
        'from' => [
            'address' => env('MFA_EMAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', 'noreply@sandpitnquarry.com')),
            'name' => env('MFA_EMAIL_FROM_NAME', env('MAIL_FROM_NAME', 'Sand Pit N Quarry')),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable detailed logging of MFA events for security auditing.
    |
    */
    'logging' => [
        'enabled' => env('MFA_LOGGING_ENABLED', true),
        'channel' => env('MFA_LOGGING_CHANNEL', 'stack'),
    ],
];
