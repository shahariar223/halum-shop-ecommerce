<?php 
include 'db.php'; 
session_start(); 

// ১. একদম বেসিক লগইন চেক
if(!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

// ২. রোল চেক (Super Admin এবং Sell Communication Admin এক্সেস পাবে)
if(!isset($_SESSION['role']) || $_SESSION['role'] === 'user'){
    header("Location: index.php"); 
    exit();
}

$current_role = $_SESSION['role'];
$current_page = basename($_SERVER['PHP_SELF']);

// রোল ভিত্তিক পেজ পারমিশন
if($current_role !== 'super_admin'){
    $support_pages = ['view_orders.php', 'view_messages.php', 'update_status.php'];
    if(in_array($current_page, $support_pages) && $current_role !== 'sell_communication_admin'){
        header("Location: admin_dashboard.php");
        exit();
    }
}

// ৩. স্ট্যাটাস আপডেট লজিক
if(isset($_POST['update_status_btn'])){
    $update_id = $_POST['order_id'];
    $update_status = $_POST['new_status'];
    mysqli_query($conn, "UPDATE `orders` SET status = '$update_status' WHERE id = '$update_id'");
    header('location:view_orders.php');
    exit();
}

// Accept/Reject বাটন লজিক
if(isset($_GET['id']) && isset($_GET['status'])){
    $order_id = $_GET['id'];
    $new_st = $_GET['status'];
    mysqli_query($conn, "UPDATE `orders` SET status = '$new_st' WHERE id = '$order_id'");
    header('location:view_orders.php');
    exit();
}

// ৪. সার্চ লজিক
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// বর্তমান অ্যাডমিনের তথ্য আনা
$admin_id = $_SESSION['user_id'];
$admin_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = '$admin_id'"));

// নোটিফিকেশন কাউন্ট
$new_orders_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'"))['total'];
$unread_msgs_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM messages WHERE is_read = 0"))['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders | Halum Admin</title>
    <link rel="icon" type="image/png" href="halum.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary: #ffc107; --dark: #1a1a1a; --bg: #f4f6f9; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; display: flex; margin: 0; background: var(--bg); color: #333; overflow-x: hidden; }
        
        /* === Universal Sidebar Style (Consistent with your screenshots) === */
        .sidebar { width: 280px; background: #1a1a1a; color: white; height: 100vh; padding: 40px 20px; position: fixed; box-shadow: 4px 0 10px rgba(0,0,0,0.1); overflow-y: auto; }
        .sidebar h2 { color: #ffc107; font-size: 28px; margin-bottom: 40px; text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .sidebar a { display: block; color: #aaa; padding: 15px 20px; text-decoration: none; border-radius: 12px; margin-bottom: 8px; transition: 0.3s; font-size: 15px; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,193,7,0.1); color: #ffc107; font-weight: bold; }
        
        .admin-profile { display: flex; align-items: center; gap: 12px; background: rgba(255, 255, 255, 0.05); padding: 12px; border-radius: 10px; margin-bottom: 25px; border: 1px solid rgba(255, 255, 255, 0.1); }
        .profile-img { width: 38px; height: 38px; background: #ffc107; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; color: #1a1a1a; overflow: hidden; }
        .profile-img img { width: 100%; height: 100%; object-fit: cover; }
        
        .sidebar .badge { background-color: #dc3545; color: white; padding: 2px 8px; border-radius: 50px; font-size: 11px; float: right; margin-top: 2px; }
        .logout-link { background: #dc3545; color: white !important; text-align: center; margin-top: 50px !important; font-weight: bold; border-radius: 12px; }
        .logout-link:hover { background: #c82333; transform: scale(1.02); }

        /* === Main Content Area === */
        .main-content { flex: 1; margin-left: 280px; padding: 50px; }
        .page-header { margin-bottom: 40px; border-left: 5px solid var(--primary); padding-left: 20px; }
        .page-header h1 { font-size: 2.2rem; font-weight: 800; color: var(--dark); margin: 0; }

        /* Search Section */
        .search-container { display: flex; gap: 10px; margin-bottom: 40px; max-width: 600px; }
        .search-container input { flex: 1; padding: 12px 20px; border-radius: 10px; border: 1px solid #ddd; outline: none; background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .search-btn { background: var(--dark); color: var(--primary); border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; font-weight: bold; transition: 0.3s; }

        /* === Grid and Card System (3 cards per row) === */
        .order-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; }
        .order-card { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #eee; position: relative; }
        
        .card-id { position: absolute; top: 15px; right: 15px; font-size: 10px; font-weight: 800; color: #bbb; background: #f8f9fa; padding: 3px 8px; border-radius: 50px; }
        .order-card h3 { margin-bottom: 15px; color: var(--dark); font-size: 17px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #f1f1f1; padding-bottom: 10px; }
        
        .info-item { display: flex; margin-bottom: 8px; font-size: 13px; }
        .label { width: 80px; font-weight: 700; color: #999; }
        .value { flex: 1; color: #333; font-weight: 600; }

        .bill { font-size: 1.2rem; color: #28a745; font-weight: 900; margin: 15px 0 10px; display: block; border-top: 1px dashed #eee; padding-top: 12px; }
        
        .status-badge { padding: 5px 12px; border-radius: 50px; font-size: 9px; font-weight: 900; text-transform: uppercase; display: inline-block; margin-bottom: 15px; }
        .pending { background: #fff3cd; color: #856404; }
        .accepted { background: #e3f2fd; color: #0d6efd; }
        .shipped { background: #d1ecf1; color: #0c5460; }
        .delivered { background: #d4edda; color: #155724; }
        .cancelled, .rejected { background: #f8d7da; color: #721c24; }

        .action-form { display: flex; gap: 8px; border-top: 1px solid #f1f1f1; padding-top: 15px; }
        select { flex: 1; padding: 8px; border-radius: 8px; border: 1px solid #ddd; font-size: 12px; font-weight: 600; }
        .btn-update { background: var(--dark); color: var(--primary); border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; font-weight: 800; font-size: 11px; }

        .completed { background: #f8f9fa; text-align: center; padding: 10px; border-radius: 8px; color: #ccc; font-weight: 800; font-size: 11px; border-top: 1px solid #f1f1f1; margin-top: 15px; }

        @media (max-width: 1400px) { .order-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 900px) { .order-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Halum.</h2>
    <div class="admin-profile">
        <div class="profile-img">
            <?php echo !empty($admin_data['profile_pic']) ? '<img src="uploaded_img/'.$admin_data['profile_pic'].'">' : strtoupper(substr($admin_data['full_name'], 0, 1)); ?>
        </div>
        <div class="admin-info">
            <h4 style="margin:0; font-size:13px;"><?php echo htmlspecialchars($admin_data['full_name']); ?></h4>
            <small style="color:#aaa; font-size:9px; text-transform:uppercase;"><?php echo str_replace('_', ' ', $current_role); ?></small>
        </div>
    </div>
    
    <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard Home</a>
    
    <?php if($current_role === 'super_admin' || $current_role === 'product_admin'): ?>
        <a href="admin.php"><i class="fas fa-plus-circle"></i> Add New Product</a>
        <a href="manage_products.php"><i class="fas fa-boxes"></i> Manage Products</a>
    <?php endif; ?>
    
    <?php if($current_role === 'super_admin' || $current_role === 'sell_communication_admin'): ?>
        <a href="view_orders.php" class="active"><span><i class="fas fa-shopping-cart"></i> Customer Orders</span> <span class="badge"><?php echo $new_orders_count; ?></span></a>
        <a href="view_messages.php"><span><i class="fas fa-envelope"></i> Support Inbox</span> <span class="badge"><?php echo $unread_msgs_count; ?></span></a>
    <?php endif; ?>

    <?php if($current_role === 'super_admin'): ?>
        <a href="manage_admins.php" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 10px; padding-top: 15px;"><i class="fas fa-users-cog"></i> Manage Admins</a>
        <a href="admin_profile.php"><i class="fas fa-user-cog"></i> Update Profile</a>
    <?php endif; ?>

    <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout Account</a>
</div>

<div class="main-content">
    <div class="page-header">
        <h1>Order Management</h1>
        <p style="color: #888; font-size: 14px;">Monitor and update customer orders in real-time.</p>
    </div>

    <form action="" method="GET" class="search-container">
        <input type="text" name="search" placeholder="Search by Order ID (#) or Customer Name..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="search-btn"><i class="fas fa-search"></i> Search</button>
        <?php if($search != ''): ?>
            <a href="view_orders.php" style="background:#eee; padding:12px; border-radius:10px; color:#333; text-decoration:none; font-weight:bold; font-size:14px;">Clear</a>
        <?php endif; ?>
    </form>

    <div class="order-grid">
        <?php
        // সার্চ কুয়েরি (id অথবা full_name দিয়ে খোঁজা হবে)
        $query = "SELECT * FROM `orders` WHERE id LIKE '%$search%' OR full_name LIKE '%$search%' ORDER BY id DESC";
        $res = mysqli_query($conn, $query);

        if(mysqli_num_rows($res) > 0){
            while($row = mysqli_fetch_assoc($res)){
                $st_class = strtolower($row['status']);
        ?>
        <div class="order-card">
            <div class="card-id">#ORD-<?php echo $row['id']; ?></div>
            <h3><i class="fas fa-user-circle"></i> Customer Details</h3>
            
            <div class="info-item">
                <div class="label">Name:</div>
                <div class="value"><?php echo htmlspecialchars($row['full_name']); ?></div>
            </div>
            <div class="info-item">
                <div class="label">Phone:</div>
                <div class="value"><?php echo htmlspecialchars($row['phone']); ?></div>
            </div>
            <div class="info-item">
                <div class="label">Address:</div>
                <div class="value"><?php echo htmlspecialchars($row['address']); ?></div>
            </div>
            <div class="info-item">
                <div class="label">Items:</div>
                <div class="value" style="color:#777; font-style:italic; font-size:12px;"><?php echo htmlspecialchars($row['total_products']); ?></div>
            </div>
            
            <span class="bill">Bill: <?php echo number_format($row['total_price'], 2); ?> BDT</span>
            <span class="status-badge <?php echo $st_class; ?>"><?php echo $row['status']; ?></span>

            <?php if($row['status'] == 'Delivered' || $row['status'] == 'Cancelled'): ?>
                <div class="completed">ORDER PROCESS COMPLETED</div>
            <?php else: ?>
                <form action="" method="POST" class="action-form">
                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                    <select name="new_status">
                        <option value="Pending" <?php if($row['status']=='Pending') echo 'selected'; ?>>Pending</option>
                        <option value="Accepted" <?php if($row['status']=='Accepted') echo 'selected'; ?>>Accepted</option>
                        <option value="Shipped" <?php if($row['status']=='Shipped') echo 'selected'; ?>>Shipped</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                    <button type="submit" name="update_status_btn" class="btn-update">Update</button>
                </form>
            <?php endif; ?>
        </div>
        <?php
            }
        } else {
            echo "<p style='color:#999; text-align:center; grid-column: span 3; padding: 50px;'>No orders found matching your search.</p>";
        }
        ?>
    </div>
</div>

</body>
</html>