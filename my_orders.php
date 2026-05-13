<?php
// ১. হেডার ইনক্লুড করা হলো (এতেই ডাটাবেস, সেশন এবং প্রিমিয়াম ন্যাভবার চলে আসবে)
include 'header.php';

// লগইন চেক
if(!isset($_SESSION['user_email'])){
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$u_email = $_SESSION['user_email'];

// ২. অর্ডার ক্যানসেল লজিক
if(isset($_GET['cancel_id'])){
    $cancel_id = $_GET['cancel_id'];
    // শুধুমাত্র 'Pending' অবস্থায় থাকলেই ক্যানসেল করা যাবে (নিরাপত্তার জন্য)
    $check_order = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$cancel_id' AND email = '$u_email' AND status = 'Pending'");
    if(mysqli_num_rows($check_order) > 0){
        mysqli_query($conn, "UPDATE `orders` SET status = 'Cancelled' WHERE id = '$cancel_id'");
        echo "<script>alert('Order cancelled successfully!'); window.location.href='my_orders.php';</script>";
    }
}
?>

<style>
    /* === My Orders Page Specific Styles === */
    body { background-color: #f4f7f6; }
    
    /* যেহেতু এই পেজে হিরো ইমেজ নেই, ন্যাভবার কালো রাখা হলো */
    nav, #navbar { background: #1a1a1a !important; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }

    .main-content { 
        min-height: 80vh;
        padding: 130px 5% 80px; 
    }

    .order-wrapper { 
        max-width: 1200px;
        margin: 0 auto;
        background: #fff; 
        border-radius: 20px; 
        box-shadow: 0 15px 40px rgba(0,0,0,0.05); 
        padding: 40px;
        border: 1px solid #eee;
    }

    .header-box { 
        margin-bottom: 40px; 
        border-left: 6px solid #ffc107;
        padding-left: 20px;
    }
    .header-box h2 { 
        font-size: 2.2rem; 
        font-weight: 900; 
        text-transform: uppercase; 
        color: #1a1a1a; 
    }
    .header-box p { color: #666; font-size: 15px; margin-top: 5px; }

    /* Premium Table Design */
    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    
    thead th { 
        background: #f8f9fa; 
        color: #1a1a1a; 
        padding: 20px 15px; 
        text-align: left; 
        font-size: 13px; 
        text-transform: uppercase; 
        font-weight: 800;
        letter-spacing: 1px;
        border-bottom: 2px solid #eee;
    }
    
    tbody td { 
        padding: 20px 15px; 
        border-bottom: 1px solid #f1f1f1; 
        vertical-align: middle; 
        color: #444; 
        font-size: 14.5px; 
    }
    tr:hover { background-color: #fafafa; }

    .col-id { font-weight: 800; color: #1a1a1a; }
    .col-items { font-weight: 600; color: #555; max-width: 250px; }
    .col-price { font-weight: 800; color: #28a745; font-size: 1.1rem; }

    /* Status Badges */
    .status-action-row { display: flex; align-items: center; gap: 15px; }
    
    .status-badge { 
        padding: 8px 15px; 
        border-radius: 50px; 
        font-size: 10px; 
        font-weight: 900; 
        text-transform: uppercase;
        min-width: 100px;
        text-align: center;
        letter-spacing: 0.5px;
    }
    .pending { background: #fff9e6; color: #856404; border: 1px solid #ffeeba; }
    .shipped { background: #e3f2fd; color: #0d6efd; border: 1px solid #cfe2ff; }
    .delivered { background: #e8f5e9; color: #28a745; border: 1px solid #d1e7dd; }
    .cancelled { background: #ffebeb; color: #dc3545; border: 1px solid #f8d7da; }

    /* Buttons */
    .action-btns { display: flex; gap: 8px; }
    .btn-small { 
        padding: 8px 15px; 
        border-radius: 8px; 
        font-size: 11px; 
        font-weight: 800; 
        text-decoration: none; 
        text-transform: uppercase; 
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .btn-invoice { background: #1a1a1a; color: #ffc107; }
    .btn-invoice:hover { background: #000; transform: translateY(-2px); }
    
    .btn-cancel { background: #fff; color: #dc3545; border: 1px solid #dc3545; }
    .btn-cancel:hover { background: #dc3545; color: #fff; transform: translateY(-2px); }

    /* Empty State */
    .no-orders { text-align: center; padding: 100px 20px; }
    .no-orders i { font-size: 4rem; color: #ddd; margin-bottom: 20px; }
    .shop-link { color: #ffc107; font-weight: bold; text-decoration: none; border-bottom: 2px solid #ffc107; padding-bottom: 2px; }
</style>

<div class="main-content">
    <div class="order-wrapper">
        <div class="header-box">
            <h2>My Order History</h2>
            <p>Review your premium purchases and download detailed invoices.</p>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Products</th>
                        <th>Delivery Info</th>
                        <th>Total Bill</th>
                        <th>Status & Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $order_q = mysqli_query($conn, "SELECT * FROM `orders` WHERE email = '$u_email' ORDER BY id DESC");
                    if(mysqli_num_rows($order_q) > 0){
                        while($row = mysqli_fetch_assoc($order_q)){
                            $st = $row['status'];
                            $st_class = strtolower($st);
                    ?>
                    <tr>
                        <td class="col-id">#<?php echo $row['id']; ?></td>
                        <td class="col-items"><?php echo htmlspecialchars($row['total_products']); ?></td>
                        <td>
                            <div style="font-size: 13px; color: #777;">
                                <i class="fas fa-map-marker-alt" style="font-size: 10px;"></i> 
                                <?php echo htmlspecialchars($row['address']); ?>
                            </div>
                        </td>
                        <td class="col-price"><?php echo number_format($row['total_price'], 2); ?> BDT</td>
                        <td>
                            <div class="status-action-row">
                                <span class="status-badge <?php echo $st_class; ?>">
                                    <?php echo $st; ?>
                                </span>

                                <div class="action-btns">
                                    <a href="generate_invoice.php?order_id=<?php echo $row['id']; ?>" target="_blank" class="btn-small btn-invoice">
                                        <i class="fas fa-file-invoice"></i> Invoice
                                    </a>

                                    <?php if($st == 'Pending'){ ?>
                                        <a href="my_orders.php?cancel_id=<?php echo $row['id']; ?>" class="btn-small btn-cancel" onclick="return confirm('Are you sure you want to cancel this order?')">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php } } else { ?>
                        <tr>
                            <td colspan="5">
                                <div class="no-orders">
                                    <i class="fas fa-shopping-basket"></i>
                                    <h3>No orders found yet!</h3>
                                    <p>Start your shopping journey today. <a href="index.php" class="shop-link">Browse Collection</a></p>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
// ৩. ফুটার ইনক্লুড করা হলো
include 'footer.php'; 
?>