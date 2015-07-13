<?php

require_once 'Request.php';
require_once 'RzFactoryForm.class.php';

class RequestForm extends Request {
    // 拡張
    public $checksheet;

    public $errflag = 0;
    
    public $errorfmt = '<span class="err">%s</span>';

    private $factory;           // factoryオブジェクト

    // 増やしていく
    private $factory_form_arr = array('type-category', 'type-gmapxy', 'type-text', 'type-file', 'type-textarea', 'type-hidden', 'type-radio', 'type-select', 'type-date', 'type-phone', 'type-zip', 'type-address', 'type-password', 'type-checkbox', 'type-payment');

    public function __construct() {
        parent::__construct();  // これだけならなくてもいいはずなのだが・・これがないと動かない..なぜ？！
        $this->factory = new RzFactoryForm();
    }

    /**
     * checkシートを登録します
     */
    public function setCheckSheet($sheet) {
        $this->checksheet = $sheet;
    }
    
    // getFormへの追加項目
    public function getForm($name, $interval = 3) {
        $form = "";
        $sheet = $this->checksheet[$name];

        if (is_array($sheet)) {
            if (in_array($sheet['type'], $this->factory_form_arr)) {
                $obj = $this->factory->create($name, $sheet);
                $obj->setRequest($this);
                $form = $obj->getForm();
            } else {
                var_dump($sheet);
                throw new Exception("Error: not find type:" . $sheet['type']);
                exit;
            }
        }

        if (isset($sheet['remark']) && $sheet['remark'] != "") {
            $form .= sprintf("<span class=\"remark\">%s</span>", $sheet['remark']);
        }
        return $form;
    }

    // check関数への追加項目
    function check($name) {
        if (isset($this->checksheet[$name])) {
            $sheet = $this->checksheet[$name];
        }
        $msg = "";
        if (in_array($sheet['type'], $this->factory_form_arr)) {
            // 今後はこちらにシフトする
            $obj = $this->factory->create($name, $sheet);
            $obj->setRequest($this);
            $msg = $obj->check();
            if($msg != "") {
                $this->errflag++;
            }
        } else {
            $value = $this->get($name);
            $err_msg = $this->_check($name, $value, $sheet);
            if ($err_msg) {
                // エラーならエラーメッセージを入れる
                $this->errflag++;
                $msg = $err_msg;
            } else {
                // 問題なければ、値を入れる
                $msg = $value;
            }
        }
        return $msg;
    }


    // hiddenの追加
    function getHiddenValues() {
        $hidden = "";
        
        foreach ($this->checksheet as $name => $sheet) {
            if (in_array($sheet['type'], $this->factory_form_arr)) {
                // 今後はこちらにシフトする
                $obj = $this->factory->create($name, $sheet);
                $obj->setRequest($this);
                $hidden .= $obj->getHiddenTag();
            } else {
                $hidden .= $this->getHiddenTag($name, $this->get($name));
            }
        }
        return $hidden;
    }



    /**
     * チェックしてエラーがあれば、メッセージを、なければnullを返す
     * errorフラグはカウントしない
     * value: フィルターを通した値を入れる
     */
    function _check($name, $value, $chk = null) {
        $msg = null;
        if ($chk == null) {
            if (isset($this->checksheet[$name])) {
                $chk = $this->checksheet[$name];
            }
        }

        if (is_array($chk)) {
            if ($value != '') {
                
                // 値が入っている時の処理

                // if($chk['check-mail'] && ! ereg("[a-zA-Z0-9_.\-]+@[a-zA-Z0-9_.\-]+", $value)) {
                if ($chk['check-mail'] && !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i", $value)) {
                    $msg = sprintf($this->errorfmt, $chk['check-mail']);
                    return $msg;
                }

                require_once( ABSPATH . WPINC . '/registration.php');
                if ($chk['check-mail-exist'] && email_exists($value)) {
                    $msg = sprintf($this->errorfmt, $chk['check-mail-exist']);
                    return $msg;
                }

                if ($chk['check-mail-again']) {
                    $email2name_value = $this->get($chk['check-mail-again']);
                    if ($value != $email2name_value) {
                        $msg = sprintf($this->errorfmt, $chk['check-mail-err']);
                    }
                    return $msg;
                }

                // 同じ要素でないとエラーにする
                if ($chk['check-same-again']) {
                    $email2name_value = $this->get($chk['check-same-again']);
                    if ($value != $email2name_value) {
                        $msg = sprintf($this->errorfmt, $chk['check-same-err']);
                    }
                    return $msg;
                }
                
                // 値のチェック
                if ($chk['check-int'] && !ereg("^[0-9]+$", $value)) {
                    // 整数以外駄目
                    $msg = sprintf($this->errorfmt, $chk['check-int']);
                    return $msg;
                }

                if ($chk['check-num-haifun'] && !ereg("^[0-9-]+$", $value)) {
                    // echo $value;
                    $msg = sprintf($this->errorfmt, $chk['check-num-haifun']);
                    return $msg;
                }
                
            } else {
                // 入力チェック、値が入ってなければエラー
                if(isset($chk['check-must'])) {
                    $msg = sprintf($this->errorfmt, $chk['check-must']);
                    return $msg;
                } else if (isset($chk['check-if-must'])) {
                    $chkif = $chk['check-if-must'];
                    if ($this->get($chkif[0]) != '') {
                        $msg = sprintf($this->errorfmt, $chkif[1]);
                    }
                    return $msg;
                }
            }

            
        }
        return $msg;
    }

    
    function getErrorCount() {
        return $this->errflag;
    }
    
