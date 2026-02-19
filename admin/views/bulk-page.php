<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$bulk_url = admin_url('upload.php?page=kdr-iaa-bulk');
$settings_url = admin_url('admin.php?page=kdr-iaa-settings');
?>

<div class="wrap kdr-iaa-wrap">
  <div class="kdr-iaa-header">
    <div>
      <h1 class="wp-heading-inline">Alt Text Generator KDR</h1>
      <p class="description">Scan your Media Library and bulk-generate missing alt text (safe batching, progress tracking).</p>
    </div>

    <div class="kdr-iaa-header-actions">
      <a class="button" href="<?php echo esc_url($settings_url); ?>">Settings</a>
    </div>
  </div>

  <hr class="wp-header-end">

  <div class="kdr-iaa-cards">
    <div class="kdr-iaa-card">
      <div class="kdr-iaa-card-label">Missing Alt Text</div>
      <div class="kdr-iaa-card-value" id="kdr-iaa-missing-count">—</div>
      <div class="kdr-iaa-card-sub">Images detected without alt text</div>
    </div>

    <div class="kdr-iaa-card">
      <div class="kdr-iaa-card-label">Generated</div>
      <div class="kdr-iaa-card-value" id="kdr-iaa-generated-count">0</div>
      <div class="kdr-iaa-card-sub">Updated in this session</div>
    </div>

    <div class="kdr-iaa-card">
      <div class="kdr-iaa-card-label">Skipped</div>
      <div class="kdr-iaa-card-value" id="kdr-iaa-skipped-count">0</div>
      <div class="kdr-iaa-card-sub">Already had alt / rules skipped</div>
    </div>

    <div class="kdr-iaa-card">
      <div class="kdr-iaa-card-label">Errors</div>
      <div class="kdr-iaa-card-value" id="kdr-iaa-errors-count">0</div>
      <div class="kdr-iaa-card-sub">File missing or update failure</div>
    </div>
  </div>

  <div class="kdr-iaa-panel">
    <div class="kdr-iaa-panel-row">
      <div class="kdr-iaa-actions">
        <button class="button button-primary" id="kdr-iaa-scan-btn">
          Scan Library
        </button>

        <button class="button button-primary" id="kdr-iaa-generate-btn" disabled>
          Generate Alt Text
        </button>

        <button class="button" id="kdr-iaa-stop-btn" disabled>
          Stop
        </button>

        <button class="button" id="kdr-iaa-reset-btn" disabled>
          Reset Session
        </button>
      </div>

      <div class="kdr-iaa-status">
        <span class="kdr-iaa-badge" id="kdr-iaa-status-badge">Idle</span>
      </div>
    </div>

    <div class="kdr-iaa-progress">
      <div class="kdr-iaa-progress-bar">
        <div class="kdr-iaa-progress-fill" id="kdr-iaa-progress-fill" style="width:0%"></div>
      </div>
      <div class="kdr-iaa-progress-meta">
        <span id="kdr-iaa-progress-text">Scan to begin.</span>
        <span id="kdr-iaa-progress-percent">0%</span>
      </div>
    </div>

    <div class="kdr-iaa-log" id="kdr-iaa-log" aria-live="polite">
      <div class="kdr-iaa-log-line">Tip: Start with “Scan Library” to calculate how many images are missing alt text.</div>
    </div>
  </div>

  <div class="kdr-iaa-footer-note">
    <p>
      This tool processes images in small batches to avoid timeouts. If you stop, you can resume without harm.
    </p>
  </div>
</div>
