<?php
// ১. হেডার ইনক্লুড করা হলো 
include 'header.php';

// যদি আগে থেকেই লগইন করা থাকে, তাহলে সরাসরি হোমপেজে পাঠিয়ে দেবে
if(isset($_SESSION['user_id'])){
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

$message = [];

if(isset($_POST['submit'])){
    $email = trim($_POST['email']);
    // পাসওয়ার্ড ভেরিফিকেশন (আপনার ডাটাবেসে যদি md5 হ্যাস থাকে তাহলে md5() ব্যবহার করবেন, নইলে শুধু $_POST['password'] রাখবেন)
    $password = $_POST['password'];

    // Prepared Statement for Security
    $stmt = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
    $row = $result->fetch_assoc();
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['user_email'] = $row['email'];
    $_SESSION['user'] = $row['full_name'];
    $_SESSION['role'] = $row['role']; // এই লাইনটি অবশ্যই যোগ করবেন
    
    echo "<script>window.location.href='index.php';</script>";
}else{
        $message[] = 'Incorrect email or password!';
    }
    $stmt->close();
}
?>

<style>
    /* === Navbar Fix for Login Page === */
    nav, #navbar { 
        background: rgba(20, 20, 20, 0.9) !important; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.3) !important;
        padding: 15px 5% !important;
    }

    /* === Premium Glassmorphism Login Page === */
    .login-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        /* ব্যাকগ্রাউন্ডে একটি প্রিমিয়াম ছবি দিন (যেমন: bike.jpg বা car.jpg) */
        background: url('banners/bike.jpg') no-repeat center center/cover;
        position: relative;
        padding-top: 80px; /* ন্যাভবারের জন্য স্পেস */
    }

    /* ডার্ক ওভারলে */
    .login-wrapper::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(135deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 100%);
        z-index: 1;
    }

    /* গ্লাস কার্ড */
    .login-card {
        background: rgba(25, 25, 25, 0.4);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 50px 40px;
        border-radius: 20px;
        width: 100%;
        max-width: 450px;
        z-index: 5;
        box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        text-align: center;
        animation: fadeIn 0.8s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .login-card h2 {
        color: #fff;
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 2px;
    }
    .login-card h2 span { color: #ffc107; }
    .login-card p.subtitle { color: #ccc; margin-bottom: 30px; font-size: 14px; }

    /* ইনপুট ফিল্ড */
    .input-group { position: relative; margin-bottom: 25px; text-align: left; }
    .input-group i {
        position: absolute;
        top: 50%; left: 15px;
        transform: translateY(-50%);
        color: #ffc107;
        font-size: 18px;
    }
    .input-group input {
        width: 100%;
        padding: 15px 15px 15px 45px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        color: #fff;
        font-size: 15px;
        outline: none;
        transition: 0.3s;
    }
    .input-group input::placeholder { color: #aaa; }
    .input-group input:focus {
        border-color: #ffc107;
        background: rgba(0, 0, 0, 0.4);
        box-shadow: 0 0 15px rgba(255, 193, 7, 0.2);
    }

    /* লগইন বাটন */
    .login-btn {
        width: 100%;
        padding: 15px;
        background: #ffc107;
        color: #1a1a1a;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: 0.3s;
        margin-top: 10px;
    }
    .login-btn:hover {
        background: #fff;
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(255, 255, 255, 0.2);
    }

    /* অন্যান্য লিংক */
    .register-link { margin-top: 25px; color: #ccc; font-size: 14px; }
    .register-link a { color: #ffc107; text-decoration: none; font-weight: bold; transition: 0.3s; }
    .register-link a:hover { color: #fff; text-decoration: underline; }

    /* এরর মেসেজ */
    .error-msg {
        background: rgba(220, 53, 69, 0.8);
        color: #fff;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        font-weight: bold;
    }
</style>

<div class="login-wrapper">
    <div class="login-card">
        <h2>Welcome to <span>Halum</span></h2>
        <p class="subtitle">Sign in to access your premium account</p>

        <?php
        // এরর মেসেজ দেখানোর লজিক
        if(isset($message)){
            foreach($message as $msg){
                echo '<div class="error-msg"><i class="fas fa-exclamation-circle"></i> '.$msg.'</div>';
            }
        }
        ?>

        <form action="" method="post">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <button type="submit" name="submit" class="login-btn">Sign In <i class="fas fa-sign-in-alt" style="margin-left: 5px;"></i></button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Create Account</a>
        </div>
    </div>
</div>

<?php 
// ২. ফুটার ইনক্লুড করা হলো (লগইন পেজে ফুটার না রাখলেও হয়, তবে আপনি চাইলে রাখতে পারেন)
include 'footer.php'; 
?>