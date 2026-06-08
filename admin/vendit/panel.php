<?php
declare(strict_types=1);

/** @var array<string, mixed> $vendit */
$vendit = $vendit ?? vendit_admin_dashboard_data();
?>

<?php if (!empty($vendit['flash'])): ?>
  <div class="vendit-flash <?= vendit_admin_h((string) $vendit['flashType']) ?>">
    <?= vendit_admin_h((string) $vendit['flash']) ?>
  </div>
<?php endif; ?>

<div class="vendit-flow">
  <h3><i class="fas fa-diagram-project"></i> Local integration workflow</h3>
  <div class="vendit-flow-steps">
    <div class="vendit-step"><strong>1. Import</strong>Drop XML into <code>integrations/vendit/import/</code>.</div>
    <div class="vendit-step"><strong>2. Process</strong>Run import from dashboard or test routes.</div>
    <div class="vendit-step"><strong>3. Database</strong>Data stored in <code>vendit_*</code> tables.</div>
    <div class="vendit-step"><strong>4. Export</strong>UTF-16 XML written to <code>integrations/vendit/export/</code>.</div>
    <div class="vendit-step"><strong>5. SFTP</strong>Configure credentials in config when ready.</div>
  </div>
</div>

<div class="vendit-kpi-grid">
  <div class="vendit-kpi">
    <h4>Last sync</h4>
    <div class="num" style="font-size:1rem"><?= vendit_admin_h($vendit['lastSync'] ?? 'Never') ?></div>
  </div>
  <div class="vendit-kpi">
    <h4>Customers</h4>
    <div class="num"><?= (int) $vendit['totalCustomers'] ?></div>
    <div class="sub">Import: <?= vendit_admin_h($vendit['lastCustomerImport'] ?? 'Never') ?></div>
  </div>
  <div class="vendit-kpi">
    <h4>Orders</h4>
    <div class="num"><?= (int) $vendit['totalOrders'] ?></div>
    <div class="sub">Import: <?= vendit_admin_h($vendit['lastOrderImport'] ?? 'Never') ?></div>
  </div>
  <div class="vendit-kpi">
    <h4>Pending export</h4>
    <div class="num" style="color:<?= (int) $vendit['pendingCount'] > 0 ? 'var(--warn)' : 'var(--success)' ?>">
      <?= (int) $vendit['pendingCount'] ?>
    </div>
    <div class="sub">Exported: <?= (int) $vendit['totalOrdersExported'] ?></div>
  </div>
  <div class="vendit-kpi">
    <h4>Success runs</h4>
    <div class="num" style="color:var(--success)"><?= (int) $vendit['successCount'] ?></div>
  </div>
  <div class="vendit-kpi">
    <h4>Error runs</h4>
    <div class="num" style="color:var(--danger)"><?= (int) $vendit['errorCount'] ?></div>
  </div>
</div>

<div class="vendit-actions">
  <form method="post" action="index.php">
    <input type="hidden" name="vendit_action" value="seed_import_samples">
    <button class="btn secondary" type="submit"><i class="fas fa-copy"></i> Copy samples to import</button>
  </form>
  <form method="post" action="index.php">
    <input type="hidden" name="vendit_action" value="import_customers">
    <button class="btn primary" type="submit"><i class="fas fa-file-import"></i> Import customers</button>
  </form>
  <form method="post" action="index.php">
    <input type="hidden" name="vendit_action" value="import_orders">
    <button class="btn primary" type="submit"><i class="fas fa-file-import"></i> Import orders</button>
  </form>
  <form method="post" action="index.php">
    <input type="hidden" name="vendit_action" value="export_customers">
    <button class="btn primary" type="submit"><i class="fas fa-file-export"></i> Export customers</button>
  </form>
  <form method="post" action="index.php">
    <input type="hidden" name="vendit_action" value="export_orders">
    <button class="btn primary" type="submit"><i class="fas fa-file-export"></i> Export orders</button>
  </form>
  <form method="post" action="index.php">
    <input type="hidden" name="vendit_action" value="validate_samples">
    <button class="btn secondary" type="submit"><i class="fas fa-check-double"></i> Validate sample XML</button>
  </form>
