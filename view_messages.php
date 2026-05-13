<?php 
include 'db.php'; 
session_start(); 

// ১. একদম বেসিক লগইন চেক
if(!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

// ২. রোল চেক
if(!isset($_SESSION['role']) || $_SESSION['role'] === 'user'){
    header("Location: index.php"); 
    exit();
}

// ৩. পেজ ভিত্তিক পারমিশন চেক (নতুন রোল অনুযায়ী)
$current_role = $_SESSION['role'];
$current_page = basename($_SERVER['PHP_SELF']);

if($current_role !== 'super_admin'){
    // প্রোডাক্ট পেজগুলোর জন্য চেক
    $product_pages = ['admin.php', 'manage_products.php'];
    if(in_array($current_page, $product_pages) && $current_role !== 'product_admin'){
        header("Location: admin_dashboard.php");
        exit();
    }

    // অর্ডার ও মেসেজ পেজগুলোর জন্য চেক
    $support_pages = ['view_orders.php', 'view_messages.php', 'update_status.php'];
    if(in_array($current_page, $support_pages) && $current_role !== 'sell_communication_admin'){
        header("Location: admin_dashboard.php");
        exit();
    }
}

// ৪. মেসেজ রিপ্লাই লজিক (Real Chat History এর জন্য)
if(isset($_POST['send_reply'])){
    $user_email = mysqli_real_escape_string($conn, $_POST['email']);
    $reply_text = mysqli_real_escape_string($conn, $_POST['reply_text']);
    
    // ইউজারের নাম খুঁজে বের করা (ডাটাবেসে সেভ করার জন্য)
    $name_query = mysqli_query($conn, "SELECT name FROM `messages` WHERE email = '$user_email' LIMIT 1");
    $user_name = (mysqli_num_rows($name_query) > 0) ? mysqli_fetch_assoc($name_query)['name'] : 'Customer';
    
    // অ্যাডমিনের রিপ্লাইটি একটি নতুন মেসেজ হিসেবে ইনসার্ট করা হচ্ছে
    $insert_reply = "INSERT INTO `messages` (name, email, subject, message, reply, is_read) VALUES ('$user_name', '$user_email', 'Admin Reply', '', '$reply_text', 1)";
    mysqli_query($conn, $insert_reply);

    // একইসাথে ইউজারের পাঠানো আগের আনরিড মেসেজগুলো Read মার্ক করে দেওয়া হচ্ছে
    mysqli_query($conn, "UPDATE `messages` SET is_read = 1 WHERE email = '$user_email'");

    header("Location: view_messages.php");
    exit();
}

// ৫. মেসেজ Read করার লজিক (জাভাস্ক্রিপ্ট Fetch এর জন্য)
if(isset($_GET['read_email'])){
    $read_email = mysqli_real_escape_string($conn, $_GET['read_email']);
    mysqli_query($conn, "UPDATE `messages` SET is_read = 1 WHERE email = '$read_email'");
    exit(); // এটি ব্যাকগ্রাউন্ডে কাজ করবে, তাই পেজ রিলোড দরকার নেই
}

// বর্তমান অ্যাডমিনের তথ্য
$admin_id = $_SESSION['user_id'];
$admin_q = mysqli_query($conn, "SELECT * FROM users WHERE id = '$admin_id'");
$admin_data = mysqli_fetch_assoc($admin_q);

// নোটিফিকেশন কাউন্ট লজিক
$new_orders_count = 0;
$unread_msgs_count = 0;

$order_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
if($order_q) { $new_orders_count = mysqli_fetch_assoc($order_q)['total']; }

$msg_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM messages WHERE is_read = 0");
if($msg_q) { $unread_msgs_count = mysqli_fetch_assoc($msg_q)['total']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Inbox | Halum Admin</title>
    <link rel="icon" type="image/png" href="halum.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary: #ffc107; --dark: #1a1a1a; --success: #28a745; --light: #f4f6f9; }
        body { font-family: 'Segoe UI', Roboto, sans-serif; display: flex; margin: 0; background-color: var(--light); color: #333; overflow-x: hidden; }
        
        /* === Universal Sidebar Style === */
        .sidebar { width: 280px; background: #1a1a1a; color: white; height: 100vh; padding: 40px 20px; position: fixed; box-shadow: 4px 0 10px rgba(0,0,0,0.1); overflow-y: auto; }
        .sidebar h2 { color: #ffc107; font-size: 28px; margin-bottom: 40px; text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .sidebar a { display: block; color: #aaa; padding: 15px 20px; text-decoration: none; border-radius: 12px; margin-bottom: 8px; transition: 0.3s; font-size: 15px; }
        .sidebar a:hover { background: rgba(255,193,7,0.1); color: #ffc107; transform: translateX(5px); }
        .sidebar a.active { background: rgba(255,193,7,0.2); color: #ffc107; font-weight: bold; }

        .admin-profile { display: flex; align-items: center; gap: 12px; background: rgba(255, 255, 255, 0.05); padding: 12px; border-radius: 10px; margin-bottom: 25px; border: 1px solid rgba(255, 255, 255, 0.1); }
        .profile-img { width: 38px; height: 38px; background: #ffc107; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; color: #1a1a1a; overflow: hidden; }
        .profile-img img { width: 100%; height: 100%; object-fit: cover; }
        .admin-info h4 { margin: 0; color: white; font-size: 14px; font-weight: 600; }
        .admin-info small { color: #aaa; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; }
        
        /* সাইডবারের ব্যাজ */
        .sidebar .badge { background-color: #dc3545; color: white; padding: 2px 8px; border-radius: 50px; font-size: 11px; float: right; margin-top: 2px; }

        .logout-link { background: #dc3545; color: white !important; text-align: center; margin-top: 50px !important; font-weight: bold; border-radius: 12px; }
        .logout-link:hover { background: #c82333; transform: scale(1.02); }

        /* === Main Content === */
        .main-content { flex: 1; margin-left: 280px; padding: 50px; }
        .header-title { border-bottom: 2px solid var(--primary); padding-bottom: 10px; margin-bottom: 30px; display: inline-block; }
        
        .msg-card { background: #fff; border-radius: 20px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; border: none; }
        .msg-header { background: var(--dark); color: white; padding: 20px 25px; display: flex; justify-content: space-between; align-items: center; }
        .unread-card { border-left: 6px solid #dc3545 !important; }
        .unread-badge { background: #dc3545; color: #fff; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; margin-left: 10px; }
        
        .msg-body { padding: 25px; background: #fdfdfd; max-height: 400px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; }
        
        .reply-area { padding: 20px 25px; background: #fff; border-top: 1px solid #f0f0f0; }
        .action-row { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; }
        .update-btn { background: var(--success); color: white; border: none; padding: 12px 30px; border-radius: 50px; font-weight: bold; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .update-btn:hover { background: #218838; transform: translateY(-3px); box-shadow: 0 8px 15px rgba(40, 167, 69, 0.3); }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Halum.</h2>
        
        <div class="admin-profile">
            <div class="profile-img">
                <?php
                $img_path = 'uploaded_img/' . (isset($admin_data['profile_pic']) ? $admin_data['profile_pic'] : '');
                if(!empty($admin_data['profile_pic']) && file_exists($img_path)){
                    echo '<img src="'.$img_path.'">';
                } else {
                    echo strtoupper(substr($admin_data['full_name'], 0, 1));
                }
                ?>
            </div>
            <div class="admin-info">
                <h4><?php echo htmlspecialchars($admin_data['full_name']); ?></h4>
                <small style="text-transform: uppercase;"><?php echo str_replace('_', ' ', $current_role); ?></small>
            </div>
        </div>

        <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard Home</a>

        <?php if($current_role === 'super_admin' || $current_role === 'product_admin'): ?>
            <a href="admin.php"><i class="fas fa-plus-circle"></i> Add New Product</a>
            <a href="manage_products.php"><i class="fas fa-boxes"></i> Manage Products</a>
        <?php endif; ?>
        
        <?php if($current_role === 'super_admin' || $current_role === 'sell_communication_admin'): ?>
            <a href="view_orders.php" style="display: flex; justify-content: space-between;">
                <span><i class="fas fa-shopping-cart"></i> Customer Orders</span>
                <?php if(isset($new_orders_count) && $new_orders_count > 0){ echo '<span class="badge">'.$new_orders_count.'</span>'; } ?>
            </a>
            <a href="view_messages.php" class="active" style="display: flex; justify-content: space-between;">
                <span><i class="fas fa-envelope"></i> Support Inbox</span>
                <?php if(isset($unread_msgs_count) && $unread_msgs_count > 0){ echo '<span class="badge">'.$unread_msgs_count.'</span>'; } ?>
            </a>
        <?php endif; ?>

        <?php if($current_role === 'super_admin'): ?>
            <a href="manage_admins.php" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 10px; padding-top: 15px;">
                <span style="color: #ffc107;"><i class="fas fa-users-cog"></i> Manage Admins</span>
            </a>
            <a href="admin_profile.php"><i class="fas fa-user-cog"></i> Update Profile</a>
        <?php endif; ?>

        <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout Account</a>
    </div>

    <div class="main-content">
        <div class="header-title"><h2>📩 Inbox: Customer Support</h2></div>
        
        <?php
        $res = mysqli_query($conn, "SELECT *, MAX(id) as last_id, SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_count FROM `messages` GROUP BY email ORDER BY unread_count DESC, last_id DESC");

        if(mysqli_num_rows($res) > 0){
            while($row = mysqli_fetch_assoc($res)){ 
                $user_email = $row['email'];
                $unread = $row['unread_count'];
                $chat_box_id = "chat_" . md5($user_email);
        ?>
                <div class="msg-card <?php echo ($unread > 0) ? 'unread-card' : ''; ?>" 
                     id="<?php echo $chat_box_id; ?>" 
                     onclick="markRead(this, '<?php echo $user_email; ?>')">
                    
                    <div class="msg-header">
                        <div class="sender-info">
                            <h4 style="margin:0;"><?php echo htmlspecialchars($row['name']); ?>
                                <?php if($unread > 0): ?><span class="unread-badge"><?php echo $unread; ?> New</span><?php endif; ?>
                            </h4>
                            <p style="margin:5px 0 0 0; font-size:13px;">📧 <?php echo htmlspecialchars($user_email); ?></p>
                        </div>
                        <div class="msg-date" style="font-size:12px;">Chat Active: <?php echo date('M d, Y', strtotime($row['date'])); ?></div>
                    </div>
                    
                    <div class="msg-body">
                        <?php 
                        $chat_history = mysqli_query($conn, "SELECT * FROM `messages` WHERE email = '$user_email' ORDER BY id ASC");
                        while($chat = mysqli_fetch_assoc($chat_history)){ 
                            
                            // ১. ইউজারের মেসেজ
                            if(!empty(trim($chat['message']))){ ?>
                                <div style="align-self: flex-start; max-width: 80%;">
                                    <div style="background: #eef2f7; padding: 12px 18px; border-radius: 18px 18px 18px 0; border: 1px solid #e0e6ed;">
                                        <small style="color: #856404; font-weight: bold; font-size: 11px;">Subject: <?php echo htmlspecialchars($chat['subject']); ?></small><br>
                                        <?php echo nl2br(htmlspecialchars($chat['message'])); ?>
                                        <div style="text-align: right; font-size: 10px; color: #999; margin-top: 5px;"><?php echo date('h:i A', strtotime($chat['date'])); ?></div>
                                    </div>
                                </div>
                            <?php } 

                            // ২. অ্যাডমিনের রিপ্লাই
                            if(!empty(trim($chat['reply']))){ ?>
                                <div style="align-self: flex-end; max-width: 80%;">
                                    <div style="background: #1a1a1a; color: #ffc107; padding: 12px 18px; border-radius: 18px 18px 0 18px;">
                                        <strong>💡 Admin Response:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($chat['reply'])); ?>
                                    </div>
                                </div>
                            <?php } 
                        } ?>
                    </div>

                    <div class="reply-area">
                        <form action="view_messages.php" method="POST">
                            <input type="hidden" name="email" value="<?php echo $user_email; ?>">
                            
                            <textarea name="reply_text" class="chat-input" rows="2" placeholder="Write a response..." required
                            style="width: 100%; padding: 15px; border: 2px solid #eee; border-radius: 12px; outline: none; box-sizing: border-box; resize: none; overflow: hidden; min-height: 55px; font-family: inherit;"></textarea>
                            
                            <div class="action-row">
                                <span style="font-size: 13px; font-weight: bold; color: #28a745;">
                                    <?php echo !empty($row['reply']) ? '✅ Conversation Replied' : '⏳ Needs Response'; ?>
                                </span>
                                <button type="submit" name="send_reply" class="update-btn">🚀 Send Reply</button>
                            </div>
                        </form>
                    </div>
                </div>
        <?php }
        } else { echo "<div style='text-align:center; padding:50px; background:#fff; border-radius:20px; color:#999;'><h3>No messages found in your inbox.</h3></div>"; }
        ?>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // অটো স্ক্রল (সর্বশেষ মেসেজ দেখার জন্য)
        var chatBoxes = document.querySelectorAll('.msg-body');
        chatBoxes.forEach(function(box) {
            box.scrollTop = box.scrollHeight;
        });

        // কীবোর্ড লজিক (Enter চাপলে সেন্ড হবে)
        var inputs = document.querySelectorAll('.chat-input');
        inputs.forEach(function(textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });

            textarea.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault(); 
                    var form = this.closest('form');
                    var btn = form.querySelector('button[name="send_reply"]');
                    if(btn) btn.click();
                }
            });
        });
        
        // লাল দাগ সরানোর লজিক (Read Status)
        window.markRead = function(element, email) {
            if(element.classList.contains('unread-card')) {
                element.classList.remove('unread-card');
                const badge = element.querySelector('.unread-badge');
                if(badge) badge.style.display = 'none';
                
                // ব্যাকগ্রাউন্ডে ডাটাবেস আপডেট করা
                fetch('view_messages.php?read_email=' + email);
            }
        };
    });
    </script>
</body>
</html>