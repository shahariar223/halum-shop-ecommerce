<?php
include 'db.php';

if(isset($_GET['id'])){
    $order_id = $_GET['id'];
    $sql = "UPDATE orders SET status = 'Rejected' WHERE id = $order_id";
    
    if(mysqli_query($conn, $sql)){
        header("Location: view_orders.php");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>