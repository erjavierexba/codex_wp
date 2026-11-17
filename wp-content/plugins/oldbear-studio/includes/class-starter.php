<?php
namespace OldBearStudio;
if (!defined('ABSPATH')) { exit; }

final class Starter {
    private static function widget(string $type, array $settings): array {
        return ['id'=>substr(str_replace('-', '', wp_generate_uuid4()),0,8),'elType'=>'widget','widgetType'=>$type,'settings'=>$settings,'elements'=>[]];
    }
    private static function section(array $elements, string $class = '', string $id = ''): array {
        return ['id'=>substr(str_replace('-', '', wp_generate_uuid4()),0,8),'elType'=>'container','isInner'=>false,
            'settings'=>['content_width'=>str_contains($class, 'ob-editor-section') ? 'boxed' : 'full','boxed_width'=>['unit'=>'px','size'=>1200],'flex_direction'=>$class === 'ob-editor-section ob-studio-layout' ? 'row' : 'column','flex_direction_mobile'=>'column','css_classes'=>$class,'_element_id'=>$id,'padding'=>['unit'=>'px','top'=>str_contains($class,'ob-editor-section') ? '90' : '0','right'=>str_contains($class,'ob-editor-section') ? '24' : '0','bottom'=>str_contains($class,'ob-editor-section') ? '90' : '0','left'=>str_contains($class,'ob-editor-section') ? '24' : '0','isLinked'=>false],'flex_gap'=>['unit'=>'px','row'=>'0','column'=>'0','isLinked'=>true]],
            'elements'=>$elements];
    }
    private static function heading(string $title, string $tag = 'h2'): array {
        return self::widget('heading', ['title'=>$title,'header_size'=>$tag,'title_color'=>'#f2eadc']);
    }
    public static function document(): array {
        return [
            self::section([self::widget('oldbear-hero',[])], 'ob-editor-hero'),
            self::section([self::widget('text-editor',['editor'=>'<p>MUNDOS PROPIOS &nbsp; / &nbsp; ESPÍRITU INDIE &nbsp; / &nbsp; GANAS DE JUGAR</p>'])], 'ob-editor-strip'),
            self::section([
                self::widget('text-editor',['editor'=>'<p class="ob-eyebrow">01 / NUESTROS MUNDOS</p>']),
                self::heading('La aventura toma forma.'),
                self::widget('text-editor',['editor'=>'<p>Cada mundo empieza con una pregunta: ¿y si lo jugamos de otra manera?</p>']),
                self::widget('oldbear-catalog',['per_page'=>6,'filters'=>'yes'])
            ], 'ob-editor-section', 'juegos'),
            self::section([
                self::section([self::widget('image',['image'=>['url'=>Kit::art('logo')],'image_size'=>'full','image_alt'=>'Emblema geométrico de OldBearStudios'])], 'ob-studio-emblem'),
                self::section([
                    self::widget('text-editor',['editor'=>'<p class="ob-eyebrow">02 / DETRÁS DEL OSO</p>']),
                    self::heading('El instinto de crear. La paciencia de un oso.'),
                    self::widget('text-editor',['editor'=>'<p>OldBearStudios nace de una idea sencilla: hacer los juegos que nos gustaría descubrir. Explorar una mecánica hasta encontrar la chispa, dar personalidad a cada mundo y cuidar esos pequeños detalles que se quedan contigo.</p><p>Nos mueve una decisión difícil, un descubrimiento inesperado y las ganas de jugar una vez más.</p>'])
                ], 'ob-studio-copy')
            ], 'ob-editor-section ob-studio-layout', 'estudio'),
            self::section([
                self::widget('text-editor',['editor'=>'<p class="ob-eyebrow">03 / EN LA MESA DE TRABAJO</p>']),
                self::heading('El próximo capítulo, paso a paso.'),
                self::widget('text-editor',['editor'=>'<p>Una mirada al desarrollo. Los hitos marcados como DEMO ilustran el funcionamiento del plugin.</p>']),
                self::widget('oldbear-roadmap',['limit'=>30])
            ], 'ob-editor-section', 'roadmap'),
            self::section([
                self::heading('Desde la madriguera.'),
                self::widget('text-editor',['editor'=>'<p>Procesos creativos, avances y pequeñas historias del desarrollo.</p>']),
                self::widget('oldbear-journal',['per_page'=>3,'pagination'=>'','images'=>'']),
                self::widget('button',['text'=>'Visita el diario','link'=>['url'=>home_url('/diario/')],'background_color'=>'#efaa59','button_text_color'=>'#101412'])
            ], 'ob-editor-section ob-journal-panel', 'diario'),
            self::section([self::widget('oldbear-contact',[])], 'ob-editor-section ob-contact-panel', 'contacto')
        ];
    }
    /** Container with explicit settings (header, footer, inner pages). */
    private static function box(array $elements, array $settings = []): array {
        $settings += ['content_width'=>'boxed','boxed_width'=>['unit'=>'px','size'=>1200],'flex_direction'=>'column','flex_gap'=>['unit'=>'px','row'=>'0','column'=>'0','isLinked'=>true],
            'padding'=>['unit'=>'px','top'=>'0','right'=>'24','bottom'=>'0','left'=>'24','isLinked'=>false]];
        return ['id'=>substr(str_replace('-', '', wp_generate_uuid4()),0,8),'elType'=>'container','isInner'=>false,'settings'=>$settings,'elements'=>$elements];
    }
    private static function row(array $elements, string $class): array {
        return self::box($elements, ['content_width'=>'full','css_classes'=>$class,'flex_direction'=>'row','flex_direction_mobile'=>'column','flex_justify_content'=>'space-between','flex_align_items'=>'center','flex_align_items_mobile'=>'flex-start','flex_wrap'=>'wrap','flex_gap'=>['unit'=>'px','row'=>'24','column'=>'24','isLinked'=>true],'padding'=>['unit'=>'px','top'=>'0','right'=>'0','bottom'=>'0','left'=>'0','isLinked'=>true]]);
    }
    private static function page_section(array $elements, string $class = ''): array {
        return self::box($elements, ['html_tag'=>'section','css_classes'=>trim('ob-editor-section ' . $class),'padding'=>['unit'=>'px','top'=>'90','right'=>'24','bottom'=>'90','left'=>'24','isLinked'=>false]]);
    }
    private static function eyebrow(string $text): array {
        return self::widget('text-editor',['editor'=>'<p class="ob-eyebrow">' . esc_html($text) . '</p>']);
    }
    public static function header_document(): array {
        return [self::box([self::widget('oldbear-brand',[]), self::widget('oldbear-nav',[])], [
            'html_tag'=>'header','css_classes'=>'ob-site-header','flex_direction'=>'row','flex_justify_content'=>'space-between','flex_align_items'=>'center','flex_wrap'=>'nowrap',
            'flex_gap'=>['unit'=>'px','row'=>'16','column'=>'32','isLinked'=>false],'min_height'=>['unit'=>'px','size'=>102],'min_height_mobile'=>['unit'=>'px','size'=>80]
        ])];
    }
    public static function footer_document(): array {
        return [self::box([
            self::row([
                self::widget('oldbear-brand',[]),
                self::widget('text-editor',['editor'=>'<p>Hecho con instinto.<br><span>Jugado con el corazón.</span></p>']),
                self::widget('oldbear-social',[])
            ], 'ob-footer-top'),
            self::row([self::widget('oldbear-credits',[])], 'ob-footer-bottom')
        ], ['html_tag'=>'footer','css_classes'=>'ob-site-footer','padding'=>['unit'=>'px','top'=>'55','right'=>'24','bottom'=>'24','left'=>'24','isLinked'=>false]])];
    }
    public static function error_document(): array {
        return [self::page_section([
            self::eyebrow('404 / FUERA DEL MAPA'),
            self::heading('Hasta los osos se pierden a veces.', 'h1'),
            self::widget('text-editor',['editor'=>'<p>Esta página no está por aquí. Volvamos al campamento.</p>']),
            self::widget('button',['text'=>'Volver al inicio','link'=>['url'=>home_url('/')],'background_color'=>'#efaa59','button_text_color'=>'#101412'])
        ], 'ob-error-section')];
    }
    public static function journal_document(string $intro = ''): array {
        return [self::page_section([
            self::eyebrow('DESDE LA MADRIGUERA'),
            self::heading('El diario del estudio.', 'h1'),
            self::widget('text-editor',['editor'=>$intro !== '' ? $intro : '<p>Procesos creativos, avances y pequeñas historias del desarrollo.</p>']),
            self::widget('oldbear-journal',['per_page'=>9,'pagination'=>'yes','images'=>'yes'])
        ], 'ob-journal-page')];
    }
    public static function legal_document(string $title, string $html): array {
        return [self::page_section([self::heading($title, 'h1'), self::widget('text-editor',['editor'=>$html])], 'ob-legal')];
    }
    /** Saves elements into a post through Elementor's document API and verifies the result. */
    private static function build(int $id, array $elements) {
        $document = \Elementor\Plugin::$instance->documents->get($id, false);
        if (!$document) { return new \WP_Error('document_failed', 'No se pudo abrir el documento de Elementor.'); }
        $document->set_is_built_with_elementor(true);
        if (!$document->save(['elements'=>$elements,'settings'=>['hide_title'=>'yes']])) { return new \WP_Error('document_failed', 'Elementor no pudo guardar el diseño.'); }
        $stored = json_decode((string) get_post_meta($id, '_elementor_data', true), true);
        if (!is_array($stored) || count($stored) !== count($elements)) { return new \WP_Error('incomplete_document', 'Elementor no ha guardado todos los contenedores.'); }
        return $id;
    }
    private static function has_design(int $id): bool {
        $data = json_decode((string) get_post_meta($id, '_elementor_data', true), true);
        return get_post_meta($id, '_elementor_edit_mode', true) === 'builder' && is_array($data) && $data;
    }
    private static function ready() {
        if (!current_user_can('manage_options')) { return new \WP_Error('forbidden','Se necesitan permisos de administración.'); }
        if (!did_action('elementor/loaded') || !class_exists('Elementor\\Plugin')) { return new \WP_Error('elementor_missing','Activa Elementor Free antes de preparar la web.'); }
        update_option('elementor_experiment-container', 'active');
        // Native widgets inherit the theme's typography and palette instead of Elementor's defaults.
        update_option('elementor_disable_color_schemes', 'yes');
        update_option('elementor_disable_typography_schemes', 'yes');
        if (!\Elementor\Plugin::$instance->elements_manager->get_element_types('container')) {
            return new \WP_Error('containers_unavailable', 'Activa Contenedores en Elementor → Ajustes → Características y recarga el panel.');
        }
        return true;
    }
    /**
     * Header, footer and 404 templates plus the journal and privacy pages, all as Elementor documents.
     * Idempotent: existing Elementor designs are never overwritten.
     */
    public static function site() {
        $ready = self::ready();
        if (is_wp_error($ready)) { return $ready; }
        $ids = Layout::ids();
        $documents = ['header'=>[self::class,'header_document'],'footer'=>[self::class,'footer_document'],'404'=>[self::class,'error_document']];
        foreach ($documents as $key=>$builder) {
            $existing = $ids[$key] ?? 0;
            if ($existing && get_post_type($existing) === 'elementor_library' && get_post_status($existing) !== 'trash') { continue; }
            $document = \Elementor\Plugin::$instance->documents->create('section', ['post_title'=>'OldBear · ' . Layout::PARTS[$key],'post_status'=>'publish']);
            if (is_wp_error($document) || !$document) { return is_wp_error($document) ? $document : new \WP_Error('document_failed', 'No se pudo crear la plantilla.'); }
            $result = self::build($document->get_main_id(), call_user_func($builder));
            if (is_wp_error($result)) { wp_delete_post($document->get_main_id(), true); return $result; }
            Layout::remember($key, $result);
        }

        $journal = $ids['journal'] ?? 0;
        if (!$journal || get_post_type($journal) !== 'page' || get_post_status($journal) === 'trash') {
            $journal = absint(get_option('page_for_posts'));
            if (get_post_type($journal) !== 'page') { $page = get_page_by_path('diario'); $journal = $page ? $page->ID : 0; }
            if (!$journal) {
                $journal = wp_insert_post(['post_type'=>'page','post_title'=>'Diario','post_name'=>'diario','post_status'=>'publish'], true);
                if (is_wp_error($journal)) { return $journal; }
            }
            // WordPress' posts page ignores its own content and Elementor refuses to edit it.
            if (absint(get_option('page_for_posts')) === $journal) { update_option('page_for_posts', 0); }
            if (!self::has_design($journal)) {
                $intro = trim((string) get_post_field('post_content', $journal));
                $result = self::build($journal, self::journal_document($intro !== '' ? do_blocks($intro) : ''));
                if (is_wp_error($result)) { return $result; }
            }
            Layout::remember('journal', $journal);
        }

        $privacy = absint(get_option('wp_page_for_privacy_policy'));
        if (get_post_type($privacy) === 'page' && get_post_status($privacy) !== 'trash' && !self::has_design($privacy)) {
            $content = do_blocks((string) get_post_field('post_content', $privacy));
            $result = self::build($privacy, self::legal_document(get_the_title($privacy), $content !== '' ? $content : '<p>Redacta aquí tu política de privacidad.</p>'));
            if (is_wp_error($result)) { return $result; }
            Layout::remember('privacy', $privacy);
        }
        return Layout::ids();
    }
    public static function install(bool $set_front = false) {
        $ready = self::ready();
        if (is_wp_error($ready)) { return $ready; }
        $existing = absint(get_option('oldbear_elementor_home'));
        if (get_post_type($existing) === 'page' && get_post_status($existing) !== 'trash') { return $existing; }
        $front = absint(get_option('page_on_front'));
        $adopt = $set_front && get_post_type($front) === 'page' && trim((string) get_post_field('post_content', $front)) === '' && !self::has_design($front);
        $id = $adopt
            ? wp_update_post(['ID'=>$front,'post_title'=>'Inicio','post_status'=>'publish','post_content'=>'[oldbear_games] [oldbear_roadmap] [oldbear_contact]'], true)
            : wp_insert_post(['post_type'=>'page','post_title'=>'Inicio · Elementor','post_status'=>$set_front ? 'publish' : 'draft','post_content'=>'[oldbear_games] [oldbear_roadmap] [oldbear_contact]'], true);
        if (is_wp_error($id)) { return $id; }
        $document = \Elementor\Plugin::$instance->documents->get($id);
        if (!$document) { if (!$adopt) { wp_delete_post($id, true); } return new \WP_Error('document_failed','No se pudo crear el documento de Elementor.'); }
        $document->set_is_built_with_elementor(true);
        if (!$document->save(['elements'=>self::document(),'settings'=>['hide_title'=>'yes']])) {
            if (!$adopt) { wp_delete_post($id, true); }
            return new \WP_Error('document_failed','Elementor no pudo guardar el diseño.');
        }
        $stored = json_decode(get_post_meta($id, '_elementor_data', true), true);
        if (!is_array($stored) || count($stored) !== count(self::document())) {
            if (!$adopt) { wp_delete_post($id, true); }
            return new \WP_Error('incomplete_document', 'Elementor no ha guardado todos los contenedores. No se ha cambiado la portada.');
        }
        update_option('oldbear_elementor_home', $id, false);
        if ($set_front) { update_option('show_on_front','page'); update_option('page_on_front',$id); self::seed(); flush_rewrite_rules(false); }
        return $id;
    }
    public static function seed(): array {
        if (!current_user_can('manage_options')) { return []; }
        $ids=[];
        foreach ([
            'forest'=>['Bosque del umbral · Demo','Exploración','Linux','Un portal entre árboles. Concepto visual de muestra para probar el catálogo y sus filtros.'],
            'dunes'=>['Dunas gemelas · Demo','Aventura','PC','Ruinas bajo dos lunas. Concepto visual de muestra para explorar otra dirección artística.']
        ] as $key=>$info) {
            $existing=get_posts(['post_type'=>'oldbear_game','post_status'=>['publish','draft','private','trash'],'posts_per_page'=>1,'meta_key'=>'_ob_demo_key','meta_value'=>$key]);
            if ($existing) {
                $ids[$key]=$existing[0]->ID;
                if ($existing[0]->post_status === 'trash') { wp_untrash_post($existing[0]->ID); wp_update_post(['ID'=>$existing[0]->ID,'post_status'=>'publish']); }
                continue;
            }
            $id=wp_insert_post(['post_type'=>'oldbear_game','post_status'=>'publish','post_title'=>$info[0],'post_excerpt'=>$info[3],'post_content'=>'<p><strong>Demostración técnica.</strong> Este registro es un concepto de muestra, no el anuncio de un videojuego real.</p><p>Su ficha, estado, géneros y plataformas se gestionan desde OldBear Studio.</p>'], true);
            if (is_wp_error($id)) { continue; }
            $ids[$key]=$id;
            update_post_meta($id,'_ob_demo_key',$key);
            update_post_meta($id,'_ob_art',$key);
            update_post_meta($id,'oldbear_status','Concepto · Demo');
            foreach (['ob_genre'=>$info[1],'ob_platform'=>$info[2]] as $taxonomy=>$name) {
                $term=term_exists($name,$taxonomy);
                if (!$term) { $term=wp_insert_term($name,$taxonomy); }
                if (!is_wp_error($term)) { wp_set_object_terms($id,(int)(is_array($term) ? $term['term_id'] : $term),$taxonomy); }
            }
        }
        foreach ([
            'demo-prototype'=>['planned','Probar nuevas mecánicas','Un ejemplo de hito pendiente. Cambia su estado desde el panel para ver cómo se mueve.'],
            'demo-art'=>['active','Explorar la dirección de arte','Un ejemplo de tarea en marcha, vinculada al bosque de demostración.'],
            'demo-controls'=>['done','Definir el primer prototipo','Un ejemplo de hito completado. El indicador de progreso se calcula desde los estados.']
        ] as $key=>$info) {
            $existing=get_posts(['post_type'=>'ob_milestone','post_status'=>['publish','draft','private','trash'],'posts_per_page'=>1,'meta_key'=>'_ob_demo_key','meta_value'=>$key]);
            if ($existing) {
                if ($existing[0]->post_status === 'trash') { wp_untrash_post($existing[0]->ID); wp_update_post(['ID'=>$existing[0]->ID,'post_status'=>'publish']); }
                continue;
            }
            $id=wp_insert_post(['post_type'=>'ob_milestone','post_status'=>'publish','post_title'=>$info[1],'post_excerpt'=>$info[2]],true);
            if (is_wp_error($id)) { continue; }
            update_post_meta($id,'_ob_demo_key',$key);
            update_post_meta($id,'ob_stage',$info[0]);
            update_post_meta($id,'ob_game_id',$ids['forest'] ?? 0);
        }
        Catalog::invalidate();
        return $ids;
    }
}
