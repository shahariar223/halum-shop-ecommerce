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

// ৪. প্রোডাক্ট ডিলিট লজিক (আগে ছিল না)
if(isset($_GET['delete_id'])){
    $delete_id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM `products` WHERE id = '$delete_id'");
    header('location:manage_products.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory | Halum Admin</title>
    <link rel="icon" type="image/png" href="halum.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary: #ffc107; --dark: #1a1a1a; --bg: #f4f6f9; --white: #ffffff; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; display: flex; margin: 0; background: var(--bg); color: #333; }
        
        /* Universal Sidebar Style */
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
        .main-content { flex: 1; margin-left: 280px; padding: 50px; }
        .inventory-card { background: var(--white); padding: 35px; border-radius: 25px; box-shadow: 0 15px 45px rgba(0,0,0,0.05); border: 1px solid #eee; }
        .inventory-card h2 { margin-bottom: 10px; color: var(--dark); font-weight: 800; font-size: 26px; }
        .inventory-card p { color: #777; margin-bottom: 20px; font-size: 14px; }

        /* সার্চ বার স্টাইল */
        .search-bar { display: flex; gap: 10px; margin-bottom: 25px; }
        .search-bar input { flex: 1; padding: 12px 20px; border: 1px solid #ddd; border-radius: 10px; outline: none; font-size: 14px; }
        .search-bar input:focus { border-color: var(--primary); box-shadow: 0 0 10px rgba(255,193,7,0.1); }
        .search-btn { background: var(--dark); color: var(--primary); padding: 12px 25px; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .search-btn:hover { background: #000; }
        .clear-btn { background: #eee; color: #333; padding: 12px 20px; text-decoration: none; border-radius: 10px; font-weight: bold; font-size: 14px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: var(--dark); color: var(--primary); padding: 18px; text-align: left; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; vertical-align: middle; }
        tr:hover { background-color: #fcfcfc; }
        
        .btn-group { display: flex; gap: 10px; }
        .edit-btn { background: #e7f1ff; color: #007bff; text-decoration: none; padding: 8px 15px; border-radius: 8px; font-weight: 600; font-size: 13px; transition: 0.3s; border: 1px solid #007bff; }
        .edit-btn:hover { background: #007bff; color: white; }
        .remove-btn { background: #fff5f5; color: #dc3545; text-decoration: none; padding: 8px 15px; border-radius: 8px; font-weight: 600; font-size: 13px; transition: 0.3s; border: 1px solid #dc3545; }
        .remove-btn:hover { background: #dc3545; color: white; }
        .cat-badge { background: #f0f0f0; color: #555; padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: bold; }
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
        <a href="admin.php"><i class="fas fa-plus-circle"></i> Add New Product</a>
        <a href="manage_products.php" class="active"><i class="fas fa-boxes"></i> Manage Products</a>
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
        <div class="inventory-card">
            <h2>Product Inventory Management</h2>
            <p>Search, update, or remove products from your inventory.</p>

            <form action="" method="GET" class="search-bar">
                <input type="text" name="search" placeholder="Search by Product Name or Category..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i> Search</button>
                <?php if(isset($_GET['search'])): ?>
                    <a href="manage_products.php" class="clear-btn">Clear</a>
                <?php endif; ?>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Current Price</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // ৬. সার্চ লজিক সহ কোয়েরি
                    if(isset($_GET['search']) && !empty($_GET['search'])){
                        $search_item = mysqli_real_escape_string($conn, $_GET['search']);
                        // নাম বা ক্যাটাগরি যেকোনো একটি ম্যাচ করলেই ডাটা আসবে
                        $query = "SELECT * FROM products WHERE name LIKE '%{$search_item}%' OR category LIKE '%{$search_item}%' ORDER BY id DESC";
                    } else {
                        // সার্চ না করলে সব ডাটা আসবে
                        $query = "SELECT * FROM products ORDER BY id DESC";
                    }

                    $res = mysqli_query($conn, $query);

                    if(mysqli_num_rows($res) > 0) {
                        while($row = mysqli_fetch_assoc($res)) {
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                        <td><span class="cat-badge"><?php echo ($row['category'] ? htmlspecialchars($row['category']) : 'General'); ?></span></td>
                        <td><strong style="color: #28a745;"><?php echo number_format($row['price'], 2); ?> BDT</strong></td>
                        <td style="text-align: center;">
                            <div class="btn-group" style="justify-content: center;">
                                <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="edit-btn">Update</a>
                                <a href="manage_products.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to remove this product?')" class="remove-btn">Remove</a>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center; padding: 30px; color: #999;'><i class='fas fa-search'></i> No products found matching your search.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>