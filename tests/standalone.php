<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
if (class_exists('Elementor\\Plugin')) { WP_CLI::error('Esta prueba debe ejecutarse con Elementor desactivado.'); }
foreach (['oldbear_games','oldbear_roadmap','oldbear_contact'] as $tag) {
    if (!shortcode_exists($tag) || trim(do_shortcode('['.$tag.']')) === '') { WP_CLI::error('Fallo del shortcode independiente: '.$tag); }
}
if (!str_contains(do_shortcode('[oldbear_games]'), 'ob-cards')) { WP_CLI::error('El catálogo depende del tema o de Elementor.'); }
if (!str_contains(do_shortcode('[oldbear_roadmap]'), 'ob-roadmap-lanes')) { WP_CLI::error('El roadmap depende del tema o de Elementor.'); }
WP_CLI::success('Catálogo, roadmap y contacto funcionan sin Elementor ni cargar el tema.');