    function getHiddenTag($name, $value) {
        if (is_array($value)) {
            $format = '<input type="hidden" name="%s[]" value="%s" />' . "\n";
            foreach ($value as $v) {
                $hidden .= sprintf("<input type=\"hidden\" name=\"%s[]\" value=\"%s\" />\n", $name, $v);
            }
        } else {
            $format = '<input type="hidden" name="%s" value="%s" />' . "\n";
            $hidden = sprintf($format, $name, $value);
        }
        return $hidden;
    }
    
    function setUsermetaValues($user_id) {
        foreach ($this->checksheet as $name => $sheet) {
            $v = $this->get($name);
            if ($sheet['type-checkbox-2'] || $sheet['type-radio-2']) {
                update_usermeta( $user_id, $name, $v);
                $name_o = $name . "_o";
                update_usermeta($user_id, $name_o, $this->get($name_o));
            } else if ($sheet['type-phone'] || $sheet['type-date']) {
                update_usermeta($user_id, $name . '-1', $this->get($name . '-1'));
                update_usermeta($user_id, $name . '-2', $this->get($name . '-2'));
                update_usermeta($user_id, $name . '-3', $this->get($name . '-3'));
            } else if ($sheet['type-zip']) {
                update_usermeta($user_id, $name . '-1', $this->get($name . '-1'));
                update_usermeta($user_id, $name . '-2', $this->get($name . '-2'));
            } else if ($sheet['type-address']) {
                update_usermeta($user_id, $name . '-1', $this->get($name . '-1'));
                update_usermeta($user_id, $name . '-2', $this->get($name . '-2'));
                update_usermeta($user_id, $name . '-3', $this->get($name . '-3'));
            } else {
                update_usermeta($user_id, $name, $v);
            }
        }
        
    }
    
    // getValueの追加
    function getValue($name, $format = '') {
        $string = "";
        $sheet = $this->checksheet[$name];
        if (in_array($sheet['type'], $this->factory_form_arr)) {
            // 今後はこちらにシフトする
            $obj = $this->factory->create($name, $sheet);
            $obj->setRequest($this);
            $string = $obj->getValue($format);
        } else {
            $string = sprintf($format, $v);
        }
        return $string;
    }


    // selectで選択されている部分の表示
    function isSelected($name, $value) {
        if ($this->params[$name] == $value) {
            return "selected";
        }
        return "";
    }

    function isChecked($name, $value) {
        if ($this->params[$name] == $value) {
            return "checked";
        }
        return "";
    }

    // メールアドレスの入力チェック
    function checkEmailInputAgain($email1,$email2) {
        $msg = "";
        $m1 = $this->get($email1);
        $m2 = $this->get($email2);
        
        $msg = $m1;

        $err_msg = $this->_check($email1, $m1);
        if ($err_msg) {
            $this->errflag++;
            return $err_msg;
        }
        
        $err_msg = $this->_check($email2, $m2);
        if ($err_msg) {
            $this->errflag++;
            return $err_msg;
        }
        
        if ($m1 != $m2 ) {
            $msg = sprintf($this->errorfmt, "再入力メールアドレスが違います");
            $this->errflag++;
            return $msg;
        }
        
        return $msg;
        
    }

    // 郵便番号グループのチェック
    function checkZipcode($name1, $name2, $format = '%s-%s') {
        $v1 = $this->get($name1);
        $v2 = $this->get($name2);

        $err_msg = $this->_check($name1, $v1);
        if ($err_msg) {
            $this->errflag++;
            return $err_msg;
        }

        $err_msg = $this->_check($name2, $v2);
        if ($err_msg) {
            $this->errflag++;
            return $err_msg;
        }
        
        $msg = sprintf($format, $v1, $v2);

        return $msg;
    }
    // 電話番号のチェック
    function checkPhone($name1, $name2, $name3, $format = "%s - %s - %s") {
        $v1 = $this->get($name1);
        $v2 = $this->get($name2);
        $v3 = $this->get($name3);

        $err_msg = $this->_check($name1, $v1);
        if ($err_msg) {
            $this->errflag++;
            return $err_msg;
        }

        $err_msg = $this->_check($name2, $v2);
        if ($err_msg) {
            $this->errflag++;
            return $err_msg;
        }

        $err_msg = $this->_check($name3, $v3);
        if ($err_msg) {
            $this->errflag++;
            return $err_msg;
        }
        
        
        $msg = sprintf($format, $v1, $v2, $v3);

        return $msg;
    }

