<?php
session_start();
include 'db.php'; 

// ১. হাই-লেভেল সিকিউরিটি চেক (শুধুমাত্র সুপার অ্যাডমিন এই পেজে ঢুকতে পারবে)
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin'){
    header("Location: admin_dashboard.php"); 
    exit();
}

$admin_id = $_SESSION['user_id'];
$current_role = $_SESSION['role'];
$message = [];

// বর্তমান সুপার অ্যাডমিনের তথ্য আনা (সাইডবারের জন্য)
$admin_q = mysqli_query($conn, "SELECT * FROM users WHERE id = '$admin_id'");
$admin_data = mysqli_fetch_assoc($admin_q);

// ২. নতুন অ্যাডমিন অ্যাড করার লজিক
if(isset($_POST['add_admin'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $image = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/'.$image;

    // ইমেইল চেক করা হচ্ছে
    $check_email = mysqli_query($conn, "SELECT email FROM `users` WHERE email = '$email'");
    if(mysqli_num_rows($check_email) > 0){
        $message[] = 'Error: Email already exists!';
    } else {
        $insert_query = "INSERT INTO `users`(full_name, email, phone, password, role, profile_pic) VALUES('$name', '$email', '$phone', '$password', '$role', '$image')";
        if(mysqli_query($conn, $insert_query)){
            if(!empty($image)){
                move_uploaded_file($image_tmp_name, $image_folder);
            }
            $message[] = 'Success: New Admin Added Successfully!';
        } else {
            $message[] = 'Error: Failed to add admin.';
        }
    }
}

// ৩. অ্যাডমিন রিমুভ (ডিলিট) করার লজিক
if(isset($_GET['delete'])){
    $delete_id = $_GET['delete'];
    
    // নিজেকে ডিলিট করা থেকে বিরত রাখা
    if($delete_id == $admin_id){
        $message[] = 'Error: You cannot delete your own Super Admin account!';
    } else {
        mysqli_query($conn, "DELETE FROM `users` WHERE id = '$delete_id'");
        header('location:manage_admins.php');
        exit();
    }
}

// নোটিফিকেশন কাউন্ট (সাইডবারের জন্য)
$new_orders_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'"))['total'];
$unread_msgs_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM messages WHERE is_read = 0"))['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Admins | Halum Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary: #ffc107; --dark: #1a1a1a; --bg: #f4f6f9; --danger: #dc3545; --success: #28a745; }
        body { font-family: 'Segoe UI', sans-serif; display: flex; margin: 0; background: var(--bg); color: #333; }
        
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
        
        /* সাইডবারের ব্যাজ */
        .sidebar .badge { background-color: #dc3545; color: white; padding: 2px 8px; border-radius: 50px; font-size: 11px; float: right; margin-top: 2px; }

        .logout-link { background: #dc3545; color: white !important; text-align: center; margin-top: 50px !important; font-weight: bold; border-radius: 12px; }
        .logout-link:hover { background: #c82333; transform: scale(1.02); }

        /* Main Content */
        .main-content { flex: 1; margin-left: 280px; padding: 40px; }
        .page-title { margin-bottom: 30px; font-size: 28px; font-weight: 800; color: var(--dark); border-left: 5px solid var(--primary); padding-left: 15px; }

        /* Form Card */
        .form-card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 40px; border: 1px solid #eee; }
        .form-card h3 { margin-top: 0; color: var(--dark); margin-bottom: 20px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .input-box { margin-bottom: 15px; }
        .input-box label { display: block; font-weight: 600; font-size: 13px; color: #555; margin-bottom: 8px; }
        .input-box input, .input-box select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; box-sizing: border-box; }
        .input-box input:focus, .input-box select:focus { border-color: var(--primary); box-shadow: 0 0 8px rgba(255,193,7,0.2); }
        .btn-add { background: var(--dark); color: var(--primary); padding: 12px 30px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 15px; }
        .btn-add:hover { background: #000; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

        /* Table Card */
        .table-card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #eee; }
        table { width: 100%; border-collapse: collapse; }
        th { background: var(--dark); color: var(--primary); padding: 15px; text-align: left; font-size: 14px; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; vertical-align: middle; }
        .user-cell { display: flex; align-items: center; gap: 10px; }
        .user-cell img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary); }
        .role-badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .role-super { background: #ffebee; color: #c62828; }
        .role-product { background: #e3f2fd; color: #1565c0; }
        .role-sell { background: #e8f5e9; color: #2e7d32; }
        .btn-delete { background: var(--danger); color: white; padding: 8px 15px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold; transition: 0.3s; }
        .btn-delete:hover { background: #c82333; }

        .edit-btn { background: #e7f1ff; color: #007bff; padding: 8px 15px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold; transition: 0.3s; border: 1px solid #007bff; }
        .edit-btn:hover { background: #007bff; color: white; }

        .msg-alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
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
            <a href="manage_admins.php" class="active" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 10px; padding-top: 15px;">
                <span style="color: #ffc107;"><i class="fas fa-users-cog"></i> Manage Admins</span>
            </a>
            <a href="admin_profile.php"><i class="fas fa-user-cog"></i> Update Profile</a>
        <?php endif; ?>

        <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout Account</a>
    </div>

    <div class="main-content">
        <h1 class="page-title">👑 Admin Management Hub</h1>

        <?php 
        foreach($message as $msg){
            $class = strpos($msg, 'Error') !== false ? 'msg-error' : 'msg-success';
            echo '<div class="msg-alert '.$class.'">'.$msg.'</div>';
        }
        ?>

        <div class="form-card">
            <h3>Add New Staff / Admin</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="input-box">
                        <label>Full Name</label>
                        <input type="text" name="name" placeholder="Enter full name" required>
                    </div>
                    <div class="input-box">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="Enter valid email" required>
                    </div>
                    <div class="input-box">
                        <label>Phone Number</label>
                        <input type="text" name="phone" placeholder="Enter phone number" required>
                    </div>
                    <div class="input-box">
                        <label>Assign Role</label>
                        <select name="role" required>
                            <option value="product_admin">Product Admin (Can manage products)</option>
                            <option value="sell_communication_admin">Sales & Comm. Admin (Orders & Inbox)</option>
                            <option value="super_admin">Super Admin (Full Access)</option>
                        </select>
                    </div>
                    <div class="input-box">
                        <label>Secure Password</label>
                        <input type="password" name="password" placeholder="Create a password" required>
                    </div>
                    <div class="input-box">
                        <label>Profile Picture</label>
                        <input type="file" name="image" accept="image/jpg, image/jpeg, image/png">
                    </div>
                </div>
                <button type="submit" name="add_admin" class="btn-add"><i class="fas fa-user-plus"></i> Create Admin Account</button>
            </form>
        </div>

        <div class="table-card">
            <h3>Active Admins Directory</h3>
            <table>
                <thead>
                    <tr>
                        <th>Admin Info</th>
                        <th>Contact</th>
                        <th>Assigned Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // শুধু অ্যাডমিনদের লিস্ট করা হচ্ছে (ইউজারদের বাদ দিয়ে)
                    $select_admins = mysqli_query($conn, "SELECT * FROM users WHERE role != 'user' ORDER BY id DESC");
                    if(mysqli_num_rows($select_admins) > 0){
                        while($row = mysqli_fetch_assoc($select_admins)){
                            // ডাইনামিক ব্যাজ ক্লাস
                            $role_class = 'role-product';
                            if($row['role'] == 'super_admin') $role_class = 'role-super';
                            if($row['role'] == 'sell_communication_admin') $role_class = 'role-sell';
                    ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($row['email']); ?><br>
                            <small style="color: #777;"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['phone']); ?></small><br>
                            <small style="color: #555; background: #eee; padding: 2px 5px; border-radius: 4px;">
                                <i class="fas fa-key"></i> 
                                <span id="pwd_<?php echo $row['id']; ?>">••••••••</span>
                                <i class="fas fa-eye" style="cursor:pointer; color:#007bff; margin-left:5px;" title="Show Password" onclick="togglePwd('pwd_<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['password']); ?>', this)"></i>
                            </small>
                        </td>
                        <td>
                            <span class="role-badge <?php echo $role_class; ?>">
                                <?php echo str_replace('_', ' ', $row['role']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if($row['id'] != $admin_id): ?>
                                <div style="display: flex; gap: 5px;">
                                    <a href="edit_admin.php?id=<?php echo $row['id']; ?>" class="edit-btn"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="manage_admins.php?delete=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to completely remove this admin?');">
                                        <i class="fas fa-trash"></i> Remove
                                    </a>
                                </div>
                            <?php else: ?>
                                <span style="color:#aaa; font-size: 12px; font-weight:bold;">Current User</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center; padding: 20px;'>No admins found!</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<script>
function togglePwd(spanId, actualPwd, iconElement) {
    var pwdSpan = document.getElementById(spanId);
    if(pwdSpan.innerText === '••••••••') {
        pwdSpan.innerText = actualPwd;
        iconElement.classList.remove('fa-eye');
        iconElement.classList.add('fa-eye-slash');
    } else {
        pwdSpan.innerText = '••••••••';
        iconElement.classList.remove('fa-eye-slash');
        iconElement.classList.add('fa-eye');
    }
}
</script>
</body>
</html>