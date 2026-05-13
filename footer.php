<style>
/* === Footer Styles === */
.main-footer { background: #0a0a0a; color: #fff; padding: 80px 8% 20px; border-top: 1px solid rgba(255, 193, 7, 0.1); margin-top: 50px; }
.footer-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; max-width: 1200px; margin: 0 auto; }
.footer-col h3 { color: #ffc107; margin-bottom: 25px; font-size: 1.2rem; }
.footer-col p { line-height: 1.8; font-size: 14px; opacity: 0.7; }
.footer-col ul { list-style: none; padding:0; }
.footer-col ul li { margin-bottom: 12px; }
.footer-col ul li a { color: #fff; text-decoration: none; font-size: 14px; opacity: 0.7; transition: 0.3s; }
.footer-col ul li a:hover { color: #ffc107; opacity: 1; padding-left: 8px; }
.footer-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
.footer-logo img { height: 40px; }
.footer-logo h2 { color: #ffc107; font-size: 24px; }
.footer-bottom { text-align: center; margin-top: 60px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.05); font-size: 13px; opacity: 0.5; }
.footer-reveal { opacity: 0; transform: translateY(30px); transition: all 0.8s ease-out; }
.footer-reveal.reveal { opacity: 1; transform: translateY(0); }
.social-icons { display: flex; gap: 15px; margin-top: 25px; }
.social-icons a { display: inline-flex; justify-content: center; align-items: center; width: 45px; height: 45px; background: rgba(255, 255, 255, 0.08); color: #fff; border-radius: 50%; text-decoration: none; font-size: 18px; transition: all 0.4s; border: 1px solid rgba(255, 255, 255, 0.1); }
.social-icons a:hover { background: #ffc107; color: #000; transform: translateY(-5px) rotate(360deg); box-shadow: 0 8px 25px rgba(255, 193, 7, 0.5); border-color: #ffc107; }
</style>

<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-col footer-reveal">
            <div class="footer-logo">
                <img src="halum.png" alt="Halum Logo">
                <h2>Halum</h2>
            </div>
            <p>Experience the peak of luxury shopping with Halum. We bring you unmatched quality and unbeatable style since 2026.</p>
        </div>

        <div class="footer-col footer-reveal">
            <h3>Quick Shop</h3>
            <ul>
                <li><a href="index.php?cat=Bike#products">Motorcycles</a></li>
                <li><a href="index.php?cat=Car#products">Luxury Cars</a></li>
                <li><a href="index.php?cat=Sharee#products">Fashion</a></li>
                <li><a href="index.php?cat=Watch#products">Premium Watches</a></li>
            </ul>
        </div>

        <div class="footer-col footer-reveal">
            <h3>Support</h3>
            <ul>
                <li><a href="about.php">Our Journey</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="my_orders.php">Order Tracking</a></li>
                <li><a href="#">Privacy Policy</a></li>
            </ul>
        </div>

        <div class="footer-col footer-reveal" style="transition-delay: 0.45s;">
            <h3>Connect With Us</h3>
            <p>📍 123 Luxury Plaza, Banani, Dhaka</p>
            <p>📧 support@halum.com</p>
            <div class="social-icons">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a> 
                <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 Halum Online Shop. All rights reserved.</p>
    </div>
</footer>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Navbar Scroll Effect
    window.addEventListener('scroll', function() {
        const nav = document.getElementById('navbar');
        if(nav) {
            if (window.scrollY > 50) nav.classList.add("scrolled");
            else nav.classList.remove("scrolled");
        }
    });

    // Footer Reveal Effect
    const footerObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('reveal');
            }
        });
    }, { threshold: 0.2 });

    document.querySelectorAll('.footer-reveal').forEach((col, index) => {
        col.style.transitionDelay = `${index * 0.15}s`; 
        footerObserver.observe(col);
    });
});
</script>
</body>
</html>