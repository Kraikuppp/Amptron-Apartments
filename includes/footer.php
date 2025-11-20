<footer class="bg-dark text-white py-5 mt-5">
    <div class="container">
        <div class="row">
            <!-- Company Info -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="mb-3 fw-bold text-white">
                    <i class="bi bi-building me-2"></i><?php echo SITE_NAME; ?>
                </h5>
                <p class="text-white-50 small mb-3">Platform for finding rental rooms and purchasing electrical meters and CT Meter equipment</p>
                <div class="text-white small">
                    <p class="mb-2">
                        <i class="bi bi-geo-alt-fill me-2" style="color: var(--sky-blue);"></i>
                        <strong>Address:</strong><br>
                        <span class="ms-4">7-9-11-13 Petchkasem 77<br>
                        <span class="ms-4">Nongkangplu, Nongkhaem<br>
                        <span class="ms-4">Bangkok 10160, Thailand</span>
                    </p>
                    <p class="mb-2">
                        <i class="bi bi-telephone-fill me-2" style="color: var(--sky-blue);"></i>
                        <strong>Phone:</strong> 086 341 2503
                    </p>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="mb-3 fw-bold text-white">About Us</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="about.php" class="text-white text-decoration-none hover-link">
                            <i class="bi bi-chevron-right me-1"></i>About Us
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="blog.php" class="text-white text-decoration-none hover-link">
                            <i class="bi bi-chevron-right me-1"></i>Blog
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="register-property.php" class="text-white text-decoration-none hover-link">
                            <i class="bi bi-chevron-right me-1"></i>Register Property with Renthub
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="contact.php" class="text-white text-decoration-none hover-link">
                            <i class="bi bi-chevron-right me-1"></i>Contact
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Legal Links -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="mb-3 fw-bold text-white">Terms & Policies</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="terms.php" class="text-white text-decoration-none hover-link">
                            <i class="bi bi-chevron-right me-1"></i>Listing Agreement
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="terms-of-service.php" class="text-white text-decoration-none hover-link">
                            <i class="bi bi-chevron-right me-1"></i>Terms of Service
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="privacy-policy.php" class="text-white text-decoration-none hover-link">
                            <i class="bi bi-chevron-right me-1"></i>Privacy Policy
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Social Media & Products -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="mb-3 fw-bold text-white">Follow Us</h5>
                <div class="d-flex gap-3 mb-4">
                    <a href="#" class="text-white social-icon" title="Facebook">
                        <i class="bi bi-facebook fs-4"></i>
                    </a>
                    <a href="#" class="text-white social-icon" title="Line">
                        <i class="bi bi-line fs-4"></i>
                    </a>
                    <a href="#" class="text-white social-icon" title="Instagram">
                        <i class="bi bi-instagram fs-4"></i>
                    </a>
                    <a href="#" class="text-white social-icon" title="Twitter">
                        <i class="bi bi-twitter fs-4"></i>
                    </a>
                    <a href="#" class="text-white social-icon" title="YouTube">
                        <i class="bi bi-youtube fs-4"></i>
                    </a>
                </div>
                <h6 class="mb-2 text-white">Our Services</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <a href="rooms.php" class="text-white text-decoration-none hover-link">
                            <i class="bi bi-house-door me-1"></i>Find Rentals
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="products.php" class="text-white text-decoration-none hover-link">
                            <i class="bi bi-lightning-charge me-1"></i>Electric Meters & Equipment
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">

        <!-- Copyright -->
        <div class="row">
            <div class="col-12 text-center">
                <p class="text-white mb-0 small">
                    Copyright © <?php echo date(
                        "Y",
                    ); ?> Amptron Instruments Thailand Co., Ltd - All rights reserved.
                </p>
            </div>
        </div>
    </div>
</footer>

<style>
.hover-link {
    transition: all 0.3s ease;
    opacity: 0.9;
}

.hover-link:hover {
    color: var(--sky-blue) !important;
    transform: translateX(5px);
    opacity: 1;
}

.social-icon {
    transition: all 0.3s ease;
    display: inline-block;
    opacity: 0.9;
}

.social-icon:hover {
    color: var(--sky-blue) !important;
    transform: translateY(-3px);
    opacity: 1;
}

footer a {
    font-size: 0.9rem;
}

footer h5 {
    position: relative;
    padding-bottom: 10px;
}

footer h5::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 50px;
    height: 2px;
    background: var(--sky-blue);
}

footer .text-white {
    color: #ffffff !important;
}

footer .text-white-50 {
    color: rgba(255, 255, 255, 0.7) !important;
}
</style>
