<?php
include 'db.php';
session_start();

// ১. সিকিউরিটি চেক (আপনার সব ধরণের অ্যাডমিন যাতে ঢুকতে পারে)
if(!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'product_admin')){
    header("Location: login.php");
    exit();
}

// ২. আইডি ধরা এবং বর্তমান ডাটা আনা
if(!isset($_GET['id'])){
    header("Location: manage_products.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$select_query = mysqli_query($conn, "SELECT * FROM products WHERE id = '$id'");

if(mysqli_num_rows($select_query) > 0){
    $fetch_p = mysqli_fetch_assoc($select_query);
} else {
    header("Location: manage_products.php");
    exit();
}

// ৩. আপডেট লজিক
if(isset($_POST['update_product'])){
    $update_name = mysqli_real_escape_string($conn, $_POST['name']);
    $update_price = mysqli_real_escape_string($conn, $_POST['price']);
    $update_category = mysqli_real_escape_string($conn, $_POST['category']);
    $update_details = mysqli_real_escape_string($conn, $_POST['details']); // এই লাইনটি যোগ করা হয়েছে
    
    // ছবি আপলোড লজিক
    $update_image = $_FILES['update_image']['name'];
    $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
    $update_image_folder = 'images/'.$update_image; // পাথ images/ করা হলো
    $old_image = $_POST['old_image'];

    // মেইন আপডেট কুয়েরি (details সহ)
    $update_q = "UPDATE products SET name='$update_name', price='$update_price', details='$update_details', category='$update_category' WHERE id='$id'";
    
    if(mysqli_query($conn, $update_q)){
        // যদি নতুন ছবি আপলোড করা হয়
        if(!empty($update_image)){
            if(move_uploaded_file($update_image_tmp_name, $update_image_folder)){
                mysqli_query($conn, "UPDATE products SET image='$update_image' WHERE id='$id'");
            }
        }
        echo "<script>alert('Product updated successfully!'); window.location.href='manage_products.php';</script>";
    } else {
        echo "<script>alert('Update failed!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product | Halum Admin</title>
    <link rel="icon" type="image/png" href="halum.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary: #ffc107; --dark: #1a1a1a; --light: #f4f6f9; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--light); padding: 40px 20px; }
        .edit-container { max-width: 650px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.08); border: 1px solid #eee; }
        h2 { text-align: center; color: var(--dark); font-weight: 800; margin-bottom: 30px; text-transform: uppercase; }
        label { display: block; margin-top: 15px; font-weight: 700; color: #555; font-size: 13px; text-transform: uppercase; }
        input, select, textarea { width: 100%; padding: 12px 15px; margin-top: 5px; border: 1px solid #ddd; border-radius: 10px; box-sizing: border-box; outline: none; transition: 0.3s; font-size: 15px; }
        input:focus, select:focus, textarea:focus { border-color: var(--primary); box-shadow: 0 0 10px rgba(255,193,7,0.1); }
        .current-img-box { text-align: center; margin: 20px 0; background: #fafafa; padding: 15px; border-radius: 12px; border: 1px dashed #ddd; }
        .current-img { width: 120px; height: 120px; object-fit: contain; border-radius: 10px; }
        .btn-save { width: 100%; background: var(--dark); color: var(--primary); padding: 16px; border: none; border-radius: 50px; font-weight: 800; cursor: pointer; margin-top: 30px; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px; }
        .btn-save:hover { background: #000; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
    </style>
</head>
<body>

<div class="edit-container">
    <h2><i class="fas fa-edit"></i> Edit Product</h2>
    
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="old_image" value="<?php echo $fetch_p['image']; ?>">

        <label>Product Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($fetch_p['name']); ?>" required>

        <label>Price (BDT):</label>
        <input type="number" name="price" value="<?php echo $fetch_p['price']; ?>" required>

        <label>Product Details:</label>
        <textarea name="details" rows="5" placeholder="Enter product description..." required><?php echo htmlspecialchars($fetch_p['details']); ?></textarea>

        <label>Category:</label>
        <select name="category" required>
            <option value="Car" <?php if($fetch_p['category'] == 'Car') echo 'selected'; ?>>Car Collection</option>
            <option value="Bike" <?php if($fetch_p['category'] == 'Bike') echo 'selected'; ?>>Bike Collection</option>
            <option value="Watch" <?php if($fetch_p['category'] == 'Watch') echo 'selected'; ?>>Watch Collection</option>
            <option value="Sharee" <?php if($fetch_p['category'] == 'Sharee') echo 'selected'; ?>>Sharee Collection</option>
        </select>

        <div class="current-img-box">
            <label style="margin-top:0;">Current Product Image:</label><br>
            <?php 
                $img_path = 'images/' . $fetch_p['image']; 
                if(!empty($fetch_p['image']) && file_exists($img_path)){
                    echo '<img src="'.$img_path.'" class="current-img">';
                } else {
                    echo '<p style="color:#999; font-size:12px;">No image available</p>';
                }
            ?>
        </div>
        
        <label>Upload New Image (Optional):</label>
        <input type="file" name="update_image" accept="image/*">

        <button type="submit" name="update_product" class="btn-save">🚀 Save All Changes</button>
        <a href="manage_products.php" style="display:block; text-align:center; margin-top:20px; color:#999; text-decoration:none; font-size:14px; font-weight:600;">← Cancel and Go Back</a>
    </form>
</div>

</body>
</html>