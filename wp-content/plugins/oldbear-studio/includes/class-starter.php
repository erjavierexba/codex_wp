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
                self::widget('button',['text'=>'Visita el diario','link'=>['url'=>get_permalink((int)get_option('page_for_posts')) ?: '#roadmap'],'background_color'=>'#efaa59','button_text_color'=>'#101412'])
            ], 'ob-editor-section ob-journal-panel', 'diario'),
            self::section([self::widget('oldbear-contact',[])], 'ob-editor-section ob-contact-panel', 'contacto')
        ];
    }
    public static function install(bool $set_front = false) {
        if (!current_user_can('manage_options')) { return new \WP_Error('forbidden','Se necesitan permisos de administración.'); }
        if (!did_action('elementor/loaded') || !class_exists('Elementor\\Plugin')) { return new \WP_Error('elementor_missing','Activa Elementor Free antes de preparar la landing.'); }
        $existing = absint(get_option('oldbear_elementor_home'));
        if (get_post_type($existing) === 'page' && get_post_status($existing) !== 'trash') { return $existing; }
        $id = wp_insert_post(['post_type'=>'page','post_title'=>'Inicio · Elementor','post_status'=>$set_front ? 'publish' : 'draft','post_content'=>'[oldbear_games] [oldbear_roadmap] [oldbear_contact]'], true);
        if (is_wp_error($id)) { return $id; }
        $document = \Elementor\Plugin::$instance->documents->get($id);
        if (!$document) { wp_delete_post($id, true); return new \WP_Error('document_failed','No se pudo crear el documento de Elementor.'); }
        $document->set_is_built_with_elementor(true);
        if (!$document->save(['elements'=>self::document(),'settings'=>['hide_title'=>'yes']])) {
            wp_delete_post($id, true);
            return new \WP_Error('document_failed','Elementor no pudo guardar el diseño.');
        }
        update_option('oldbear_elementor_home', $id, false);
        if ($set_front) { update_option('show_on_front','page'); update_option('page_on_front',$id); self::seed(); }
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
