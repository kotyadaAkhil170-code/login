<?php
// contactus.php
// Single-file Contact Form: backend (MySQL + SMTP) + frontend (nightclub UI)
// Place in: C:\xampp\htdocs\auth_system\contactus.php

// ---------------------- CONFIG ----------------------
$db_host    = '127.0.0.1';
$db_user    = 'root';
$db_pass    = '';                
$db_name    = 'contact_db';       

$admin_email = 'kotyadaakhil170@gmail.com';   

$smtp_host   = 'smtp.gmail.com';
$smtp_port   = 587;
$smtp_user   = 'kotyadaakhil170@gmail.com';
$smtp_pass   = 'rkmhoufziqchbrnn
'; // <-- REPLACE with your 16-char Gmail App Password (no spaces)
// ----------------------------------------------------

// ---------- Helper: read multi-line SMTP responses ----------
function smtp_read_response($fp, &$debug) {
    $resp = '';
    while (($line = fgets($fp, 515)) !== false) {
        $resp .= $line;
        $debug .= "S: {$line}";
        if (isset($line[3]) && $line[3] === ' ') break;
    }
    return $resp;
}

// ---------- Raw SMTP send (STARTTLS) ----------
function smtp_send($host, $port, $user, $pass, $from, $to, $subject, $htmlBody, $replyTo = null) {
    $debug = "";

    $fp = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 15);
    if (!$fp) {
        $debug = "Socket open failed: {$errno} {$errstr}";
        return [false, $debug];
    }

    stream_set_blocking($fp, true);
    stream_set_timeout($fp, 15);

    // Greeting
    $line = fgets($fp, 515); $debug .= "S: {$line}";

    // EHLO
    fwrite($fp, "EHLO localhost\r\n");
    smtp_read_response($fp, $debug);

    // STARTTLS
    fwrite($fp, "STARTTLS\r\n");
    smtp_read_response($fp, $debug);

    // enable TLS
    $crypto_ok = @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    if ($crypto_ok !== true) {
        $debug .= "TLS handshake failed: " . var_export($crypto_ok, true) . "\n";
        fclose($fp);
        return [false, $debug];
    }

    // EHLO again
    fwrite($fp, "EHLO localhost\r\n");
    smtp_read_response($fp, $debug);

    // AUTH LOGIN
    fwrite($fp, "AUTH LOGIN\r\n");
    smtp_read_response($fp, $debug);

    // username
    fwrite($fp, base64_encode($user) . "\r\n");
    smtp_read_response($fp, $debug);

    // password
    fwrite($fp, base64_encode($pass) . "\r\n");
    smtp_read_response($fp, $debug);

    // MAIL FROM
    fwrite($fp, "MAIL FROM:<{$from}>\r\n");
    smtp_read_response($fp, $debug);

    // RCPT TO
    fwrite($fp, "RCPT TO:<{$to}>\r\n");
    smtp_read_response($fp, $debug);

    // DATA
    fwrite($fp, "DATA\r\n");
    smtp_read_response($fp, $debug);

    // headers + body
    $headers  = "From: {$from}\r\n";
    if ($replyTo) $headers .= "Reply-To: {$replyTo}\r\n";
    $headers .= "To: {$to}\r\n";
    $headers .= "Subject: {$subject}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";

    $message = $headers . $htmlBody . "\r\n.\r\n";

    fwrite($fp, $message);
    smtp_read_response($fp, $debug);

    // QUIT
    fwrite($fp, "QUIT\r\n");
    smtp_read_response($fp, $debug);

    fclose($fp);

    // success if server responded with 250 anywhere (simple heuristic)
    if (strpos($debug, "250") !== false) return [true, $debug];

    return [false, $debug];
}

