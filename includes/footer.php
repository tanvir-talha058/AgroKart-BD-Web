<footer class="footer-enhanced">
    <div class="footer-background">
        <div class="footer-pattern"></div>
    </div>

    <div class="footer-content">
        <div class="footer-section footer-brand">
            <div class="footer-logo-wrapper">
                <div class="footer-brand-text">
                    <h3 class="footer-brand-title">AgroKart BD</h3>
                    <p class="footer-brand-subtitle">Fresh From Farm</p>
                </div>
            </div>
            <p class="footer-description">Connecting farmers directly to consumers for the freshest produce experience. Quality guaranteed, prices you'll love.</p>
            <div class="footer-social">
                <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>

        <div class="footer-section footer-links">
            <h4 class="footer-section-title">Quick Links</h4>
            <ul class="footer-links-list">
                <li><a href="index.php">Home</a></li>
                <li><a href="#products">Products</a></li>
                <li><a href="#about">About Us</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="my_orders.php">My Orders</a></li>
            </ul>
        </div>

        <div class="footer-section footer-company">
            <h4 class="footer-section-title">Company</h4>
            <ul class="footer-links-list">
                <li><a href="#">About Us</a></li>
                <li><a href="#">Our Mission</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
                <li><a href="#">Careers</a></li>
            </ul>
        </div>

        <div class="footer-section footer-contact">
            <h4 class="footer-section-title">Contact Us</h4>
            <div class="contact-info">
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span style="color: black;">Dhaka, Bangladesh</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span style="color: black;">+880 1234-567890</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span style="color: black;">info@agrokartbd.com</span>
                </div>
            </div>
            <form class="newsletter-form">
                <h5>Newsletter</h5>
                <p>Stay updated with fresh offers!</p>
                <div class="newsletter-input-group">
                    <input type="email" placeholder="Your email address" required>
                    <button type="submit">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="footer-bottom-content">
            <p class="copyright">&copy; <?php echo date("Y"); ?> AgroKart BD. All rights reserved.</p>
            <div class="footer-bottom-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Cookie Policy</a>
            </div>
        </div>
    </div>
</footer>

<style>
 /* Enhanced Footer Styles */
.footer-enhanced {
    position: relative;
    background: linear-gradient(135deg, #1a472a 0%, #2c5f2d 50%, #4CAF50 100%);
    color: white;
    overflow: hidden;
}

.footer-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.footer-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image:
        radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
}

.footer-content {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1.5fr;
    gap: 40px;
    max-width: 1400px;
    margin: 0 auto;
    padding: 80px 20px 40px;
}

/* Brand Section */
.footer-brand {
    max-width: 400px;
}

.footer-logo-wrapper {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}



.footer-brand-title {
    font-size: 1.8rem;
    font-weight: 800;
    margin: 0;
    background: linear-gradient(135deg, #ffffff, #e8f5e8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.footer-brand-subtitle {
    font-size: 0.9rem;
    color: #8BC34A;
    margin: 0;
    font-weight: 600;
}

.footer-description {
    color: #e8f5e8;
    line-height: 1.6;
    margin-bottom: 30px;
    font-size: 0.95rem;
}

.footer-social {
    display: flex;
    gap: 15px;
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 45px;
    height: 45px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.social-link:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-3px);
    color: #4CAF50;
}

/* Section Titles */
.footer-section-title {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 25px;
    color: white;
    position: relative;
}

.footer-section-title::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 30px;
    height: 3px;
    background: linear-gradient(135deg, #4CAF50, #8BC34A);
    border-radius: 2px;
}

/* Links Lists */
.footer-links-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links-list li {
    margin-bottom: 12px;
}

.footer-links-list a {
    color: #e8f5e8;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 0.95rem;
    position: relative;
    padding-left: 0;
}

.footer-links-list a::before {
    content: '→';
    position: absolute;
    left: -15px;
    opacity: 0;
    transition: all 0.3s ease;
    color: #4CAF50;
}

.footer-links-list a:hover {
    color: #4CAF50;
    padding-left: 15px;
}

.footer-links-list a:hover::before {
    opacity: 1;
}

/* Contact Section */
.contact-info {
    margin-bottom: 30px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
    color: #e8f5e8;
    font-size: 0.95rem;
}

.contact-item i {
    color: #4CAF50;
    width: 16px;
    text-align: center;
}

/* Newsletter Form */
.newsletter-form h5 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 8px;
    color: white;
}

.newsletter-form p {
    color: #e8f5e8;
    font-size: 0.9rem;
    margin-bottom: 20px;
}

.newsletter-input-group {
    display: flex;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50px; /* Increased for a more rounded look */
    overflow: hidden;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.newsletter-input-group input {
    flex: 1;
    background: transparent; /* Changed to transparent for a better glass effect */
    border: none;
    padding: 12px 20px;
    color: white !important; /* Changed to white */
    font-size: 0.9rem;
    outline: none;
}

.newsletter-input-group input::placeholder {
    color: #e8f5e8 !important; /* Lightened placeholder color */
}

/* === FIX STARTS HERE === */
/* This rule overrides browser autofill styles that add unwanted backgrounds. */
.newsletter-input-group input:-webkit-autofill,
.newsletter-input-group input:-webkit-autofill:hover,
.newsletter-input-group input:-webkit-autofill:focus,
.newsletter-input-group input:-webkit-autofill:active {
    background-image: none !important;
    -webkit-box-shadow: 0 0 0 30px #3a7a44 inset !important; 
    -webkit-text-fill-color: white !important;
}
/* === FIX ENDS HERE === */

.newsletter-input-group button {
    background: linear-gradient(135deg, #4CAF50, #8BC34A);
    border: none;
    color: white;
    padding: 12px 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.newsletter-input-group button:hover {
    background: linear-gradient(135deg, #45a049, #7cb342);
    transform: scale(1.05);
}

/* Footer Bottom */
.footer-bottom {
    position: relative;
    z-index: 2;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
}

.footer-bottom-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.copyright {
    color: #e8f5e8;
    font-size: 0.9rem;
    margin: 0;
}

.footer-bottom-links {
    display: flex;
    gap: 20px;
}

.footer-bottom-links a {
    color: #e8f5e8;
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.footer-bottom-links a:hover {
    color: #4CAF50;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .footer-content {
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }

    .footer-brand {
        grid-column: 1 / -1;
        max-width: none;
    }
}

@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
        gap: 30px;
        padding: 60px 20px 30px;
    }

    .footer-bottom-content {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }

    .footer-bottom-links {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .footer-content {
        padding: 50px 15px 25px;
    }

    .footer-logo-wrapper {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }

    .footer-social {
        justify-content: center;
    }
}
</style>

<!-- Enhanced Chatbot Styles -->
<link rel="stylesheet" href="css/chatbot_enhanced.css">

</body>

</html>