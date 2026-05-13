<?php 
// ১. একদম শুরুতে হেডার ইনক্লুড করা হলো (ডাটাবেস, ন্যাভবার সব চলে আসবে)
include 'header.php'; 
?>

    <style>
    /* কার্ট আইকন মেইন কন্টেইনার */
.cart-icon {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 18px;
    text-decoration: none;
    color: #fff !important;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    background: rgba(255, 255, 255, 0.05); /* হালকা গ্লাস টাচ */
    border-radius: 50px;
}

/* হোভার করলে আইকনটি উপরে উঠবে এবং রঙ বদলাবে */
.cart-icon:hover {
    color: #ffc107 !important; /* হালুমের সিগনেচার হলুদ রঙ */
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.1);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}

/* কার্ট ব্যাজ (নোটিফিকেশন বৃত্ত) */
.cart-badge {
    background: #ffc107;
    color: #000;
    font-size: 10px;
    font-weight: 900;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    position: absolute;
    top: -8px;
    right: -2px;
    border: 2px solid #1a1a1a;
    transition: all 0.4s ease;
    z-index: 10;
}

/* হোভার করলে ব্যাজটি বড় হবে এবং গ্লো করবে */
.cart-icon:hover .cart-badge {
    transform: scale(1.3) rotate(15deg); /* ব্যাজটি বড় হয়ে হালকা বাঁকবে */
    background: #fff; /* সাদার ওপর হলুদ শ্যাডো */
    box-shadow: 0 0 15px rgba(255, 193, 7, 0.8);
    color: #000;
}
.auth-btn {
    background: #fff;       /* সাদা ব্যাকগ্রাউন্ড */
    color: #1a1a1a;         /* কালো টেক্সট */
    padding: 8px 20px;      /* সাইজ ঠিক করা */
    border-radius: 50px;    /* গোল শেপ */
    text-decoration: none;  /* আন্ডারলাইন রিমুভ */
    font-weight: bold;
    font-size: 14px;
    transition: 0.3s;
}

/* হোভার ইফেক্ট */
.auth-btn:hover {
    background: #ffc107;    /* মাউস নিলে গোল্ডেন কালার */
    color: #1a1a1a;
    transform: translateY(-2px); /* হালকা উপরে উঠবে */
    box-shadow: 0 4px 10px rgba(255, 193, 7, 0.3);
}

    /* === Hero Section === */
    .contact-hero { 
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('banners/limon.jpg') center/cover fixed; 
        height: 60vh; display: flex; flex-direction: column; justify-content: center; align-items: center; 
        text-align: center; color: white; margin-top: 0;
    }
    /* ১. শিরোনামের জন্য অ্যানিমেশন */
.contact-hero h1 { 
    font-size: 4rem; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px;
    
    /* অ্যানিমেশন প্রপার্টি */
    opacity: 0; /* শুরুতে দেখা যাবে না */
    transform: translateY(30px); /* একটু নিচে থাকবে */
    animation: flyInUp 0.8s ease-out forwards; /* অ্যানিমেশন চালু হবে */
}

/* ২. নিচের লেখার জন্য অ্যানিমেশন (একটু দেরি করে আসবে) */
.contact-hero p {
    font-size: 1.3rem; font-weight: 500;
    
    /* অ্যানিমেশন প্রপার্টি */
    opacity: 0; /* শুরুতে দেখা যাবে না */
    transform: translateY(30px); /* একটু নিচে থাকবে */
    animation: flyInUp 0.8s ease-out 0.3s forwards; /* ০.৩ সেকেন্ড পর শুরু হবে */
}

