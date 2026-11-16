<?php if (!defined('ABSPATH')) { exit; } ?>
<footer class="site-footer">
    <div class="shell footer-top">
        <?php oldbear_brand(); ?>
        <p>Hecho con instinto.<br><span>Jugado con el corazón.</span></p>
        <div class="social-links">
        <?php foreach (['steam_url'=>'Steam','itch_url'=>'itch.io','discord_url'=>'Discord'] as $key=>$label) : if (oldbear_text($key)) : ?>
            <a href="<?php echo esc_url(oldbear_text($key)); ?>"><?php echo esc_html($label); ?> <span aria-hidden="true">↗</span></a>
        <?php endif; endforeach; ?>
        </div>
    </div>
    <div class="shell footer-bottom">
        <small>© <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html(get_bloginfo('name')); ?>.</small>
        <div><?php if (get_privacy_policy_url()) : ?><a href="<?php echo esc_url(get_privacy_policy_url()); ?>">Privacidad</a><?php endif; ?> <a href="#contenido">Volver arriba ↑</a></div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
