<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<section class="ps-panel ps-import-panel" id="ps-tools-panel">
            <div class="ps-panel-head"><div><h2><?php esc_html_e('Backup, Import & Export','preloader-studio-animated-loading-screens'); ?></h2><p><?php esc_html_e('Move your preloader and navigation designs between installations using a JSON file. Image IDs and page IDs may need reassignment on a different site.','preloader-studio-animated-loading-screens'); ?></p></div></div>
            <div class="ps-tools-grid">
              <div><h3><?php esc_html_e('Export configuration','preloader-studio-animated-loading-screens'); ?></h3><p><?php esc_html_e('Download your current preloader settings, menu settings and saved designs.','preloader-studio-animated-loading-screens'); ?></p><a class="button button-secondary" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=ps_studio_export'),'ps_studio_export')); ?>"><?php esc_html_e('Download JSON','preloader-studio-animated-loading-screens'); ?></a></div>
              <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
                <h3><?php esc_html_e('Import configuration','preloader-studio-animated-loading-screens'); ?></h3><p><?php esc_html_e('Import replaces current studio settings, menu settings and the saved design library. Back up first.','preloader-studio-animated-loading-screens'); ?></p>
                <input type="hidden" name="action" value="ps_studio_import"/><?php wp_nonce_field('ps_studio_import','ps_studio_import_nonce'); ?>
                <input type="file" name="ps_import_file" accept=".json,application/json" required aria-label="<?php esc_attr_e('Choose exported JSON file','preloader-studio-animated-loading-screens'); ?>" />
                <button type="submit" class="button button-secondary"><?php esc_html_e('Import JSON','preloader-studio-animated-loading-screens'); ?></button>
              </form>
            </div>
          </section>
