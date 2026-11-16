<?php if (!defined('ABSPATH')) { exit; } get_header(); ?>
<main id="contenido" class="shell section article-index">
    <p class="eyebrow">DESDE LA MADRIGUERA</p>
    <h1><?php if (is_home()) { echo 'El diario del estudio.'; } elseif (is_search()) { printf('Resultados para: %s', esc_html(get_search_query())); } else { the_archive_title(); } ?></h1>
    <?php if (have_posts()) : ?><div class="journal-grid"><?php while (have_posts()) : the_post(); ?><article class="journal-card"><?php if (has_post_thumbnail()) : ?><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium_large', ['loading'=>'lazy']); ?></a><?php endif; ?><p class="eyebrow"><?php echo esc_html(get_the_date()); ?></p><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article><?php endwhile; ?></div><?php the_posts_pagination(['prev_text'=>'← Anterior','next_text'=>'Siguiente →']); ?>
    <?php else : ?><p>Este espacio está preparando su primera historia. Vuelve pronto.</p><a class="button" href="<?php echo esc_url(home_url('/')); ?>">Volver al inicio ↗</a><?php endif; ?>
</main>
<?php get_footer(); ?>
