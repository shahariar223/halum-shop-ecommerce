<?php 
// ১. হেডার ইনক্লুড করা হলো
include 'header.php'; 

// লগইন না থাকলে লগইন পেজে পাঠিয়ে দেবে
if(!isset($_SESSION['user_email'])){
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id']; 
$u_email = $_SESSION['user_email']; 

// ইউজারের নাম ফেচ করা (ইনপুটে ডিফল্ট ভ্যালু হিসেবে দেখানোর জন্য)
$user_name = '';
$user_query = mysqli_query($conn, "SELECT full_name FROM `users` WHERE id = '$user_id'");
if($user_query && mysqli_num_rows($user_query) > 0){
    $u_data = mysqli_fetch_assoc($user_query);
    $user_name = $u_data['full_name'];
}
?>

<style>
    /* === Navbar Fix for Checkout Page === */
    nav, #navbar { 
        background: #1a1a1a !important; /* ন্যাভবার স্থায়ীভাবে কালো করা হলো */
        box-shadow: 0 4px 15px rgba(0,0,0,0.3) !important;
        padding: 15px 5% !important;
    }

    /* === Premium Checkout Page Styles === */
    .checkout-wrapper {
        background-color: #f4f7f6;
        min-height: 80vh;
        padding: 130px 5% 80px; /* ন্যাভবারের নিচে জায়গা দেওয়া হলো */
    }
    .checkout-container {
        max-width: 1100px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 1.3fr; /* 2-Column Layout */
        gap: 40px;
        align-items: start;
    }

    /* Left Side: Order Summary (Dark Theme) */
    .order-summary-card {
        background: #1a1a1a;
        color: #fff;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        position: sticky;
        top: 120px;
    }
    .order-summary-card h2 { color: #ffc107; font-size: 1.8rem; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; }
    
    .item-list { margin-bottom: 20px; max-height: 300px; overflow-y: auto; padding-right: 10px; }
    .item-list::-webkit-scrollbar { width: 5px; }
    .item-list::-webkit-scrollbar-thumb { background: #ffc107; border-radius: 10px; }

    .summary-item {
        display: flex; justify-content: space-between; align-items: center;
        padding: 15px 0; border-bottom: 1px dashed rgba(255,255,255,0.1);
    }
    .summary-item:last-child { border-bottom: none; }
    .item-name { font-weight: 600; font-size: 15px; color: #e0e0e0; }
    .item-qty { color: #ffc107; font-weight: bold; font-size: 13px; margin-left: 8px; }
    .item-price { font-weight: bold; color: #fff; }

    .total-bill-box {
        background: rgba(255,255,255,0.05); padding: 20px; border-radius: 12px; margin-top: 20px; text-align: center; border: 1px solid rgba(255,193,7,0.2);
    }
    .total-bill-box p { font-size: 14px; color: #aaa; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px; }
    .total-bill-box h3 { font-size: 2rem; color: #28a745; font-weight: 900; }

    /* Right Side: Billing Form (Light Theme) */
    .billing-card {
        background: #fff; padding: 50px 40px; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.05); border-top: 5px solid #1a1a1a;
    }
    .billing-card h2 { font-size: 2.2rem; color: #1a1a1a; margin-bottom: 10px; font-weight: 900; }
    .billing-card p.subtitle { color: #666; margin-bottom: 30px; font-size: 15px; }

    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; font-size: 14px; }
    .form-group input, .form-group textarea {
        width: 100%; padding: 15px; border: 2px solid #eee; border-radius: 10px; font-size: 15px; background: #fdfdfd; outline: none; transition: 0.3s;
    }
    .form-group input:focus, .form-group textarea:focus { border-color: #ffc107; background: #fff; }
    .form-group input[readonly] { background: #f1f1f1; color: #888; cursor: not-allowed; }

    .payment-method {
        background: #fff9e6; border: 1px solid #ffeeba; padding: 15px; border-radius: 10px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; color: #856404; font-weight: bold;
    }

    .place-order-btn {
        background: #1a1a1a; color: #ffc107; width: 100%; padding: 18px; border: none; border-radius: 50px; font-size: 16px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .place-order-btn:hover { background: #28a745; color: #fff; transform: translateY(-3px); box-shadow: 0 15px 30px rgba(40, 167, 69, 0.3); }

    /* Success Message */
    .success-box {
        background: #fff; padding: 60px 40px; border-radius: 20px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.1); max-width: 600px; margin: 50px auto; border-top: 5px solid #28a745;
    }
    .success-box .icon { font-size: 60px; color: #28a745; margin-bottom: 20px; display: inline-block; animation: pop 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .success-box h2 { font-size: 2.5rem; color: #1a1a1a; margin-bottom: 10px; }
    .success-box p { color: #666; font-size: 16px; margin-bottom: 20px; }
    @keyframes pop { 0% { transform: scale(0); } 100% { transform: scale(1); } }

    /* Responsive */
    @media (max-width: 850px) { .checkout-container { grid-template-columns: 1fr; } .order-summary-card { position: static; } }
</style>

<div class="checkout-wrapper">
    <?php
    if(isset($_POST['confirm'])){
        $name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);

        $final_cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'");
        $grand_total = 0;
        $items_list = "";

        while($cart_row = mysqli_fetch_assoc($final_cart_query)){
            $item_total = $cart_row['price'] * $cart_row['quantity']; 
            $grand_total += $item_total;
            $items_list .= $cart_row['name'] . " (x" . $cart_row['quantity'] . "), ";
        }
        $items_final = rtrim($items_list, ", ");

        if($grand_total > 0){
            $stmt = $conn->prepare("INSERT INTO orders (user_id, full_name, phone, email, address, total_products, total_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("isssssd", $user_id, $name, $phone, $u_email, $address, $items_final, $grand_total);
            
            if($stmt->execute()){
                mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'"); 
                
                echo "<div class='success-box'>";
                echo "<div class='icon'>🎉</div>";
                echo "<h2>Order Confirmed!</h2>";
                echo "<p>Thank you for shopping with Halum. Your order has been placed successfully and is currently pending review.</p>";
                echo "<p style='font-size: 14px; color: #aaa;'>Redirecting to your orders...</p>";
                echo "<script>setTimeout(function(){ window.location.href='my_orders.php'; }, 3000);</script>";
                echo "</div></div>"; 
                include 'footer.php';
                exit();
            }
            $stmt->close();
        } else {
            echo "<script>window.location.href='cart.php';</script>";
            exit();
        }
    } else {
    ?>
    <div class="checkout-container">
        
        <div class="order-summary-card">
            <h2><i class="fas fa-shopping-bag"></i> Order Summary</h2>
            <div class="item-list">
                <?php
                $display_total = 0;
                $cart_display = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'"); 
                if(mysqli_num_rows($cart_display) > 0){
                    while($row = mysqli_fetch_assoc($cart_display)){
                        $row_total = $row['price'] * $row['quantity'];
                        $display_total += $row_total;
                        ?>
                        <div class="summary-item">
                            <div class="item-name">
                                <?php echo htmlspecialchars($row['name']); ?> 
                                <span class="item-qty">x<?php echo $row['quantity']; ?></span>
                            </div>
                            <div class="item-price"><?php echo number_format($row_total, 2); ?> BDT</div>
                        </div>
                    <?php }
                } else {
                    echo "<script>window.location.href='index.php';</script>";
                    exit();
                }
                ?>
            </div>
            <div class="total-bill-box">
                <p>Total Payable Amount</p>
                <h3><?php echo number_format($display_total, 2); ?> BDT</h3>
            </div>
        </div>

        <div class="billing-card">
            <h2>Billing Details</h2>
            <p class="subtitle">Please provide your delivery information carefully.</p>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="E.g. Md. Shahariar Sultan" value="<?php echo htmlspecialchars($user_name); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" placeholder="E.g. 017XXXXXXXX" required>
                </div>
                
                <div class="form-group">
                    <label>Email Address (Cannot be changed)</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($u_email); ?>" readonly title="Email associated with this account">
                </div>
                
                <div class="form-group">
                    <label>Complete Delivery Address</label>
                    <textarea name="address" placeholder="House/Flat No, Road Name, Area, City" rows="3" required></textarea>
                </div>

                <div class="payment-method">
                    <i class="fas fa-money-bill-wave"></i> Payment Method: <strong>Cash on Delivery (COD)</strong>
                </div>

                <button type="submit" name="confirm" class="place-order-btn">Confirm & Place Order <i class="fas fa-check-circle" style="margin-left: 8px;"></i></button>
            </form>
        </div>

    </div>
    <?php } ?>
</div>

<?php 
// ২. ফুটার ইনক্লুড করা হলো
include 'footer.php'; 
?>