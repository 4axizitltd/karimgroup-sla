<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/* Shown instead of any report when the SLA is not active. The popup has NO close/cancel button. */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SLA Expired - HR &amp; Payroll</title>
    <base href="<?php echo base_url(); ?>">
    <style>
        html, body { margin:0; height:100%; font-family: Arial, Helvetica, sans-serif; }
        body { background:#e9edf2; }
        .ov { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,.6);
              display:flex; align-items:center; justify-content:center; padding:16px; }
        .md { background:#fff; max-width:500px; width:100%; border-radius:6px; border-top:6px solid #d9534f;
              box-shadow:0 10px 40px rgba(0,0,0,.35); padding:28px 30px; color:#333; }
        h1 { margin:0 0 10px; font-size:21px; color:#c9302c; }
        p { line-height:1.6; margin:8px 0; font-size:14px; }
        .msg { background:#fdf2f2; border:1px solid #f5c6cb; padding:10px 14px; margin:14px 0; font-size:14px; }
        .contact { background:#f7f7f7; padding:10px 14px; font-size:13px; }
        .btns { margin-top:18px; text-align:right; }
        .btn { display:inline-block; padding:9px 16px; border-radius:3px; text-decoration:none; font-size:14px; margin-left:6px; }
        .btn-p { background:#337ab7; color:#fff; }
        .btn-g { background:#e5e5e5; color:#333; }
        small { color:#888; }
    </style>
</head>
<body>
<div class="ov">
    <div class="md" role="alertdialog" aria-modal="true">
        <h1>SLA expired &mdash; reports locked</h1>
        <p>Your software support agreement (SLA) with 4axiz is not active, so reports cannot be generated.</p>
        <p>আপনার SLA মেয়াদ শেষ হয়ে গেছে, তাই রিপোর্ট বন্ধ। 4axiz-এর সাথে যোগাযোগ করে SLA নবায়ন করুন এবং activation code দিন।</p>
        <div class="msg"><?php echo htmlspecialchars($sla['message']); ?></div>
        <div class="contact">
            Contact 4axiz to renew the SLA and get an activation code.<br>
            <small>Installation ID: <?php echo htmlspecialchars($sla['install_id']); ?></small>
        </div>
        <div class="btns">
            <a class="btn btn-g" href="<?php echo site_url('dashboard'); ?>">Back to dashboard</a>
            <a class="btn btn-p" href="<?php echo site_url('setting/sla'); ?>">Enter activation code</a>
        </div>
    </div>
</div>
</body>
</html>
