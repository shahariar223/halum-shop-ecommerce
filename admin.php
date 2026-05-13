<?php 
include 'db.php'; 
session_start(); 

// ১. একদম বেসিক লগইন চেক
if(!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

// ২. রোল চেক
if(!isset($_SESSION['role']) || $_SESSION['role'] === 'user'){
    header("Location: index.php"); 
    exit();
}

// ৩. পেজ ভিত্তিক পারমিশন চেক (product_admin এবং super_admin এর জন্য)
$current_role = $_SESSION['role'];
$current_page = basename($_SERVER['PHP_SELF']);

if($current_role !== 'super_admin'){
    $product_pages = ['admin.php', 'manage_products.php'];
    if(in_array($current_page, $product_pages) && $current_role !== 'product_admin'){
        header("Location: admin_dashboard.php");
        exit();
    }

    $support_pages = ['view_orders.php', 'view_messages.php', 'update_status.php'];
    if(in_array($current_page, $support_pages) && $current_role !== 'sell_communication_admin'){
        header("Location: admin_dashboard.php");
        exit();
    }
}

// বর্তমান অ্যাডমিনের তথ্য
$admin_id = $_SESSION['user_id'];
$admin_q = mysqli_query($conn, "SELECT * FROM users WHERE id = '$admin_id'");
$admin_data = mysqli_fetch_assoc($admin_q);

// নোটিফিকেশন কাউন্ট
$new_orders_count = 0;
$unread_msgs_count = 0;
$order_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
if($order_q) { $new_orders_count = mysqli_fetch_assoc($order_q)['total']; }
$msg_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM messages WHERE is_read = 0");
if($msg_q) { $unread_msgs_count = mysqli_fetch_assoc($msg_q)['total']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product | Halum Admin</title>
    <link rel="icon" type="image/png" href="halum.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary: #ffc107; --dark: #1a1a1a; --bg: #f4f6f9; --white: #ffffff; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; display: flex; margin: 0; background: var(--bg); color: #333; overflow-x: hidden; }
        
        /* === Universal Sidebar Style === */
        .sidebar { width: 280px; background: #1a1a1a; color: white; height: 100vh; padding: 40px 20px; position: fixed; box-shadow: 4px 0 10px rgba(0,0,0,0.1); overflow-y: auto; }
        .sidebar h2 { color: #ffc107; font-size: 28px; margin-bottom: 40px; text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .sidebar a { display: block; color: #aaa; padding: 15px 20px; text-decoration: none; border-radius: 12px; margin-bottom: 8px; transition: 0.3s; font-size: 15px; }
        .sidebar a:hover { background: rgba(255,193,7,0.1); color: #ffc107; transform: translateX(5px); }
        .sidebar a.active { background: rgba(255,193,7,0.2); color: #ffc107; font-weight: bold; }

        .admin-profile { display: flex; align-items: center; gap: 12px; background: rgba(255, 255, 255, 0.05); padding: 12px; border-radius: 10px; margin-bottom: 25px; border: 1px solid rgba(255, 255, 255, 0.1); }
        .profile-img { width: 38px; height: 38px; background: #ffc107; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; color: #1a1a1a; overflow: hidden; }
        .profile-img img { width: 100%; height: 100%; object-fit: cover; }
        .admin-info h4 { margin: 0; color: white; font-size: 14px; font-weight: 600; }
        .admin-info small { color: #aaa; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; }
        
        .sidebar .badge { background-color: #dc3545; color: white; padding: 2px 8px; border-radius: 50px; font-size: 11px; float: right; margin-top: 2px; }

        .logout-link { background: #dc3545; color: white !important; text-align: center; margin-top: 50px !important; font-weight: bold; border-radius: 12px; }
        .logout-link:hover { background: #c82333; transform: scale(1.02); }

        /* Main Content */
        .main-content { flex: 1; margin-left: 280px; padding: 60px; display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; }
        
        .form-card { background: white; padding: 45px; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.05); width: 100%; max-width: 500px; text-align: center; border: 1px solid #eee; }
        .form-card h2 { margin-bottom: 30px; color: var(--dark); font-weight: 800; font-size: 26px; }

        .input-group { text-align: left; margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; font-size: 14px; }
        input, select, textarea { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 10px; outline: none; transition: 0.3s; font-size: 15px; background: #fdfdfd; box-sizing: border-box; }
        input:focus, select:focus, textarea:focus { border-color: var(--primary); box-shadow: 0 0 8px rgba(255,193,7,0.1); }

        button { background: var(--dark); color: var(--primary); width: 100%; padding: 15px; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; font-size: 16px; transition: 0.3s; margin-top: 10px; text-transform: uppercase; letter-spacing: 1px; }
        button:hover { background: var(--primary); color: var(--dark); transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        
        .msg { margin-top: 20px; padding: 12px; border-radius: 8px; font-weight: 600; font-size: 14px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Halum.</h2>
    <div class="admin-profile">
        <div class="profile-img">
            <?php
            $img_path = 'uploaded_img/' . (isset($admin_data['profile_pic']) ? $admin_data['profile_pic'] : '');
            if(!empty($admin_data['profile_pic']) && file_exists($img_path)){
                echo '<img src="'.$img_path.'">';
            } else {
                echo strtoupper(substr($admin_data['full_name'], 0, 1));
            }
            ?>
        </div>
        <div class="admin-info">
            <h4><?php echo htmlspecialchars($admin_data['full_name']); ?></h4>
            <small style="text-transform: uppercase;"><?php echo str_replace('_', ' ', $current_role); ?></small>
        </div>
    </div>

    <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard Home</a>

    <?php if($current_role === 'super_admin' || $current_role === 'product_admin'): ?>
        <a href="admin.php" class="active"><i class="fas fa-plus-circle"></i> Add New Product</a>
        <a href="manage_products.php"><i class="fas fa-boxes"></i> Manage Products</a>
    <?php endif; ?>
    
    <?php if($current_role === 'super_admin' || $current_role === 'sell_communication_admin'): ?>
        <a href="view_orders.php" style="display: flex; justify-content: space-between;">
            <span><i class="fas fa-shopping-cart"></i> Customer Orders</span>
            <?php if(isset($new_orders_count) && $new_orders_count > 0){ echo '<span class="badge">'.$new_orders_count.'</span>'; } ?>
        </a>
        <a href="view_messages.php" style="display: flex; justify-content: space-between;">
            <span><i class="fas fa-envelope"></i> Support Inbox</span>
            <?php if(isset($unread_msgs_count) && $unread_msgs_count > 0){ echo '<span class="badge">'.$unread_msgs_count.'</span>'; } ?>
        </a>
    <?php endif; ?>

    <?php if($current_role === 'super_admin'): ?>
        <a href="manage_admins.php" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 10px; padding-top: 15px;">
            <span style="color: #ffc107;"><i class="fas fa-users-cog"></i> Manage Admins</span>
        </a>
        <a href="admin_profile.php"><i class="fas fa-user-cog"></i> Update Profile</a>
    <?php endif; ?>

    <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout Account</a>
</div>

<div class="main-content">
    <div class="form-card">
        <img src="halum.png" alt="Logo" style="height: 55px; margin-bottom: 15px;">
        <h2>Add New Product</h2>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label>Product Name</label>
                <input type="text" name="p_name" placeholder="Enter product name" required>
            </div>
            
            <div class="input-group">
                <label>Price (BDT)</label>
                <input type="number" name="p_price" placeholder="Enter price" required>
            </div>

            <div class="input-group">
                <label>Product Details (Description)</label>
                <textarea name="p_details" rows="4" placeholder="Write detailed description here..." required></textarea>
            </div>

            <div class="input-group">
                <label>Collection Category</label>
                <select name="p_cat" required>
                    <option value="" disabled selected>Select Collection</option>
                    <option value="Bike">Bike Collection</option>
                    <option value="Car">Car Collection</option>
                    <option value="Sharee">Sharee Collection</option>
                    <option value="Watch">Watch Collection</option>
                </select>
            </div>

            <div class="input-group">
                <label>Product Image</label>
                <input type="file" name="p_image" accept="image/*" required>
            </div>

            <button type="submit" name="add_product">🚀 Upload Product</button>
        </form>

        <?php
        if(isset($_POST['add_product'])){
            $name = mysqli_real_escape_string($conn, $_POST['p_name']);
            $price = mysqli_real_escape_string($conn, $_POST['p_price']);
            $category = mysqli_real_escape_string($conn, $_POST['p_cat']);
            $details = mysqli_real_escape_string($conn, $_POST['p_details']); // ডেসক্রিপশন ধরলাম
            
            $image_name = $_FILES['p_image']['name'];
            $target = "images/" . basename($image_name);

            // SQL কুয়েরি আপডেট করা হলো (details কলাম যোগ করা হয়েছে)
            $sql = "INSERT INTO products (name, price, details, category, image) VALUES ('$name', '$price', '$details', '$category', '$image_name')";
            
            if(mysqli_query($conn, $sql)){
                if(move_uploaded_file($_FILES['p_image']['tmp_name'], $target)){
                    echo "<div class='msg' style='background:#e8f5e9; color:#2e7d32;'>✅ Product added to $category Collection!</div>";
                }
            } else {
                echo "<div class='msg' style='background:#ffebee; color:#c62828;'>❌ Error: " . mysqli_error($conn) . "</div>";
            }
        }
        ?>

        <div style="margin-top: 25px; border-top: 1px solid #eee; padding-top: 20px;">
            <a href="index.php" style="color: #666; text-decoration: none; font-size: 14px; margin-right: 15px;"><i class="fas fa-home"></i> View Shop</a>
            <a href="manage_products.php" style="color: #666; text-decoration: none; font-size: 14px;"><i class="fas fa-boxes"></i> Manage Inventory</a>
        </div>
    </div>
</div>

</body>
</html>