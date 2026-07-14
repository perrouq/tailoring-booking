
<style>
    /* Footer Styles */
    .site-footer {
        background: linear-gradient(135deg, var(--primary-dark), var(--primary));
        color: var(--text-light);
        padding: 3rem 0 0;
        margin-top: 3rem;
        position: relative;
    }

    .footer-container {
        max-width: var(--container-width);
        margin: 0 auto;
        padding: 0 1rem;
    }

    .footer-content {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .footer-column {
        display: flex;
        flex-direction: column;
    }

    .footer-logo {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .footer-logo i {
        font-size: 2rem;
        color: var(--secondary);
    }

    .footer-logo h3 {
        font-size: 1.25rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0;
    }

    .footer-description {
        margin-bottom: 1.5rem;
        line-height: 1.6;
        opacity: 0.9;
    }

    .social-icons {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .social-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.1);
        color: var(--text-light);
        transition: var(--transition);
    }

    .social-icon:hover {
        background-color: var(--secondary);
        transform: translateY(-3px);
    }

    .footer-column h4 {
        color: var(--text-light);
        font-size: 1.1rem;
        margin-bottom: 1.25rem;
        position: relative;
        padding-bottom: 0.75rem;
    }

    .footer-column h4::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 40px;
        height: 2px;
        background-color: var(--secondary);
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 0.75rem;
    }

    .footer-links a {
        color: var(--text-light);
        opacity: 0.8;
        text-decoration: none;
        transition: var(--transition);
        display: block;
        padding: 0.25rem 0;
    }

    .footer-links a:hover {
        opacity: 1;
        color: var(--secondary);
        padding-left: 5px;
    }

    .contact-info {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .contact-info li {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .contact-info i {
        color: var(--secondary);
        font-size: 1rem;
        margin-top: 0.25rem;
    }

    .newsletter-section {
        padding: 2rem 0;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .newsletter-section h4 {
        margin-bottom: 0.5rem;
        font-size: 1.2rem;
    }

    .newsletter-section p {
        margin-bottom: 1.5rem;
        opacity: 0.9;
    }

    .newsletter-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        max-width: 500px;
        margin: 0 auto;
    }

    .newsletter-form input {
        padding: 0.75rem 1rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 4px;
        background-color: rgba(255, 255, 255, 0.1);
        color: var(--text-light);
        width: 100%;
    }

    .newsletter-form input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .newsletter-form button {
        padding: 0.75rem 1.5rem;
        background-color: var(--secondary);
        color: var(--text-dark);
        border: none;
        border-radius: 4px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
    }

    .newsletter-form button:hover {
        background-color: var(--secondary-light);
    }

    .footer-bottom {
        padding: 1.5rem 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .copyright p {
        margin: 0;
        opacity: 0.8;
        font-size: 0.9rem;
    }

    .payment-methods {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .payment-methods span {
        font-size: 0.9rem;
        opacity: 0.8;
    }

    .payment-methods i {
        font-size: 1.5rem;
        opacity: 0.8;
        transition: var(--transition);
    }

    .payment-methods i:hover {
        opacity: 1;
        color: var(--secondary);
    }

    /* Responsive Styles */
    @media (min-width: 640px) {
        .newsletter-form {
            flex-direction: row;
        }

        .newsletter-form button {
            width: auto;
        }
    }

    @media (min-width: 768px) {
        .footer-content {
            grid-template-columns: repeat(2, 1fr);
        }

        .brand-column {
            grid-column: span 2;
        }

        .footer-bottom {
            flex-direction: row;
            justify-content: space-between;
        }
    }

    @media (min-width: 1024px) {
        .footer-content {
            grid-template-columns: 2fr repeat(4, 1fr);
        }

        .brand-column {
            grid-column: span 1;
        }
    }

    @media (max-width: 767px) {
        .site-footer {
            padding-top: 2rem;
        }

        .footer-column h4 {
            margin-bottom: 1rem;
        }
        
        .payment-methods {
            flex-wrap: wrap;
            justify-content: center;
        }
    }
</style>


<!-- Footer Section -->
<footer class="site-footer">
    <div class="footer-container">
        <!-- Top Section with Multiple Columns -->
        <div class="footer-content">
            <!-- Brand Column -->
            <div class="footer-column brand-column">
                <div class="footer-logo">
                    <i class="fas fa-cut"></i>
                    <h3>Tailor Booking System</h3>
                </div>
                <p class="footer-description">
                    Your one-stop destination for premium tailoring services. Book appointments, browse styles, and get custom-fitted clothing.
                </p>
                <div class="social-icons">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-pinterest-p"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#about.php">About Us</a></li>
                    <li><a href="#services.php">Services</a></li>
                    <li><a href="#contact.php">Contact</a></li>
                </ul>
            </div>

            <!-- Categories -->
            <div class="footer-column">
                <h4>Clothes Typeses</h4>
                <ul class="footer-links">
                    <li><a href="clothes_types.php?category=SHADDAS">Shaddah's</a></li>
                    <li><a href="clothes_types.php?category=KAFTANIS">Kaftani's</a></li>
                    <li><a href="clothes_types.php?category=YADIS">Yadi's</a></li>
                    <li><a href="clothes_types.php">All Styles</a></li>
                </ul>
            </div>

            <!-- Customer Support -->
            <div class="footer-column">
                <h4>Customer Support</h4>
                <ul class="footer-links">
                    <li><a href="#faq.php">FAQ</a></li>
                    <li><a href="#shipping.php">Shipping Info</a></li>
                    <li><a href="#returns.php">Returns Policy</a></li>
                    <li><a href="#privacy.php">Privacy Policy</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-column">
                <h4>Contact Us</h4>
                <ul class="contact-info">
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>123 Fashion Street, Style City</span>
                    </li>
                    <li>
                        <i class="fas fa-phone"></i>
                        <span>+1 (234) 567-8900</span>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <span>info@tailorbooking.com</span>
                    </li>
                    <li>
                        <i class="fas fa-clock"></i>
                        <span>Mon-Fri: 9am-6pm</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Newsletter Section -->
        <div class="newsletter-section">
            <h4>Subscribe to Our Newsletter</h4>
            <p>Stay updated with our latest styles and promotions</p>
            <form class="newsletter-form">
                <input type="email" placeholder="Your Email Address" required>
                <button type="submit">Subscribe</button>
            </form>
        </div>

        <!-- Bottom Copyright Section -->
        <div class="footer-bottom">
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Tailor Booking System. All Rights Reserved.</p>
            </div>
            <div class="payment-methods">
                <span>Payment Methods:</span>
                <i class="fab fa-cc-visa"></i>
                <i class="fab fa-cc-mastercard"></i>
                <i class="fab fa-cc-paypal"></i>
                <i class="fab fa-cc-apple-pay"></i>
            </div>
        </div>
    </div>
</footer>
</body>
</html>