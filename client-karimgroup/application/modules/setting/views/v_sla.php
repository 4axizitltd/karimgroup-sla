<?php
$result = $this->session->flashdata('sla_result');
$labels = array(
    'active'        => array('success', 'Active'),
    'none'          => array('danger', 'Not activated'),
    'expired'       => array('danger', 'Expired'),
    'not_started'   => array('warning', 'Not started yet'),
    'invalid'       => array('danger', 'Invalid'),
    'clock'         => array('danger', 'Wrong system date'),
    'sync_required' => array('danger', 'Online check required'),
);
$label = isset($labels[$sla['status']]) ? $labels[$sla['status']] : array('default', $sla['status']);
?>
<ul class="breadcrumb" style="text-align:center;">
    <li style="display:inline;list-style:none;"><a href="setting/sla">SLA Activation</a></li>
    <li style="float:right;margin-right:-30px;color:#707478;display:inline;list-style:none;"><?php echo date("j M, Y"); ?></li>
</ul>

<div class="row">
    <div class="col-md-8 col-md-offset-2">

        <?php if (!empty($result)) { ?>
            <div class="alert alert-<?php echo $result['ok'] ? 'success' : 'danger'; ?>">
                <?php echo htmlspecialchars($result['message']); ?>
            </div>
        <?php } ?>

        <div class="panel panel-default">
            <div class="panel-heading"><strong>SLA status</strong></div>
            <div class="panel-body">
                <table class="table table-bordered" style="margin-bottom:0;">
                    <tr>
                        <th style="width:35%;">Status</th>
                        <td><span class="label label-<?php echo $label[0]; ?>" style="font-size:13px;"><?php echo $label[1]; ?></span></td>
                    </tr>
                    <tr>
                        <th>Details</th>
                        <td><?php echo htmlspecialchars($sla['message']); ?></td>
                    </tr>
                    <?php if ($sla['from']) { ?>
                    <tr>
                        <th>Valid period</th>
                        <td><?php echo date('d M Y', strtotime($sla['from'])); ?> &ndash; <?php echo date('d M Y', strtotime($sla['until'])); ?>
                            <?php if ($sla['valid']) { ?>(<?php echo (int) $sla['days_left']; ?> days left)<?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if (!empty($sla['payload']['lid'])) { ?>
                    <tr>
                        <th>License ID</th>
                        <td><?php echo htmlspecialchars($sla['payload']['lid']); ?></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <th>Installation ID</th>
                        <td><code><?php echo htmlspecialchars($sla['install_id']); ?></code></td>
                    </tr>
                    <?php if ($online) { ?>
                    <tr>
                        <th>Last online check</th>
                        <td><?php echo !empty($state['last_sync_ok']) ? date('d M Y h:i A', $state['last_sync_ok']) : 'Never'; ?></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading"><strong>Activate / Renew</strong></div>
            <div class="panel-body">
                <form action="<?php echo site_url('setting/sla/activate'); ?>" method="post">
                    <div class="form-group">
                        <label>Activation code from 4axiz</label>
                        <textarea class="form-control" name="code" rows="3" required
                                  placeholder="<?php echo $online ? 'e.g. KG-7F3A-92BC-11DE  (or paste the full license key)' : 'Paste the license key you received from 4axiz'; ?>"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Activate</button>
                </form>
                <p class="text-muted" style="margin-top:12px;">
                    Reports require an active SLA. After payment, 4axiz will send you an activation code.
                </p>
            </div>
        </div>

    </div>
</div>
