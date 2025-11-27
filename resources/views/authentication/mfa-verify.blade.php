<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<x-head/>

<style>
    body {
        margin: 0;
        padding: 0;
        position: relative;
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    .auth {
        position: relative;
        width: 100%;
        min-height: 100vh;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .background-image-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 1;
    }
    .background-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        filter: brightness(0.9) contrast(1.1);
        transition: transform 0.8s ease-in-out;
        animation: gentle-zoom 20s infinite alternate;
    }
    
    @keyframes gentle-zoom {
        0% {
            transform: scale(1);
        }
        100% {
            transform: scale(1.05);
        }
    }
    .mfa-container {
        position: relative;
        background-color: rgba(255, 255, 255, 0.95);
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        margin: 20px;
        max-width: 550px;
        width: 100%;
        z-index: 10;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        color: #333;
        padding: 50px !important;
        border-top: 4px solid #0d6efd;
    }
    
    .mfa-header {
        text-align: center;
        margin-bottom: 35px;
    }
    
    .mfa-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    .mfa-icon svg {
        width: 40px;
        height: 40px;
        color: white;
    }
    
    .mfa-title {
        font-size: 28px;
        font-weight: 700;
        color: #212529;
        margin-bottom: 10px;
    }
    
    .mfa-subtitle {
        font-size: 15px;
        color: #6c757d;
        line-height: 1.6;
    }
    
    .email-display {
        background-color: #e7f3ff;
        padding: 12px 20px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 25px;
        border-left: 3px solid #0d6efd;
    }
    
    .email-display strong {
        color: #0d6efd;
        font-weight: 600;
    }
    
    .code-input-group {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin: 30px 0;
    }
    
    .code-input {
        width: 55px;
        height: 65px;
        text-align: center;
        font-size: 28px;
        font-weight: bold;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        transition: all 0.3s;
        font-family: 'Courier New', monospace;
    }
    
    .code-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        outline: none;
        transform: scale(1.05);
    }
    
    .timer-display {
        text-align: center;
        margin: 20px 0;
        padding: 15px;
        background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
        border-radius: 8px;
        border-left: 3px solid #ffc107;
    }
    
    .timer-display.expired {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        border-left: 3px solid #dc3545;
    }
    
    .timer-display .timer-icon {
        font-size: 24px;
        margin-right: 8px;
    }
    
    .timer-display .timer-text {
        font-size: 16px;
        font-weight: 600;
        color: #856404;
    }
    
    .timer-display.expired .timer-text {
        color: #721c24;
    }
    
    .btn-verify {
        width: 100%;
        padding: 14px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 8px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .btn-verify:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    
    .btn-verify:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .resend-section {
        text-align: center;
        margin-top: 25px;
        padding-top: 25px;
        border-top: 1px solid #dee2e6;
    }
    
    .resend-text {
        color: #6c757d;
        font-size: 14px;
        margin-bottom: 10px;
    }
    
    .btn-resend {
        color: #0d6efd;
        background: none;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: underline;
    }
    
    .btn-resend:hover {
        color: #0a58ca;
    }
    
    .btn-resend:disabled {
        color: #adb5bd;
        cursor: not-allowed;
        text-decoration: none;
    }
    
    .btn-cancel {
        width: 100%;
        padding: 12px;
        font-size: 14px;
        border-radius: 8px;
        background: transparent;
        border: 1px solid #dee2e6;
        color: #6c757d;
        transition: all 0.3s;
        margin-top: 15px;
    }
    
    .btn-cancel:hover {
        background-color: #f8f9fa;
        border-color: #adb5bd;
    }
    
    .alert {
        padding: 14px 18px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 3px solid;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        border-color: #dc3545;
        color: #721c24;
    }
    
    .alert-success {
        background-color: #d1e7dd;
        border-color: #198754;
        color: #0f5132;
    }
    
    .security-tips {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-top: 25px;
        font-size: 13px;
        color: #6c757d;
    }
    
    .security-tips h6 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .security-tips ul {
        margin: 0;
        padding-left: 20px;
    }
    
    .security-tips li {
        margin: 5px 0;
    }
    
    @media (max-width: 576px) {
        .mfa-container {
            padding: 30px 20px !important;
        }
        
        .code-input {
            width: 45px;
            height: 55px;
            font-size: 24px;
        }
        
        .mfa-title {
            font-size: 24px;
        }
    }
</style>

<body data-remaining-time="{{ $remainingTime ?? 0 }}">
    <section class="auth">
        <div class="background-image-container">
            <img src="{{ asset('assets/images/thumbs/auth-img2.png') }}" alt="" class="background-image">
        </div>

        <div class="mfa-container">
            <div class="mfa-header">
                <div class="mfa-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h2 class="mfa-title">Verify Your Identity</h2>
                <p class="mfa-subtitle">
                    We've sent a 6-digit verification code to your email address
                </p>
            </div>

            <div class="email-display">
                <small>Code sent to:</small><br>
                <strong>{{ $email }}</strong>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Error:</strong>
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('mfa.verify') }}" method="POST" id="mfaForm">
                @csrf
                
                <div class="code-input-group">
                    <input type="text" class="code-input" maxlength="1" name="digit1" id="digit1" autocomplete="off" required>
                    <input type="text" class="code-input" maxlength="1" name="digit2" id="digit2" autocomplete="off" required>
                    <input type="text" class="code-input" maxlength="1" name="digit3" id="digit3" autocomplete="off" required>
                    <input type="text" class="code-input" maxlength="1" name="digit4" id="digit4" autocomplete="off" required>
                    <input type="text" class="code-input" maxlength="1" name="digit5" id="digit5" autocomplete="off" required>
                    <input type="text" class="code-input" maxlength="1" name="digit6" id="digit6" autocomplete="off" required>
                </div>
                
                <input type="hidden" name="code" id="codeInput">

                @if($remainingTime !== null && $remainingTime > 0)
                    <div class="timer-display" id="timerDisplay">
                        <span class="timer-icon">⏱️</span>
                        <span class="timer-text">Code expires in <span id="countdown">{{ gmdate('i:s', $remainingTime) }}</span></span>
                        @if($expiresAt)
                            <div style="font-size: 12px; margin-top: 5px; color: #6c757d;">
                                Expires at {{ $expiresAt->format('g:i A') }}
                            </div>
                        @endif
                    </div>
                @else
                    <div class="timer-display expired" id="timerDisplay">
                        <span class="timer-icon">⚠️</span>
                        <span class="timer-text">Code has expired. Please request a new one.</span>
                        @if($expiresAt)
                            <div style="font-size: 12px; margin-top: 5px;">
                                Expired at {{ $expiresAt->format('g:i A') }}
                            </div>
                        @endif
                    </div>
                @endif

                <button type="submit" class="btn-verify" id="verifyBtn">
                    Verify Code
                </button>
            </form>

            <div class="resend-section">
                <p class="resend-text">Didn't receive the code?</p>
                <form action="{{ route('mfa.resend') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-resend" id="resendBtn" {{ !$canResend ? 'disabled' : '' }}>
                        Resend Code
                    </button>
                </form>
            </div>

            <form action="{{ route('mfa.cancel') }}" method="POST">
                @csrf
                <button type="submit" class="btn-cancel">
                    Cancel and Return to Login
                </button>
            </form>

            <div class="security-tips">
                <h6>🛡️ Security Tips</h6>
                <ul>
                    <li>Never share your verification code with anyone</li>
                    <li>Check that you're on the official Sand Pit N Quarry website</li>
                    <li>If you didn't request this code, contact support immediately</li>
                </ul>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.code-input');
            const form = document.getElementById('mfaForm');
            const codeInput = document.getElementById('codeInput');
            const verifyBtn = document.getElementById('verifyBtn');
            const countdownElement = document.getElementById('countdown');
            const timerDisplay = document.getElementById('timerDisplay');
            const remainingSeconds = parseInt(document.body.getAttribute('data-remaining-time')) || 0;
            
            // Auto-focus first input
            inputs[0].focus();
            
            // Handle input and auto-advance
            inputs.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    // Only allow numbers
                    this.value = this.value.replace(/[^0-9]/g, '');
                    
                    if (this.value.length === 1 && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                    
                    // Update hidden input with complete code
                    updateCodeInput();
                });
                
                input.addEventListener('keydown', function(e) {
                    // Handle backspace
                    if (e.key === 'Backspace' && this.value === '' && index > 0) {
                        inputs[index - 1].focus();
                    }
                    
                    // Handle paste
                    if (e.key === 'v' && (e.ctrlKey || e.metaKey)) {
                        e.preventDefault();
                        navigator.clipboard.readText().then(text => {
                            const digits = text.replace(/[^0-9]/g, '').split('').slice(0, 6);
                            digits.forEach((digit, i) => {
                                if (inputs[i]) {
                                    inputs[i].value = digit;
                                }
                            });
                            updateCodeInput();
                            if (digits.length === 6) {
                                inputs[5].focus();
                            }
                        });
                    }
                });
            });
            
            function updateCodeInput() {
                const code = Array.from(inputs).map(input => input.value).join('');
                codeInput.value = code;
                
                // Enable/disable verify button based on code length
                verifyBtn.disabled = code.length !== 6;
            }
            
            // Countdown timer
            if (remainingSeconds > 0) {
                let timeLeft = remainingSeconds;
                
                const countdownInterval = setInterval(function() {
                    timeLeft--;
                    
                    if (timeLeft <= 0) {
                        clearInterval(countdownInterval);
                        timerDisplay.classList.add('expired');
                        timerDisplay.innerHTML = '<span class="timer-icon">⚠️</span><span class="timer-text">Code has expired. Please request a new one.</span>';
                        verifyBtn.disabled = true;
                    } else {
                        const minutes = Math.floor(timeLeft / 60);
                        const seconds = timeLeft % 60;
                        countdownElement.textContent = minutes + ':' + seconds.toString().padStart(2, '0');
                        
                        // Warning when less than 1 minute
                        if (timeLeft < 60) {
                            timerDisplay.style.borderLeftColor = '#ffc107';
                        }
                    }
                }, 1000);
            }
            
            // Form submission
            form.addEventListener('submit', function(e) {
                updateCodeInput();
                if (codeInput.value.length !== 6) {
                    e.preventDefault();
                    alert('Please enter the complete 6-digit code');
                }
            });
        });
    </script>
</body>
</html>
