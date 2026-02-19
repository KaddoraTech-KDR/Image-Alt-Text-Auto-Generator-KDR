<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! current_user_can('manage_options') ) {
  wp_die('Unauthorized');
}

$bulk_url = admin_url('upload.php?page=kdr-iaa-bulk');

// Load current settings
$settings = class_exists('KDR_IAA_Settings') ? KDR_IAA_Settings::get_all() : [];

$saved_notice = false;

// Handle save
if ( isset($_POST['kdr_iaa_save_settings']) ) {

  check_admin_referer('kdr_iaa_settings_save', 'kdr_iaa_settings_nonce');

  $raw = [
    'enable_upload'      => isset($_POST['enable_upload']) ? 1 : 0,
    'case_mode'          => isset($_POST['case_mode']) ? sanitize_text_field(wp_unslash($_POST['case_mode'])) : 'sentence',
    'remove_prefixes'    => isset($_POST['remove_prefixes']) ? 1 : 0,
    'skip_numeric'       => isset($_POST['skip_numeric']) ? 1 : 0,
    'overwrite_existing' => isset($_POST['overwrite_existing']) ? 1 : 0,
    'batch_size'         => isset($_POST['batch_size']) ? absint($_POST['batch_size']) : 50,
  ];

  if ( class_exists('KDR_IAA_Settings') ) {
    $settings = KDR_IAA_Settings::update( $raw );
    $saved_notice = true;
  }
}
?>

<div class="wrap kdr-iaa-wrap">
  <div class="kdr-iaa-header">
    <div>
      <h1 class="wp-heading-inline">Alt Text Generator KDR Settings</h1>
      <p class="description">Control auto-generation rules and bulk processing preferences.</p>
    </div>

    <div class="kdr-iaa-header-actions">
      <a class="button" href="<?php echo esc_url($bulk_url); ?>">Open Bulk Tool</a>
    </div>
  </div>

  <hr class="wp-header-end">

  <?php if ( $saved_notice ) : ?>
    <div class="notice notice-success is-dismissible">
      <p><strong>Settings saved.</strong></p>
    </div>
  <?php endif; ?>

  <div class="kdr-iaa-settings-grid">

    <!-- Left: Settings Card -->
    <div class="kdr-iaa-card kdr-iaa-card--big">
      <div class="kdr-iaa-card-title">
        <h2>Settings</h2>
        <p>Choose how alt text is generated and when it runs.</p>
      </div>

      <form method="post">
        <?php wp_nonce_field('kdr_iaa_settings_save', 'kdr_iaa_settings_nonce'); ?>

        <div class="kdr-iaa-field">
          <label class="kdr-iaa-switch">
            <input type="checkbox" name="enable_upload" <?php checked( !empty($settings['enable_upload']) ); ?>>
            <span class="kdr-iaa-switch-slider"></span>
          </label>
          <div class="kdr-iaa-field-text">
            <div class="kdr-iaa-field-label">Enable auto-generate on upload</div>
            <div class="kdr-iaa-field-help">Automatically adds alt text for new images if missing.</div>
          </div>
        </div>

        <div class="kdr-iaa-divider"></div>

        <div class="kdr-iaa-two-col">
          <div class="kdr-iaa-field-block">
            <label class="kdr-iaa-label" for="case_mode">Text case</label>
            <select class="kdr-iaa-select" id="case_mode" name="case_mode">
              <option value="sentence" <?php selected(($settings['case_mode'] ?? 'sentence'), 'sentence'); ?>>Sentence case (Recommended)</option>
              <option value="title" <?php selected(($settings['case_mode'] ?? 'sentence'), 'title'); ?>>Title Case</option>
            </select>
            <div class="kdr-iaa-help">Example: “red shoes men” → “Red shoes men” or “Red Shoes Men”.</div>
          </div>

          <div class="kdr-iaa-field-block">
            <label class="kdr-iaa-label" for="batch_size">Bulk batch size</label>
            <input class="kdr-iaa-input" type="number" min="10" max="200" id="batch_size" name="batch_size"
                   value="<?php echo esc_attr((int)($settings['batch_size'] ?? 50)); ?>">
            <div class="kdr-iaa-help">Higher = faster but may stress server. Recommended: 50.</div>
          </div>
        </div>

        <div class="kdr-iaa-checkboxes">
          <label class="kdr-iaa-check">
            <input type="checkbox" name="remove_prefixes" <?php checked( !empty($settings['remove_prefixes']) ); ?>>
            <span>Remove camera prefixes (IMG, DSC, PXL, WP, etc.)</span>
          </label>

          <label class="kdr-iaa-check">
            <input type="checkbox" name="skip_numeric" <?php checked( !empty($settings['skip_numeric']) ); ?>>
            <span>Skip numeric-only filenames (e.g. 12345.jpg)</span>
          </label>

          <label class="kdr-iaa-check">
            <input type="checkbox" name="overwrite_existing" <?php checked( !empty($settings['overwrite_existing']) ); ?>>
            <span>Overwrite existing alt text (not recommended)</span>
          </label>
        </div>

        <div class="kdr-iaa-actions-row">
          <button type="submit" class="button button-primary" name="kdr_iaa_save_settings" value="1">
            Save Settings
          </button>
          <a class="button" href="<?php echo esc_url($bulk_url); ?>">Go to Bulk Tool</a>
        </div>

      </form>
    </div>

    <!-- Right: Preview + Quick Link -->
    <div class="kdr-iaa-right-col">

      <div class="kdr-iaa-card">
        <div class="kdr-iaa-card-title">
          <h2>Live Preview</h2>
          <p>Test how your rules convert filenames into alt text.</p>
        </div>

        <div class="kdr-iaa-preview">
          <label class="kdr-iaa-label" for="kdr-iaa-preview-filename">Filename</label>
          <input class="kdr-iaa-input" id="kdr-iaa-preview-filename" type="text"
                 value="red-shoes_men.jpg" autocomplete="off">

          <div class="kdr-iaa-preview-output">
            <div class="kdr-iaa-preview-label">Generated alt text</div>
            <div class="kdr-iaa-preview-value" id="kdr-iaa-preview-output">—</div>
          </div>
        </div>
      </div>

      <div class="kdr-iaa-card">
        <div class="kdr-iaa-card-title">
          <h2>Bulk Generation</h2>
          <p>To generate alt text for existing images, use the bulk tool.</p>
        </div>

        <div class="kdr-iaa-callout">
          <div class="kdr-iaa-callout-title">Media → Alt Text Generator KDR</div>
          <a class="button button-primary" href="<?php echo esc_url($bulk_url); ?>">Open Bulk Tool</a>
        </div>
      </div>

    </div>
  </div>
</div>
