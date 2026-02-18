<div class="wrap">
    <h1>Alt Text Generator KDR Settings</h1>

    <form method="post">
        <table class="form-table">
            <tr>
                <th>Enable auto-generate on upload</th>
                <td>
                    <input type="checkbox" name="kdr_iaa_enable_upload" value="1">
                </td>
            </tr>
        </table>

        <p>
            <button class="button button-primary">
                Save Settings
            </button>
        </p>
    </form>
    <div class="notice notice-info">
        <p>
            To bulk generate alt text for existing images, go to:
            <a href="<?php echo admin_url('upload.php?page=kdr-iaa-bulk'); ?>">
                Media → Alt Text Generator KDR
            </a>
        </p>
    </div>

</div>