<?php
session_start();
include 'includes/header.php';

// Get error message if exists
$error_message = isset($_SESSION['payment_error']) ? $_SESSION['payment_error'] : 'An unknown error occurred during payment processing.';

// Clear error from session
unset($_SESSION['payment_error']);
?>

<div class="container">
    <div class="payment-error">
        <div class="error-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
        </div>
        <h1>Payment Failed</h1>
        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <p>We were unable to process your payment. Please try again.</p>
        
        <div class="error-actions">
            <a href="checkout.php" class="btn btn-primary">Try Again</a>
            <a href="cart.php" class="btn btn-secondary">Return to Cart</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
	/* Payment Error Page Styles */
.container {
  max-width: 80rem;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.payment-error {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 3rem 2rem;
  text-align: center;
  max-width: 36rem;
  margin: 2rem auto;
}

.error-icon {
  color: var(--red-500);
  margin-bottom: 1.5rem;
}

.payment-error h1 {
  font-size: 1.875rem;
  font-weight: bold;
  color: var(--gray-800);
  margin-bottom: 1rem;
}

.error-message {
  background-color: var(--gray-100);
  padding: 1rem;
  border-radius: 0.375rem;
  margin-bottom: 1.5rem;
  color: var(--red-500);
  font-weight: 600;
}

.payment-error p {
  color: var(--gray-600);
  margin-bottom: 1.5rem;
}

.error-actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 2rem;
}

@media (min-width: 640px) {
  .error-actions {
    flex-direction: row;
    justify-content: center;
  }
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.3s ease-in-out;
}

.btn-primary {
  background-color: var(--primary);
  color: white;
}

.btn-primary:hover {
  background-color: var(--primary-hover);
}

.btn-secondary {
  background-color: var(--gray-600);
  color: white;
}

.btn-secondary:hover {
  background-color: var(--gray-800);
}

.btn-outline {
  border: 1px solid var(--gray-600);
  color: var(--gray-600);
  background-color: transparent;
}

.btn-outline:hover {
  background-color: var(--gray-100);
}
</style>