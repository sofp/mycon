<?php
/**
 * Hiddenフォーム作成
 */
class RzFormHidden extends RzFormBase {
    var $name;
    var $request;
    var $sheet;
    
    public function __construct($name, $sheet) {
        $this->name = $name;
        $this->sheet = $sheet;
    }


    public function setRequest($request) {
        $this->request = $request;
    }
    
    /**
     * 入力フォームを作成
     */
    public function getForm() {
        $form = "";
        $format_temp = '<input type="hidden" name="%s" value="%s" />';
        $form = sprintf($format_temp, $this->name, $this->request->get($this->name));
        
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

    public function setValueFromWPUserdata($user_id) {
    }

    /* 投稿データから値を取得し、requestにセットする（投稿編集用） */
    public function setValueFromWPPostdata($post_id) {
    }

    /* 指定された投稿IDでアップデートする */
    public function updateValueWPPostByPid($post_id) {
        update_post_meta( $post_id, $this->name, $this->request->get($this->name));
    }
}