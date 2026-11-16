<?php
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<main id="contenido">
    <section class="hero shell" aria-labelledby="hero-title">
        <div class="hero-copy">
            <p class="eyebrow"><span class="tiny-star" aria-hidden="true">✳</span> <?php echo esc_html(oldbear_text('eyebrow')); ?></p>
            <h1 id="hero-title"><?php echo esc_html(oldbear_text('hero_title')); ?></h1>
            <p class="hero-description"><?php echo esc_html(oldbear_text('hero_text')); ?></p>
            <div class="button-row">
                <a class="button" href="#juegos">Explora nuestros mundos <span aria-hidden="true">↗</span></a>
                <?php if (oldbear_text('trailer_url')) : ?><a class="text-link" href="<?php echo esc_url(oldbear_text('trailer_url')); ?>">Ver tráiler <span aria-hidden="true">▶</span></a><?php endif; ?>
            </div>
            <p class="hero-footnote"><span aria-hidden="true">●</span> Ideas propias. Pasión por jugar.</p>
        </div>
        <figure class="hero-art">
            <img src="<?php echo esc_url(get_theme_file_uri('assets/wilderness.svg')); ?>" alt="Un oso contempla un valle de montañas bajo un gran sol ámbar." width="900" height="1000" fetchpriority="high">
            <figcaption><span>THE NEXT ADVENTURE</span><span>STARTS HERE ↗</span></figcaption>
        </figure>
    </section>
    <div class="manifesto-strip" aria-label="Nuestra filosofía"><div class="shell"><span>MUNDOS PROPIOS</span><b aria-hidden="true">✳</b><span>ESPÍRITU INDIE</span><b aria-hidden="true">✳</b><span>GANAS DE JUGAR</span><b aria-hidden="true">✳</b></div></div>
    <section class="section shell" id="juegos" aria-labelledby="games-title">
        <div class="section-heading"><div><p class="eyebrow">01 / NUESTROS MUNDOS</p><h2 id="games-title">La aventura toma forma.</h2></div><p>Cada mundo empieza con una pregunta:<br>¿y si lo jugamos de otra manera?</p></div>
        <?php $games = new WP_Query(['post_type'=>'oldbear_game','post_status'=>'publish','posts_per_page'=>6,'no_found_rows'=>true]); ?>
        <div class="game-grid">
        <?php if ($games->have_posts()) : while ($games->have_posts()) : $games->the_post(); ?>
            <article class="game-card">
                <a class="game-art" href="<?php the_permalink(); ?>">
                <?php if (has_post_thumbnail()) : the_post_thumbnail('large', ['loading'=>'lazy']); else : ?><img src="<?php echo esc_url(get_theme_file_uri('assets/world-forest.svg')); ?>" alt="" width="960" height="640" loading="lazy"><?php endif; ?>
                <span class="game-arrow" aria-hidden="true">↗</span><span class="screen-reader-text"><?php the_title(); ?></span>
                </a>
                <div class="game-meta"><span class="pill"><?php echo esc_html(get_post_meta(get_the_ID(), 'oldbear_status', true) ?: 'En desarrollo'); ?></span><span><?php echo esc_html(get_post_meta(get_the_ID(), 'oldbear_genre', true)); ?></span></div>
                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <?php the_excerpt(); ?>
            </article>
        <?php endwhile; wp_reset_postdata(); else : ?>
            <article class="game-card concept">
                <div class="game-art"><img src="<?php echo esc_url(get_theme_file_uri('assets/world-forest.svg')); ?>" alt="Concepto gráfico de un bosque nocturno con una puerta luminosa." width="960" height="640" loading="lazy"><span class="concept-label">CONCEPTO VISUAL · MUESTRA</span></div>
                <div class="game-meta"><span class="pill">Lo que viene</span><span>Explorar lo desconocido</span></div><h3>Universos por descubrir.</h3><p>Estamos preparando este espacio para nuestras próximas aventuras.</p>
            </article>
            <article class="game-card concept">
                <div class="game-art"><img src="<?php echo esc_url(get_theme_file_uri('assets/world-dunes.svg')); ?>" alt="Concepto gráfico de dunas anaranjadas y ruinas bajo dos lunas." width="960" height="640" loading="lazy"><span class="concept-label">CONCEPTO VISUAL · MUESTRA</span></div>
                <div class="game-meta"><span class="pill">En el horizonte</span><span>Imaginar otros caminos</span></div><h3>Historias por jugar.</h3><p>Las primeras novedades tendrán su sitio aquí. La curiosidad ya está en marcha.</p>
            </article>
        <?php endif; ?>
        </div>
    </section>
    <section class="studio-section" id="estudio" aria-labelledby="studio-title">
        <div class="shell studio-grid">
            <div class="studio-seal" aria-hidden="true"><span>STAY CURIOUS · PLAY WILD</span><img src="<?php echo esc_url(get_theme_file_uri('assets/bear-mark.svg')); ?>" alt="" width="200" height="200"><small>OLD BEAR / NEW WORLDS</small></div>
            <div><p class="eyebrow">02 / DETRÁS DEL OSO</p><h2 id="studio-title"><?php echo esc_html(oldbear_text('studio_title')); ?></h2><p class="lead"><?php echo esc_html(oldbear_text('studio_text')); ?></p><p><?php echo esc_html(oldbear_text('studio_note')); ?></p><a class="text-link" href="#contacto">Conversemos <span aria-hidden="true">↗</span></a></div>
        </div>
    </section>
    <section class="section shell principles" aria-labelledby="process-title">
        <div class="section-heading"><div><p class="eyebrow">NUESTRA FORMA DE JUGAR</p><h2 id="process-title">De la chispa al mando.</h2></div></div>
        <div class="principle-grid">
            <article><span class="principle-number">01 —</span><h3>Encontrar la chispa.</h3><p>Una buena idea invita a probar. El primer paso es descubrir qué hace especial cada experiencia.</p></article>
            <article><span class="principle-number">02 —</span><h3>Darle personalidad.</h3><p>Mecánicas, arte y sonido al servicio del mismo mundo. Cada detalle cuenta una parte de la historia.</p></article>
            <article><span class="principle-number">03 —</span><h3>Jugar. Escuchar. Pulir.</h3><p>Probar, aprender y volver a probar. Porque las mejores ideas crecen cuando llegan a las manos de alguien.</p></article>
        </div>
    </section>
    <section class="section shell journal-section" id="diario" aria-labelledby="journal-title">
        <div class="section-heading"><div><p class="eyebrow">03 / DESDE LA MADRIGUERA</p><h2 id="journal-title">El diario del estudio.</h2></div>
        <?php if (get_option('page_for_posts')) : ?><a class="text-link" href="<?php echo esc_url(get_permalink((int) get_option('page_for_posts'))); ?>">Todo el diario ↗</a><?php endif; ?></div>
        <?php $journal = new WP_Query(['post_type'=>'post','post_status'=>'publish','posts_per_page'=>3,'no_found_rows'=>true]); ?>
        <?php if ($journal->have_posts()) : ?><div class="journal-grid"><?php while ($journal->have_posts()) : $journal->the_post(); ?><article class="journal-card"><p class="eyebrow"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time></p><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?> <span aria-hidden="true">↗</span></a></h3><?php the_excerpt(); ?></article><?php endwhile; wp_reset_postdata(); ?></div>
        <?php else : ?><div class="journal-empty"><span aria-hidden="true">✳</span><div><h3>Las buenas historias también se cuentan por el camino.</h3><p>Próximamente: procesos creativos, avances y pequeñas historias del desarrollo.</p></div></div><?php endif; ?>
    </section>
    <section class="contact-section" id="contacto" aria-labelledby="contact-title">
        <div class="shell contact-grid">
            <div><p class="eyebrow">04 / HABLEMOS</p><h2 id="contact-title">Toda gran aventura empieza con un hola.</h2><p class="lead"><?php echo esc_html(oldbear_text('contact_text')); ?></p>
            <?php if (oldbear_text('public_email')) : ?><a class="contact-email" href="<?php echo esc_url('mailto:' . oldbear_text('public_email')); ?>"><?php echo esc_html(oldbear_text('public_email')); ?> ↗</a><?php endif; ?>
            <p class="contact-note">Colaboraciones · Prensa · Comunidad</p></div>
            <div class="contact-panel"><?php if (shortcode_exists('oldbear_contact')) : echo do_shortcode('[oldbear_contact]'); else : ?><h3>Nos vemos muy pronto.</h3><p>El canal de contacto estará disponible próximamente.</p><?php endif; ?></div>
        </div>
    </section>
</main>
<?php get_footer(); ?>
