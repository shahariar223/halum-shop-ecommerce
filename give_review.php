<?php include 'db.php'; session_start(); 
if(!isset($_SESSION['user'])){ header("Location: login.php"); }
?>
<!DOCTYPE html>
<html>
<head><title>Give Review</title></head>
<body style="font-family: Arial; padding: 40px; text-align: center;">
    <h2>Share Your Experience</h2>
    <form action="" method="POST" style="display: inline-block; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); width: 400px;">
        <input type="text" name="p_name" placeholder="Product Name (e.g. Motorcycle)" required style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px;">
        <select name="rating" style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px;">
            <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
            <option value="4">⭐⭐⭐⭐ (4/5)</option>
            <option value="3">⭐⭐⭐ (3/5)</option>
            <option value="2">⭐⭐ (2/5)</option>
            <option value="1">⭐ (1/5)</option>
        </select>
        <textarea name="comment" placeholder="Write your review here..." required style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; height: 100px;"></textarea>
        <button type="submit" name="submit_review" style="background: #28a745; color: white; padding: 12px; width: 100%; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Submit Review</button>
    </form>
    <?php
    if(isset($_POST['submit_review'])){
        $p_name = mysqli_real_escape_string($conn, $_POST['p_name']);
        $user = $_SESSION['user'];
        $rating = $_POST['rating'];
        $comment = mysqli_real_escape_string($conn, $_POST['comment']);

        $sql = "INSERT INTO reviews (product_name, username, rating, comment) VALUES ('$p_name', '$user', '$rating', '$comment')";
        if(mysqli_query($conn, $sql)){ 
            echo "<p style='color:green; margin-top:15px;'>Thank you! Your review has been added.</p>"; 
        }
    }
    ?>
    <br><br><a href="index.php" style="text-decoration: none; color: #007bff;">← Back to Home</a>
</body>
</html>