</div>

<?php if (!empty($vendit['generatedFiles'])): ?>
<div class="admin-table-card" style="margin-bottom:16px">
  <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
    <div class="panel-heading">Generated export files</div>
    <div class="panel-desc" style="margin-bottom:0">UTF-16 XML files in the export folder.</div>
  </div>
  <div class="table-container">
    <table>
      <thead><tr><th>File</th></tr></thead>
      <tbody>
        <?php foreach ($vendit['generatedFiles'] as $file): ?>
          <tr><td><code><?= vendit_admin_h($file) ?></code></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="admin-table-card" style="margin-bottom:16px">
  <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
    <div class="panel-heading">Sample XML validation</div>
    <div class="panel-desc" style="margin-bottom:0">Reference files in <code>integrations/vendit/xml_samples/</code>.</div>
  </div>
  <div class="table-container">
    <table>
      <thead><tr><th>File</th><th>Type</th><th>Status</th><th>Errors</th></tr></thead>
      <tbody>
        <?php foreach ($vendit['sampleValidations'] as $row): ?>
          <tr>
            <td><code><?= vendit_admin_h($row['file']) ?></code></td>
            <td><?= vendit_admin_h($row['type'] ?? '-') ?></td>
            <td><?= $row['valid'] ? vendit_admin_status_badge('success') : vendit_admin_status_badge('failed') ?></td>
            <td style="color:var(--danger);font-size:.82rem"><?= $row['errors'] === [] ? '—' : vendit_admin_h(implode('; ', $row['errors'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="admin-table-card">
  <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
    <div class="panel-heading">Sync logs</div>
    <div class="panel-desc" style="margin-bottom:0">Import/export history with validation status.</div>
  </div>
  <div class="table-container">
    <table>
      <thead>
        <tr><th>Started</th><th>Type</th><th>Dir</th><th>File</th><th>Status</th><th>Valid</th><th>OK</th><th>Fail</th><th>Message</th></tr>
      </thead>
      <tbody>
        <?php if ($vendit['logs'] === []): ?>
          <tr><td colspan="9"><div class="empty-state" style="padding:24px"><i class="fas fa-clock"></i><p>No sync logs yet.</p></div></td></tr>
        <?php else: ?>
          <?php foreach ($vendit['logs'] as $log): ?>
            <tr>
              <td style="font-size:.78rem"><?= vendit_admin_h($log['started_at'] ?? '') ?></td>
              <td><?= vendit_admin_h($log['sync_type'] ?? '') ?></td>
              <td><?= vendit_admin_h($log['direction'] ?? '') ?></td>
              <td><code style="font-size:.72rem"><?= vendit_admin_h($log['file_name'] ?? '-') ?></code></td>
              <td><?= vendit_admin_status_badge((string) ($log['status'] ?? 'started')) ?></td>
              <td><?= vendit_admin_h($log['validation_status'] ?? 'unknown') ?></td>
              <td><?= (int) ($log['records_processed'] ?? 0) ?></td>
              <td><?= (int) ($log['records_failed'] ?? 0) ?></td>
              <td style="max-width:200px;font-size:.78rem;color:var(--text-muted)"><?= vendit_admin_h($log['message'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<p class="vendit-note">
  Import: <code><?= vendit_admin_h($vendit['config']['import_path'] ?? '') ?></code> ·
  Export: <code><?= vendit_admin_h($vendit['config']['export_path'] ?? '') ?></code> ·
  Logs: <code><?= vendit_admin_h($vendit['config']['logs_path'] ?? '') ?></code> ·
  FTP: <strong><?= !empty($vendit['config']['ftp_host']) ? 'configured' : 'not configured (local folders)' ?></strong>
</p>

<p class="vendit-note" style="margin-top:8px">
  Test routes:
  <code>/test-vendit-import-customers.php</code>,
  <code>/test-vendit-import-orders.php</code>,
  <code>/test-vendit-export-customers.php</code>,
  <code>/test-vendit-export-orders.php</code>
</p>
