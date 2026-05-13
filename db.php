<?php
// লোকাল XAMPP এর জন্য আগের কোডটি ফিরিয়ে আনুন
$conn = mysqli_connect('localhost', 'root', '', 'shop_db');

if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>