    // チェックボックス用
    function checkCheckbox($name) {
        $values = $this->get($name);
        $msg = "";
        // print_r($values);
        if (is_array($values) && count($values) > 0) {
            foreach ($values as $value) {
                $msg .= $value . "<br />";
            }
        }
        return $msg;
    }
    

    /**
     * type=text 用いらない？
     */
    function getFormText($name, $size, $maxlength, $class_str = '', $type_str = "text") {
        $form = "";
        $format_temp = '<input type="%s" name="%s" %s value="%s" class="%s" />';
        $option = "";
        if ($size != "") {
            $option .= sprintf(" size=\"%s\" ", $size);
        }
        
        if ($maxlength != "") {
            $option .= sprintf(" maxlength=\"%s\" ", $maxlength);
        }
        $form = sprintf($format_temp, $type_str, $name, $option, $this->get($name), $class_str);
        return $form;
    }

    
    /**
     * ?
     */
    function getItemname($name, $format = '%s') {
        $sheet = $this->checksheet[$name];
        $format = '%s';
        if ((isset($sheet['check-must']) && $sheet['check-must'] != "")
            || (isset($sheet['check-if-must']) && $sheet['check-if-must'] != "")) {
            $format = '%s <span class="mark">*</span>';
        }
        return sprintf($format, $sheet['item-name']);
    }
    
    // textのパターン変換用
    function getItemNamePatterns($bodyformat) {
        $pattern = array();
        $replace = array();
        $sheets = $this->checksheet;

        foreach ($sheets as $name => $sheet) {
            $pattern[] = sprintf("/\[name:%s\]/", $name);
            $replace[] = $this->getItemname($name);
            $pattern[] = sprintf("/\[val:%s\]/", $name);
            $replace[] = $this->getValue($name);
        }
        $mailbody = preg_replace($pattern, $replace, $bodyformat);
        return $mailbody;
    }


    // textのパターン変換用(旧タイプ)
    function getItemNamePatternsOld($bodyformat) {
        $pattern = array();
        $replace = array();
        $sheets = $this->checksheet;

        foreach ($sheets as $name => $sheet) {
            $pattern[] = sprintf("/<%s>/", $sheet['item-name']);
            $replace[] = $this->get($name);
        }


        // print_r($pattern);
        // print_r($replace);
        $mailbody = preg_replace($pattern, $replace, $bodyformat);
        return $mailbody;
    }
    

    /**
     * 現在カテゴリのみなので、分岐はいらない
     */
    function getCategoryIds($name) {
        // $ids_arr = array();
        $ids_arr = array();;
        $sheet = $this->checksheet[$name];
        $obj = $this->factory->create($name, $sheet);
        $obj->setRequest($this);
        $ids_arr = array_merge($ids_arr, $obj->getCategoryIDs());
        
        return $ids_arr;
    }

    /**
     * 指定された入力POSTを記事として登録する
     */
    function postRegistByPids($post_id) {
        $form = "";
        foreach ($this->checksheet as $name => $sheet) {
            
            if ($name != 'post_title' && $name != 'post_content' && in_array($sheet['type'], $this->factory_form_arr)) {
                // 今後はこちらにシフトする
                $obj = $this->factory->create($name, $sheet);
                $obj->setRequest($this);
                $form .= $obj->postRegistByPid($post_id);
            }
        }
        return $form;
    }

    /**
     * 指定された入力POSTを指定のテーブルに
     */
    function registDB($tablename) {
        global $wpdb;

        // echo "<p>---RequestForm::postRegistDB()--</p>";
        $form = "";

        $dbinst_arr = array();
        foreach ($this->checksheet as $name => $sheet) {
            $obj = $this->factory->create($name, $sheet);
            $obj->setRequest($this);
            $dbinst_arr[$name] = $obj->getValue();
        }

        // print_r($dbinst_arr);
        
        $wpdb->insert($tablename, $dbinst_arr);

        // echo "<p>--- END  RequestForm::postRegistDB()--</p>";
        return $form;
    }

    /**
     * ユーザーデータから値を取得し、requestにセットする (編集用)
     */
    function setRequestValueFromWPUserdata($user_id) {

        foreach ($this->checksheet as $name => $sheet) {
            $obj = $factory->create($name, $sheet);
            $obj->setRequest($this);
            $obj->setValueFromWPUserdata($user_id);
        }
    }


    /**
     * 投稿データから値を取得し、requestにセットする（投稿編集用）
     */
    function setRequestValueFromWPPostdata($post_id) {

        foreach ($this->checksheet as $name => $sheet) {
            $obj = $this->factory->create($name, $sheet);
            $obj->setRequest($this);
            $obj->setValueFromWPPostdata($post_id);
        }
    }

    /**
     * 指定された投稿IDでアップデートする
     */
    function updateValueWPPostByPid($post_id) {
        
        foreach ($this->checksheet as $name => $sheet) {
            $obj = $this->factory->create($name, $sheet);
            $obj->setRequest($this);
            $obj->updateValueWPPostByPid($post_id);
        }
    }
}
?>