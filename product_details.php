<?php 
// ১. হেডার ইনক্লুড
include 'header.php'; 

// ইউজার লগইন না থাকলেও ডিটেইলস দেখতে পারবে, কিন্তু কার্টে নিতে হলে লগইন লাগবে (এটা লজিকে আছে)

// ২. URL থেকে প্রোডাক্ট আইডি ধরা
if(isset($_GET['pid'])){
    $pid = mysqli_real_escape_string($conn, $_GET['pid']);
    $select_product = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$pid'");
    
    if(mysqli_num_rows($select_product) > 0){
        $product = mysqli_fetch_assoc($select_product);
    } else {
        echo "<script>window.location.href='index.php';</script>";
        exit();
    }
} else {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

// ৩. অ্যাড টু কার্ট লজিক
if(isset($_POST['add_to_cart'])){
    if(!isset($_SESSION['user_id'])){
        echo "<script>alert('Please login first!'); window.location.href='login.php';</script>";
        exit();
    }
    
    $u_id = $_SESSION['user_id'];
    $p_name = $product['name'];
    $p_price = $product['price'];
    $p_image = $product['image'];
    $p_qty = $_POST['product_quantity'];

    // কার্টে অলরেডি আছে কিনা চেক
    $check_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$p_name' AND user_id = '$u_id'");

    if(mysqli_num_rows($check_cart) > 0){
        mysqli_query($conn, "UPDATE `cart` SET quantity = quantity + $p_qty WHERE name = '$p_name' AND user_id = '$u_id'");
    } else {
        mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, quantity, image) VALUES('$u_id', '$p_name', '$p_price', '$p_qty', '$p_image')");
    }
    echo "<script>alert('Product added to cart!'); window.location.href='cart.php';</script>";
}
?>

<style>
    /* === Product Details Page Styles === */
    body { background-color: #f8f9fa; }
    nav, #navbar { background: #1a1a1a !important; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }

    .details-wrapper { padding: 130px 8% 80px; min-height: 80vh; }
    
    .product-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        background: #fff;
        padding: 50px;
        border-radius: 30px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.05);
        align-items: center;
    }

    /* ইমেজ সেকশন ও জুম ইফেক্ট */
    .img-container {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        background: #fdfdfd;
        border: 1px solid #eee;
        cursor: crosshair;
    }
    .img-container img {
        width: 100%;
        height: 500px;
        object-fit: contain;
        transition: transform 0.5s ease;
    }
    .img-container:hover img {
        transform: scale(1.3); /* মাউস নিলে জুম হবে */
    }

    /* টেক্সট সেকশন */
    .product-info h1 { font-size: 3rem; font-weight: 900; color: #1a1a1a; margin-bottom: 10px; text-transform: uppercase; }
    .category-badge { background: #ffc107; color: #000; padding: 5px 15px; border-radius: 50px; font-size: 12px; font-weight: 800; text-transform: uppercase; }
    
    .price-tag { font-size: 2.2rem; font-weight: 800; color: #28a745; margin: 25px 0; }
    .price-tag span { font-size: 1.2rem; color: #888; text-decoration: line-through; margin-left: 10px; font-weight: 400; }

    .description { color: #666; line-height: 1.8; margin-bottom: 30px; font-size: 16px; border-top: 1px solid #eee; padding-top: 20px; }

    /* কোয়ান্টিটি ও বাটন */
    .purchase-box { display: flex; align-items: center; gap: 20px; margin-top: 40px; }
    
    .qty-box {
        display: flex; align-items: center; border: 2px solid #eee; border-radius: 50px; padding: 5px 15px;
    }
    .qty-box input { width: 50px; border: none; text-align: center; font-weight: bold; font-size: 18px; outline: none; background: transparent; }

    .add-cart-btn {
        background: #1a1a1a; color: #ffc107; padding: 18px 45px; border-radius: 50px;
        border: none; font-weight: 800; font-size: 16px; text-transform: uppercase;
        cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        display: flex; align-items: center; gap: 10px;
    }
    .add-cart-btn:hover { background: #000; color: #fff; transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.2); }

    /* রেসপনসিভ */
    @media (max-width: 900px) { .product-grid { grid-template-columns: 1fr; } .product-info h1 { font-size: 2rem; } }
</style>

<div class="details-wrapper">
    <div class="product-grid">
        
        <div class="img-container" id="zoom-img">
            <img src="images/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>

        <div class="product-info">
            <span class="category-badge"><?php echo $product['category'] ?? 'Premium'; ?></span>
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="price-tag">
                <?php echo number_format($product['price'], 2); ?> BDT
                <span><?php echo number_format($product['price'] * 1.2, 2); ?> BDT</span>
            </div>

            <p class="description">
                <?php echo nl2br(htmlspecialchars($product['details'])); ?>
            </p>

            <form action="" method="post">
                <div class="purchase-box">
                    <div class="qty-box">
                        <span style="font-weight:bold; color:#888;">QTY:</span>
                        <input type="number" name="product_quantity" value="1" min="1" max="10">
                    </div>
                    
                    <button type="submit" name="add_to_cart" class="add-cart-btn">
                        <i class="fas fa-shopping-cart"></i> Add To Cart
                    </button>
                </div>
            </form>
            
            <div style="margin-top: 30px; display:flex; gap:20px; color:#aaa; font-size:14px;">
                <span><i class="fas fa-truck"></i> Fast Delivery</span>
                <span><i class="fas fa-shield-alt"></i> Genuine Product</span>
                <span><i class="fas fa-undo"></i> 7 Days Return</span>
            </div>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>