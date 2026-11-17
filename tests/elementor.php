<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
use OldBearStudio\Catalog;
use OldBearStudio\Content;
use OldBearStudio\Starter;
use OldBearStudio\Admin;
$checks=0;
$assert=static function($ok,$message) use (&$checks) { if (!$ok) { throw new RuntimeException($message); } $checks++; };
$admins=get_users(['role'=>'administrator','number'=>1]);
wp_set_current_user($admins[0]->ID);
$assert(defined('ELEMENTOR_VERSION'), 'Elementor debe estar activo.');
$assert(post_type_exists('ob_milestone'), 'Falta roadmap.');
$assert(taxonomy_exists('ob_genre') && taxonomy_exists('ob_platform'), 'Faltan taxonomías.');
$home=absint(get_option('oldbear_elementor_home'));
$assert($home === (int)get_option('page_on_front'), 'La landing Elementor debe ser portada.');
$assert(get_post_meta($home,'_elementor_edit_mode',true) === 'builder', 'La portada debe tener documento Elementor.');
$data=json_decode(get_post_meta($home,'_elementor_data',true),true);
$assert(is_array($data) && count($data) >= 6, 'La portada necesita secciones editables separadas.');
$saved_before=get_post_meta($home,'_elementor_data',true);
$assert(Starter::install(true) === $home, 'Reinstalar no debe duplicar la portada.');
$assert(get_post_meta($home,'_elementor_data',true) === $saved_before, 'Reinstalar no debe sobrescribir el documento.');
$widgets=\Elementor\Plugin::$instance->widgets_manager->get_widget_types();
foreach (['oldbear-hero','oldbear-catalog','oldbear-roadmap','oldbear-contact'] as $name) { $assert(isset($widgets[$name]), 'Widget no registrado: '.$name); }

