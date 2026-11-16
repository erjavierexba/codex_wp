<?php if (!defined('ABSPATH')) { exit; } get_header(); ?>
<main id="contenido" class="shell section">
<?php while (have_posts()) : the_post(); ?>
<article <?php post_class('article-body'); ?>>
    <a class="text-link" href="<?php echo esc_url(home_url('/')); ?>">← Volver al estudio</a>
    <?php if (is_singular('post')) : ?><p class="eyebrow"><?php echo esc_html(get_the_date()); ?></p><?php endif; ?>
    <h1><?php the_title(); ?></h1>
    <?php if (is_singular('oldbear_game')) : ?><div class="game-meta"><?php foreach (['oldbear_status','oldbear_genre','oldbear_platforms'] as $key) : $value = get_post_meta(get_the_ID(), $key, true); if ($value) : ?><span class="pill"><?php echo esc_html($value); ?></span><?php endif; endforeach; ?></div><?php endif; ?>
    <?php if (has_post_thumbnail()) { the_post_thumbnail('large', ['class'=>'article-cover']); } ?>
    <div class="entry-content"><?php the_content(); ?></div>
    <?php wp_link_pages(); ?>
    <?php if (is_singular('oldbear_game')) : $store_url = get_post_meta(get_the_ID(), 'oldbear_store_url', true); if ($store_url) : ?><a class="button" href="<?php echo esc_url($store_url); ?>">Ver en la tienda ↗</a><?php endif; endif; ?>
</article>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
