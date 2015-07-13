<?php
/**
 * Textフォーム作成
 */
class RzFormZip extends RzFormBase {

    var $name1, $name2;
    
    
    public function __construct($name, $sheet) {
        parent::__construct($name, $sheet);
        
        $name = $this->name;
        
        $this->name1 = $name . '-1';
        $this->name2 = $name . '-2';
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

        $typezip = $this->sheet['type-zip'];
        $size1 = 3;
        $size2 = 4;

        $class_str = 'typeZip ' . $this->name;
        if (isset($this->sheet['class'])) {
            $class_str = $class_str . ' ' . $this->sheet['class'];
        }
        
                
        if(is_array($typezip) && count($typezip) > 0) {
            $size1 = $typezip['size1'];
            $size2 = $typezip['size2'];
        }

        
        $format = "%s - %s";
        $form = sprintf($format,
        $this->request->getFormText($this->name1, $size1 ,$size1, $class_str),
        $this->request->getFormText($this->name2, $size2 ,$size2, $class_str));
        

        /* 郵便番号から自動入力 */
        /*
        $format = '
        <input type="text" name="%s" size="%d" maxlength="%d" class="%s" value="%s">
    &nbsp;−&nbsp <input type="text" name=%s" size="%d" maxlength="%d" class="%s"  value="%s" onKeyUp="AjaxZip3.zip2addr(\'%s\', \'%s\', \'address-1\',\'address-2\',\'address-3\');">';
        $form = sprintf($format,
        $this->name1,
        $size1,
        $size1,
        $class_str,
        $this->request->get($this->name1),
        $this->name2,
        $size2,
        $size2,
        $class_str,
        $this->request->get($this->name2),
        $this->name1,
        $this->name2);
        */
        
        return $form;
    }


    /* 入力をチェック */
    public function check() {
        $msg = "";
        
        $v1 = $this->request->get($this->name1);
        $v2 = $this->request->get($this->name2);
        
            
        $msg1 = $this->request->_check($this->name, $v1, $this->sheet);
        $msg2 = $this->request->_check($this->name, $v2, $this->sheet);
        
        if ($msg1) $msg = $msg1;
        if ($msg2) $msg = $msg2;
        
        return $msg;
    }


    /* 値を取得 */
    public function getValue($format = '') {
        $format = "%s-%s";
        
        $string = sprintf($format,
                          $this->request->get($this->name1),
                          $this->request->get($this->name2));
        return $string;
    }
    /* hidden */
    public function getHiddenTag() {
        $v = "";
        $v .= $this->request->getHiddenTag($this->name1,
                                           $this->request->get($this->name1));
        $v .= $this->request->getHiddenTag($this->name2,
                                           $this->request->get($this->name2));
        return $v;
    }

    public function postRegistByPid($post_id) {
        $form = "";
        if ($this->request->get($this->name1) != "") {
            add_post_meta( $post_id, $this->name1, $this->request->get($this->name1));
            add_post_meta( $post_id, $this->name2, $this->request->get($this->name2));
        }
        
        return $form;
    }

    public function setValueFromWPUserdata($user_id) {
        $this->request->add($this->name1, get_usermeta($user_id, $this->name1));
        $this->request->add($this->name2, get_usermeta($user_id, $this->name2));
    }

    /* 投稿データから値を取得し、requestにセットする（投稿編集用） */
    public function setValueFromWPPostdata($post_id) {
        $this->request->add($this->name1, get_post_meta($post_id, $this->name1, true));
        $this->request->add($this->name2, get_post_meta($post_id, $this->name2, true));
    }

    /* 指定された投稿IDでアップデートする */
    public function updateValueWPPostByPid($post_id) {
        update_post_meta($post_id, $this->name1, $this->request->get($this->name1));
        update_post_meta($post_id, $this->name2, $this->request->get($this->name2));
    }
    
}