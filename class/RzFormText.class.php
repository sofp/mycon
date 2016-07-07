<?php
/**
 * Textフォーム作成
 */
class RzFormText extends RzFormBase {
    protected $size;
    protected $maxlength;
    
    public function __construct($name, $sheet) {
        parent::__construct($name, $sheet);
        $textform = $this->sheet['type-text'];
        $this->size = $textform['size'];
        $this->maxlength = $textform['maxlength'];
    }


    public function setRequest($request) {
        $this->request = $request;
    }
    
    /**
     * 入力フォームを作成
     */
    public function getForm() {
        $form = "";

        
        $class_str = 'typeText ' . $this->name;
		$class_str .= ' form-control';
        if (isset($this->sheet['class'])) {
            $class_str .= ' ' . $this->sheet['class'];
        }

        $type_str = "text";
        if(isset($this->sheet['type-str'])) {
            $type_str = $this->sheet['type-str'];
        }
        
        $format_temp = '<input type="%s" id="%s" name="%s" %s value="%s" class="%s" />';
        
        $option = "";
        if ($this->size != "") {
            $option .= sprintf(" size=\"%s\" ", $this->size);
        }
        
        if ($this->maxlength != "") {
            $option .= sprintf(" maxlength=\"%s\" ", $this->maxlength);
        }

        
        $form = sprintf($format_temp, $type_str, $this->name, $this->name, $option, $this->request->get($this->name), $class_str);
        
        return $form;
    }


    /* 入力をチェック */
    public function check() {
        $msg = "";
        $chk = $this->sheet;
        $value = trim($this->request->get($this->name));
        if ($value != '') {
            // echo "<br />name: $this->name<br />";
            // print_r($chk);
            // echo "<br />---<br />";
            
            if ($chk['check-mail'] && !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i", $value)) {
                $msg = sprintf($this->request->errorfmt, $chk['check-mail']);
                return $msg;
            }

            require_once( ABSPATH . WPINC . '/registration.php');
            if ($chk['check-mail-exist'] && email_exists($value)) {
                $msg = sprintf($this->request->errorfmt, $chk['check-mail-exist']);
                return $msg;
            }

             // 同じ要素でないとエラーにする
            if ($chk['check-mail-again']) {
                $email2name_value = $this->request->get($chk['check-mail-again']);
                if ($value != $email2name_value) {
                    $msg = sprintf($this->request->errorfmt, $chk['check-mail-err']);
                }
                return $msg;
            }

            // 値のチェック
            if ($chk['check-int'] && !ereg("^[0-9]+$", $value)) {
                // 整数以外駄目
                $msg = sprintf($this->request->errorfmt, $chk['check-int']);
                return $msg;
            }

            if ($chk['check-num-haifun'] && !ereg("^[0-9-]+$", $value)) {
                // echo $value;
                $msg = sprintf($this->request->errorfmt, $chk['check-num-haifun']);
                return $msg;
            }

            
        } else if(isset($chk['check-must'])) {
            $msg = sprintf($this->request->errorfmt, $chk['check-must']);
        }
        
        return $msg;
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
        if ($this->name == "post_title") {
            $this->request->add($this->name, get_the_title($post_id));  
        } else {
            $this->request->add($this->name, get_post_meta($post_id, $this->name, true));
        }
    }

    /* 指定された投稿IDでアップデートする */
    public function updateValueWPPostByPid($post_id) {
        if ($this->name == "post_title") {
            // $this->request->add($this->name, get_the_title($post_id));  
        } else {
            update_post_meta($post_id, $this->name, $this->request->get($this->name));
        }
    }
}