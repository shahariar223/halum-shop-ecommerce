<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

// ১. সেশনের সব ভেরিয়েবল মুছে ফেলুন
session_unset(); 

// ২. সেশনটি পুরোপুরি ধ্বংস করুন
session_destroy(); 

// ৩. ব্রাউজারের সেশন কুকি মুছে ফেলুন (এটি খুবই গুরুত্বপূর্ণ)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ৪. ক্যাশ ক্লিয়ার করার জন্য হেডার যোগ করুন যাতে ব্যাক বাটন চাপলে পুরনো ডেটা না আসে
header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

// ৫. রিডাইরেক্ট করে লগইন পেজে পাঠিয়ে দিন যাতে নতুন করে লগইন করতে হয়
header("Location: index.php"); 
exit(); 
?>