$prefix='obtest' . strtolower(wp_generate_password(8,false));
$ids=[]; $terms=[]; $page=0;
try {
    foreach (['ob_genre','ob_platform'] as $taxonomy) {
        $term=wp_insert_term($prefix,$taxonomy,['slug'=>$prefix]);
        $assert(!is_wp_error($term),'No se pudo crear taxonomía de prueba.');
        $terms[$taxonomy]=(int)$term['term_id'];
    }
    for ($i=0;$i<3;$i++) {
        $id=wp_insert_post(['post_type'=>'oldbear_game','post_status'=>'publish','post_title'=>$prefix.' Mundo '.$i,'post_excerpt'=>'Una ficha de prueba.'],true);
        if (is_wp_error($id)) { throw new RuntimeException($id->get_error_message()); }
        $ids[]=$id;
        wp_set_object_terms($id,$terms['ob_genre'],'ob_genre');
        if ($i === 0) { wp_set_object_terms($id,$terms['ob_platform'],'ob_platform'); }
    }
    $draft=wp_insert_post(['post_type'=>'oldbear_game','post_status'=>'draft','post_title'=>$prefix.' secreto'],true);
    $ids[]=$draft;
    $found=Catalog::query(['search'=>$prefix,'per_page'=>2]);
    $assert($found['total'] === 3 && $found['pages'] === 2, 'Paginación o exclusión de borradores incorrecta.');
    $assert(count(Catalog::query(['search'=>$prefix,'per_page'=>2,'page'=>2])['items']) === 1, 'La segunda página debe contener un juego.');
    $assert(Catalog::query(['genre'=>$prefix,'platform'=>$prefix])['total'] === 1, 'Los filtros deben combinarse.');
    $assert(Catalog::query(['search'=>$prefix.'不存在'])['total'] === 0, 'La búsqueda vacía debe devolver cero.');
    update_post_meta($ids[0],'oldbear_status','Caché actualizada');
    $changed=Catalog::query(['genre'=>$prefix,'platform'=>$prefix]);
    $assert($changed['items'][0]['status'] === 'Caché actualizada', 'La edición de metadatos debe invalidar caché.');
    wp_update_post(['ID'=>$ids[0],'post_title'=>'<img src=x onerror=alert(1)> '.$prefix]);
    $html=Catalog::cards(Catalog::query(['genre'=>$prefix]));
    $assert(!str_contains($html,'<img src=x'), 'El título debe escaparse.');
    $milestone=wp_insert_post(['post_type'=>'ob_milestone','post_status'=>'publish','post_title'=>'Hito temporal'],true);
    $ids[]=$milestone;
    update_post_meta($milestone,'ob_game_id',$ids[0]);
    update_post_meta($milestone,'ob_stage','done');
    $assert(str_contains(Content::roadmap(['game'=>$ids[0]]),'1 de 1 hitos completados'), 'El roadmap debe calcular el progreso.');
    $assert(Content::stage('unexpected') === 'planned', 'Los estados deben tener una lista permitida.');

    $server=rest_get_server();
    $request=new WP_REST_Request('GET','/oldbear/v1/games');
    $request->set_query_params(['search'=>$prefix,'per_page'=>2]);
    wp_set_current_user(0);
    $response=$server->dispatch($request);
    $assert($response->get_status() === 200 && $response->get_data()['total'] === 3, 'La API pública debe devolver solo publicados.');
    $assert(isset($response->get_data()['html']), 'La API debe compartir el render del servidor.');
    $invalid=new WP_REST_Request('GET','/oldbear/v1/games');
    $invalid->set_query_params(['per_page'=>999]);
    $assert($server->dispatch($invalid)->get_status() === 400, 'La API debe limitar el tamaño de página.');
    $array=new WP_REST_Request('GET','/oldbear/v1/games');
    $array->set_query_params(['search'=>['bad']]);
    $assert($server->dispatch($array)->get_status() === 400, 'La API debe rechazar arrays.');
    $edit=new WP_REST_Request('POST','/wp/v2/oldbear_game/'.$ids[0]);
    $edit->set_param('title','Ataque');
    $assert($server->dispatch($edit)->get_status() >= 400, 'Un visitante no puede editar juegos.');
    $assert(!Admin::authorize('invalid'), 'Las herramientas requieren administrador y nonce.');
    $assert(is_wp_error(Starter::install()), 'Un visitante no puede importar la landing.');

    wp_set_current_user($admins[0]->ID);
    $assert(Admin::authorize(wp_create_nonce('ob_studio_action')), 'Un administrador con nonce puede usar las herramientas.');
    $assert(!Admin::authorize('invalid'), 'Un administrador sin nonce tampoco debe poder usarlas.');
    $page=wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'Prueba Elementor'],true);
    $document=\Elementor\Plugin::$instance->documents->get($page);
    $document->set_is_built_with_elementor(true);
    $elements=Starter::document();
    $elements[0]['elements'][0]['settings']['title']='EditableHero <script>alert(1)</script>';
    $elements[2]['elements'][1]['settings']['title']='EditableNativeHeading';
    $assert($document->save(['elements'=>$elements]), 'Elementor debe guardar el documento con su API.');
    $render=\Elementor\Plugin::$instance->frontend->get_builder_content_for_display($page);
    $assert(str_contains($render,'EditableHero') && str_contains($render,'EditableNativeHeading'), 'Los controles personalizados y nativos deben cambiar el render.');
    $assert(!str_contains($render,'<script>alert(1)</script>'), 'El widget debe escapar su contenido.');
    $before=get_post_meta($home,'_elementor_data',true);
    Starter::install(true);
    $assert($before === get_post_meta($home,'_elementor_data',true), 'La importación debe conservar ediciones existentes.');
} finally {
    wp_set_current_user($admins[0]->ID);
    if ($page) { wp_delete_post($page,true); }
    foreach ($ids as $id) { if (!is_wp_error($id)) { wp_delete_post($id,true); } }
    foreach ($terms as $taxonomy=>$id) { wp_delete_term($id,$taxonomy); }
    Catalog::invalidate();
}
WP_CLI::success($checks.' pruebas de Studio Kit y Elementor superadas.');