// ------------------ Handle POST submission ------------------
$error = '';
$success = '';
// Keep previous inputs to refill form on error/success
$old_name = '';
$old_email = '';
$old_subject = '';
$old_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_name    = trim($_POST['name'] ?? '');
    $old_email   = trim($_POST['email'] ?? '');
    $old_subject = trim($_POST['subject'] ?? 'Contact Form Message');
    $old_message = trim($_POST['message'] ?? '');

    if ($old_name === '' || $old_email === '' || $old_message === '') {
        $error = 'Name, email and message are required.';
    } elseif (!filter_var($old_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        // ---- SAVE TO DATABASE ----
     // ---- SAVE TO DATABASE ----
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_errno) {
    $error = 'Database connection failed.';
    error_log("DB connect error: {$mysqli->connect_error}");
} else {

    // AUTO-CREATE TABLE IF NOT EXISTS
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS feedback (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      name VARCHAR(191) NOT NULL,
      email VARCHAR(191) NOT NULL,
      subject VARCHAR(255) NOT NULL,
      message TEXT NOT NULL,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    ) ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4
    COLLATE=utf8mb4_unicode_ci;
    ";

    if (!$mysqli->query($createTableSQL)) {
        $error = 'Failed to create feedback table.';
        error_log('Table creation error: ' . $mysqli->error);
    } else {

        $stmt = $mysqli->prepare(
            "INSERT INTO feedback (name, email, subject, message)
             VALUES (?, ?, ?, ?)"
        );

        if (!$stmt) {
            $error = 'Database error (prepare).';
            error_log("DB prepare error: {$mysqli->error}");
        } else {

            $stmt->bind_param(
                'ssss',
                $old_name,
                $old_email,
                $old_subject,
                $old_message
            );

            if (!$stmt->execute()) {
                $error = 'Database error (execute).';
                error_log("DB execute error: {$stmt->error}");
            }

            $stmt->close();
        }
    }

   
}

 $mysqli->close();

}

    // if DB ok, send admin email and auto-reply
    if ($error === '') {
        // ---------- ADMIN EMAIL ----------
        $adminBody = "
        <h2>New Contact Form Submission</h2>
        <p><strong>Name:</strong> " . htmlspecialchars($old_name) . "</p>
        <p><strong>Email:</strong> " . htmlspecialchars($old_email) . "</p>
        <p><strong>Subject:</strong> " . htmlspecialchars($old_subject) . "</p>
        <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($old_message)) . "</p>
        <hr><small>Sent at: " . date('Y-m-d H:i:s') . "</small>
        ";

        list($sent_admin, $debug_admin) = smtp_send(
            $smtp_host, $smtp_port, $smtp_user, $smtp_pass,
            $smtp_user,
            $admin_email,
            "New Feedback from " . $old_name,
            $adminBody,
            $old_email
        );

        // write debug for admin email
        @file_put_contents(__DIR__ . "/smtp_debug_admin.txt", $debug_admin);
        error_log("SMTP debug (admin email):\n" . $debug_admin);

        // ---------- THANK-YOU EMAIL TO USER ----------
        $userBody = "
        <h2>Thank You, " . htmlspecialchars($old_name) . "!</h2>
        <p>We received your message:</p>
        <blockquote>" . nl2br(htmlspecialchars($old_message)) . "</blockquote>
        <p>Our team will get back to you shortly.</p>
        <br><b>Regards,<br>Neon Nights Team</b>
        ";

        list($sent_user, $debug_user) = smtp_send(
            $smtp_host, $smtp_port, $smtp_user, $smtp_pass,
            $smtp_user,
            $old_email,
            "Thank You for Your Feedback!",
            $userBody,
            $admin_email
        );

        // write debug for user email
        @file_put_contents(__DIR__ . "/smtp_debug_user.txt", $debug_user);
        error_log("SMTP debug (user email):\n" . $debug_user);

        if ($sent_admin && $sent_user) {
            $success = "Your message was sent successfully! A confirmation email has also been sent to you.";
            // clear old inputs
            $old_name = $old_email = $old_subject = $old_message = '';
        } elseif ($sent_admin) {
            $success = "Message sent to admin, but automatic user email failed.";
        } else {
            $error = "Saved to DB, but email sending failed. Check smtp_debug files and Apache error log.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Neon Nights — Contact</title>

<!-- Google font -->
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;500;700&display=swap" rel="stylesheet">

<style>
  :root{
    --bg:#07060a;
    --panel-bg: rgba(255,255,255,0.03);
    --neon-pink: #ff2d95;
    --neon-cyan: #00e5ff;
    --neon-yellow: #ffd166;
    --glass: rgba(255,255,255,0.04);
  }
  html,body{height:100%;margin:0;font-family: 'Montserrat', system-ui, -apple-system, sans-serif;background:var(--bg);color:#fff;}

/* Animated glowing radial lights background */
  .bg-lights{
    position:fixed; inset:0; z-index:0; overflow:hidden;
    background: radial-gradient(800px 400px at 10% 20%, rgba(0,229,255,0.06), transparent 8%),
                radial-gradient(600px 300px at 80% 70%, rgba(255,45,149,0.06), transparent 7%),
                linear-gradient(180deg, rgba(10,8,15,1) 0%, rgba(7,6,10,1) 100%);
  }
  .bg-lights::after{
    content:""; position:absolute; inset:0; mix-blend-mode:screen;
    background:
      radial-gradient(circle at 20% 20%, rgba(0,229,255,0.04), transparent 10%),
      radial-gradient(circle at 85% 80%, rgba(255,45,149,0.035), transparent 8%);
    filter: blur(40px);
    animation: floaty 10s ease-in-out infinite;
  }
  @keyframes floaty{
    0%{transform: translateY(0px);}
    50%{transform: translateY(-25px);}
    100%{transform: translateY(0px);}
  }

/* Page layout */
  .wrap{
    position:relative; z-index:2; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:40px;
  }
  .card {
    width:100%; max-width:1100px; display:grid; grid-template-columns: 1fr 420px; gap:32px; align-items:center;
    background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
    border-radius:14px; padding:28px; box-shadow: 0 6px 40px rgba(0,0,0,0.6); border:1px solid rgba(255,255,255,0.04);
    backdrop-filter: blur(6px);
  }

/* Left hero area */
  .hero{
    padding:28px; border-radius:12px; position:relative; overflow:hidden;
    background: linear-gradient(135deg, rgba(0,229,255,0.04), rgba(255,45,149,0.03));
    min-height:420px; display:flex; flex-direction:column; justify-content:center;
  }
  .logo {
    display:flex; align-items:center; gap:12px; margin-bottom:18px;
  }
  .logo .badge{
    width:56px; height:56px; border-radius:10px; display:inline-grid; place-items:center;
    background: linear-gradient(135deg, var(--neon-pink), var(--neon-cyan));
    box-shadow: 0 6px 30px rgba(0,229,255,0.12), 0 2px 10px rgba(255,45,149,0.08);
    font-weight:700; color:#07060a; font-size:22px;
  }
  h1{font-size:36px; margin:0 0 10px; letter-spacing:0.6px; text-shadow: 0 2px 20px rgba(0,229,255,0.08);}
  p.lead{opacity:0.85; margin:0; font-size:16px; line-height:1.5;}

/* Neon strip */
  .neon-strip{position:absolute; right:-120px; top:-60px; width:280px; height:280px; transform:rotate(25deg);
    background: conic-gradient(from 120deg, rgba(0,229,255,0.06), rgba(255,45,149,0.06));
    filter: blur(30px); opacity:0.9;}

/* Right form area */
  .form-panel{
    padding:22px; border-radius:10px; background: linear-gradient(180deg, rgba(255,255,255,0.015), rgba(255,255,255,0.01));
    border: 1px solid rgba(255,255,255,0.03);
  }

  label{display:block; font-size:13px; color: rgba(255,255,255,0.8); margin-bottom:6px; font-weight:500;}
  .field{
    margin-bottom:14px;
  }
  input[type="text"], input[type="email"], textarea{
    width:100%; padding:12px 14px; border-radius:8px; outline:none; border:1px solid rgba(255,255,255,0.06);
    background: linear-gradient(180deg, rgba(255,255,255,0.015), rgba(255,255,255,0.01));
    color:#fff; font-size:14px; box-shadow: inset 0 1px 0 rgba(255,255,255,0.02);
    transition: box-shadow .18s, border-color .18s, transform .08s;
  }
  input:focus, textarea:focus{
    border-color: rgba(0,229,255,0.6);
    box-shadow: 0 0 18px rgba(0,229,255,0.08), 0 0 6px rgba(255,45,149,0.03);
    transform: translateY(-1px);
  }
  textarea{min-height:140px; resize:vertical;}

/* Neon outline on inputs */
  .neon-outline{
    position:relative;
  }
  .neon-outline::after{
    content:''; position:absolute; inset:-2px; border-radius:10px; pointer-events:none;
    background: linear-gradient(90deg, rgba(0,229,255,0.12), rgba(255,45,149,0.08), rgba(255,209,102,0.06));
    opacity:0; transition:opacity .18s, transform .25s;
    filter: blur(10px);
    transform: scale(1.02);
  }
  input:focus + .neon-outline::after,
  textarea:focus + .neon-outline::after { opacity:1; }

/* Submit button */
  .btn {
    display:inline-flex; align-items:center; gap:10px; padding:12px 20px; border-radius:10px;
    border:none; cursor:pointer; color:#07060a; font-weight:700; font-size:15px;
    background: linear-gradient(90deg, var(--neon-pink), var(--neon-cyan));
    box-shadow: 0 10px 30px rgba(0,229,255,0.12), 0 4px 16px rgba(255,45,149,0.08);
    transition: transform .12s, filter .12s;
  }
  .btn:active{ transform: translateY(1px) scale(.997); }
  .btn:hover{ filter:brightness(1.06) saturate(1.05); }
  .btn[disabled]{ opacity:.7; cursor:not-allowed; transform:none; }

/* small footer */
  .small { font-size:13px; color: rgba(255,255,255,0.65); margin-top:12px; }

/* mobile */
  @media (max-width:900px){
    .card{ grid-template-columns: 1fr; padding:18px; gap:18px; }
    .hero{ min-height:220px; }
    .logo .badge{ width:48px; height:48px; font-size:18px; }
  }

/* --- ADDED: loading overlay + spinner --- */
  .overlay-spinner {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none; /* shown via JS */
    align-items: center;
    justify-content: center;
    background: linear-gradient(180deg, rgba(7,6,10,0.6), rgba(7,6,10,0.6));
    backdrop-filter: blur(3px);
  }
  .spinner {
    width: 84px;
    height: 84px;
    border-radius: 50%;
    display: inline-grid;
    place-items: center;
    background: linear-gradient(90deg, rgba(255,45,149,0.08), rgba(0,229,255,0.08));
    box-shadow: 0 10px 40px rgba(0,0,0,0.6);
    position: relative;
  }
  .spinner::after {
    content: '';
    position: absolute;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    border: 6px solid rgba(0,0,0,0.08);
    border-top-color: var(--neon-cyan);
    border-right-color: var(--neon-pink);
    animation: spin 1s linear infinite;
  }
  @keyframes spin { to { transform: rotate(360deg); } }

  .spinner-text {
    font-weight:700;
    font-size:12px;
    color:#dcefff;
    text-align:center;
    margin-top:8px;
    letter-spacing:0.6px;
  }

/* fade-out for server success message */
  .fade-out {
    transition: opacity 400ms ease, transform 400ms ease;
    opacity: 0;
    transform: translateY(-8px);
  }
  .server-message-hidden {
    display:none !important;
  }

</style>

</head>
<body>

<!-- animated background lights -->
<div class="bg-lights" aria-hidden="true"></div>

<div class="wrap">
  <div class="card" role="main" aria-labelledby="contact-title">

    <!-- HERO / Branding (left) -->
    <section class="hero" aria-label="Neon Nights info">
      <div class="logo">
        <div class="badge">NN</div>
        <div>
          <div style="font-weight:700; font-size:18px;">Neon Nights</div>
          <div style="font-size:12px; opacity:0.8;">Club • Events • Vibes</div>
        </div>
      </div>

      <h1 id="contact-title">Hit Us Up — Book a Night or Send Feedback</h1>
      <p class="lead">Got a booking, collaboration, or feedback? Tell us — we’ll get back before last call. Expect bright vibes, quick replies.</p>

      <div class="neon-strip" aria-hidden="true"></div>

      <div style="margin-top:20px; display:flex; gap:12px; flex-wrap:wrap;">
        <div style="padding:10px 14px; border-radius:10px; background:var(--glass); font-size:13px;">
          <strong>Hours</strong><br>8pm — 4am
        </div>
        <div style="padding:10px 14px; border-radius:10px; background:var(--glass); font-size:13px;">
          <strong>Location</strong><br>Downtown Neon District
        </div>
      </div>
    </section>

    <!-- CONTACT FORM (right) -->
    <aside class="form-panel" aria-label="Contact form">
      <!-- server-side messages -->
      <?php if ($error): ?>
        <div style="margin-bottom:12px; padding:10px; background:#3a0f12; border-left:4px solid var(--neon-pink); border-radius:6px; color:#ffdede;">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="server-success" style="margin-bottom:12px; padding:10px; background:#0b2f20; border-left:4px solid var(--neon-cyan); border-radius:6px; color:#d6ffe0;">
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <form id="contactForm" method="POST" novalidate>
        <div class="field">
          <label for="name">Your name</label>
          <input id="name" name="name" type="text" placeholder="e.g. Akhil" required value="<?= htmlspecialchars($old_name) ?>">
          <span class="neon-outline" aria-hidden="true"></span>
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" placeholder="you@example.com" required value="<?= htmlspecialchars($old_email) ?>">
          <span class="neon-outline" aria-hidden="true"></span>
        </div>

        <div class="field">
          <label for="subject">Subject</label>
          <input id="subject" name="subject" type="text" placeholder="Booking / Feedback / Collab" value="<?= htmlspecialchars($old_subject) ?>">
          <span class="neon-outline" aria-hidden="true"></span>
        </div>

        <div class="field">
          <label for="message">Message</label>
          <textarea id="message" name="message" placeholder="Tell us what's up..." required><?= htmlspecialchars($old_message) ?></textarea>
          <span class="neon-outline" aria-hidden="true"></span>
        </div>

        <div style="display:flex; gap:12px; align-items:center; justify-content:space-between;">
          <button id="submitBtn" type="submit" class="btn" aria-label="Send message">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="filter:drop-shadow(0 6px 10px rgba(0,0,0,0.4));">
              <path d="M2 21l21-9L2 3v7l15 2-15 2v7z" fill="#07060a"/>
            </svg>
            Send Message
          </button>
          <div class="small">We reply within 24 hrs — mostly before midnight.</div>
        </div>
      </form>

    </aside>

  </div>
</div>

<!-- Overlay spinner shown after submit -->
<div id="overlaySpinner" class="overlay-spinner" role="status" aria-hidden="true">
  <div style="text-align:center;">
    <div class="spinner" aria-hidden="true"></div>
    <div class="spinner-text">Sending…</div>
  </div>
</div>

<script>
(function(){
  const form = document.getElementById('contactForm');
  const submitBtn = document.getElementById('submitBtn');
  const overlay = document.getElementById('overlaySpinner');

  // Show overlay & disable button when form is submitted.
  form.addEventListener('submit', function (ev) {
    try {
      // show overlay
      overlay.style.display = 'flex';
      overlay.setAttribute('aria-hidden', 'false');

      // disable submit button
      submitBtn.setAttribute('disabled', 'disabled');

      // Optionally change text (we won't mutate innerHTML too much)
      const originalLabel = submitBtn.innerHTML;
      submitBtn.dataset.orig = originalLabel;
      submitBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="filter:drop-shadow(0 6px 10px rgba(0,0,0,0.4));"><path d="M2 21l21-9L2 3v7l15 2-15 2v7z" fill="#07060a"/></svg> Sending...';

      // Let the form submit naturally (page will reload). We do NOT perform AJAX here
      // because backend must handle sending and you said not to change it.
    } catch (e) {
      // if anything fails, allow default behavior
      console.error(e);
    }
  });

  // On page load, if a server success message exists, auto-hide it after 2 seconds.
  document.addEventListener('DOMContentLoaded', function () {
    try {
      // Prefer selecting the exact success element rendered by your PHP:
      const possibleSuccess = document.querySelector('.form-panel .server-success');

      // fallback: look for any element in .form-panel that looks like a success (green/cyan border)
      let successEl = possibleSuccess || null;
      if (!successEl) {
        const divs = Array.from(document.querySelectorAll('.form-panel > div'));
        for (const d of divs) {
          const txt = (d.textContent || '').toLowerCase();
          // heuristics: success messages often contain words like 'sent' and 'thank' or 'confirmation'
          if ((txt.includes('sent') && (txt.includes('thank') || txt.includes('confirmation') || txt.includes('confirmation'))) ||
              txt.includes('thank you')) {
            successEl = d;
            break;
          }
        }
      }

      if (successEl) {
        // keep visible briefly (2s) then fade out and remove
        setTimeout(() => {
          successEl.classList.add('fade-out');
          // after the fade transition, remove it from DOM so it doesn't reserve space
          setTimeout(() => {
            if (successEl && successEl.parentNode) {
              successEl.parentNode.removeChild(successEl);
            }
          }, 420); // just after CSS transition 400ms
        }, 2000);
      }
    } catch (e) {
      console.error(e);
    }
  });

  // If user navigates away or the page unloads, ensure overlay remains visible (UX)
  window.addEventListener('beforeunload', function () {
    try {
      overlay.style.display = 'flex';
      submitBtn.setAttribute('disabled', 'disabled');
    } catch(e){}
  });

})();
</script>

</body>
</html>