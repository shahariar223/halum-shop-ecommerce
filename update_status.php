<?php
include 'db.php';
session_start();

// 1. Check if ID and Status are received via GET
if(isset($_GET['id']) && isset($_GET['status'])){
    $order_id = (int)$_GET['id']; // Casting to integer for security
    $status = mysqli_real_escape_string($conn, $_GET['status']);

    // 2. Query to update order status in the database
    $update_query = "UPDATE orders SET status = '$status' WHERE id = '$order_id'";

    if(mysqli_query($conn, $update_query)){
        // 3. Success message and redirect back to order list
        echo "<script>
                alert('Order status has been updated to $status successfully!');
                window.location.href='view_orders.php';
              </script>";
    } else {
        // Log error if query fails
        echo "Error updating record: " . mysqli_error($conn);
    }
} else {
    // Redirect if page is accessed directly without parameters
    header("Location: view_orders.php");
    exit();
}
?>