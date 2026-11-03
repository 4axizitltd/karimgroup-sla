<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/* $sla = Sla_license::check() result. Shown on every page of a logged-in user. */
$sla_act = site_url('setting/sla');
?>
<style>
    .sla-ov { position:fixed; inset:0; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,.55); z-index:100000;
              display:flex; align-items:center; justify-content:center; padding:16px; font-family:Arial,Helvetica,sans-serif; }
    .sla-md { background:#fff; max-width:480px; width:100%; border-radius:6px; box-shadow:0 10px 40px rgba(0,0,0,.3);
              border-top:6px solid #f0ad4e; padding:26px 28px; color:#333; text-align:left; }
    .sla-md h3 { margin:0 0 10px; font-size:20px; color:#b9770e; }
    .sla-md p { margin:8px 0; line-height:1.55; font-size:14px; }
    .sla-md .sla-days { font-size:34px; font-weight:bold; color:#b9770e; margin:6px 0; }
    .sla-md .sla-btns { margin-top:18px; text-align:right; }
    .sla-md .sla-btn { display:inline-block; padding:8px 16px; border-radius:3px; border:0; cursor:pointer; font-size:14px;
                       text-decoration:none; margin-left:6px; }
    .sla-md .sla-primary { background:#337ab7; color:#fff; }
    .sla-md .sla-grey { background:#e5e5e5; color:#333; }
    .sla-bar { position:fixed; left:0; right:0; bottom:0; z-index:99999; background:#c9302c; color:#fff; text-align:center;
               padding:9px 12px; font:14px Arial,Helvetica,sans-serif; }
    .sla-bar a { color:#fff; text-decoration:underline; font-weight:bold; }
    .sla-bar.sla-bar-warn { background:#f0ad4e; color:#3b2a00; }
    .sla-bar.sla-bar-warn a { color:#3b2a00; }
    .sla-bar.sla-bar-urgent { background:#c9302c; color:#fff; }
    .sla-bar.sla-bar-urgent a { color:#fff; }
    body.sla-has-bar { padding-bottom:42px; }
</style>

<?php if ($sla['valid']) { /* ---- within 15 days of expiry: warning, can be closed ---- */ ?>
<div class="sla-ov" id="slaWarn" style="display:none;">
    <div class="sla-md" role="dialog" aria-modal="true">
        <h3>SLA expiring soon</h3>
        <div class="sla-days"><?php echo (int) $sla['days_left']; ?> days left</div>
        <p>Your software SLA with 4axiz expires on <strong><?php echo date('d M Y', strtotime($sla['until'])); ?></strong>.
           After that, <strong>reports will be locked</strong>.</p>
        <p>আপনার SLA মেয়াদ <strong><?php echo date('d M Y', strtotime($sla['until'])); ?></strong> তারিখে শেষ হবে।
           এরপর রিপোর্ট বন্ধ হয়ে যাবে। অনুগ্রহ করে 4axiz-এর সাথে যোগাযোগ করে নবায়ন করুন।</p>
        <div class="sla-btns">
            <button type="button" class="sla-btn sla-grey" id="slaWarnClose">Cancel</button>
            <a class="sla-btn sla-primary" href="<?php echo $sla_act; ?>">Enter activation code</a>
        </div>
    </div>
</div>
<script>
(function () {
    var key = 'sla_warn_hide', today = '<?php echo date('Y-m-d'); ?>';
    // "Cancel" hides the popup for the rest of the day
    if (document.cookie.indexOf(key + '=' + today) !== -1) return;
    var el = document.getElementById('slaWarn');
    el.style.display = 'flex';
    document.getElementById('slaWarnClose').onclick = function () {
        document.cookie = key + '=' + today + '; path=/; max-age=86400';
        el.style.display = 'none';
    };
})();
</script>

<?php
/* ---- permanent bottom bar for the whole warning window (cannot be closed) ---- */
$sla_left = (int) $sla['days_left'];
$sla_until = date('d M Y', strtotime($sla['until']));
if ($sla_left <= 0) {
    $sla_en = 'SLA expires TODAY (' . $sla_until . ')';
    $sla_bn = 'আজই SLA মেয়াদ শেষ';
} else {
    $sla_en = 'SLA expires in ' . $sla_left . ' day' . ($sla_left == 1 ? '' : 's') . ' (' . $sla_until . ')';
    $sla_bn = 'SLA মেয়াদ শেষ হতে আর ' . $sla_left . ' দিন বাকি';
}
?>
<div class="sla-bar <?php echo $sla_left <= 3 ? 'sla-bar-urgent' : 'sla-bar-warn'; ?>" id="slaWarnBar">
    &#9888; <?php echo $sla_en; ?> &mdash; reports will be locked after that. / <?php echo $sla_bn; ?>, এরপর রিপোর্ট বন্ধ হবে।
    <a href="<?php echo $sla_act; ?>">Enter activation code</a>
</div>
<script>document.body.className += ' sla-has-bar';</script>

<?php } else { /* ---- expired / not active: reports are locked; permanent bar on other pages ---- */ ?>
<div class="sla-bar">
    SLA expired &mdash; reports are locked. / SLA মেয়াদ শেষ &mdash; রিপোর্ট বন্ধ।
    <a href="<?php echo $sla_act; ?>">Enter activation code</a>
</div>
<?php } ?>
