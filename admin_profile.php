<?php 
include 'db.php'; 
session_start(); 

// ১. একদম বেসিক লগইন চেক
if(!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

// ২. রোল চেক - শুধুমাত্র super_admin এই পেজে ঢুকতে পারবে
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin'){
    header("Location: admin_dashboard.php"); 
    exit();
}

$admin_id = $_SESSION['user_id'];
$current_role = $_SESSION['role']; // এই ভেরিয়েবলটি সাইডবারের জন্য খুবই জরুরি
$message = [];

// ৩. সাইডবারের জন্য বর্তমান অ্যাডমিনের তথ্য এবং নোটিফিকেশন আনা
$admin_q = mysqli_query($conn, "SELECT * FROM users WHERE id = '$admin_id'");
$admin_data = mysqli_fetch_assoc($admin_q);

$new_orders_count = 0;
$unread_msgs_count = 0;
$order_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
if($order_q) { $new_orders_count = mysqli_fetch_assoc($order_q)['total']; }
$msg_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM messages WHERE is_read = 0");
if($msg_q) { $unread_msgs_count = mysqli_fetch_assoc($msg_q)['total']; }

// ৪. প্রোফাইল আপডেট লজিক
if(isset($_POST['update_profile'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    // বেসিক ইনফো আপডেট
    mysqli_query($conn, "UPDATE `users` SET full_name = '$name', email = '$email', phone = '$phone' WHERE id = '$admin_id'");
    $message[] = 'Profile information updated successfully!';

    // ছবি আপডেট লজিক
    if(!empty($_FILES['image']['name'])){
        $image = $_FILES['image']['name'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_folder = 'uploaded_img/'.$image;

        mysqli_query($conn, "UPDATE `users` SET profile_pic = '$image' WHERE id = '$admin_id'");
        move_uploaded_file($image_tmp_name, $image_folder);
        $message[] = 'Profile picture updated!';
    }

    // পাসওয়ার্ড আপডেট লজিক
    $old_pass = $_POST['old_pass'];
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    if(!empty($new_pass)){
        $select_pass = mysqli_query($conn, "SELECT password FROM `users` WHERE id = '$admin_id'");
        $fetch_pass = mysqli_fetch_assoc($select_pass);

        if($old_pass != $fetch_pass['password']){
            $message[] = 'Error: Old password not matched!';
        }elseif($new_pass != $confirm_pass){
            $message[] = 'Error: Confirm password not matched!';
        }else{
            mysqli_query($conn, "UPDATE `users` SET password = '$new_pass' WHERE id = '$admin_id'");
            $message[] = 'Password updated successfully!';
        }
    }
}

// ৫. ফর্মের জন্য বর্তমান ডাটা নিয়ে আসা (আপডেটের পর রিফ্রেশ ডাটা পেতে)
$select_profile = mysqli_query($conn, "SELECT * FROM `users` WHERE id = '$admin_id'");
$fetch_profile = mysqli_fetch_assoc($select_profile);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Profile | Halum Admin</title>
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

        /* === Main Content === */
        .main-content { flex: 1; padding: 40px; margin-left: 280px; display: flex; flex-direction: column; align-items: center; }
        
        .profile-card { background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 800px; border: 1px solid #eee; }
        .profile-card h1 { font-size: 24px; margin-top: 0; margin-bottom: 30px; color: var(--dark); border-bottom: 2px solid var(--primary); display: inline-block; padding-bottom: 5px; }
        
        .flex-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #555; }
        .input-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none; transition: 0.3s; box-sizing: border-box; }
        .input-group input:focus { border-color: var(--primary); box-shadow: 0 0 10px rgba(255,193,7,0.1); }

        .profile-preview { text-align: center; margin-bottom: 30px; }
        .profile-preview img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary); margin-bottom: 15px; }
        
        .btn-save { background: var(--dark); color: var(--primary); padding: 15px 40px; border: none; border-radius: 50px; font-weight: 800; cursor: pointer; transition: 0.3s; width: 100%; margin-top: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .btn-save:hover { background: #000; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        
        .msg-alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; font-size: 14px; text-align: center; width: 100%; max-width: 800px; }
        .msg-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .msg-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
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
        <a href="admin_profile.php" class="active"><i class="fas fa-user-cog"></i> Update Profile</a>
    <?php endif; ?>

    <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout Account</a>
</div>

<div class="main-content">
    
    <?php 
    // অ্যালার্ট মেসেজ দেখানোর লজিক
    foreach($message as $msg){
        $class = strpos($msg, 'Error') !== false ? 'msg-error' : 'msg-success';
        echo '<div class="msg-alert '.$class.'">'.$msg.'</div>';
    }
    ?>

    <div class="profile-card">
        <h1>Update Your Profile</h1>

        <form action="" method="post" enctype="multipart/form-data">
            <div class="profile-preview">
                <?php
                $pic = !empty($fetch_profile['profile_pic']) ? 'uploaded_img/'.$fetch_profile['profile_pic'] : 'halum.png'; 
                echo "<img src='$pic' alt='Admin'>";
                ?>
                <div><input type="file" name="image" accept="image/jpg, image/jpeg, image/png"></div>
            </div>

            <div class="flex-form">
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($fetch_profile['full_name']); ?>" required>
                </div>
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($fetch_profile['email']); ?>" required>
                </div>
                <div class="input-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($fetch_profile['phone']); ?>" required>
                </div>
                <div class="input-group">
                    <label>Old Password (To change)</label>
                    <input type="password" name="old_pass" placeholder="Enter old password">
                </div>
                <div class="input-group">
                    <label>New Password</label>
                    <input type="password" name="new_pass" placeholder="Enter new password">
                </div>
                <div class="input-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_pass" placeholder="Confirm new password">
                </div>
            </div>

            <button type="submit" name="update_profile" class="btn-save"><i class="fas fa-save"></i> SAVE ALL CHANGES</button>
        </form>
    </div>
</div>

</body>
</html>