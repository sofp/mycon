<?php
/**
 * Radioフォーム作成
 */
class RzFormRadio extends RzFormBase {
    
    public function __construct($name, $sheet) {
        parent::__construct($name, $sheet);
    }

    /**
     * 入力フォームを作成
     */
    public function getForm() {
        $form = "";

        $checkarray = array(); // 値が入力された時の処理用
        $checked = "";
        $i = 1;
                
        if(isset($this->sheet['interval']) && $this->sheet['interval'] > 0) {
            $interval = $this->sheet['interval'];
        } else {
            $interval = 3;      /* デフォルト */
        }
                
        // 初期値
        $get_val = $this->request->get($this->name);
        if (!isset($get_val) || $get_val == "") {
            if ( isset($this->sheet['default']) && $this->sheet['default'] != "") {
                $get_val = $this->sheet['default'];
            }
        }
                
        // class
        $class_str = 'typeRadio ' . $this->name;
        if (isset($this->sheet['class'])) {
            $class_str .= ' ' . $this->sheet['class'];
        }
                
        $arr = $this->sheet['type-radio']; /* ここ itemを取るようにすればいいんじゃないかと */
        foreach ($arr as $value) {
            // echo "[$value]";
            if (preg_match("/^(.*)\:(.*)$/", $value, $matches)) {
                // echo "match";
                // print_r($matches);
                $value = trim($matches[1]);
                $value_str = trim($matches[2]);
            } else {
                $value_str = $value;
            }
            
            
            if ($get_val != "") {
                // 値が入力された時の処理
                if ($value == $get_val) {
                    $checked = " checked ";
                } else {
                    $checked = "";
                }
            }
                    
            /* $form .= sprintf("<input type=\"radio\" class=\"%s\" name=\"%s\" value=\"%s\" %s /><span class=\"%s\">%s</span>\n", $class_str, $this->name, $value, $checked, $class_str, $value_str); */


            $id_str = sprintf("%s-%d", $this->name, $i);
            
            $form .= sprintf("<input type=\"radio\" id=\"%s\" class=\"%s\" name=\"%s\" value=\"%s\" %s /><label class=\"%s\" for=\"%s\">%s</label>\n", $id_str, $class_str, $this->name, $value, $checked, $class_str, $id_str, $value_str);
            
            
            if (($i % $interval) == 0) {
                $form .= "<br />\n";
            }
            $i++;
        }
        
        return $form;
    }


    /* 入力をチェック */
    public function check() {
        $msg = "";
        $chk = $this->sheet;
        $value = trim($this->request->get($this->name));
        if ($value == '') {
            if(isset($chk['check-must'])) {
                $msg = sprintf($this->request->errorfmt, $chk['check-must']);
            }
        }
        
        return $msg;
    }


    /* 値を取得 */
    public function getValue($format = '') {
        // echo "test:[". $this->name . "]";

        $arr = $this->sheet['type-radio'];

        $val_arr = array();
        
        foreach ($arr as $value) {
            if (preg_match("/^(.*)\:(.*)$/", $value, $matches)) {
                $value = trim($matches[1]);
                $value_str = trim($matches[2]);

                $val_arr[$value] = $value_str;
            } else {
                $val_arr[$value] = $value;
            }
        }
        
        $get_val = $this->request->get($this->name);
        $v = $val_arr[$get_val];
        return $v;
    }
    /* hidden */
    public function getHiddenTag() {
        $v = "";
        $v .= $this->request->getHiddenTag($this->name,
                                           $this->request->get($this->name));
        return $v;
    }

    public function postRegistByPid($post_id) {
        $form = "";
        if ($this->request->get($this->name) != "") {
            add_post_meta( $post_id, $this->name, $this->request->get($this->name));
        }
        
        return $form;
    }

    /**
     * ユーザーデータから取得
     */
    public function setValueFromWPUserdata($user_id) {
        $this->request->add($this->name, get_usermeta($user_id, $this->name));
    }

    /* 投稿データから値を取得し、requestにセットする（投稿編集用） */
    public function setValueFromWPPostdata($post_id) {
        // printf( "<p>[radio: %s: %d : %s]</p>", $this->name, $post_id, get_post_meta($post_id, $this->name, true));
        $this->request->add($this->name, get_post_meta($post_id, $this->name, true));
    }

    /* 指定された投稿IDでアップデートする */
    public function updateValueWPPostByPid($post_id) {
        update_post_meta($post_id, $this->name, $this->request->get($this->name));
    }
}