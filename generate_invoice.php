<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ১. নিরাপত্তা চেক: লগইন না থাকলে এক্সেস ব্লক
if(!isset($_SESSION['user_id'])){
    header('location:login.php');
    exit();
}

$u_id = $_SESSION['user_id'];
$u_email = $_SESSION['user_email'];

// ২. ইনভয়েস ডাটা ফেচ করা
if(isset($_GET['order_id'])){
    $order_id = mysqli_real_escape_string($conn, $_GET['order_id']);
    
    // নিরাপত্তা: ইউজার শুধুমাত্র নিজের অর্ডার দেখতে পারবে
    $query = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_id' AND email = '$u_email'");
    $order = mysqli_fetch_assoc($query);

    if(!$order){
        die("<div style='text-align:center; padding:50px;'><h2>Access Denied!</h2><a href='my_orders.php'>Back to Orders</a></div>");
    }
} else {
    header('location:my_orders.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo $order['id']; ?> | Halum Official</title>
    <link rel="icon" type="image/png" href="halum.png">
    <style>
        :root { --main-color: #ffc107; --secondary-color: #1a1a1a; --border-color: #e2e8f0; }
        body { font-family: 'Inter', 'Segoe UI', sans-serif; background: #f1f5f9; margin: 0; padding: 50px 0; -webkit-print-color-adjust: exact; }
        
        .invoice-wrapper { 
            max-width: 850px; margin: auto; background: #fff; border-radius: 16px; 
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.1); position: relative; overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .watermark {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 150px; font-weight: 900; color: rgba(0,0,0,0.015); pointer-events: none; z-index: 0;
            text-transform: uppercase;
        }

        .invoice-header { 
            background: var(--secondary-color); color: #fff; padding: 60px 50px; 
            display: flex; justify-content: space-between; align-items: flex-start; position: relative; z-index: 1;
        }
        .brand h1 { margin: 0; font-size: 42px; color: var(--main-color); font-weight: 800; letter-spacing: -1.5px; }
        .invoice-title h2 { margin: 0; font-size: 26px; text-transform: uppercase; color: var(--main-color); letter-spacing: 2px; }

        .invoice-content { padding: 50px; position: relative; z-index: 1; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; margin-bottom: 50px; }
        .section-title { font-size: 11px; font-weight: 800; text-transform: uppercase; color: #94a3b8; margin-bottom: 12px; border-bottom: 2px solid #f1f5f9; padding-bottom: 6px; letter-spacing: 1px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f8fafc; padding: 15px; text-align: left; font-size: 12px; font-weight: 700; color: #475569; border-bottom: 2px solid var(--secondary-color); text-transform: uppercase; }
        td { padding: 22px 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; }

        .invoice-footer { 
            background: #f8fafc; padding: 45px 50px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color);
        }
        .grand-total { background: var(--secondary-color); color: #fff; padding: 22px 35px; border-radius: 14px; text-align: right; }
        .grand-total h3 { margin: 0; font-size: 34px; color: var(--main-color); font-weight: 800; }

        .btn-group { text-align: center; margin-bottom: 30px; }
        .btn { 
            padding: 14px 32px; border-radius: 50px; cursor: pointer; border: none; 
            font-weight: 700; font-size: 14px; transition: 0.3s; margin: 0 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-print { background: #10b981; color: white; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3); }
        .btn-back { background: var(--secondary-color); color: white; }
        .btn:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }

        @media print { 
            .btn-group { display: none; } 
            body { padding: 0; background: #fff; } 
            .invoice-wrapper { box-shadow: none; border: none; width: 100%; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="btn-group">
    <a href="my_orders.php" class="btn btn-back">⬅ Back to Orders</a>
    <button onclick="window.print()" class="btn btn-print">🖨️ Print / Download</button>
</div>

<div class="invoice-wrapper">
    <div class="watermark">HALUM</div>
    
    <div class="invoice-header">
        <div class="brand">
            <h1>Halum.</h1>
            <p style="margin-top: 6px; opacity: 0.8; font-weight: 500;">Premium Lifestyle Store</p>
        </div>
        <div class="invoice-title">
            <h2>Tax Invoice</h2>
            <p style="margin: 8px 0 0; font-weight: 700; font-size: 15px;">#ORD-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></p>
            <p style="margin: 5px 0 0; opacity: 0.8; font-size: 13px;"><?php echo date('d M, Y', strtotime($order['order_date'])); ?></p>
        </div>
    </div>

    <div class="invoice-content">
        <div class="grid">
            <div>
                <div class="section-title">Bill To</div>
                <p style="margin: 0; font-size: 19px; font-weight: 700; color: var(--secondary-color);"><?php echo htmlspecialchars($order['full_name']); ?></p>
                <p style="margin: 8px 0; color: #475569; line-height: 1.6; font-size: 14px;"><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                <p style="margin: 0; color: #1e293b; font-weight: 600; font-size: 14px;">📞 <?php echo $order['phone']; ?></p>
            </div>
            <div style="text-align: right;">
                <div class="section-title">Seller</div>
                <p style="margin: 0; font-weight: 700; color: var(--secondary-color);">Halum Online Shop Ltd.</p>
                <p style="margin: 8px 0; color: #475569; font-size: 14px;">Bashundhara R/A, Dhaka<br>Bangladesh</p>
                <p style="margin: 0; color: #1e293b; font-weight: 600; font-size: 14px;">✉️ support@halum.com</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="70%">Description</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div style="font-weight: 700; color: var(--secondary-color); font-size: 15px; margin-bottom: 4px;">
                            <?php echo htmlspecialchars($order['total_products']); ?>
                        </div>
                        <span style="color: #64748b; font-size: 12px;">Premium Quality Selection</span>
                    </td>
                    <td style="text-align: right; font-weight: 800; font-size: 20px; color: var(--secondary-color);">
                        ৳ <?php echo number_format($order['total_price'], 2); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="invoice-footer">
        <div>
            <p style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 800; margin-bottom: 10px;">Payment Details</p>
            <div style="display: inline-block; padding: 7px 14px; background: #f1f5f9; border-radius: 6px; font-weight: 700; font-size: 13px; color: var(--secondary-color);">
                <?php 
                    // এরর ফিক্স: যদি method কী না থাকে তবে ডিফল্ট COD দেখাবে
                    echo isset($order['method']) ? strtoupper($order['method']) : 'CASH ON DELIVERY'; 
                ?>
            </div>
            <p style="font-size: 13px; color: #10b981; font-weight: 700; margin-top: 18px; display: flex; align-items: center; gap: 6px;">
                <span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></span>
                Status: <?php echo ucfirst($order['status']); ?>
            </p>
        </div>
        <div class="grand-total">
            <p style="font-size: 11px; text-transform: uppercase; opacity: 0.9; margin-bottom: 8px; font-weight: 700; letter-spacing: 1px;">Total Payable</p>
            <h3>৳ <?php echo number_format($order['total_price'], 2); ?></h3>
        </div>
    </div>

    <div style="padding: 40px; text-align: center; border-top: 1px solid #f1f5f9;">
        <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 1.6;">
            This is a computer-generated tax invoice and no signature is required. <br>
            Thank you for shopping with <strong>Halum.</strong>
        </p>
    </div>
</div>

<div style="text-align: center; margin-top: 30px; color: #94a3b8; font-size: 12px;">
    &copy; 2026 Halum Official. Premium Quality Guaranteed.
</div>

</body>
</html>