<?php
namespace OldBearStudio;
use Elementor\Controls_Manager as Controls;
if (!defined('ABSPATH') || !class_exists('Elementor\\Widget_Base')) { exit; }

abstract class Studio_Widget extends \Elementor\Widget_Base {
    public function get_categories(): array { return ['oldbear-studio']; }
    public function get_icon(): string { return 'eicon-gamepad'; }
    public function get_style_depends(): array { return ['oldbear-studio-kit']; }
    public function get_script_depends(): array { return ['oldbear-studio-kit']; }
    protected function is_dynamic_content(): bool { return true; }
    protected function register_controls(): void {
        $this->start_controls_section('content', ['label'=>'Contenido']);
        $this->fields();
        $this->end_controls_section();
        $this->start_controls_section('appearance', ['label'=>'Diseño','tab'=>Controls::TAB_STYLE]);
        $this->add_control('accent', ['label'=>'Color de acento','type'=>Controls::COLOR,'selectors'=>['{{WRAPPER}} .ob-kit'=>'--ob-accent: {{VALUE}};']]);
        $this->add_responsive_control('text_size', ['label'=>'Tamaño del titular','type'=>Controls::SLIDER,'size_units'=>['px','rem'],'range'=>['px'=>['min'=>20,'max'=>110],'rem'=>['min'=>1,'max'=>7]],'selectors'=>['{{WRAPPER}} .ob-widget-title, {{WRAPPER}} .ob-game h3, {{WRAPPER}} .ob-lane h3'=>'font-size: {{SIZE}}{{UNIT}};']]);
        $this->end_controls_section();
    }
    abstract protected function fields(): void;
    protected function text(string $name, string $label, string $default = '', bool $area = false): void {
        $this->add_control($name, ['label'=>$label,'type'=>$area ? Controls::TEXTAREA : Controls::TEXT,'default'=>$default,'label_block'=>true]);
    }
}
final class Hero_Widget extends Studio_Widget {
    public function get_name(): string { return 'oldbear-hero'; }
    public function get_title(): string { return 'OldBear · Portada'; }
    protected function fields(): void {
        $this->text('eyebrow','Etiqueta','ESTUDIO INDEPENDIENTE DE VIDEOJUEGOS');
        $this->text('title','Titular','Pequeño estudio. Grandes aventuras.');
        $this->text('copy','Descripción','Creamos mundos a los que apetece volver. Juegos con carácter, hechos con curiosidad, mimo y un punto de instinto.',true);
        $this->add_control('image', ['label'=>'Ilustración','type'=>Controls::MEDIA,'default'=>['url'=>Kit::art('hero')]]);
        $this->text('image_alt','Texto alternativo','Un oso contempla un valle de montañas bajo un gran sol ámbar.');
        $this->text('button','Texto del botón','Explora nuestros mundos');
        $this->add_control('link', ['label'=>'Enlace del botón','type'=>Controls::URL,'default'=>['url'=>'#juegos']]);
        $this->text('footnote','Nota breve','Ideas propias. Pasión por jugar.');
    }
    protected function render(): void {
        $s = $this->get_settings_for_display();
        echo '<section class="ob-kit ob-hero"><div><p class="ob-eyebrow">' . esc_html($s['eyebrow']) . '</p><h1 class="ob-widget-title">' . esc_html($s['title']) . '</h1><p class="ob-lead">' . esc_html($s['copy']) . '</p>';
        if (!empty($s['link']['url'])) {
            echo '<a class="ob-button" href="' . esc_url($s['link']['url']) . '"' . (!empty($s['link']['is_external']) ? ' target="_blank" rel="noopener noreferrer"' : '') . '>' . esc_html($s['button']) . '<span aria-hidden="true">→</span></a>';
        }
        echo '<p class="ob-footnote">' . esc_html($s['footnote']) . '</p></div>';
        if (!empty($s['image']['url'])) { echo '<figure><img src="' . esc_url($s['image']['url']) . '" alt="' . esc_attr($s['image_alt']) . '" width="900" height="1000" fetchpriority="high"><figcaption>THE NEXT ADVENTURE STARTS HERE</figcaption></figure>'; }
        echo '</section>';
    }
}
final class Catalog_Widget extends Studio_Widget {
    public function get_name(): string { return 'oldbear-catalog'; }
    public function get_title(): string { return 'OldBear · Catálogo de juegos'; }
    protected function fields(): void {
        $this->add_control('per_page', ['label'=>'Juegos por página','type'=>Controls::NUMBER,'min'=>1,'max'=>12,'default'=>6]);
        $this->add_control('filters', ['label'=>'Mostrar filtros','type'=>Controls::SWITCHER,'return_value'=>'yes','default'=>'yes']);
    }
    protected function render(): void {
        $s=$this->get_settings_for_display();
        echo Catalog::shortcode(['per_page'=>$s['per_page'],'filters'=>$s['filters'] === 'yes' ? 'yes' : 'no']);
    }
}
final class Roadmap_Widget extends Studio_Widget {
    public function get_name(): string { return 'oldbear-roadmap'; }
    public function get_title(): string { return 'OldBear · Roadmap'; }
    protected function fields(): void {
        $options=[0=>'Todo el estudio'];
        foreach (get_posts(['post_type'=>'oldbear_game','posts_per_page'=>100,'post_status'=>'publish']) as $game) { $options[$game->ID]=$game->post_title; }
        $this->add_control('game', ['label'=>'Juego','type'=>Controls::SELECT,'options'=>$options,'default'=>0]);
        $this->add_control('limit', ['label'=>'Máximo de hitos','type'=>Controls::NUMBER,'min'=>1,'max'=>60,'default'=>30]);
    }
    protected function render(): void {
        $s=$this->get_settings_for_display();
        echo Content::roadmap(['game'=>$s['game'],'limit'=>$s['limit']]);
    }
}
final class Contact_Widget extends Studio_Widget {
    public function get_name(): string { return 'oldbear-contact'; }
    public function get_title(): string { return 'OldBear · Contacto'; }
    protected function fields(): void {
        $this->text('title','Titular','Toda gran aventura empieza con un hola.');
        $this->text('copy','Descripción','¿Tienes una idea, quieres colaborar o simplemente saludar? Aquí empieza la conversación.',true);
    }
    protected function render(): void {
        $s=$this->get_settings_for_display();
        echo '<div class="ob-kit ob-contact"><div><p class="ob-eyebrow">HABLEMOS</p><h2 class="ob-widget-title">' . esc_html($s['title']) . '</h2><p class="ob-lead">' . esc_html($s['copy']) . '</p></div><div class="ob-contact-form">' . oldbear_render_contact() . '</div></div>';
    }
}
