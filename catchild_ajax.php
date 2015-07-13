<?php
/* 指定されたカテゴリIDの子供を返す */
define('WP_USE_THEMES', false);

// require_once dirname(dirname(__FILE__))  . '/../../wp-blog-header.php';
// require_once dirname(dirname(__FILE__))  . '/wp-blog-header.php';


require_once dirname(dirname(__FILE__)) . '/../../wp-config.php';
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();


$cid = $_REQUEST['cid'];
if (isset($cid) && intval($cid) > 0) {


    $objs = getObjsFromCID($cid);

    $disp_arr = array();
    foreach($objs as $c_obj) {
        $item['name'] = $c_obj->name;
        $item['term_id'] = $c_obj->term_id;
        $disp_arr[] = $item;
    }

    $json_value = json_encode($disp_arr);
    echo $json_value;

    // print_r($objs);
}

function getObjsFromCID($cid) {
    $args = "child_of=$cid&title_li=&depth=1&hide_empty=0&orderby=ID";
    $defaults = array(
        'show_option_all' => '', 'show_option_none' => __('No categories'),
        'orderby' => 'name', 'order' => 'ASC',
        'style' => 'list',
        'show_count' => 0, 'hide_empty' => 1,
        'use_desc_for_title' => 1, 'child_of' => 0,
        'feed' => '', 'feed_type' => '',
        'feed_image' => '', 'exclude' => '',
        'exclude_tree' => '', 'current_category' => 0,
        'hierarchical' => true, 'title_li' => __( 'Categories' ),
        'echo' => 1, 'depth' => 0,
        'taxonomy' => 'category'
    );
    $r = wp_parse_args( $args, $defaults );

    extract( $r );
    // print_r($r);
    $cat_arr = get_categories($r); /* ここではdepthの処理はやってくれない */

    // print_r($cat_arr[0]);

    // 現在の段以外は排除
    $r = array();
    foreach($cat_arr as $c) {
        if($c->parent == $cid) {
            $r[] = $c;
        }
    }
    return $r;
}

?>