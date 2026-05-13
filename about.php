<?php 
// ১. একদম শুরুতে হেডার ইনক্লুড করা হলো (ডাটাবেস, ন্যাভবার সব চলে আসবে)
include 'header.php'; 
?>

<style>
    /* === শুধুমাত্র About পেজের নির্দিষ্ট ডিজাইন === */
    .about-hero { 
        height: 70vh; margin-top: 0;
        background: linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.65)), url('banners/sharee.jpg') center/cover fixed; 
        display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: white;
    }
    .about-hero h1 { font-size: 4.5rem; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; opacity: 0; animation: fadeInUp 1s ease-out forwards; }
    .about-hero p { font-size: 1.3rem; font-weight: 300; max-width: 700px; opacity: 0; animation: fadeInUp 1s ease-out 0.5s forwards; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }

    .reveal-item { opacity: 0; transform: translateY(50px); transition: all 1.2s ease-out; }
    .fly-in-left { opacity: 0; transform: translateX(-100px); transition: all 1.5s ease-out; }
    .fly-in-right { opacity: 0; transform: translateX(100px); transition: all 1.5s ease-out; }
    .active { opacity: 1 !important; transform: translate(0, 0) !important; }

    .story-section-wrapper { background-color: #f8f9fa; padding: 100px 0; } 
    .story-section { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
    .story-img img { width: 100%; border-radius: 20px; box-shadow: -15px 15px 30px rgba(0,0,0,0.1); }
    .story-content-box { background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.05); border-left: 5px solid #ffc107; }
    .story-content h2 { font-size: 2.8rem; margin-bottom: 25px; color: #1a1a1a; font-weight: 800; }
    .story-content p { font-size: 1.1rem; line-height: 1.8; color: #555; }

    /* === Index এর মতো Stats Section === */
    .stats-section { background: #1a1a1a; padding: 100px 8%; color: white; display: flex; justify-content: space-around; text-align: center; }
    .stat-item h2 { font-size: 3.5rem; color: #ffc107; font-weight: 800; margin-bottom: 10px; }
    .stat-item p { font-size: 1.1rem; opacity: 0.8; letter-spacing: 1px; }

    .why-choose-us { text-align: center; max-width: 1200px; margin: 100px auto; padding: 0 20px; }
    .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; }
    .why-choose-us h2, .feature-card { opacity: 0; transform: translateY(50px) scale(0.9); transition: all 1.5s cubic-bezier(0.2, 0.8, 0.2, 1); }
    .feature-card { background: #fff; padding: 50px 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .why-choose-us h2.active, .feature-card.active { opacity: 1; transform: translateY(0) scale(1); }
    .feature-card:hover { transform: perspective(1000px) rotateX(0deg) translateY(-15px); border-bottom: 5px solid #ffc107; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .feature-card .icon { font-size: 3.5rem; margin-bottom: 25px; display: inline-block; transition: 0.6s; }
    .feature-card:hover .icon { transform: rotateY(360deg) scale(1.2); }
</style>

<div class="about-hero">
    <h1>Our Journey</h1>
    <p>We are crafting premium experiences since 2026.</p>
</div>

<div class="story-section-wrapper">
    <div class="story-section">
        <div class="story-img fly-in-left">
            <img src="banners/car.jpg" alt="Our Story">
        </div>
        <div class="story-content fly-in-right">
            <div class="story-content-box">
                <h2>Who We Are</h2>
                <p>Halum Online Shop is not just an e-commerce platform; it's a destination for quality and lifestyle. Born in 2026, we aim to provide an exclusive collection of premium products ranging from luxury automobiles to authentic traditional fashion.</p>
                <p style="margin-top:20px;">We believe in quality over quantity. Every item on our shelf is handpicked and verified to ensure you get nothing but the absolute best shopping experience.</p>
            </div>
        </div>
    </div>
</div>

<div class="stats-section observer-item">
    <div class="stat-item"><h2 class="counter" data-target="5000">0</h2><p>HAPPY CUSTOMERS</p></div>
    <div class="stat-item"><h2 class="counter" data-target="120">0</h2><p>PREMIUM BRANDS</p></div>
    <div class="stat-item"><h2 class="counter" data-target="24">0</h2><p>HOURS SUPPORT</p></div>
    <div class="stat-item"><h2 class="counter" data-target="100">0</h2><p>QUALITY ASSURED %</p></div>
</div>

<div class="why-choose-us">
    <h2 class="reveal-item" style="color:#1a1a1a; margin-bottom: 40px; font-size: 2.5rem; font-weight:800;">Why Choose Halum?</h2>
    <div class="features-grid">
        <div class="feature-card reveal-item" style="transition-delay: 0.3s;">
            <span class="icon">🚀</span>
            <h3>Fast Delivery</h3>
            <p>We ensure your premium products reach your doorstep in record time.</p>
        </div>
        <div class="feature-card reveal-item" style="transition-delay: 0.6s;">
            <span class="icon">🛡️</span>
            <h3>100% Authentic</h3>
            <p>No fakes, no replicas. We guarantee 100% original products.</p>
        </div>
        <div class="feature-card reveal-item" style="transition-delay: 0.9s;">
            <span class="icon">🎧</span>
            <h3>24/7 Support</h3>
            <p>Our dedicated team is ready to assist you any time you need.</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // About পেজের আগের Reveal এনিমেশন
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active'); 
                    observer.unobserve(entry.target); 
                }
            });
        }, { threshold: 0.05, rootMargin: "0px 0px -20px 0px" });

        document.querySelectorAll('.reveal-item, .fly-in-left, .fly-in-right').forEach((el) => {
            observer.observe(el);
        });

        // Index পেজের লাইভ কাউন্টার লজিক
        const counters = document.querySelectorAll('.counter');
        const statsObserver = new IntersectionObserver((entries) => {
            if(entries[0].isIntersecting) {
                counters.forEach(counter => {
                    const updateCount = () => {
                        const target = +counter.getAttribute('data-target');
                        const count = +counter.innerText;
                        const inc = target / 200;
                        if (count < target) {
                            counter.innerText = Math.ceil(count + inc);
                            setTimeout(updateCount, 1);
                        } else { 
                            counter.innerText = target + '+'; 
                        }
                    };
                    updateCount();
                });
                statsObserver.unobserve(entries[0].target);
            }
        }, { threshold: 0.5 });
        
        if(document.querySelector('.stats-section')) {
            statsObserver.observe(document.querySelector('.stats-section'));
        }
    });
</script>

<?php 
// ৩. একদম শেষে ফুটার ইনক্লুড করা হলো
include 'footer.php'; 
?>