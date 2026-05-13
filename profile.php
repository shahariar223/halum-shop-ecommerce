<?php 
// ১. হেডার ইনক্লুড করা হলো (এতেই ডাটাবেস, সেশন এবং ন্যাভবার চলে আসবে)
include 'header.php'; 

if(!isset($_SESSION['user_id'])){
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$u_id = $_SESSION['user_id'];
$msg = "";

// ইউজার ডাটা ফেচ করা
$user_res = mysqli_query($conn, "SELECT * FROM users WHERE id = '$u_id'");
$user = mysqli_fetch_assoc($user_res);

// ২. প্রোফাইল আপডেট লজিক
if(isset($_POST['update_profile'])){
    $name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $new_pass = $_POST['new_pass'];
    $re_pass = $_POST['re_pass'];

    // ফোন নম্বর ট্র্যাকিং (পুরানো নম্বর সেভ রাখা)
    if($phone != $user['phone']){
        $old_phone = $user['phone'];
        mysqli_query($conn, "UPDATE users SET old_phone = '$old_phone' WHERE id = '$u_id'");
    }

    // প্রোফাইল পিকচার আপলোড লজিক
    $profile_pic = $user['profile_pic']; 
    if(!empty($_FILES['update_image']['name'])){
        $image_name = $_FILES['update_image']['name'];
        $image_tmp_name = $_FILES['update_image']['tmp_name'];
        $image_folder = 'uploaded_img/'.$image_name;

        if(move_uploaded_file($image_tmp_name, $image_folder)){
            $profile_pic = $image_name;
        }
    }

    // মেইন আপডেট কোয়েরি
    mysqli_query($conn, "UPDATE users SET full_name = '$name', phone = '$phone', profile_pic = '$profile_pic' WHERE id = '$u_id'");

    // পাসওয়ার্ড পরিবর্তনের লজিক
    if(!empty($new_pass)){
        if($new_pass === $re_pass){
            mysqli_query($conn, "UPDATE users SET password = '$new_pass' WHERE id = '$u_id'");
            $msg = "<div class='alert success'>✅ Profile & Password updated!</div>";
        } else {
            $msg = "<div class='alert error'>❌ Passwords do not match!</div>";
        }
    } else {
        $msg = "<div class='alert success'>✅ Profile updated successfully!</div>";
    }
    
    // ডাটা রিফ্রেশ করার জন্য
    echo "<script>setTimeout(function(){ window.location.href='profile.php'; }, 1500);</script>";
}
?>

<style>
    /* === Profile Page Specific Design === */
    body { background-color: #f4f7f6; }
    
    /* Navbar Fix: যেহেতু হিরো ইমেজ নেই, ন্যাভবার কালো রাখা হলো */
    nav, #navbar { background: #1a1a1a !important; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }

    .profile-wrapper {
        min-height: 80vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 120px 20px 60px;
    }

    .profile-card { 
        background: white; width: 100%; max-width: 480px; padding: 40px; 
        border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.05); text-align: center;
        border: 1px solid #eee;
    }

    /* Avatar styling with upload icon */
    .avatar-wrapper { position: relative; width: 130px; height: 130px; margin: 0 auto 25px; }
    .avatar-wrapper img { 
        width: 100%; height: 100%; border-radius: 50%; object-fit: cover; 
        border: 4px solid #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
    }
    
    .upload-label {
        position: absolute; bottom: 5px; right: 5px; background: #ffc107;
        width: 38px; height: 38px; border-radius: 50%; display: flex;
        justify-content: center; align-items: center; cursor: pointer;
        border: 3px solid white; font-size: 18px; transition: 0.3s;
    }
    .upload-label:hover { transform: scale(1.1); background: #1a1a1a; color: #ffc107; }

    .form-group { text-align: left; margin-bottom: 18px; }
    label { font-size: 11px; font-weight: 800; color: #888; display: block; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; }
    input { 
        width: 100%; padding: 14px; border: 2px solid #f0f0f0; border-radius: 12px; 
        outline: none; transition: 0.3s; font-size: 15px; color: #333;
    }
    input:focus { border-color: #ffc107; background: #fff; }
    input[readonly] { background: #f9f9f9; color: #aaa; cursor: not-allowed; }

    .btn-save { 
        width: 100%; background: #1a1a1a; color: #ffc107; padding: 16px; 
        border: none; border-radius: 50px; font-weight: 800; cursor: pointer; 
        margin-top: 20px; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px;
    }
    .btn-save:hover { background: #000; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.15); }

    .alert { padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; font-weight: 600; }
    .success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
    .error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
</style>

<div class="profile-wrapper">
    <div class="profile-card">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="avatar-wrapper">
                <img src="uploaded_img/<?php echo !empty($user['profile_pic']) ? $user['profile_pic'] : 'default_user.png'; ?>" alt="Profile">
                <label for="update_image" class="upload-label">
                    <i class="fas fa-camera"></i>
                </label>
                <input type="file" name="update_image" id="update_image" accept="image/*" style="display:none;">
            </div>

            <h2 style="font-weight: 900; color: #1a1a1a;">Account Settings</h2>
            <p style="color: #ffc107; font-weight: 700; margin-bottom: 30px; font-size: 14px;"><?php echo $user['email']; ?></p>

            <?php echo $msg; ?>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            </div>

            <div class="form-group">
                <label>Email Address (Immutable)</label>
                <input type="email" value="<?php echo $user['email']; ?>" readonly>
            </div>

            <div style="border-top: 1px dashed #ddd; margin: 25px 0; padding-top: 20px;">
                <div class="form-group">
                    <label>Change Password</label>
                    <input type="password" name="new_pass" placeholder="Enter new password">
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="re_pass" placeholder="Confirm new password">
                </div>
            </div>

            <button type="submit" name="update_profile" class="btn-save">Save Changes</button>
            <a href="index.php" style="display:block; margin-top:20px; color:#999; text-decoration:none; font-size:13px; font-weight:600;">
                <i class="fas fa-chevron-left"></i> Return to Store
            </a>
        </form>
    </div>
</div>

<?php 
// ৩. ফুটার ইনক্লুড করা হলো
include 'footer.php'; 
?>