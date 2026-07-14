<?php include 'includes/header.php'; ?>

<main>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-header">
          <div class="logo-container">
            <i class="fas fa-cut logo-icon"></i>
          </div>
          <h2>Welcome to Tailor Booking System</h2>
          <p>Your personal tailoring service platform</p>
        </div>

        <div class="tabs">
          <div class="tab <?php echo (!isset($_GET['register'])) ? 'active' : ''; ?>" onclick="switchTab('login')">
            <i class="fas fa-sign-in-alt"></i> Login
          </div>
          <div class="tab <?php echo (isset($_GET['register'])) ? 'active' : ''; ?>" onclick="switchTab('register')">
            <i class="fas fa-user-plus"></i> Register
          </div>
        </div>
      
      <?PHP  if (isset($_SESSION['flash_message'])) {
    echo '<div class="alert ' . $_SESSION['flash_type'] . '">';
    echo '<i class="fas fa-exclamation-circle"></i> ' . $_SESSION['flash_message'];
    echo '</div>';
    
    // Clear the flash message after displaying
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}
?>
      
        <div id="login-tab" class="tab-content <?php echo (!isset($_GET['register'])) ? 'active' : ''; ?>">
          <form action="includes/process_login.php" method="post">
            <div class="form-group">
              <label for="login-email">
                <i class="fas fa-envelope"></i> Email Address
              </label>
              <input type="email" id="login-email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
              <label for="login-password">
                <i class="fas fa-lock"></i> Password
              </label>
              <div class="password-input-container">
                <input type="password" id="login-password" name="password" placeholder="Enter your password" required>
                <i class="fas fa-eye toggle-password" data-target="login-password"></i>
              </div>
            </div>
            <div class="form-group remember-me">
              <div class="checkbox-container">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
              </div>
              <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
            </div>
            <div class="form-group">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Login
              </button>
            </div>
          </form>
          
          <div class="social-login">
            <p>Or login with</p>
            <div class="social-buttons">
              <button class="social-btn google">
                <i class="fab fa-google"></i>
              </button>
              <button class="social-btn facebook">
                <i class="fab fa-facebook-f"></i>
              </button>
              <button class="social-btn twitter">
                <i class="fab fa-twitter"></i>
              </button>
            </div>
          </div>
        </div>
      
        <div id="register-tab" class="tab-content <?php echo (isset($_GET['register'])) ? 'active' : ''; ?>">
          <form action="includes/process_register.php" method="post" id="register-form">
            <div class="form-row">
              <div class="form-group">
                <label for="first-name">
                  <i class="fas fa-user"></i> First Name
                </label>
                <input type="text" id="first-name" name="first_name" placeholder="Enter first name" required>
              </div>
              <div class="form-group">
                <label for="last-name">
                  <i class="fas fa-user"></i> Last Name
                </label>
                <input type="text" id="last-name" name="last_name" placeholder="Enter last name" required>
              </div>
            </div>
            <div class="form-group">
              <label for="email">
                <i class="fas fa-envelope"></i> Email Address
              </label>
              <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
              <label for="phone">
                <i class="fas fa-phone"></i> Phone Number
              </label>
              <input type="tel" id="phone" name="phone" placeholder="Enter phone number" required>
            </div>
            <div class="form-group">
              <label for="password">
                <i class="fas fa-lock"></i> Password
              </label>
              <div class="password-input-container">
                <input type="password" id="password" name="password" placeholder="Create password (min. 8 characters)" required minlength="8">
                <i class="fas fa-eye toggle-password" data-target="password"></i>
              </div>
              <div class="password-strength">
                <div class="strength-bar">
                  <div class="strength-progress" id="password-strength"></div>
                </div>
                <span class="strength-text" id="strength-text">Password strength</span>
              </div>
            </div>
            <div class="form-group">
              <label for="confirm-password">
                <i class="fas fa-lock"></i> Confirm Password
              </label>
              <div class="password-input-container">
                <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm your password" required minlength="8">
                <i class="fas fa-eye toggle-password" data-target="confirm-password"></i>
              </div>
            </div>
            <div class="form-group terms">
              <div class="checkbox-container">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a></label>
              </div>
            </div>
            <div class="form-group">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Create Account
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include 'includes/footer.php'; ?>



<script>
  function switchTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
      content.classList.remove('active');
    });
    
    // Deactivate all tabs
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
      tab.classList.remove('active');
    });
    
    // Activate the selected tab and content
    document.getElementById(`${tabName}-tab`).classList.add('active');
    document.querySelector(`.tab:nth-child(${tabName === 'login' ? '1' : '2'})`).classList.add('active');
    
    // Update URL without refreshing the page
    const url = new URL(window.location);
    if (tabName === 'register') {
      url.searchParams.set('register', '1');
    } else {
      url.searchParams.delete('register');
    }
    window.history.pushState({}, '', url);
  }
  
  document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(button => {
      button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const passwordInput = document.getElementById(targetId);
        
        if (passwordInput.type === 'password') {
          passwordInput.type = 'text';
          this.classList.remove('fa-eye');
          this.classList.add('fa-eye-slash');
        } else {
          passwordInput.type = 'password';
          this.classList.remove('fa-eye-slash');
          this.classList.add('fa-eye');
        }
      });
    });
    
    // Form validation for registration
    const registerForm = document.querySelector('#register-form');
    if (registerForm) {
      // Password strength checker
      const passwordInput = document.getElementById('password');
      const strengthBar = document.getElementById('password-strength');
      const strengthText = document.getElementById('strength-text');
      
      passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        let message = '';
        
        if (password.length >= 8) strength += 25;
        if (password.match(/[A-Z]/)) strength += 25;
        if (password.match(/[0-9]/)) strength += 25;
        if (password.match(/[^A-Za-z0-9]/)) strength += 25;
        
        strengthBar.style.width = strength + '%';
        
        if (strength < 25) {
          strengthBar.style.backgroundColor = '#ff4d4d';
          message = 'Very Weak';
        } else if (strength < 50) {
          strengthBar.style.backgroundColor = '#ffaa00';
          message = 'Weak';
        } else if (strength < 75) {
          strengthBar.style.backgroundColor = '#ffcc00';
          message = 'Medium';
        } else if (strength < 100) {
          strengthBar.style.backgroundColor = '#99cc00';
          message = 'Strong';
        } else {
          strengthBar.style.backgroundColor = '#22cc22';
          message = 'Very Strong';
        }
        
        strengthText.textContent = password.length > 0 ? message : 'Password strength';
      });
      
      // Confirm password validation
      registerForm.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        
        if (password !== confirmPassword) {
          e.preventDefault();
          
          // Create custom alert instead of using native alert
          const alertDiv = document.createElement('div');
          alertDiv.className = 'alert error';
          alertDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Passwords do not match!';
          
          // Insert after the confirm password field
          const confirmPasswordGroup = document.getElementById('confirm-password').closest('.form-group');
          confirmPasswordGroup.parentNode.insertBefore(alertDiv, confirmPasswordGroup.nextSibling);
          
          // Remove alert after 3 seconds
          setTimeout(() => {
            alertDiv.remove();
          }, 3000);
        }
      });
    }
  });
</script>