<?php
/**
 * Selectフォーム作成
 */
class RzFormSelect extends RzFormBase {
    /**
     * 入力フォームを作成
     */
    public function getForm() {
        $form = "";
        $selectarray = array(); // 値が入力された時の処理用
        $selected = "";
        $sheet = $this->sheet;
        $arr = $sheet['type-select'];


        $class_str = 'typeSelect ' . $this->name;
        if (isset($sheet['class'])) {
            $class_str = $class_str . ' ' . $sheet['class'];
        }
                

        $form = sprintf("<select name=\"%s\" class=\"%s\">", $this->name, $class_str);

        if (isset($sheet['first-no-value-item']) && $sheet['first-no-value-item'] != '') {  
            $form .= sprintf("<option value=\"\">%s</option>\n", $sheet['first-no-value-item']);;
        }
        foreach ($arr as $value) {

            if ($this->request->get($this->name)) {
                // 値が入力された時の処理
                if ($value == $this->request->get($this->name)) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
            }
                    
            $form .= sprintf("<option value=\"%s\" %s >%s</option>\n", $value, $selected, $value);
        }
        $form .= "</select>";
        
        return $form;
    }


    /* 入力をチェック */
    public function check() {
        $v = "";
        return $v;
    }


    /* 値を取得 */
    public function getValue($format = '') {
        $v = $this->request->get($this->name);
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
        // printf( "<p>[select: %s: %d : %s]</p>", $this->name, $post_id,  get_post_meta($post_id, $this->name, true));
        $this->request->add($this->name, get_post_meta($post_id, $this->name, true));
    }

    /* 指定された投稿IDでアップデートする */
    public function updateValueWPPostByPid($post_id) {
        update_post_meta($post_id, $this->name, $this->request->get($this->name));
    }
}