<?php
session_start();
include 'db.php'; 

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin'){
    header("Location: admin_dashboard.php"); 
    exit();
}

$admin_id = $_SESSION['user_id'];
$current_role = $_SESSION['role'];
$message = [];

// অ্যাডমিনের ডাটা নিয়ে আসা
if(isset($_GET['id'])){
    $edit_id = $_GET['id'];
    $edit_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$edit_id'");
    if(mysqli_num_rows($edit_query) > 0){
        $fetch_edit = mysqli_fetch_assoc($edit_query);
    } else {
        header('location:manage_admins.php');
        exit();
    }
}

// আপডেট লজিক
if(isset($_POST['update_admin'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // বেসিক ডাটা আপডেট (ইমেইল বাদে)
    mysqli_query($conn, "UPDATE `users` SET full_name = '$name', phone = '$phone', password = '$password', role = '$role' WHERE id = '$edit_id'");
    
    // ছবি আপডেট (যদি নতুন ছবি দেয়)
    if(!empty($_FILES['image']['name'])){
        $image = $_FILES['image']['name'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_folder = 'uploaded_img/'.$image;
        mysqli_query($conn, "UPDATE `users` SET profile_pic = '$image' WHERE id = '$edit_id'");
        move_uploaded_file($image_tmp_name, $image_folder);
    }

    // আপডেট শেষে রিডাইরেক্ট
    header('location:manage_admins.php');
    exit();
}

$admin_q = mysqli_query($conn, "SELECT * FROM users WHERE id = '$admin_id'");
$admin_data = mysqli_fetch_assoc($admin_q);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Admin | Halum Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary: #ffc107; --dark: #1a1a1a; --bg: #f4f6f9; }
        body { font-family: 'Segoe UI', sans-serif; display: flex; margin: 0; background: var(--bg); color: #333; }
        
        .sidebar { width: 280px; background: var(--dark); color: white; height: 100vh; padding: 40px 20px; position: fixed; }
        .sidebar h2 { color: var(--primary); text-align: center; margin-bottom: 40px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .sidebar a { display: block; color: #aaa; padding: 15px 20px; text-decoration: none; border-radius: 12px; margin-bottom: 8px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,193,7,0.2); color: var(--primary); font-weight: bold; }
        .admin-profile { display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.05); padding: 12px; border-radius: 10px; margin-bottom: 25px; }
        .profile-img { width: 38px; height: 38px; background: var(--primary); border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; color: var(--dark); overflow: hidden; }
        .profile-img img { width: 100%; height: 100%; object-fit: cover; }
        .admin-info h4 { margin: 0; color: white; font-size: 14px; }
        .admin-info small { color: #aaa; font-size: 10px; text-transform: uppercase; }

        .main-content { flex: 1; margin-left: 280px; padding: 40px; display: flex; justify-content: center; }
        .form-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 600px; border: 1px solid #eee; }
        .form-card h2 { margin-top: 0; color: var(--dark); margin-bottom: 30px; border-bottom: 2px solid var(--primary); display: inline-block; padding-bottom: 5px; }
        
        .input-box { margin-bottom: 15px; }
        .input-box label { display: block; font-weight: 600; font-size: 13px; color: #555; margin-bottom: 8px; }
        .input-box input, .input-box select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; box-sizing: border-box; }
        .input-box input:focus, .input-box select:focus { border-color: var(--primary); box-shadow: 0 0 8px rgba(255,193,7,0.2); }
        .input-box input[readonly] { background: #f0f0f0; cursor: not-allowed; color: #777; }
        
        .btn-update { background: var(--dark); color: var(--primary); padding: 15px; width: 100%; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 15px; font-size: 16px; }
        .btn-update:hover { background: #000; transform: translateY(-2px); }
        .btn-cancel { display: block; text-align: center; margin-top: 15px; color: #777; text-decoration: none; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Halum.</h2>
    <div class="admin-profile">
        <div class="profile-img">
            <?php
            $img_path = 'uploaded_img/' . ($admin_data['profile_pic'] ?? '');
            if(!empty($admin_data['profile_pic']) && file_exists($img_path)) echo '<img src="'.$img_path.'">';
            else echo strtoupper(substr($admin_data['full_name'], 0, 1));
            ?>
        </div>
        <div class="admin-info">
            <h4><?php echo htmlspecialchars($admin_data['full_name']); ?></h4>
            <small><?php echo str_replace('_', ' ', $current_role); ?></small>
        </div>
    </div>
    <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard Home</a>
    <a href="manage_admins.php" class="active"><i class="fas fa-users-cog"></i> Manage Admins</a>
</div>

<div class="main-content">
    <div class="form-card">
        <h2><i class="fas fa-user-edit"></i> Edit Admin Profile</h2>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="input-box">
                <label>Email Address (Cannot be changed)</label>
                <input type="email" value="<?php echo htmlspecialchars($fetch_edit['email']); ?>" readonly>
            </div>
            <div class="input-box">
                <label>Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($fetch_edit['full_name']); ?>" required>
            </div>
            <div class="input-box">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($fetch_edit['phone']); ?>" required>
            </div>
            <div class="input-box">
                <label>Assign Role</label>
                <select name="role" required>
                    <option value="product_admin" <?php if($fetch_edit['role'] == 'product_admin') echo 'selected'; ?>>Product Admin</option>
                    <option value="sell_communication_admin" <?php if($fetch_edit['role'] == 'sell_communication_admin') echo 'selected'; ?>>Sales & Comm. Admin</option>
                    <option value="super_admin" <?php if($fetch_edit['role'] == 'super_admin') echo 'selected'; ?>>Super Admin</option>
                </select>
            </div>
            <div class="input-box">
                <label>Password (You can view/update here)</label>
                <input type="text" name="password" value="<?php echo htmlspecialchars($fetch_edit['password']); ?>" required>
            </div>
            <div class="input-box">
                <label>Update Profile Picture (Optional)</label>
                <input type="file" name="image" accept="image/jpg, image/jpeg, image/png">
            </div>
            
            <button type="submit" name="update_admin" class="btn-update">Save Changes</button>
            <a href="manage_admins.php" class="btn-cancel">Cancel</a>
        </form>
    </div>
</div>

</body>
</html>