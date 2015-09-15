<?php
/**
 * Textフォーム作成
 */
class RzFormTextarea extends RzFormBase {

    /**
     * 入力フォームを作成
     */
    public function getForm() {
        $form = "";
        $textform = $this->sheet['type-textarea'];


        $class_str = 'typeTextArea ' . $this->name;
        if (isset($this->sheet['class'])) {
            $class_str = $class_str . ' ' . $this->sheet['class'];
        }
        
        $format_temp = '<textarea name="%s" %s class="%s">%s</textarea>';
        $option = "";
        if (is_array($textform) && count($textform) > 0) {
            if (isset($textform['rows']) && $textform['rows'] != "") {
                $option .= sprintf(" rows=\"%s\" ", $textform['rows']);
            }
            
            if (isset($textform['cols']) && $textform['cols'] != "") {
                $option .= sprintf(" cols=\"%s\" ", $textform['cols']);
            }
        }
        $form = sprintf($format_temp, $this->name, $option, $class_str, $this->request->get($this->name));
        return $form;
    }


    /* 入力をチェック */
    public function check() {
        
        $msg = "";
        $chk = $this->sheet;
        $value = trim($this->request->get($this->name));

        if ($value != '') {
            // 入力時のチェック
        } else if(isset($chk['check-must'])) {
            $msg = sprintf($this->request->errorfmt, $chk['check-must']);
        }
    }


    /* 値を取得 */
    public function getValue($format = '') {
        $v = "";
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

        add_post_meta( $post_id, $this->name, $this->request->get($this->name));
        
        return $form;
    }

    public function setValueFromWPUserdata($user_id) {
        $this->request->add($this->name, get_usermeta($user_id, $this->name));
    }

    /* 投稿データから値を取得し、requestにセットする（投稿編集用） */
    public function setValueFromWPPostdata($post_id) {
        if ($this->name == "post_content") {
            // echo "<p>[conent:$post_id]</p>";
            $p = get_post($post_id);
            $this->request->add($this->name, $p->post_content);
        } else {
            $this->request->add($this->name, get_post_meta($post_id, $this->name, true));
        }
    }


    /* 指定された投稿IDでアップデートする */
    public function updateValueWPPostByPid($post_id) {
        if ($this->name == "post_content") {
            // $this->request->add($this->name, get_the_title($post_id));  
        } else {
            update_post_meta($post_id, $this->name, $this->request->get($this->name));
        }
    }
}