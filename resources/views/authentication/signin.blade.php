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
    .auth-right {
        position: relative;
        background-color: rgba(255, 255, 255, 0.95);
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        margin: 50px;
        max-width: 800px;
        z-index: 10;
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        color: #333;
        padding: 40px !important;
        border-left: 4px solid #0d6efd;
    }
    .auth-left {
        display: none !important;
    }
    
    /* Form styling */
    .form-control {
        color: #212529 !important;
        border: 1px solid #dee2e6;
        transition: border-color 0.2s, box-shadow 0.2s;
        padding-left: 45px !important;
        font-size: 15px;
    }
    
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.2);
    }
    
    .icon-field {
        position: relative;
    }
    
    .icon-field .icon {
        left: 15px;
        z-index: 10;
        font-size: 18px;
    }
    
    /* Background overlay */
    .background-image-container::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(0, 0, 0, 0.6) 0%, rgba(0, 0, 0, 0.7) 100%);
        pointer-events: none;
    }
    
    /* Checkbox styling */
    .form-check-input {
        width: 18px !important;
        height: 18px !important;
        border: 1px solid #ced4da !important;
        box-shadow: none !important;
        background-color: #fff !important;
    }
    
    .form-check-input:checked {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
    }
    
    .form-check-label {
        font-size: 14px;
        color: #495057 !important;
        font-weight: 400 !important;
    }
    
    /* Button styling */
    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
        box-shadow: 0 2px 5px rgba(13, 110, 253, 0.2);
        transition: all 0.2s ease;
        font-weight: 500 !important;
        letter-spacing: 0.3px;
    }
    
    .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0b5ed7;
        box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
        transform: translateY(-1px);
    }
</style>

<body>
    <section class="auth d-flex flex-wrap justify-content-end align-items-center">
        <div class="background-image-container">
            <img src="https://storage.googleapis.com/snq-website-images/customer/Merchant.png" alt="Sand Pit n Quarry Admin" class="background-image">
        </div>
        <div class="auth-right d-flex flex-column justify-content-center">
            <div class="w-100">
                    <div class="text-center mb-4">
                        <a href="{{ route('dashboard.index') }}" class="d-inline-block mb-4">
                            <img src="https://storage.googleapis.com/snq-website-images/customer/Logo-1.png" alt="Sand Pit n Quarry" style="max-height: 60px;">
                        </a>
                        <h3 class="mb-2 text-dark fw-bold">SNQ Admin Panel</h3>
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="bg-primary" style="height: 3px; width: 40px;"></div>
                        </div>
                        <p class="mt-3 text-secondary">Sign in with your <strong>@sandpitnquarry.com</strong> credentials</p>
                    </div>
                    
                    <form action="{{ route('signin') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="email" class="form-label text-dark fw-medium mb-2">Email Address</label>
                            <div class="icon-field">
                                <span class="icon top-50 translate-middle-y text-primary">
                                    <iconify-icon icon="mage:email"></iconify-icon>
                                </span>
                                <input type="email" name="email" id="email" class="form-control h-50-px bg-white radius-8 text-dark" placeholder="username@sandpitnquarry.com" value="{{ old('email') }}">
                            </div>
                            @if ($errors->has('email'))
                                <div class="text-danger mt-2 small">{{ $errors->first('email') }}</div>
                            @endif
                        </div>
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="your-password" class="form-label text-dark fw-medium">Password</label>
                                <!-- You can add "Forgot Password" link here if needed -->
                            </div>
                            <div class="position-relative">
                                <div class="icon-field">
                                    <span class="icon top-50 translate-middle-y text-primary">
                                        <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
                                    </span>
                                    <input type="password" name="password" class="form-control h-50-px bg-white radius-8 text-dark" id="your-password" placeholder="Enter your password">
                                </div>
                                <span class="toggle-password ri-eye-line cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-primary" data-toggle="#your-password"></span>
                            </div>
                            @if ($errors->has('password'))
                                <div class="text-danger mt-2 small">{{ $errors->first('password') }}</div>
                            @endif
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check d-flex align-items-center">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label ms-2" for="remember">Keep me signed in</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary text-white py-3 w-100 radius-8 fw-medium">SIGN IN</button>
                        
                        <div class="mt-4 text-center">
                            <p class="text-secondary small">This portal is restricted to authorized personnel only.<br>Access with @sandpitnquarry.com email addresses only</p>
                        </div>
            </div>
        </div>
    </section>

<x-script />

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password visibility toggle functionality
        const togglePasswordButtons = document.querySelectorAll('.toggle-password');
        togglePasswordButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                this.classList.toggle('ri-eye-off-line');
                this.classList.toggle('ri-eye-line');
                const inputSelector = this.getAttribute('data-toggle');
                const inputField = document.querySelector(inputSelector);
                
                if (inputField.getAttribute('type') === 'password') {
                    inputField.setAttribute('type', 'text');
                } else {
                    inputField.setAttribute('type', 'password');
                }
            });
        });
        
        // Form validation enhancement
        const loginForm = document.querySelector('form');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                const emailInput = document.querySelector('input[name="email"]');
                const passwordInput = document.querySelector('input[name="password"]');
                let isValid = true;
                
                // Simple validation
                if (emailInput && !emailInput.value.trim().endsWith('@sandpitnquarry.com')) {
                    // Only show error if no server-side error is already displayed
                    if (!document.querySelector('.text-danger')) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'text-danger mt-2 small client-error';
                        errorDiv.textContent = 'Please use your @sandpitnquarry.com email address';
                        emailInput.parentNode.appendChild(errorDiv);
                    }
                    isValid = false;
                }
                
                // Remove any client-side error messages when typing
                ['input', 'focus'].forEach(event => {
                    emailInput.addEventListener(event, function() {
                        const errors = this.parentNode.querySelectorAll('.client-error');
                        errors.forEach(err => err.remove());
                    });
                });
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        }
    });
</script>
</body>

</html>