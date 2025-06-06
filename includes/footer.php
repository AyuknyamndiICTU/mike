<?php
// Close any open database connections or perform any cleanup if needed
?>
<!-- Footer -->
<footer class="footer">
    <div class="container footer-content">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="footer-brand">
                    <h3>Event Booking System</h3>
                    <p>Find and book the best events in your area. Your one-stop destination for memorable experiences.</p>
                </div>
                <div class="footer-social">
                    <h4>Follow Us</h4>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="about.php"><i class="bi bi-chevron-right"></i>About Us</a></li>
                        <li><a href="contact.php"><i class="bi bi-chevron-right"></i>Contact</a></li>
                        <li><a href="terms.php"><i class="bi bi-chevron-right"></i>Terms & Conditions</a></li>
                        <li><a href="privacy.php"><i class="bi bi-chevron-right"></i>Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="footer-links">
                    <h4>Event Categories</h4>
                    <ul>
                        <li><a href="events.php?category=Music"><i class="bi bi-chevron-right"></i>Music</a></li>
                        <li><a href="events.php?category=Theater"><i class="bi bi-chevron-right"></i>Theater</a></li>
                        <li><a href="events.php?category=Business"><i class="bi bi-chevron-right"></i>Business</a></li>
                        <li><a href="events.php?category=Sports"><i class="bi bi-chevron-right"></i>Sports</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="footer-links">
                    <h4>Contact Info</h4>
                    <ul>
                        <li><a href="tel:+1234567890"><i class="bi bi-telephone"></i>+237 653 59 14 60</a></li>
                        <li><a href="mailto:info@eventbooking.com"><i class="bi bi-envelope"></i>info@eventbooking.com</a></li>
                        <li><a href="#"><i class="bi bi-geo-alt"></i>Tradex Eleveur, Yaounde, Cameroon</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Event Booking System. All rights reserved.</p>
        </div>
    </div>
</footer>

<style>
/* Enhanced Footer Styles */
.footer {
    background: linear-gradient(135deg, #f8f9fc, #ffffff);
    padding: 100px 0 40px;
    position: relative;
    overflow: hidden;
    margin-top: 80px;
}

.footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(to right, var(--primary-color), #224abe, var(--primary-color));
}

.footer-content {
    position: relative;
    z-index: 1;
}

.footer-brand {
    margin-bottom: 30px;
}

.footer-brand h3 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 15px;
    position: relative;
    display: inline-block;
}

.footer-brand h3::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 50px;
    height: 3px;
    background: var(--primary-color);
    border-radius: 2px;
}

.footer-brand p {
    font-size: 1.1rem;
    color: #6c757d;
    line-height: 1.6;
}

.footer-links h4 {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 25px;
    position: relative;
    padding-bottom: 15px;
    color: #333;
}

.footer-links h4::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 40px;
    height: 3px;
    background: var(--primary-color);
    border-radius: 2px;
}

.footer-links ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links ul li {
    margin-bottom: 15px;
}

.footer-links ul li a {
    color: #6c757d;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.footer-links ul li a:hover {
    color: var(--primary-color);
    transform: translateX(8px);
}

.footer-links ul li a i {
    font-size: 0.8rem;
    transition: all 0.3s ease;
}

.footer-social {
    margin-top: 30px;
}

.footer-social h4 {
    margin-bottom: 20px;
    font-size: 1.3rem;
    font-weight: 600;
    color: #333;
}

.social-links {
    display: flex;
    gap: 15px;
}

.social-links a {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: white;
    color: var(--primary-color);
    font-size: 1.2rem;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.social-links a:hover {
    transform: translateY(-5px) rotate(8deg);
    background: var(--primary-color);
    color: white;
    box-shadow: 0 8px 20px rgba(var(--primary-color-rgb), 0.2);
}

.footer-bottom {
    margin-top: 60px;
    padding-top: 30px;
    border-top: 1px solid #eef0f5;
    text-align: center;
}

.footer-bottom p {
    color: #6c757d;
    margin: 0;
}

@media (max-width: 768px) {
    .footer {
        padding: 60px 0 30px;
    }

    .footer-brand h3 {
        font-size: 1.75rem;
    }

    .footer-links h4 {
        margin-top: 30px;
    }

    .social-links {
        justify-content: center;
    }
}
</style>

<!-- Bootstrap JS and other script includes -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Any additional scripts -->
</body>
</html> 