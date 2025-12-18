/**
 * Wolvebite Community - Main JavaScript
 * Client-side validation and UI interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // ========================================
    // Mobile Navigation Toggle
    // ========================================
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            const icon = navToggle.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
                const icon = navToggle.querySelector('i');
                icon.classList.add('fa-bars');
                icon.classList.remove('fa-times');
            }
        });
    }
    
    // ========================================
    // Form Validation
    // ========================================
    
    /** Validate email format */
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    /** Validate phone number (Indonesian format) */
    function isValidPhone(phone) {
        const phoneRegex = /^(08|\+62|62)[0-9]{8,12}$/;
        return phoneRegex.test(phone.replace(/[\s-]/g, ''));
    }
    
    /** Show error message for a field */
    function showError(input, message) {
        const formGroup = input.closest('.form-group');
        input.classList.add('error');
        
        let errorEl = formGroup.querySelector('.form-error');
        if (!errorEl) {
            errorEl = document.createElement('span');
            errorEl.className = 'form-error';
            formGroup.appendChild(errorEl);
        }
        errorEl.textContent = message;
    }
    
    /** Clear error message for a field */
    function clearError(input) {
        const formGroup = input.closest('.form-group');
        input.classList.remove('error');
        
        const errorEl = formGroup.querySelector('.form-error');
        if (errorEl) {
            errorEl.remove();
        }
    }
    
    /**Validate a single field */
    function validateField(input) {
        const value = input.value.trim();
        const type = input.type;
        const name = input.name;
        const required = input.hasAttribute('required');
        const minLength = input.getAttribute('minlength');
        const maxLength = input.getAttribute('maxlength');
        
        clearError(input);
        
        // Required check
        if (required && !value) {
            showError(input, 'Field ini wajib diisi');
            return false;
        }
        
        // Email validation
        if (type === 'email' && value && !isValidEmail(value)) {
            showError(input, 'Format email tidak valid');
            return false;
        }
        
        // Phone validation
        if (name === 'phone' && value && !isValidPhone(value)) {
            showError(input, 'Format nomor telepon tidak valid');
            return false;
        }
        
        // Minimum length
        if (minLength && value.length < parseInt(minLength)) {
            showError(input, `Minimal ${minLength} karakter`);
            return false;
        }
        
        // Maximum length
        if (maxLength && value.length > parseInt(maxLength)) {
            showError(input, `Maksimal ${maxLength} karakter`);
            return false;
        }
        
        // Password confirmation
        if (name === 'confirm_password') {
            const password = document.querySelector('input[name="password"]');
            if (password && value !== password.value) {
                showError(input, 'Konfirmasi password tidak cocok');
                return false;
            }
        }
        
        return true;
    }
    
    // Login Form Validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            const email = loginForm.querySelector('input[name="email"]');
            const password = loginForm.querySelector('input[name="password"]');
            
            if (!validateField(email)) isValid = false;
            if (!validateField(password)) isValid = false;
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        loginForm.querySelectorAll('input').forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    }
    
    // ========================================
    // Register Form Validation
    // ========================================
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            const inputs = registerForm.querySelectorAll('input[required], input[name="phone"]');
            inputs.forEach(input => {
                if (!validateField(input)) isValid = false;
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        registerForm.querySelectorAll('input').forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    }
    
    // ========================================
    // Booking Form Validation
    // ========================================
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        const bookingDate = bookingForm.querySelector('input[name="booking_date"]');
        const startTime = bookingForm.querySelector('select[name="start_time"]');
        const endTime = bookingForm.querySelector('select[name="end_time"]');
        
        // Set minimum date to today
        if (bookingDate) {
            const today = new Date().toISOString().split('T')[0];
            bookingDate.setAttribute('min', today);
        }
        
        // Validate time range
        if (endTime && startTime) {
            endTime.addEventListener('change', function() {
                if (startTime.value && endTime.value) {
                    if (endTime.value <= startTime.value) {
                        showError(endTime, 'Waktu selesai harus lebih dari waktu mulai');
                    } else {
                        clearError(endTime);
                    }
                }
            });
        }
        
        bookingForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            if (!bookingDate.value) {
                showError(bookingDate, 'Pilih tanggal booking');
                isValid = false;
            }
            
            if (!startTime.value) {
                showError(startTime, 'Pilih waktu mulai');
                isValid = false;
            }
            
            if (!endTime.value) {
                showError(endTime, 'Pilih waktu selesai');
                isValid = false;
            } else if (startTime.value && endTime.value <= startTime.value) {
                showError(endTime, 'Waktu selesai harus lebih dari waktu mulai');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // ========================================
    // Checkout Form Validation
    // ========================================
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            const inputs = checkoutForm.querySelectorAll('input[required], textarea[required]');
            inputs.forEach(input => {
                if (!validateField(input)) isValid = false;
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // ========================================
    // File Upload Validation
    // ========================================
    const uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        const fileInput = uploadForm.querySelector('input[type="file"]');
        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                
                if (file) {
                    // Check file size
                    if (file.size > maxSize) {
                        showError(fileInput, 'Ukuran file maksimal 5MB');
                        fileInput.value = '';
                        return;
                    }
                    
                    // Check file type
                    if (!allowedTypes.includes(file.type)) {
                        showError(fileInput, 'Format file tidak diizinkan. Gunakan PDF, JPG, atau PNG');
                        fileInput.value = '';
                        return;
                    }
                    
                    clearError(fileInput);
                }
            });
        }
    }
    
    // ========================================
    // Product Form Validation (Admin)
    // ========================================
    const productForm = document.getElementById('productForm');
    if (productForm) {
        const priceInput = productForm.querySelector('input[name="price"]');
        const stockInput = productForm.querySelector('input[name="stock"]');
        
        // Only allow positive numbers for price
        if (priceInput) {
            priceInput.addEventListener('input', function() {
                if (this.value < 0) this.value = 0;
            });
        }
        
        // Only allow positive integers for stock
        if (stockInput) {
            stockInput.addEventListener('input', function() {
                this.value = Math.floor(Math.max(0, this.value));
            });
        }
        
        productForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            const inputs = productForm.querySelectorAll('input[required], textarea[required]');
            inputs.forEach(input => {
                if (!validateField(input)) isValid = false;
            });
            
            if (priceInput && parseFloat(priceInput.value) <= 0) {
                showError(priceInput, 'Harga harus lebih dari 0');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // ========================================
    // Modal Functionality
    // ========================================
    const modalTriggers = document.querySelectorAll('[data-modal]');
    const modalCloseButtons = document.querySelectorAll('.modal-close, [data-modal-close]');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal-overlay');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
    
    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                modal.classList.remove('active');
            });
            document.body.style.overflow = '';
        }
    });
    
    // ========================================
    // Confirm Delete
    // ========================================
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Apakah Anda yakin ingin menghapus item ini?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // ========================================
    // Auto-hide Alerts
    // ========================================
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
    
    // ========================================
    // Smooth Scroll for Anchor Links
    // ========================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // ========================================
    // Format Currency Input
    // ========================================
    const currencyInputs = document.querySelectorAll('.currency-input');
    currencyInputs.forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            this.value = value;
        });
    });
    
    // ========================================
    // Image Preview
    // ========================================
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            const preview = document.getElementById(this.getAttribute('data-preview'));
            if (preview && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
    
    // ========================================
    // Quantity Controls
    // ========================================
    document.querySelectorAll('.qty-btn').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.qty-input');
            const currentValue = parseInt(input.value) || 1;
            const action = this.getAttribute('data-action');
            
            if (action === 'decrease' && currentValue > 1) {
                input.value = currentValue - 1;
            } else if (action === 'increase') {
                const max = parseInt(input.getAttribute('max')) || 999;
                if (currentValue < max) {
                    input.value = currentValue + 1;
                }
            }
            
            // Trigger change event for form submission
            input.dispatchEvent(new Event('change'));
        });
    });
    
    console.log('🐺 Wolvebite Community - JavaScript Loaded');
});
