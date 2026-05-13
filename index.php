<?php 
// উপরে হেডার ইনক্লুড করা হলো (এতেই ডাটাবেস, সেশন, এবং ন্যাভবার চলে আসবে)
include 'header.php'; 

// কার্ট হ্যান্ডলিং লজিক (Updated)
if(isset($_POST['add_to_cart'])){
    if(!isset($_SESSION['user_id'])){
        echo "<script>alert('Please login first!'); window.location.href='login.php';</script>";
        exit();
    }
    $u_id = $_SESSION['user_id']; 
    $p_name = mysqli_real_escape_string($conn, $_POST['p_name']);
    $p_price = $_POST['p_price'];
    $p_image = $_POST['p_image']; // ছবিটা ধরলাম
    
    $check_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$u_id' AND name = '$p_name'");
    if(mysqli_num_rows($check_cart) > 0){
        mysqli_query($conn, "UPDATE `cart` SET quantity = quantity + 1 WHERE user_id = '$u_id' AND name = '$p_name'");
    } else {
        // এখানে image কলামটিও যোগ করা হলো
        mysqli_query($conn, "INSERT INTO `cart` (user_id, name, price, quantity, image) VALUES ('$u_id', '$p_name', '$p_price', 1, '$p_image')");
    }
    echo "<script>alert('Product added to cart!'); window.location.href='index.php#products';</script>";
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['cat']) ? mysqli_real_escape_string($conn, $_GET['cat']) : '';
$is_filtering = ($search != '' || $category_filter != '');
?>

