<?php
// ১. হেডার ইনক্লুড করা হলো 
include 'header.php';

// যদি আগে থেকেই লগইন করা থাকে, সরাসরি হোমপেজে পাঠিয়ে দেবে
if(isset($_SESSION['user_id'])){
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

$message = [];

if(isset($_POST['submit'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    
    // পাসওয়ার্ড থেকে md5() সরিয়ে দেওয়া হয়েছে যাতে সরাসরি টেক্সট হিসেবে সেভ হয়
    $pass = $_POST['password']; 
    $cpass = $_POST['cpassword'];
    
    // ছবি আপলোডের ভেরিয়েবল
    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/'.$image;

    // ইমেইল চেক (Prepared Statement)
    $stmt = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows > 0){
        $message[] = 'This email is already registered!';
    } else {
        if($pass != $cpass){
            $message[] = 'Passwords do not match!';
        } elseif(!empty($image) && $image_size > 2000000){
            $message[] = 'Image size is too large! (Max 2MB)';
        } else {
            // যদি ছবি না দেয়, ডিফল্ট ছবি সেট হবে
            if(empty($image)){
                $image = 'default_user.png';
            }
            
            // ডাটাবেসে ইউজার সেভ করা (পাসওয়ার্ড সরাসরি $pass ভেরিয়েবল থেকে যাবে)
            $insert_stmt = $conn->prepare("INSERT INTO `users` (full_name, email, password, profile_pic) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssss", $name, $email, $pass, $image);
            
            if($insert_stmt->execute()){
                if(!empty($image) && $image != 'default_user.png'){
                    move_uploaded_file($image_tmp_name, $image_folder);
                }
                echo "<script>
                        alert('Registration successful! Please login now.');
                        window.location.href='login.php';
                      </script>";
                exit();
            } else {
                $message[] = 'Registration failed!';
            }
            $insert_stmt->close();
        }
    }
    $stmt->close();
}
?>

<style>
    /* === Navbar Fix for Register Page === */
    nav, #navbar { 
        background: rgba(20, 20, 20, 0.9) !important; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.3) !important;
        padding: 15px 5% !important;
    }

    /* === Premium Glassmorphism Register Page === */
    .register-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: url('banners/watch.jpg') no-repeat center center/cover;
        position: relative;
        padding: 120px 20px 80px; 
    }

    .register-wrapper::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(135deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.5) 100%);
        z-index: 1;
    }

    .register-card {
        background: rgba(25, 25, 25, 0.4);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 40px;
        border-radius: 20px;
        width: 100%;
        max-width: 500px;
        z-index: 5;
        box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        text-align: center;
        animation: fadeIn 0.8s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .register-card h2 { color: #fff; font-size: 2rem; font-weight: 800; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 2px; }
    .register-card h2 span { color: #ffc107; }
    .register-card p.subtitle { color: #ccc; margin-bottom: 25px; font-size: 14px; }

    .input-group { position: relative; margin-bottom: 20px; text-align: left; }
    .input-group i { position: absolute; top: 50%; left: 15px; transform: translateY(-50%); color: #ffc107; font-size: 16px; }
    .input-group input { width: 100%; padding: 14px 15px 14px 45px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 10px; color: #fff; font-size: 15px; outline: none; transition: 0.3s; }
    .input-group input:focus { border-color: #ffc107; background: rgba(0, 0, 0, 0.4); box-shadow: 0 0 15px rgba(255, 193, 7, 0.2); }

    .file-upload-group { position: relative; margin-bottom: 25px; text-align: left; }
    .file-upload-group label { display: block; color: #ffc107; font-size: 13px; font-weight: bold; margin-bottom: 8px; }
    .file-upload-group input[type="file"] { width: 100%; padding: 10px; background: rgba(255, 255, 255, 0.05); border: 1px dashed rgba(255, 255, 255, 0.3); border-radius: 10px; color: #ccc; cursor: pointer; }

    .register-btn { width: 100%; padding: 15px; background: #ffc107; color: #1a1a1a; border: none; border-radius: 10px; font-size: 16px; font-weight: 900; text-transform: uppercase; cursor: pointer; transition: 0.3s; }
    .register-btn:hover { background: #fff; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(255, 255, 255, 0.2); }

    .login-link { margin-top: 25px; color: #ccc; font-size: 14px; }
    .login-link a { color: #ffc107; text-decoration: none; font-weight: bold; }

    .error-msg { background: rgba(220, 53, 69, 0.8); color: #fff; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
</style>

<div class="register-wrapper">
    <div class="register-card">
        <h2>Join <span>Halum</span></h2>
        <p class="subtitle">Create an account to start your premium journey</p>

        <?php
        if(isset($message)){
            foreach($message as $msg){
                echo '<div class="error-msg"><i class="fas fa-exclamation-circle"></i> '.$msg.'</div>';
            }
        }
        ?>

        <form action="" method="post" enctype="multipart/form-data">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="name" placeholder="Full Name" required>
            </div>

            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Create Password" required>
            </div>

            <div class="input-group">
                <i class="fas fa-key"></i>
                <input type="password" name="cpassword" placeholder="Confirm Password" required>
            </div>

            <div class="file-upload-group">
                <label><i class="fas fa-camera"></i> Profile Picture (Optional)</label>
                <input type="file" name="image" accept="image/jpg, image/jpeg, image/png">
            </div>
            
            <button type="submit" name="submit" class="register-btn">Create Account <i class="fas fa-user-plus" style="margin-left: 5px;"></i></button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Sign In Here</a>
        </div>
    </div>
</div>

<?php 
include 'footer.php'; 
?>