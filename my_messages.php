<?php
// সেশন থেকে ইউজারের ইমেইল নেওয়া হচ্ছে
$u_email = $_SESSION['user_email']; 

// কুয়েরিতে ইমেইল এবং আইডি দুইটাই চেক করা হচ্ছে (Double Security)
$select_messages = mysqli_query($conn, "SELECT * FROM `messages` WHERE email = '$u_email'");

if(mysqli_num_rows($select_messages) > 0){
   while($fetch_message = mysqli_fetch_assoc($select_messages)){
      // মেসেজ দেখানোর কোড এখানে...
   }
} else {
   echo "<p>No messages found!</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Inquiries | Halum</title>
    <link rel="icon" type="image/png" href="halum.png">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 40px; }
        .container { max-width: 800px; margin: 0 auto; }
        .msg-card { background: #fff; padding: 25px; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .user-msg { color: #444; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .admin-reply { background: #e7f3ff; padding: 15px; border-radius: 10px; border-left: 5px solid #007bff; color: #004085; }
        .no-reply { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; display: inline-block; font-size: 13px; }
        .back-link { text-decoration: none; color: #007bff; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Back to Shop</a>
        <h2 style="margin: 20px 0;">My Messages & Support</h2>

        <?php
        // সেশনে থাকা ইউজারের আইডি অনুযায়ী মেসেজ আনা হচ্ছে
        // আপনার ডাটাবেস অনুযায়ী যদি ইমেইল দিয়ে চেক করতে হয় তবে ইমেইল ভেরিয়েবল ব্যবহার করুন
        $u_id = $_SESSION['user']; 
        $res = mysqli_query($conn, "SELECT * FROM `messages` WHERE user_id = '$u_id' OR email = (SELECT email FROM users WHERE id = '$u_id') ORDER BY id DESC");

        if(mysqli_num_rows($res) > 0){
            while($row = mysqli_fetch_assoc($res)){ ?>
                <div class="msg-card">
                    <div style="font-size: 12px; color: #999;"><?php echo $row['date']; ?></div>
                    <div class="user-msg">
                        <strong>My Question (<?php echo htmlspecialchars($row['subject']); ?>):</strong><br>
                        <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                    </div>

                    <?php if(!empty($row['reply'])){ ?>
                        <div class="admin-reply">
                            <strong>Admin Response:</strong><br>
                            <?php echo nl2br(htmlspecialchars($row['reply'])); ?>
                        </div>
                    <?php } else { ?>
                        <div class="no-reply">⌛ Waiting for admin to reply...</div>
                    <?php } ?>
                </div>
            <?php }
        } else {
            echo "<p>You haven't sent any messages yet.</p>";
        }
        ?>
    </div>
</body>
</html>