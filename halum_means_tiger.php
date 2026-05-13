<?php 
session_start();
include 'db.php'; 

// ১. ডিরেক্ট রিডাইরেক্ট চেক
// যদি ইতিমধ্যে লগইন করা থাকে এবং ইউজার সাধারণ 'user' না হয়, তবে ড্যাশবোর্ডে পাঠিয়ে দাও
if(isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] !== 'user'){
    header("Location: admin_dashboard.php");
    exit();
}

if(isset($_POST['admin_login'])){
    $user_input = mysqli_real_escape_string($conn, $_POST['user_input']);
    $password = mysqli_real_escape_string($conn, $_POST['password']); 
    
    // ২. ফিক্সড কুয়েরি: এখন রোল 'user' বাদে অন্য যেকোনোটি (admin বা super_admin) খোঁজা হবে
    $query = "SELECT * FROM users WHERE (phone='$user_input' OR email='$user_input') AND role != 'user' LIMIT 1";
    $res = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($res) > 0){
        $user_data = mysqli_fetch_assoc($res);
        
        // পাসওয়ার্ড চেক
        if($password == $user_data['password']){ 
            $_SESSION['user_id'] = (string)$user_data['id'];
            $_SESSION['user'] = $user_data['full_name']; 
            $_SESSION['user_email'] = $user_data['email'];
            
            // ৩. ডাইনামিক রোল সেট: ডাটাবেস থেকে পাওয়া আসল রোলটি (যেমন: super_admin) সেভ হবে
            $_SESSION['role'] = $user_data['role']; 

            header("Location: admin_dashboard.php"); // সোজা ড্যাশবোর্ডে
            exit();
        } else {
            $error = "Wrong Password! Tigers don't make mistakes."; 
        }
    } else {
        $error = "Access Denied! You are not an Admin.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Halum Admin | Restricted Area</title>
    <link rel="icon" type="image/png" href="halum.png">
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            display: flex; justify-content: center; align-items: center; 
            height: 100vh; margin: 0; background: #111; color: #fff; 
        }
        .login-box { 
            background: #1a1a1a; padding: 40px; border-radius: 15px; 
            width: 100%; max-width: 380px; text-align: center; 
            border: 1px solid #333; box-shadow: 0 0 20px rgba(255, 193, 7, 0.1);
        }
        h2 { color: #ffc107; margin-bottom: 5px; }
        p { color: #777; font-size: 13px; margin-bottom: 25px; }
        
        input { 
            width: 100%; padding: 12px; margin: 10px 0; 
            background: #222; border: 1px solid #333; color: #fff; 
            border-radius: 8px; box-sizing: border-box; outline: none; transition: 0.3s;
        }
        input:focus { border-color: #ffc107; background: #000; }
        
        .btn-login { 
            width: 100%; background: #ffc107; color: #000; padding: 12px; 
            border: none; border-radius: 8px; cursor: pointer; 
            font-weight: bold; margin-top: 15px; transition: 0.3s; 
        }
        .btn-login:hover { background: #e0a800; transform: scale(1.02); }
        
        .error-msg { 
            background: rgba(220, 53, 69, 0.2); color: #ff6b6b; padding: 10px; 
            border-radius: 5px; margin-bottom: 15px; font-size: 14px; border: 1px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🐯 Halum Admin</h2>
        <p>Restricted Access Only</p>
        
        <?php if(isset($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <input type="text" name="user_input" placeholder="Admin Email or Phone" required>
            <input type="password" name="password" placeholder="Secure Password" required>
            <button type="submit" name="admin_login" class="btn-login">Unlock Dashboard</button>
        </form>
        
        <br>
        <a href="index.php" style="color: #555; text-decoration: none; font-size: 12px;">← Back to Shop</a>
    </div>
</body>
</html>