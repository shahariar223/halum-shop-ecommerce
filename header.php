<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
include 'db.php'; 

// ইউজারের ছবি ও নাম ডাটাবেস থেকে নিয়ে আসা
$header_data = null;
$profile_img = 'default_user.png';

if(isset($_SESSION['user_id'])){
    $user_id_header = $_SESSION['user_id'];
    $header_query = mysqli_query($conn, "SELECT full_name, profile_pic FROM `users` WHERE id = '$user_id_header'");
    if($header_query && mysqli_num_rows($header_query) > 0){
        $header_data = mysqli_fetch_assoc($header_query);
        $profile_img = (!empty($header_data['profile_pic'])) ? $header_data['profile_pic'] : 'default_user.png';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halum | Premium Online Shop</title>
    <link rel="icon" type="image/png" href="halum.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* === Global & Navbar Styles === */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { margin: 0; padding: 0; background-color: #f8f9fa; color: #333; overflow-x: hidden; }
        
        nav { display: flex; justify-content: space-between; align-items: center; padding: 20px 5%; background: transparent; position: fixed; width: 100%; top: 0; z-index: 2000; transition: all 0.4s ease; }
        nav.scrolled { background: rgba(20, 20, 20, 0.98); padding: 12px 5%; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
        .logo { flex: 1; display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo img { height: 50px; }
        .logo h2 { color: #ffc107; font-size: 28px; font-weight: 800; }
        
        .nav-links-center { flex: 1; display: flex; justify-content: center; gap: 30px; }
        .nav-links-center a { color: #fff; text-decoration: none; font-weight: 500; font-size: 15px; transition: 0.3s; }
        .nav-links-center a:hover { color: #ffc107; }
        
        .nav-right { flex: 1; display: flex; justify-content: flex-end; align-items: center; gap: 20px; }
        
/* === User Dropdown - Small & Professional Fix === */
.user-dropdown { 
    display: flex; 
    align-items: center; 
    gap: 10px; 
    padding: 5px 15px; 
    background: rgba(255, 255, 255, 0.08); 
    border-radius: 50px; 
    transition: 0.3s; 
    position: relative; 
    cursor: pointer;
    height: 45px; /* হাইট ফিক্সড করে দেওয়া হলো যাতে বড় না হয় */
}

.nav-profile-pic { 
    width: 32px !important; 
    height: 32px !important; 
    border-radius: 50%; 
    border: 2px solid #ffc107; 
    object-fit: cover; 
}

.user-name { 
    color: #ffc107; 
    font-weight: 600; 
    font-size: 13px; /* ফন্ট সাইজ একটু কমানো হলো */
    white-space: nowrap; 
}

/* ড্রপডাউন বক্স */
.dropdown-content { 
    display: none; 
    position: absolute; 
    top: 50px; /* আইকনের ঠিক নিচে */
    right: 0; 
    background: #ffffff; 
    min-width: 180px; 
    border-radius: 12px; 
    box-shadow: 0 10px 30px rgba(0,0,0,0.2); 
    z-index: 2500; 
    border: 1px solid rgba(0,0,0,0.05); 
    padding: 8px 0;
    overflow: hidden;
}

/* === অদৃশ্য ব্রিজ (গ্যাপ ফিক্স) === */
/* এটি আইকন আর মেনুর মাঝখানের ফাঁকা জায়গায় মাউস ধরে রাখবে কিন্তু জায়গা দখল করবে না */
.user-dropdown::after {
    content: "";
    position: absolute;
    bottom: -15px; 
    left: 0;
    width: 100%;
    height: 15px;
    background: transparent;
    display: none;
}

.user-dropdown:hover::after { display: block; } /* হোভার করলে ব্রিজ চালু হবে */
.user-dropdown:hover .dropdown-content { display: block; animation: fadeInDropdown 0.3s ease; }

@keyframes fadeInDropdown {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.dropdown-content a { 
    color: #333 !important; 
    padding: 10px 20px; 
    text-decoration: none; 
    display: flex; 
    align-items: center; 
    gap: 10px; 
    font-size: 13px; 
    font-weight: 500; 
    transition: 0.2s; 
}

.dropdown-content a:hover { 
    background: #f8f9fa; 
    color: #ffc107 !important; 
    padding-left: 25px; 
}

         /* কার্ট আইকন মেইন কন্টেইনার */
.cart-icon {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 18px;
    text-decoration: none;
    color: #fff !important;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    background: rgba(255, 255, 255, 0.05); /* হালকা গ্লাস টাচ */
    border-radius: 50px;
}

/* হোভার করলে আইকনটি উপরে উঠবে এবং রঙ বদলাবে */
.cart-icon:hover {
    color: #ffc107 !important; /* হালুমের সিগনেচার হলুদ রঙ */
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.1);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}

/* কার্ট ব্যাজ (নোটিফিকেশন বৃত্ত) */
.cart-badge {
    background: #ffc107;
    color: #000;
    font-size: 10px;
    font-weight: 900;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    position: absolute;
    top: -8px;
    right: -2px;
    border: 2px solid #1a1a1a;
    transition: all 0.4s ease;
    z-index: 10;
}

/* হোভার করলে ব্যাজটি বড় হবে এবং গ্লো করবে */
.cart-icon:hover .cart-badge {
    transform: scale(1.3) rotate(15deg); /* ব্যাজটি বড় হয়ে হালকা বাঁকবে */
    background: #fff; /* সাদার ওপর হলুদ শ্যাডো */
    box-shadow: 0 0 15px rgba(255, 193, 7, 0.8);
    color: #000;
}
.auth-btn {
    background: #fff;       /* সাদা ব্যাকগ্রাউন্ড */
    color: #1a1a1a;         /* কালো টেক্সট */
    padding: 8px 20px;      /* সাইজ ঠিক করা */
    border-radius: 50px;    /* গোল শেপ */
    text-decoration: none;  /* আন্ডারলাইন রিমুভ */
    font-weight: bold;
    font-size: 14px;
    transition: 0.3s;
}
    </style>
</head>
<body>

<nav id="navbar">
    <a href="index.php" class="logo">
        <img src="halum.png" alt="Halum Logo">
        <h2>Halum</h2>
    </a>

    <div class="nav-links-center">
        <a href="index.php">Home</a>
        <a href="index.php#products">Collection</a>
        <a href="about.php">About Us</a>
        <a href="contact.php">Contact Us</a>
    </div>

    <div class="nav-right">
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="cart.php" class="cart-icon">
                🛒 Cart 
                <?php
                    $u_id = $_SESSION['user_id'];
                    $count_res = mysqli_query($conn, "SELECT SUM(quantity) as total FROM `cart` WHERE user_id = '$u_id'");
                    $count_data = mysqli_fetch_assoc($count_res);
                    $count = $count_data['total'] ? $count_data['total'] : 0;
                    if($count > 0) echo '<span class="cart-badge">' . $count . '</span>';
                ?>
            </a>

            <div class="user-dropdown">
                <span class="user-name">
                    <?php 
                    date_default_timezone_set('Asia/Dhaka');
                    $hour = date('H');
                    if ($hour >= 5 && $hour < 12) { $greet = "Good Morning ☀️"; }
                    elseif ($hour >= 12 && $hour < 17) { $greet = "Good Afternoon 🌤️"; }
                    elseif ($hour >= 17 && $hour < 21) { $greet = "Good Evening 🌙"; }
                    else { $greet = "Good Night 😴"; }
                    
                    if(!empty($header_data['full_name'])){
                        $first_name = explode(' ', $header_data['full_name'])[0];
                        echo $greet . ", " . ucfirst(htmlspecialchars($first_name));
                    } else {
                        echo "Welcome, Guest";
                    }
                    ?>
                </span>
                <img src="uploaded_img/<?php echo $profile_img; ?>" alt="User" class="nav-profile-pic">
    
                <div class="dropdown-content">
                    <a href="profile.php"><span style="font-size: 18px;">👤</span> Edit Profile</a>
                    <a href="my_orders.php"><span style="font-size: 18px;">📦</span> My Orders</a>
                    
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] !== 'user'): ?>
                        <a href="admin_dashboard.php"><span style="font-size: 18px;">⚙️</span> Admin Panel</a>
                    <?php endif; ?>
                    
                    <a href="logout.php" class="logout-link"><span style="font-size: 18px;">🚪</span> Logout</a>
                </div>
            </div>
        <?php else: ?>
            <a href="login.php" class="auth-btn">Sign In</a>
        <?php endif; ?>
    </div>
</nav>