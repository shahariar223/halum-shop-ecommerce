<?php
session_start();
include 'db.php'; 

// ১. লগইন চেক (লগইন না থাকলে লগইন পেজে পাঠাবে)
if(!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

// ২. রোল ভেরিফিকেশন (ইউজার যদি অ্যাডমিন প্যানেলের সদস্য না হয়)
if(!isset($_SESSION['role']) || $_SESSION['role'] == 'user'){
    header("Location: index.php"); 
    exit();
}

$current_role = $_SESSION['role']; // ডাটাবেস থেকে পাওয়া রোল: super_admin, product_manager, support_staff

// ৩. বর্তমান অ্যাডমিনের তথ্য আনা (ছবি ও নাম দেখানোর জন্য)
$admin_id = $_SESSION['user_id'];
$admin_q = mysqli_query($conn, "SELECT * FROM users WHERE id = '$admin_id'");
$admin_data = mysqli_fetch_assoc($admin_q);

// ৪. ডাটাবেস থেকে ডাইনামিক পরিসংখ্যান সংগ্রহ
$order_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"))['total'];
$msg_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM messages"))['total'];
$product_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products"))['total'];

// ৫. নতুন নোটিফিকেশন চেক (পেন্ডিং অর্ডার এবং আনরিড মেসেজ)
$new_orders_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'"))['total'];
$unread_msgs_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM messages WHERE is_read = 0"))['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halum Admin | Premium Dashboard</title>
    <link rel="icon" type="image/png" href="halum.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary: #ffc107; --dark: #1a1a1a; --success: #28a745; --light: #f4f6f9; }
        body { font-family: 'Segoe UI', Roboto, sans-serif; display: flex; margin: 0; background-color: var(--light); color: #333; overflow-x: hidden; }
        
        /* === Sidebar Style === */
        .sidebar { width: 280px; background: var(--dark); color: white; height: 100vh; padding: 40px 20px; position: fixed; }
        .sidebar h2 { color: var(--primary); font-size: 28px; margin-bottom: 40px; text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .sidebar a { display: block; color: #aaa; padding: 15px 20px; text-decoration: none; border-radius: 12px; margin-bottom: 8px; transition: 0.3s; font-size: 15px; }
        .sidebar a:hover { background: rgba(255,193,7,0.1); color: var(--primary); transform: translateX(5px); }
        .sidebar a.active { background: rgba(255,193,7,0.2); color: var(--primary); font-weight: bold; }
        
        .badge { background-color: #dc3545; color: white; padding: 2px 10px; border-radius: 50px; font-size: 12px; font-weight: bold; }
        
        .admin-profile { display: flex; align-items: center; gap: 12px; background: rgba(255, 255, 255, 0.05); padding: 12px; border-radius: 10px; margin-bottom: 25px; border: 1px solid rgba(255, 255, 255, 0.1); }
        .profile-img { width: 38px; height: 38px; background: var(--primary); border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; color: #1a1a1a; overflow: hidden; }
        .profile-img img { width: 100%; height: 100%; object-fit: cover; }
        .admin-info h4 { margin: 0; color: white; font-size: 14px; font-weight: 600; }
        .admin-info small { color: #aaa; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; }

        /* === Main Content Style === */
        .main-content { flex: 1; padding: 40px; margin-left: 280px; }
        .welcome-card { background: #fff; padding: 35px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-left: 6px solid var(--primary); }
        .welcome-card h1 { color: #1a1a1a; font-size: 32px; margin-bottom: 10px; font-weight: 800; text-transform: capitalize; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; margin-top: 40px; }
        .stat-box { background: #fff; padding: 35px; border-radius: 20px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.03); transition: 0.4s; border: 1px solid #eee; position: relative; }
        .stat-box:hover { transform: translateY(-10px); border-color: var(--primary); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }
        .stat-box h3 { margin: 0; color: #1a1a1a; font-size: 48px; font-weight: 800; }
        
        .notif-label { display: inline-block; margin-top: 15px; font-size: 12px; font-weight: 800; padding: 6px 15px; border-radius: 50px; }
        .order-notif { background: #fff4e5; color: #ff8800; }
        .msg-notif { background: #ffebeb; color: #dc3545; }
        .live-label { background: #e8f5e9; color: #28a745; }
        
        .logout-link { background: #dc3545; color: white !important; text-align: center; margin-top: 50px !important; font-weight: bold; border-radius: 12px; }
        .logout-link:hover { background: #c82333; transform: scale(1.02); }
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

    <a href="admin_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> Dashboard Home</a>

    <?php if($current_role === 'super_admin' || $current_role === 'product_admin'): ?>
        <a href="admin.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>"><i class="fas fa-plus-circle"></i> Add New Product</a>
        <a href="manage_products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_products.php' ? 'active' : ''; ?>"><i class="fas fa-boxes"></i> Manage Products</a>
    <?php endif; ?>
    
    <?php if($current_role === 'super_admin' || $current_role === 'sell_communication_admin'): ?>
        <a href="view_orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'view_orders.php' ? 'active' : ''; ?>" style="display: flex; justify-content: space-between;">
            <span><i class="fas fa-shopping-cart"></i> Customer Orders</span>
            <?php if(isset($new_orders_count) && $new_orders_count > 0){ echo '<span class="badge">'.$new_orders_count.'</span>'; } ?>
        </a>
        <a href="view_messages.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'view_messages.php' ? 'active' : ''; ?>" style="display: flex; justify-content: space-between;">
            <span><i class="fas fa-envelope"></i> Support Inbox</span>
            <?php if(isset($unread_msgs_count) && $unread_msgs_count > 0){ echo '<span class="badge">'.$unread_msgs_count.'</span>'; } ?>
        </a>
    <?php endif; ?>

    <?php if($current_role === 'super_admin'): ?>
        <a href="manage_admins.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_admins.php' ? 'active' : ''; ?>" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 10px; padding-top: 15px;">
            <span style="color: #ffc107;"><i class="fas fa-users-cog"></i> Manage Admins</span>
        </a>
        <a href="admin_profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_profile.php' ? 'active' : ''; ?>"><i class="fas fa-user-cog"></i> Update Profile</a>
    <?php endif; ?>

    <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout Account</a>
</div>

    <div class="main-content">
        <div class="welcome-card">
            <h1>Welcome Back, <?php echo str_replace('_', ' ', $current_role); ?> 👋</h1>
            <p>আপনার রোল অনুযায়ী ড্যাশবোর্ড প্রস্তুত। এখানে আপনি আপনার নির্ধারিত কাজগুলো পরিচালনা করতে পারবেন।</p>
        </div>

        <div class="stats-grid">
            <?php if($current_role == 'super_admin' || $current_role == 'sell_communication_admin'): ?>
                <div class="stat-box">
                    <h3><?php echo $order_count; ?></h3>
                    <span>Total Orders</span>
                    <div class="notif-label <?php echo ($new_orders_count > 0) ? 'order-notif' : 'live-label'; ?>">
                        <?php echo ($new_orders_count > 0) ? "⚠️ $new_orders_count New Pending" : "✓ All Processed"; ?>
                    </div>
                </div>
                
                <div class="stat-box">
                    <h3><?php echo $msg_count; ?></h3>
                    <span>Support Messages</span>
                    <div class="notif-label <?php echo ($unread_msgs_count > 0) ? 'msg-notif' : 'live-label'; ?>">
                        <?php echo ($unread_msgs_count > 0) ? "🔴 $unread_msgs_count Unread" : "✓ Inbox Clear"; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if($current_role == 'super_admin' || $current_role == 'product_admin'): ?>
                <div class="stat-box">
                    <h3><?php echo $product_count; ?></h3>
                    <span>Active Products</span>
                    <div class="notif-label live-label">📦 Live Inventory</div>
                </div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>