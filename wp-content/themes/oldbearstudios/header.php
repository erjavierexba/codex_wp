<?php if (!defined('ABSPATH')) { exit; } ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#contenido">Saltar al contenido</a>
<?php $oldbear_header = (string) apply_filters('oldbear_theme_part', '', 'header'); ?>
<?php if ($oldbear_header !== '') : ?>
<div class="ob-part ob-part-header"><?php echo $oldbear_header; // Elementor template output. ?></div>
<?php else : ?>
<header class="site-header">
    <div class="shell header-inner">
        <?php oldbear_brand(); ?>
        <button class="menu-toggle" aria-controls="site-nav" aria-expanded="false" hidden>Menú <span aria-hidden="true">☰</span></button>
        <nav id="site-nav" aria-label="Navegación principal">
            <?php wp_nav_menu(['theme_location'=>'primary','container'=>false,'fallback_cb'=>'oldbear_fallback_menu','depth'=>1]); ?>
        </nav>
    </div>
</header>
<?php endif; ?>
