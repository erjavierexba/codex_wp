<?php
if (!defined('ABSPATH')) { exit; }
if (!class_exists('Elementor\\Plugin') || get_post_meta(get_queried_object_id(), '_elementor_edit_mode', true) !== 'builder') {
    require get_theme_file_path('singular.php');
    return;
}
get_header();
echo '<main id="contenido" class="oldbear-elementor-page">';
while (have_posts()) { the_post(); the_content(); }
echo '</main>';
get_footer();
