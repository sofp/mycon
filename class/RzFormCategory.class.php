<?php

/**
 * カテゴリを選択するフォーム作成
 */
class RzFormCategory extends RzFormBase {
    // var $typename = "type-category";
    // var $sheet;
    var $name;
    var $category_name;
    var $request;
    var $refine_search;
    
    public function __construct($name, $sheet) {
        // $this->sheet = $sheet;
        $this->name = $name;
        $this->category_name = $sheet['category-name'];
        $this->request = $request;
        $this->refine_search = $sheet['refine-search']; /* 絞り込み検索 */
        // print_r($sheet);
    }


    /**
     * 入力フォームを作成
     */
    public function getForm() {
        $base_cat_obj = get_term_by('name', $this->category_name, 'category');
        // print_r($base_cat_arr);
        $base_cat_id = $base_cat_obj->term_id;
        
        // echo "[base_cat_id:$base_cat_id]";
        $objs = $this->getObjsFromCID($base_cat_id);

        $class = '';
        if($this->refine_search > 1) {
            $class .= "refine_search";
            $target_name = $this->name . "_1";
        } else {
            $target_name = $this->name;
        }

        
        
        $form = sprintf("<select id=\"%s\" class=\"%s\" name=\"%s\" >", $target_name, $class, $target_name);

        // 最初の「選択してください」的な選択肢
        if (isset($sheet['first-no-value-item']) && $sheet['first-no-value-item'] != '') {  
            $form .= sprintf("<option value=\"\">%s</option>\n", $sheet['first-no-value-item']);;
        }

        // echo "<p>get:$target_name:" . $this->request->get($target_name) . "</p>";
        // print_r($this->request);
        

        $selected = "";
        foreach($objs as $catobj) {
            if ($this->request->get($target_name) == $catobj->term_id) {
                $selected = " selected ";
            } else {
                $selected = "";
            }
            $form .= sprintf("<option value=\"%s\" %s >%s</option>\n", $catobj->term_id, $selected, $catobj->name);
        }

        
        $form .= "</select>";

        if($this->refine_search > 1) {


            $parent_select_name = $target_name; /* tmp */

            for($i = 2; $i <= $this->refine_search ; $i++) {
                $target_name = $this->name . "_" . $i;

                $parent_cat_id = $this->request->get($parent_select_name);
                $form .= $this->_getOneForm($target_name, $parent_cat_id, $class);
                $parent_select_name = $target_name;
            }
        }
        
        return $form;
    }

    private function _getOneForm($target_name, $parent_cat_id, $class) {
        $form = "";

        $form .= sprintf("<select id=\"%s\" class=\"%s\" name=\"%s\" >", $target_name, $class, $target_name);
        if($parent_cat_id > 0) {
            $objs = $this->getObjsFromCID($parent_cat_id);


            $selected = "";
            foreach($objs as $catobj) {
                if ($this->request->get($target_name) == $catobj->term_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
                $form .= sprintf("<option value=\"%s\" %s >%s</option>\n", $catobj->term_id, $selected, $catobj->name);
            }
            
        } else {
            $form .= sprintf("<option value=\"%s\">%s</option>\n", 0, '選択してください');
        }
        $form .= "</select>";
        return $form;
    }


    /* 入力をチェック */
    public function check() {
        $v = "";
        
        return "";
    }


    /* 値を取得 */
    public function getValue($format = '') {
        $v = "";
        if ($this->refine_search > 0) {
            for($i = 1; $i <= $this->refine_search ; $i++) {
                $target_name = sprintf("%s_%d", $this->name, $i);
                $cid = $this->request->get($target_name);
                // echo "<p>[cid: $cid]</p>";
                if ($cid > 0) {
                    $c_name = get_the_category_by_ID($cid);
                    $v .= sprintf("[%s]", $c_name);
                }
            }
        } else {
            $cid = $this->request->get($this->name);
            // echo "<p>cid:$cid:".$this->name."</p>";
            if ($cid > 0) {
                $c_name = get_the_category_by_ID($cid);
                $v .= sprintf("[%s]", $c_name);
            }
        }
        return $v;
    }
    /* hidden */
    public function getHiddenTag() {
        $v = "";
        if ($this->refine_search > 0) {
            for($i = 1; $i <= $this->refine_search ; $i++) {
                $target_name = sprintf("%s_%d", $this->name, $i);
                $cid = $this->request->get($target_name);
                // $c_name = get_the_category_by_ID($cid);
                $v .= $this->request->getHiddenTag($target_name, $cid);
            }
        } else {
            $cid = $this->request->get($this->name);
            $v .= $this->request->getHiddenTag($this->name, $cid);
        }
        return $v;
    }

    /**
     * 指定したcatgory idの子のオブジェクト配列を返す
     */
    public function getObjsFromCID($cid) {
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
        
        $r = array();
        foreach($cat_arr as $c) {
            if($c->parent == $cid) {
                $r[] = $c;
            }
        }
        return $r;
    }

    /**
     * selectのoptionタグで返す
     */
    public function getSelectCategoryByCName($cname, $name = '') {
        $str = "";
        $base_cat_arr = is_term($cname, 'category');
        $cid = $base_cat_arr['term_id'];
        
        $cat_arr = self::getObjsFromCID($cid);

        $selected_val = 0;
        if ($name != '') {

            if(isset($_REQUEST[$name]) && $_REQUEST[$name] != '') {
                $selected_val = intval($_REQUEST[$name]);
            }
        }
        
        //$str .= sprintf("<select name=\"%s\">", $this->name);
        //$str .= sprintf("<option value=\"\">%s/option>", $cname);
        foreach ($cat_arr as $item) {

            $selected_str = '';
            if ($selected_val == $item->term_id) {
                $selected_str = ' selected="selected" ';
            }
            
            $str .= sprintf("<option value=\"%d\" %s>%s</option>\n",
                            $item->term_id,
                            $selected_str,
                            $item->name);
        }
        // $str .= "</select>\n";
        return $str;
    }


    /**
     * 最後の階層のみを返す
     */
    public function getCategoryIDs() {
        // echo "<p>Categoryform::getCategoryIDs</p>";
        // echo "<p>refine_search: " . $this->refine_search . "</p>";
        if($this->refine_search > 1) {
            // 最後の階層のみを返す
            $target_name = $this->name . "_" . $this->refine_search;
            // echo "target: " . $this->request->get($target_name);
            return array($this->request->get($target_name));
        } else {
            return array($this->request->get($this->name));
        }
    }

    public function postRegistByPid($post_id) {
        $form = "";
        return $form;
    }

    public function setValueFromWPUserdata($user_id) {
        echo $user_id;
    }

    /* 投稿データから値を取得し、requestにセットする（投稿編集用） */
    public function setValueFromWPPostdata($post_id) {
        $parent_cat_obj = get_term_by('name', $this->category_name, 'category');
        
        // printf("<p>wp_category:%s(%d)</p>",$this->category_name, $parent_cat_obj->term_id);
        
        // print_r($parent_cat_obj);
        
        $categories = get_the_category($post_id);
        // print_r($categories);
        $current_id = 0;
        foreach($categories as $cat) {
            if($cat->parent == $parent_cat_obj->term_id) {
                $current_id = $cat->term_id;
                break;
            }
        }

        if ($current_id > 0) {
            // echo "<p>[set:" . $this->name . ":$current_id]</p>";
            $this->request->add($this->name, $current_id);
        }
    }

    /* 指定された投稿IDでアップデートする */
    public function updateValueWPPostByPid($post_id) {
        
    }

    
}

?>