<?php 
// ১. হেডার ইনক্লুড করা হলো 
include 'header.php'; 

// লগইন চেক
if(!isset($_SESSION['user_id'])){
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

// ২. কোয়ান্টিটি আপডেট লজিক (Prepared Statement)
if(isset($_POST['update_update_btn'])){
    $update_value = $_POST['update_quantity'];
    $update_id = $_POST['update_quantity_id'];
    $u_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $update_value, $update_id, $u_id);
    $stmt->execute();
    $stmt->close();
    
    echo "<script>window.location.href='cart.php';</script>";
    exit();
}

// ৩. পণ্য রিমুভ করার লজিক (Prepared Statement)
if(isset($_GET['remove'])){
    $remove_id = $_GET['remove'];
    $u_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("DELETE FROM `cart` WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $remove_id, $u_id);
    $stmt->execute();
    $stmt->close();
    
    echo "<script>window.location.href='cart.php';</script>";
    exit();
}
?>

<style>
    /* === Navbar Fix for Cart Page === */
    nav, #navbar { 
        background: #1a1a1a !important; /* ন্যাভবার স্থায়ীভাবে কালো করা হলো */
        box-shadow: 0 4px 15px rgba(0,0,0,0.3) !important;
        padding: 15px 5% !important;
    }

    /* === Premium Cart Page Styles === */
    .cart-page-wrapper {
        background-color: #f8f9fa;
        min-height: 80vh;
        padding: 130px 5% 80px; /* ন্যাভবারের নিচে জায়গা দেওয়া হলো */
    }
    
    .cart-container {
        max-width: 1100px;
        margin: 0 auto;
    }
    
    .cart-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 30px;
    }
    .cart-header h1 {
        font-size: 2.2rem;
        font-weight: 900;
        color: #1a1a1a;
        text-transform: uppercase;
        border-left: 6px solid #ffc107;
        padding-left: 15px;
    }

    .cart-table-wrapper { 
        background: #fff; 
        border-radius: 20px; 
        box-shadow: 0 15px 40px rgba(0,0,0,0.05); 
        overflow: hidden;
        border: 1px solid #eee;
    }
    
    table { width: 100%; border-collapse: collapse; text-align: center; }
    
    th { 
        background: #1a1a1a; 
        color: #ffc107; 
        padding: 20px 15px; 
        text-transform: uppercase; 
        font-size: 14px; 
        font-weight: 800;
        letter-spacing: 1px; 
    }
    
    td { 
        padding: 20px 15px; 
        border-bottom: 1px solid #f1f1f1; 
        font-size: 15px; 
        vertical-align: middle;
    }
    tr:last-child td { border-bottom: none; }
    
    /* Quantity Input & Button Styling */
    .qty-form {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .qty-input {
        width: 60px;
        padding: 8px;
        border-radius: 8px;
        border: 2px solid #eee;
        text-align: center;
        font-weight: bold;
        outline: none;
        transition: 0.3s;
    }
    .qty-input:focus { border-color: #ffc107; }
    
    .update-qty-btn { 
        background: #f8f9fa; 
        color: #1a1a1a;
        border: 1px solid #ddd; 
        padding: 8px 15px; 
        cursor: pointer; 
        border-radius: 8px; 
        font-weight: bold; 
        font-size: 12px; 
        transition: 0.3s; 
    }
    .update-qty-btn:hover { 
        background: #ffc107; 
        border-color: #ffc107;
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
    }
    
    /* Remove Button Styling */
    .remove-btn {
        background: #ffe5e5;
        color: #dc3545;
        padding: 8px 15px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        font-size: 12px;
        transition: 0.3s;
        display: inline-block;
    }
    .remove-btn:hover {
        background: #dc3545;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
    }

    /* Checkout Section Styling */
    .cart-footer {
        background: #fafafa;
        padding: 30px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 2px solid #eee;
    }
    .estimated-total {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1a1a1a;
    }
    .estimated-total span { color: #28a745; font-size: 1.8rem; }
    
    .checkout-btn { 
        background: linear-gradient(135deg, #111, #222); 
        color: #ffc107; 
        padding: 16px 40px; 
        border-radius: 50px; 
        text-decoration: none; 
        font-weight: 800; 
        text-transform: uppercase; 
        letter-spacing: 1px;
        transition: 0.3s; 
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        border: none;
    }
    .checkout-btn:hover { 
        background: #000; 
        color: #fff; 
        transform: translateY(-5px); 
        box-shadow: 0 15px 30px rgba(0,0,0,0.25);
    }

    /* Empty Cart Styling */
    .empty-cart {
        text-align: center;
        padding: 80px 20px;
    }
    .empty-cart h2 { color: #ccc; font-size: 4rem; margin-bottom: 10px; }
    .empty-cart p { color: #666; font-size: 1.2rem; margin-bottom: 25px; }
    .shop-now-btn {
        display: inline-block;
        color: #1a1a1a;
        background: #ffc107;
        text-decoration: none;
        font-weight: 800;
        padding: 15px 35px;
        border-radius: 50px;
        transition: 0.3s;
        box-shadow: 0 10px 20px rgba(255, 193, 7, 0.2);
    }
    .shop-now-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(255, 193, 7, 0.4); }
</style>

<div class="cart-page-wrapper">
    <div class="cart-container">
        
        <div class="cart-header">
            <h1>🛍️ YOUR PREMIUM BAG</h1>
        </div>

        <div class="cart-table-wrapper">
            <?php
            $u_id = $_SESSION['user_id'];
            $cart_res = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$u_id'");
            
            if(mysqli_num_rows($cart_res) > 0){ ?>
                <table>
                    <thead>
                        <tr>
                            <th style="text-align: left; padding-left: 30px;">Product Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $grand_total = 0;
                    while($item = mysqli_fetch_assoc($cart_res)){
                        $subtotal = $item['price'] * $item['quantity'];
                        $grand_total += $subtotal; ?>
                        <tr>
                            <td style="font-weight: 800; color: #111; text-align: left; padding-left: 30px;">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </td> 
                            <td style="font-weight: 600; color: #555;">
                                <?php echo number_format($item['price'], 2); ?> BDT
                            </td>
                            <td>
                                <form action="" method="post" class="qty-form">
                                    <input type="hidden" name="update_quantity_id" value="<?php echo $item['id']; ?>">
                                    <input type="number" name="update_quantity" min="1" value="<?php echo $item['quantity']; ?>" class="qty-input">
                                    <button type="submit" name="update_update_btn" class="update-qty-btn">Update</button>
                                </form>
                            </td>
                            <td style="font-weight: 900; color: #28a745; font-size: 1.1rem;">
                                <?php echo number_format($subtotal, 2); ?> BDT
                            </td>
                            <td>
                                <a href="cart.php?remove=<?php echo $item['id']; ?>" class="remove-btn" onclick="return confirm('Are you sure you want to remove this premium item?')">
                                    <i class="fas fa-trash"></i> Remove
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                
                <div class="cart-footer">
                    <div class="estimated-total">
                        Total Amount: <br>
                        <span><?php echo number_format($grand_total, 2); ?> BDT</span>
                    </div>
                    <a href="checkout.php" class="checkout-btn">Proceed to Checkout <i class="fas fa-arrow-right" style="margin-left: 8px;"></i></a>
                </div>
                
            <?php } else { ?>
                <div class="empty-cart">
                    <h2>🛒</h2>
                    <p>Your premium shopping bag is currently empty.</p>
                    <a href="index.php#products" class="shop-now-btn">Discover Collection</a>
                </div>
            <?php } ?>
        </div>

    </div>
</div>

<?php 
// ৪. ফুটার ইনক্লুড করা হলো
include 'footer.php'; 
?>