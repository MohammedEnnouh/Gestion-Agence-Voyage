</main>

    <!-- Footer -->
    <style>

     footer.bg-dark {
       background: linear-gradient(180deg,
     }
     .footer-widget h5 { letter-spacing: .5px; }
     .footer-widget a { color:
     .footer-widget a:hover { color:
     .social-links a {
       display: inline-flex; width: 36px; height: 36px;
       align-items: center; justify-content: center;
       border: 1px solid rgba(255,255,255,.15);
       border-radius: 50%;
       background: rgba(255,255,255,0.05);
       transition: all .2s ease;
     }
     .social-links a:hover { background:
     .payment-methods img { filter: grayscale(100%); opacity: .8; transition: all .2s ease; }
     .payment-methods img:hover { filter: none; opacity: 1; transform: translateY(-1px); }

     .whatsapp-float { position: fixed; right: 18px; bottom: 82px; background:
     .whatsapp-float:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(37,211,102,.45); color:
     footer .contact-info i { color:
     hr.bg-secondary { opacity: .25; }
    </style>
    <footer class="bg-dark text-white pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row g-4">
                <!-- About Column -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <h5 class="text-uppercase mb-4">About Vogie</h5>
                        <p>Your trusted travel partner for unforgettable experiences in Morocco. We offer the best tours and travel packages to explore the beauty of Moroccan culture, history, and landscapes.</p>
                        <div class="social-links mt-4">
                            <a href="#" class="text-white me-2" aria-label="Facebook" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="text-white me-2" aria-label="Twitter" title="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-white me-2" aria-label="Instagram" title="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="text-white me-2" aria-label="YouTube" title="YouTube"><i class="fab fa-youtube"></i></a>
                            <a href="#" class="text-white" aria-label="Tripadvisor" title="Tripadvisor"><i class="fab fa-tripadvisor"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <div class="footer-widget">
                        <h5 class="text-uppercase mb-4">Quick Links</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="/" class="text-white text-decoration-none">Home</a></li>
                            <li class="mb-2"><a href="/about.php" class="text-white text-decoration-none">About Us</a></li>
                            <li class="mb-2"><a href="/destinations.php" class="text-white text-decoration-none">Destinations</a></li>
                            <li class="mb-2"><a href="/tours.php" class="text-white text-decoration-none">Tours</a></li>
                            <li class="mb-2"><a href="/gallery.php" class="text-white text-decoration-none">Gallery</a></li>
                            <li class="mb-2"><a href="/blog.php" class="text-white text-decoration-none">Blog</a></li>
                            <li><a href="/contact.php" class="text-white text-decoration-none">Contact Us</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Popular Destinations -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget">
                        <h5 class="text-uppercase mb-4">Popular Destinations</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="/destination/marrakech" class="text-white text-decoration-none">Marrakech</a></li>
                            <li class="mb-2"><a href="/destination/chefchaouen" class="text-white text-decoration-none">Chefchaouen</a></li>
                            <li class="mb-2"><a href="/destination/merzouga" class="text-white text-decoration-none">Merzouga Desert</a></li>
                            <li class="mb-2"><a href="/destination/fes" class="text-white text-decoration-none">Fes</a></li>
                            <li class="mb-2"><a href="/destination/casablanca" class="text-white text-decoration-none">Casablanca</a></li>
                            <li><a href="/destination/essaouira" class="text-white text-decoration-none">Essaouira</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget">
                        <h5 class="text-uppercase mb-4">Contact Us</h5>
                        <ul class="list-unstyled contact-info">
                            <li class="mb-3">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                123 Travel Street, Marrakech 40000, Morocco
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-phone-alt me-2"></i>
                                <a href="tel:+212600000000" class="text-white text-decoration-none">+212 600-000000</a>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-envelope me-2"></i>
                                <a href="mailto:info@vogie.com" class="text-white text-decoration-none">info@vogie.com</a>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-clock me-2"></i>
                                Mon - Fri: 9:00 AM - 6:00 PM
                            </li>
                        </ul>

                        <!-- Newsletter Subscription -->
                        <div class="mt-4">
                            <h6 class="text-uppercase mb-3">Newsletter</h6>
                            <p class="small">Subscribe to our newsletter for the latest updates and offers.</p>
                            <form class="mt-2" id="newsletterForm">
                                <div class="input-group">
                                    <input type="email" class="form-control form-control-sm" placeholder="Your email address" aria-label="Email address" required>
                                    <button class="btn btn-primary btn-sm" type="submit">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4 bg-secondary">

            <!-- Copyright and Payment Methods -->

        </div>
    </footer>

    <!-- Back to Top Button -->
    <button type="button" class="btn btn-primary btn-floating btn-lg rounded-circle" id="backToTop" aria-label="Back to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- WhatsApp Float Button -->
    <a href="https://wa.me/212600000000?text=Hello%20Vogie%20Team" class="whatsapp-float" target="_blank" rel="noopener noreferrer" aria-label="Chat on WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom Scripts -->
    <script src="assets/js/main.js"></script>

    <!-- Page-specific Scripts -->
    <?php

    if (!isset($currentPage) || !$currentPage) {
        $currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
    }
    ?>
    <?php if (file_exists("assets/js/{$currentPage}.js")): ?>
        <script src="assets/js/<?php echo $currentPage; ?>.js"></script>
    <?php endif; ?>

    <script>

    const backToTopButton = document.getElementById('backToTop');

    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopButton.style.display = 'block';
        } else {
            backToTopButton.style.display = 'none';
        }
    });

    backToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    const searchToggle = document.getElementById('searchToggle');
    const searchOverlay = document.getElementById('searchOverlay');
    const closeSearch = document.getElementById('closeSearch');

    if (searchToggle && searchOverlay) {
        searchToggle.addEventListener('click', function() {
            searchOverlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });

        closeSearch.addEventListener('click', function() {
            searchOverlay.style.display = 'none';
            document.body.style.overflow = '';
        });
    }

    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;

            console.log('Subscribing email:', email);

            const alert = document.createElement('div');
            alert.className = 'alert alert-success mt-2 mb-0';
            alert.role = 'alert';
            alert.innerHTML = 'Thank you for subscribing to our newsletter!';

            this.parentNode.insertBefore(alert, this.nextSibling);

            this.reset();

            setTimeout(() => {
                alert.remove();
            }, 5000);
        });
    }

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {

        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);

        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            document.documentElement.setAttribute('data-bs-theme', newTheme);

            localStorage.setItem('theme', newTheme);

            const icon = this.querySelector('i');
            if (newTheme === 'dark') {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        });

        const icon = themeToggle.querySelector('i');
        if (savedTheme === 'dark') {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        }
    }
    </script>

    <!-- Google Analytics (replace with your tracking ID) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=YOUR-GA-TRACKING-ID"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'YOUR-GA-TRACKING-ID');
    </script>

    <!-- Facebook Pixel Code (replace with your pixel ID) -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', 'YOUR-PIXEL-ID');
    fbq('track', 'PageView');
    </script>
    <noscript>
    <img height="1" width="1" style="display:none"
         src="https://www.facebook.com/tr?id=YOUR-PIXEL-ID&ev=PageView&noscript=1"/>
    </noscript>
    <!-- End Facebook Pixel Code -->

    <!-- Cookie Consent Banner -->
    <div id="cookieConsent" class="alert alert-dark text-center mb-0 rounded-0" style="display: none;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8 text-md-start">
                    <p class="mb-3 mb-md-0">
                        We use cookies to ensure you get the best experience on our website.
                        <a href="/privacy.php" class="text-white">Learn more</a>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                    <button type="button" class="btn btn-sm btn-outline-light me-2" id="btnDecline">Decline</button>
                    <button type="button" class="btn btn-sm btn-primary" id="btnAccept">Accept</button>
                </div>
            </div>
        </div>
    </div>

    <script>

    document.addEventListener('DOMContentLoaded', function() {
        const cookieConsent = document.getElementById('cookieConsent');
        const btnAccept = document.getElementById('btnAccept');
        const btnDecline = document.getElementById('btnDecline');

        if (!localStorage.getItem('cookieConsent')) {

            setTimeout(() => {
                cookieConsent.style.display = 'block';
            }, 1000);
        }

        if (btnAccept) {
            btnAccept.addEventListener('click', function() {
                localStorage.setItem('cookieConsent', 'accepted');
                cookieConsent.style.display = 'none';

            });
        }

        if (btnDecline) {
            btnDecline.addEventListener('click', function() {
                localStorage.setItem('cookieConsent', 'declined');
                cookieConsent.style.display = 'none';
            });
        }
    });
    </script>
</body>
</html>
<?php

if (isset($conn) && $conn instanceof mysqli) {

    if (@mysqli_ping($conn)) {
        $conn->close();
    }
}

if (ob_get_level() > 0) {
    ob_end_flush();
}
?>