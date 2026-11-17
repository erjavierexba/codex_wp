<?php if (!defined('ABSPATH')) { exit; } get_header(); ?>
<?php $oldbear_404 = (string) apply_filters('oldbear_theme_part', '', '404'); ?>
<?php if ($oldbear_404 !== '') : ?>
<main id="contenido" class="oldbear-elementor-page"><?php echo $oldbear_404; // Elementor template output. ?></main>
<?php else : ?>
<main id="contenido" class="shell section error-page"><p class="eyebrow">404 / FUERA DEL MAPA</p><h1>Hasta los osos se pierden a veces.</h1><p>Esta página no está por aquí. Volvamos al campamento.</p><a class="button" href="<?php echo esc_url(home_url('/')); ?>">Volver al inicio ↗</a></main>
<?php endif; ?>
<?php get_footer(); ?>