<style>
/* শুধু ইন্ডেক্স পেজের জন্য নির্দিষ্ট CSS */
.product-section { padding: 50px 8% 100px; background: #fff; text-align: center; }
.section-title { margin: 80px 0 50px; font-size: 2.5rem; font-weight: 900; color: #1a1a1a; }
.product-container { display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; max-width: 1200px; margin: 0 auto; }

.card { background: #fff; padding: 20px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: 1px solid #eee; text-align: center; display: flex; flex-direction: column; height: 100%; position: relative; overflow: hidden; opacity: 0; transform: translateY(50px) scale(0.95); transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94); }
.card:hover { transform: translateY(-12px) !important; box-shadow: 0 20px 40px rgba(0,0,0,0.15); border-color: #ffc107; }
.card img { width: 100%; height: 180px; object-fit: contain; margin-bottom: 15px; background: #fdfdfd; border-radius: 10px; transition: transform 0.5s ease; }
.card:hover img { transform: scale(1.08); }
.card h3 { font-size: 18px; margin-bottom: 5px; color: #222; font-weight: 700; }
.card .price { color: #28a745; font-weight: 900; font-size: 1.1rem; margin-bottom: 15px; display: block; }

.btn-cart { background: #1a1a1a; color: #ffc107; padding: 12px; border: none; width: 100%; cursor: pointer; border-radius: 50px; font-weight: 800; text-transform: uppercase; margin-top: auto; transition: 0.3s; box-shadow: 0 5px 10px rgba(0,0,0,0.1); }
.card:hover .btn-cart { background: #ffc107; color: #000; box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4); letter-spacing: 2px; }
.card.show-card { opacity: 1 !important; transform: translateY(0) scale(1) !important; }

/* Slider & Other Styles */
.slider-container { position: relative; width: 100%; height: 100vh; overflow: hidden; }
.slider-container::after { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.4)); z-index: 5; }
.slides { display: none; width: 100%; height: 100%; position: absolute; }
.slides img { width: 100%; height: 100%; object-fit: cover; filter: brightness(60%); }
.slider-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: white; z-index: 100; width: 85%; }
.slider-content h1 { font-size: 4.2rem; font-weight: 900; text-shadow: 0 4px 15px rgba(0,0,0,0.5); margin-bottom: 20px; }
.slider-content p { opacity: 0; transform: translateY(30px); animation: flyInUpRepeat 2.5s ease-in-out infinite; }
#hero-title { min-height: 120px; display: inline-block; }
.cursor { display: inline-block; width: 3px; height: 1em; background-color: #ffc107; margin-left: 5px; vertical-align: middle; animation: blink 0.7s infinite; }
@keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
@keyframes flyInUpRepeat { 0% { opacity: 0; transform: translateY(30px); } 20%, 80% { opacity: 1; transform: translateY(0); } 100% { opacity: 0; transform: translateY(0); } }
.fade { animation: fadeEffect 1.5s; }
@keyframes fadeEffect { from {opacity: .7} to {opacity: 1} }

.filter-section { padding: 0 5%; margin-top: -75px; position: relative; z-index: 100; perspective: 1200px; text-align: center; }
.search-form { display: flex; justify-content: center; align-items: center; gap: 15px; max-width: 750px; margin: 0 auto; padding: 18px 25px; border-radius: 20px; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 15px 35px rgba(0,0,0,0.1); transition: transform 0.2s ease-out; transform-style: preserve-3d; }
.search-form input { flex: 1; padding: 14px 25px; border-radius: 50px; border: none; outline: none; background: #fff; font-size: 15px; }
.search-btn { background: #1a1a1a; color: #ffc107; padding: 14px 35px; border: none; border-radius: 50px; cursor: pointer; font-weight: 800; transition: 0.3s; }
.search-btn:hover { background: #000; transform: scale(1.05); box-shadow: 0 10px 20px rgba(255, 193, 7, 0.2); }

.category-quick-nav { display: flex; justify-content: center; gap: 15px; margin-top: 30px; perspective: 1000px; }
.cat-pill { text-decoration: none; color: #1a1a1a; background: #ffffff; padding: 12px 28px; border-radius: 50px; font-weight: 700; display: flex; align-items: center; gap: 12px; border: 1px solid rgba(0,0,0,0.05); opacity: 0; transition: all 0.9s cubic-bezier(0.19, 1, 0.22, 1); }
.cat-pill:nth-child(1) { transform: translateX(-150px); }
.cat-pill:nth-child(2) { transform: translateY(-100px); }
.cat-pill:nth-child(3) { transform: translateY(100px); }
.cat-pill:nth-child(4) { transform: translateX(150px); }
.cat-pill.reveal { opacity: 1; transform: translate(0, 0) !important; }
.cat-pill:hover { background: #ffc107; border-color: #ffc107; color: #000; transform: scale(1.1) translateY(-5px) !important; box-shadow: 0 20px 40px rgba(255, 193, 7, 0.3); }

.stats-section { background: #1a1a1a; padding: 100px 8%; color: white; display: flex; justify-content: space-around; text-align: center; }
.stat-item h2 { font-size: 3.5rem; color: #ffc107; font-weight: 800; margin-bottom: 10px; }
.stat-item p { font-size: 1.1rem; opacity: 0.8; letter-spacing: 1px; }
</style>

<div class="slider-container">
    <div class="slides fade"><img src="banners/limon.jpg" alt="Bike"></div>
    <div class="slides fade"><img src="banners/car.jpg" alt="Car"></div>
    <div class="slides fade"><img src="banners/sharee.jpg" alt="Fashion"></div>
    <div class="slides fade"><img src="banners/watch.jpg" alt="Watch"></div>
    <div class="slider-content">
        <h1 id="hero-title"></h1><span class="cursor"></span>
        <p style="font-size: 1.5rem; letter-spacing: 2px;">Your Ultimate Premium Destination</p>
    </div>
</div>

<div class="filter-section">
    <form action="index.php#products" method="GET" class="search-form">
        <input type="text" name="search" placeholder="Search your premium style..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="search-btn">Search</button>
    </form>

    <div class="category-quick-nav">
        <a href="index.php?cat=Bike#products" class="cat-pill"><span>🏍️</span> Motorcycles</a>
        <a href="index.php?cat=Car#products" class="cat-pill"><span>🚗</span> Luxury Cars</a>
        <a href="index.php?cat=Sharee#products" class="cat-pill"><span>👗</span> Fashion</a>
        <a href="index.php?cat=Watch#products" class="cat-pill"><span>⌚</span> Watches</a>
    </div>
</div>

<div class="product-section" id="products">
    <?php 
    function display_product_card($row) {
    echo "<div class='card'>";
    
    // ১. ছবির ওপর লিংক (যাতে ক্লিক করলে ডিটেইলসে যায়)
    // নোট: আপনার ফোল্ডার নাম images হলে 'images/' রাখবেন, uploaded_img হলে সেটা দিবেন
    echo "<a href='product_details.php?pid=".$row['id']."'>";
    echo "<img src='images/".$row['image']."' alt='Product'>"; 
    echo "</a>";
    
    // ২. নামের ওপর লিংক
    echo "<a href='product_details.php?pid=".$row['id']."' style='text-decoration: none; color: inherit;'>";
    echo "<h3>".htmlspecialchars($row['name'])."</h3>";
    echo "</a>";
    
    echo "<span class='price'>".number_format($row['price'], 2)." BDT</span>";
    
    echo "<form action='index.php#products' method='POST'>
            <input type='hidden' name='p_name' value='".htmlspecialchars($row['name'])."'>
            <input type='hidden' name='p_price' value='".$row['price']."'>
            <input type='hidden' name='p_image' value='".$row['image']."'>
            <button type='submit' name='add_to_cart' class='btn-cart'>Add to Cart</button>
          </form></div>";
}

    if($is_filtering): ?>
        <h2 class="section-title">Search Results</h2>
        <div style="margin: -20px 0 40px 0; text-align: center;">
            <a href="index.php#products" style="text-decoration: none; color: #111; font-weight: bold; background: #ffc107; padding: 12px 25px; border-radius: 50px; font-size: 14px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); transition: 0.3s;">← Back to All Products</a>
        </div>
        <div class="product-container">
            <?php 
            $q = "SELECT * FROM products WHERE (name LIKE '%$search%' OR category LIKE '%$search%')";
            if($category_filter != '') $q .= " AND category = '$category_filter'";
            $res = mysqli_query($conn, $q . " ORDER BY id DESC");
            if(mysqli_num_rows($res) > 0){
                while($row = mysqli_fetch_assoc($res)) { display_product_card($row); }
            } else {
                echo "<p style='grid-column: span 4; font-size: 18px; color: #666;'>No products found!</p>";
            }
            ?>
        </div>
    <?php else: ?>
        <?php 
        $categories = ['Bike', 'Car', 'Sharee', 'Watch']; 
        foreach($categories as $cat): 
        ?>
            <div style="margin-bottom: 80px;">
                <h2 class="section-title"><?php echo $cat; ?> Collection</h2>
                <div class="product-container">
                    <?php 
                    $res = mysqli_query($conn, "SELECT * FROM products WHERE category = '$cat' LIMIT 8");
                    while($row = mysqli_fetch_assoc($res)) { display_product_card($row); }
                    ?>
                </div>
                <a href="index.php?cat=<?php echo $cat; ?>#products" class="view-all-link" style="display: inline-block; margin-top: 20px; color: #ffc107; font-weight: bold; text-decoration: none;">Browse Full <?php echo $cat; ?> Collection →</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="stats-section observer-item">
    <div class="stat-item"><h2 class="counter" data-target="5000">0</h2><p>HAPPY CUSTOMERS</p></div>
    <div class="stat-item"><h2 class="counter" data-target="150">0</h2><p>PREMIUM BRANDS</p></div>
    <div class="stat-item"><h2 class="counter" data-target="24">0</h2><p>HOURS SUPPORT</p></div>
    <div class="stat-item"><h2 class="counter" data-target="100">0</h2><p>QUALITY ASSURED %</p></div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Typewriter
    const text = "UNMATCHED QUALITY. UNBEATABLE STYLE.";
    const heroTitle = document.getElementById("hero-title");
    const words = text.split(" "); 
    async function typewriterLoop() {
        while (true) {
            for (let i = 0; i <= text.length; i++) {
                heroTitle.innerHTML = text.substring(0, i);
                await new Promise(res => setTimeout(res, 80));
            }
            await new Promise(res => setTimeout(res, 1500));
            let currentWords = [...words];
            while (currentWords.length > 0) {
                currentWords.pop(); 
                heroTitle.innerHTML = currentWords.join(" ");
                await new Promise(res => setTimeout(res, 150)); 
            }
            await new Promise(res => setTimeout(res, 500));
        }
    }
    if (heroTitle) { heroTitle.innerHTML = ""; typewriterLoop(); }

    // 2. Slider
    let slideIndex = 0;
    const slides = document.getElementsByClassName("slides");
    function showSlides() {
        for (let j = 0; j < slides.length; j++) { if(slides[j]) slides[j].style.display = "none"; }
        slideIndex++;
        if (slideIndex > slides.length) slideIndex = 1;
        if(slides[slideIndex-1]) slides[slideIndex-1].style.display = "block";
        setTimeout(showSlides, 5000); 
    }
    if(slides.length > 0) showSlides();

    // 3. Product Animation
    const productObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => { if (entry.isIntersecting) { entry.target.classList.add('show-card'); } });
    }, { threshold: 0.1 });
    document.querySelectorAll('.card').forEach((card, index) => {
        card.style.transitionDelay = `${(index % 4) * 0.1}s`;
        productObserver.observe(card);
    });

    // 4. Category Pills Animation
    const pills = document.querySelectorAll('.cat-pill');
    const pillObserver = new IntersectionObserver((entries) => {
        if(entries[0].isIntersecting) {
            pills.forEach((pill, index) => { setTimeout(() => { pill.classList.add('reveal'); }, index * 300); });
            pillObserver.unobserve(entries[0].target);
        }
    }, { threshold: 0.3 }); 
    const catNav = document.querySelector('.category-quick-nav');
    if(catNav) pillObserver.observe(catNav);

    // 5. 3D Tilt
    const searchBox = document.querySelector('.search-form');
    const filterArea = document.querySelector('.filter-section');
    filterArea.addEventListener('mousemove', (e) => {
        let xAxis = (window.innerWidth / 2 - e.pageX) / 45;
        let yAxis = (window.innerHeight / 2 - e.pageY) / 45;
        searchBox.style.transform = `rotateY(${xAxis}deg) rotateX(${yAxis}deg) translateY(-5px)`;
    });
    filterArea.addEventListener('mouseleave', () => {
        searchBox.style.transform = `rotateY(0deg) rotateX(0deg) translateY(0)`;
    });

    // 6. Live Counter
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
                    } else { counter.innerText = target + '+'; }
                };
                updateCount();
            });
            statsObserver.unobserve(entries[0].target);
        }
    }, { threshold: 0.5 });
    if(document.querySelector('.stats-section')) statsObserver.observe(document.querySelector('.stats-section'));
});
</script>

<?php include 'footer.php'; ?>