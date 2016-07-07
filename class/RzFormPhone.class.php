<?php
/**
 * Textフォーム作成
 */
class RzFormPhone extends RzFormBase {

    protected $name1, $name2, $name3;
    
    public function __construct($name, $sheet) {
        parent::__construct($name, $sheet);
        
        $name = $this->name;
            
        $this->name1 = $name . '-1';
        $this->name2 = $name . '-2';
        $this->name3 = $name . '-3';
    }
    
    /**
     * 入力フォームを作成
     */
    public function getForm() {
        $form = "";

        $size1 = 4;
        $size2 = 4;
        $size3 = 4;
        $typephone = $this->sheet['type-phone'];


        $class_str = 'typePhone ' . $this->name;
		$class_str .= ' form-control';
        if (isset($this->sheet['class'])) {
            $class_str = $class_str . ' ' . $this->sheet['class'];
        }
        
        if(is_array($typephone) && count($typephone) > 0) {
            $size1 = $typephone['size1'];
            $size2 = $typephone['size2'];
            $size3 = $typephone['size3'];
        }
        $format = "%s - %s - %s";
        $form = sprintf($format,
        $this->request->getFormText($this->name1, $size1 , $size1, $class_str, "tel"),
        $this->request->getFormText($this->name2, $size2 , $size2, $class_str, "tel"),
        $this->request->getFormText($this->name3, $size3 , $size3, $class_str, "tel"));
        return $form;
    }


    /* 入力をチェック */
    public function check() {
        $msg = "";


        $v1 = $this->request->get($this->name1);
        $v2 = $this->request->get($this->name2);
        $v3 = $this->request->get($this->name3);
            
        $msg1 = $this->request->_check($this->name, $v1, $this->sheet);
        $msg2 = $this->request->_check($this->name, $v2, $this->sheet);
        $msg3 = $this->request->_check($this->name, $v3, $this->sheet);
        if ($msg1) $msg = $msg1;
        if ($msg2) $msg = $msg2;
        if ($msg3) $msg = $msg3;
        
        return $msg;
    }


    /* 値を取得 */
    public function getValue($format = '') {
        $format = "%s-%s-%s";
        $string = sprintf($format,
                          $this->request->get($this->name1),
                          $this->request->get($this->name2),
                          $this->request->get($this->name3));
        return $string;
    }
    /* hidden */
    public function getHiddenTag() {
        $v = "";

        $v .= $this->request->getHiddenTag($this->name1,
                                           $this->request->get($this->name1));
        $v .= $this->request->getHiddenTag($this->name2,
                                           $this->request->get($this->name2));
        $v .= $this->request->getHiddenTag($this->name3,
                                           $this->request->get($this->name3));
        return $v;
    }

    public function postRegistByPid($post_id) {
        $form = "";
        if ($this->request->get($this->name) != "") {
            add_post_meta( $post_id, $this->name, $this->request->get($this->name));
        }
         return $form;
    }

     /* WordPrssのユーザーデータから取得 */
    public function setValueFromWPUserdata($user_id) {
        
        $this->request->add($this->name1, get_usermeta($user_id, $this->name1, true));
        $this->request->add($this->name2, get_usermeta($user_id, $this->name2, true));
        $this->request->add($this->name3, get_usermeta($user_id, $this->name3, true));
    }

    /* 投稿データから値を取得し、requestにセットする（投稿編集用） */
    public function setValueFromWPPostdata($post_id) {
        $this->request->add($this->name1, get_post_meta($post_id, $this->name1, true));
        $this->request->add($this->name2, get_post_meta($post_id, $this->name2, true));
        $this->request->add($this->name3, get_post_meta($post_id, $this->name3, true));
    }

    /* 指定された投稿IDでアップデートする */
    public function updateValueWPPostByPid($post_id) {
        update_post_meta($post_id, $this->name1, $this->request->get($this->name1));
        update_post_meta($post_id, $this->name2, $this->request->get($this->name2));
        update_post_meta($post_id, $this->name3, $this->request->get($this->name3));
    }
}