/* ৩. অ্যানিমেশনের ফ্রেম (এটি CSS এর একদম শেষে যোগ করতে পারেন) */
@keyframes flyInUp {
    from {
        opacity: 0;
        transform: translateY(30px); /* নিচ থেকে শুরু */
    }
    to {
        opacity: 1;
        transform: translateY(0); /* সঠিক জায়গায় এসে থামবে */
    }
}

    /* === 3D Contact Wrapper === */
    .contact-wrapper {
        max-width: 1200px; margin: -80px auto 60px; padding: 0 20px;
        display: grid; grid-template-columns: 1fr 1.5fr; gap: 40px; /* গ্রিড লেআউট */
        perspective: 1000px; /* ৩D ইফেক্টের জন্য */
    }

    /* Left Side: Info Box */
    .contact-info {
        background: #1a1a1a; color: white; padding: 50px; border-radius: 20px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.2); 
        /* Animation Init */

        opacity: 0; 
    transform: translateX(-100px) rotateY(-10deg); 
    transition: all 1.2s cubic-bezier(0.2, 0.8, 0.2, 1);
    }
    /* Right Side: Form Box */
    .contact-form {
        background: #fff; padding: 50px; border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1); border-top: 5px solid #ffc107;
        /* Animation Init */
        opacity: 0; 
    transform: translateX(100px) rotateY(10deg); 
    transition: all 1.2s cubic-bezier(0.2, 0.8, 0.2, 1);
    }

    /* যখন স্ক্রল করে আসবে */
    .reveal-active { opacity: 1 !important; transform: translateX(0) rotateY(0) !important; }

    /* Form Styles */
    .form-group { margin-bottom: 20px; }
    .form-group input, .form-group textarea {
        width: 100%; padding: 15px; border: 2px solid #eee; border-radius: 10px;
        font-size: 15px; background: #fdfdfd; outline: none; transition: 0.3s;
    }
    .form-group input:focus, .form-group textarea:focus { border-color: #ffc107; background: #fff; }
    
    .submit-btn {
        width: 100%; padding: 15px; background: #1a1a1a; color: #ffc107;
        border: none; border-radius: 10px; font-weight: 800; text-transform: uppercase;
        cursor: pointer; transition: 0.3s; letter-spacing: 1px;
    }
    .submit-btn:hover { background: #000; box-shadow: 0 10px 20px rgba(0,0,0,0.2); transform: translateY(-3px); }

    /* === History Section === */
    /* === Unified Chat Box Styles === */
.history-container { max-width: 1000px; margin: 0 auto 100px; padding: 0 20px; }

.chat-card { 
    background: #fff; border-radius: 20px; padding: 30px; 
    box-shadow: 0 20px 50px rgba(0,0,0,0.05); border: 1px solid #eee;
}

/* চ্যাট উইন্ডো ডিজাইন - এখানে scroll-behavior: auto রাখা হয়েছে দ্রুত লোডের জন্য */
.chat-window {
    height: 500px; 
    overflow-y: auto; 
    padding: 20px;
    display: flex; 
    flex-direction: column; 
    gap: 15px;
    background: #fdfdfd; 
    border-radius: 15px; 
    border: 1px solid #f0f0f0;
    margin-bottom: 20px; 
    scroll-behavior: auto; /* ফিক্স: smooth এর বদলে auto করা হয়েছে */
}

/* মেসেজ বাবল ডিজাইন */
.msg-bubble { max-width: 75%; padding: 12px 18px; border-radius: 20px; position: relative; font-size: 15px; line-height: 1.5; }

/* ইউজারের মেসেজ (ডানে) */
.msg-user { 
    align-self: flex-end; background: #1a1a1a; color: #ffc107; 
    border-bottom-right-radius: 5px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* অ্যাডমিনের মেসেজ (বামে) */
.msg-admin { 
    align-self: flex-start; background: #f1f1f1; color: #333; 
    border-bottom-left-radius: 5px; border-left: 4px solid #ffc107;
}

/* মেসেজের ভেতরে সাবজেক্ট ট্যাগ */
.subject-badge {
    display: inline-block; font-size: 11px; text-transform: uppercase; 
    font-weight: 800; margin-bottom: 5px; opacity: 0.7; letter-spacing: 0.5px;
}

.msg-time { display: block; font-size: 10px; margin-top: 5px; opacity: 0.5; text-align: right; }
    
    .chat-send-btn {
        background: #ffc107; color: #1a1a1a; border: none; padding: 0 25px; border-radius: 10px;
        font-weight: bold; cursor: pointer; height: 45px; transition: 0.3s;
    }
    .chat-send-btn:hover { background: #e0a800; color: #fff; transform: translateY(-2px); }

    .info-card { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; }
    .icon-box { background: rgba(255,193,7,0.1); color: #ffc107; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 20px; }
    
</style>

<div class="contact-hero">
    <h1>Get In Touch</h1>
    <p>We are here to help you with anything you need</p>
</div>

<div class="contact-wrapper">
    
    <div class="contact-info observer-item" style="transition-delay: 0.3s;">
        <h2 style="font-size: 28px; margin-bottom: 35px; color:#ffc107;">Contact Info</h2>
        <div class="info-card"><div class="icon-box">📍</div><div class="info-text"><h4>Visit Us</h4><p>123 Luxury Plaza, Banani Road 11,<br>Dhaka, Bangladesh</p></div></div>
        <div class="info-card"><div class="icon-box">📞</div><div class="info-text"><h4>Call Support</h4><p>+880 1785264709<br>Sat-Thu, 10AM-8PM</p></div></div>
        <div class="info-card"><div class="icon-box">✉️</div><div class="info-text"><h4>Email Us</h4><p>support@halum.com<br>business@halum.com</p></div></div>
    </div>

    <div class="contact-form observer-item" style="transition-delay: 0.6s;">
        <h2 style="margin-bottom: 25px; color:#1a1a1a;">Send Us a Message</h2>
        <form action="contact.php#chatbox" method="POST"> 
            <div class="form-group">
                <input type="text" name="name" 
                       value="<?php echo !empty($header_data['full_name']) ? $header_data['full_name'] : ''; ?>" 
                       placeholder="Your Name"
                       <?php echo isset($_SESSION['user_id']) ? 'readonly style="background:#f1f1f1;"' : ''; ?> required>
            </div>

            <div class="form-group">
                <input type="email" name="email" 
                       value="<?php echo !empty($_SESSION['user_email']) ? $_SESSION['user_email'] : ''; ?>" 
                       placeholder="Your Email"
                       <?php echo isset($_SESSION['user_id']) ? 'readonly style="background:#f1f1f1;"' : ''; ?> required>
            </div>
            
            <div class="form-group"><input type="text" name="subject" placeholder="Subject" required></div>
            <div class="form-group"><textarea name="message" rows="4" placeholder="How can we help?" required></textarea></div>
            
            <button type="submit" name="send_message" class="submit-btn">🚀 Send Message</button>
        </form>
    </div>
</div>


 <?php if(isset($_SESSION['user_id'])): ?>
<div class="history-container" id="chatbox">
    <div class="chat-card observer-item">
        <h2 style="margin-bottom: 25px; color:#1a1a1a; font-size: 24px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 30px;">💬</span> Conversation History
        </h2>
        
        <div class="chat-window" id="userChatWindow">
            <?php
            $u_email = $_SESSION['user_email'];
            $all_msg_res = mysqli_query($conn, "SELECT * FROM `messages` WHERE email = '$u_email' ORDER BY id ASC");
            
            $last_used_subject = "General Inquiry"; // ডিফল্ট সাবজেক্ট

            if(mysqli_num_rows($all_msg_res) > 0){
                while($msg = mysqli_fetch_assoc($all_msg_res)){
                    $last_used_subject = $msg['subject']; // লুপ শেষে এটিই হবে লেটেস্ট সাবজেক্ট
                    
                    if(!empty($msg['message'])){ ?>
                        <div class="msg-bubble msg-user">
                            <span class="subject-badge">Sub: <?php echo htmlspecialchars($msg['subject']); ?></span>
                            <div><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                        </div>
                    <?php }

                    if(!empty($msg['reply'])){ ?>
                        <div class="msg-bubble msg-admin">
                            <strong style="color: #ffc107; font-size: 12px; display: block; margin-bottom: 5px;">SUPPORT TEAM</strong>
                            <div><?php echo nl2br(htmlspecialchars($msg['reply'])); ?></div>
                        </div>
                    <?php } 
                }
            } else {
                echo "<div style='text-align: center; color: #999; margin-top: 100px;'>No message history found.</div>";
            }
            ?>
        </div>

        <div style="background: #fafafa; padding: 15px; border-radius: 15px; border: 1px solid #eee;">
            <form action="contact.php#chatbox" method="POST" style="display: flex; gap: 10px; align-items: flex-end;">
                <input type="hidden" name="name" value="<?php echo htmlspecialchars($header_data['full_name']); ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>">
                
                <input type="hidden" name="subject" value="<?php echo htmlspecialchars($last_used_subject); ?>">
                
                <textarea name="message" class="chat-input" rows="1" placeholder="Reply to this conversation..." required 
                          style="flex: 1; padding: 12px 15px; border-radius: 10px; border: 1px solid #ddd; resize: none; min-height: 45px;"></textarea>
                <button type="submit" name="send_message" class="chat-send-btn">Send</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // চ্যাট উইন্ডো তৎক্ষণাৎ নিচে রাখার লজিক
        var chatWin = document.getElementById('userChatWindow');
        if (chatWin) {
            // কোনো এনিমেশন ছাড়া সরাসরি নিচে নিয়ে যাবে
            chatWin.style.scrollBehavior = "auto"; 
            chatWin.scrollTop = chatWin.scrollHeight;
            
            // মেসেজ লোড হওয়ার পর চাইলে smooth scroll অন করে দিতে পারেন ভবিষ্যতের জন্য
            setTimeout(() => {
                chatWin.style.scrollBehavior = "smooth";
            }, 100);
        }

        // ইনপুট অটো-হাইট এবং এন্টার কী লজিক
        var inputs = document.querySelectorAll('.chat-input');
        inputs.forEach(function(textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });

            textarea.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault(); 
                    var sendBtn = this.form.querySelector('button[name="send_message"]');
                    if(sendBtn) sendBtn.click();
                }
            });
        });

        // নেভিগেশন স্ক্রল ইফেক্ট
        window.addEventListener('scroll', function() {
            var navbar = document.getElementById('navbar');
            if (navbar) {
                if (window.scrollY > 50) navbar.classList.add("scrolled");
                else navbar.classList.remove("scrolled");
            }
        });
        // ফুটার কলামগুলো একে একে ভেসে ওঠার লজিক
const footerObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            entry.target.classList.add('reveal');
        }
    });
}, { threshold: 0.2 });

document.querySelectorAll('.footer-reveal').forEach((col, index) => {
    col.style.transitionDelay = `${index * 0.15}s`; // Staggered delay
    footerObserver.observe(col);
});

        // ৩D এনিমেশন লজিক
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('reveal-active');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.observer-item').forEach((item) => {
            observer.observe(item);
        });
    });
</script>
<?php include 'footer.php'; ?> // এখানে ফুটার অ্যাড করা হলো