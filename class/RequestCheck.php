<?php
/**
 * !!!!! 現在 このクラスは使われておりません !!!
 *
 */
require_once 'Request.php';

// Requestの拡張
class RequestCheck extends Request{
    // 拡張
    var $checksheet = array();

    var $errflag = 0;
    
    var $errorfmt = '<span class="err">%s</span>';
    
    /**
     * checkシートを登録します
     */
    function setCheckSheet($sheet) {
        $this->checksheet = $sheet;
    }

    /**
     * チェックして問題があればエラーを返す
     */
    function check($name, $sheet = null) {
        $msg = null;

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

        return $msg;
    }

    

    

    // hiddenで値を受渡し
    function getHiddenValues() {
        $hidden = "";
        foreach ($this->checksheet as $name => $value) {
            $v = $this->get($name);
            if (isset($value['type-checkbox-2']) || isset($value['type-radio-2'])) {
                $hidden .= $this->getHiddenTag($name, $v);
                $name_o = $name . "_o";
                $hidden .= $this->getHiddenTag($name_o, $this->get($name_o));
            } else if(!isset($value['type-file'])){
                $hidden .= $this->getHiddenTag($name, $v);
            }
        }
        return $hidden;
    }

    


    /**
     * 値をフォーマットに従い取り出す
     */
    function getValue($name, $format = "%s ") {
        $string = "";
        $v = $this->get($name);
        $sheet = $this->checksheet[$name];
        if($sheet['type-checkbox']) {
            if(is_array($v) ){
                foreach ($v as $vitem) {
                    $string .= sprintf($format, $vitem);
                }
            }
        } else if($sheet['type-checkbox-2']) {
            if(is_array($v) ){
                
                foreach ($v as $vitem) {
                    $string .= sprintf($format, $vitem);
                }
            }
            $name_o = $name . "_o";
            $name_o_v = $this->get($name_o);
            if ($name_o_v != "") {
                $string .= "その他の入力:" . $name_o_v;
            }
        } else if($sheet['type-radio-2']) {
            $string = sprintf($format, $v);
            $name_o = $name . "_o";
            $name_o_v = $this->get($name_o);
            if ($name_o_v != "") {
                $string .= "その他の入力:" . $name_o_v;
            }
        } else if ($sheet['type-textarea']) {
            // $v = nl2br(htmlspecialchars($v));
            $string = sprintf($format, $v);
        } else {
            $string = sprintf($format, $v);
        }
        return $string;
    }

    

    // form の表示
    function getForm($name, $interval = 3) {
        $form = "";
        $sheet = $this->checksheet[$name];
        // print_r($sheet);
        if (is_array($sheet)) {
            // 通常のテキスト入力の作成
            if (isset($sheet['type-text']) && is_array($sheet['type-text'])) {
                $textform = $sheet['type-text'];
                $form = $this->getFormText($name, $textform['size'], $textform['maxlength']);
            }
            // パスワード
            else if (isset($sheet['type-password'])) {
                // 通常のテキスト入力の作成
                $textform = $sheet['type-password'];

                $format_temp = '<input type="password" name="%s" %s value="%s" />';
                $option = "";
                if ($size != "") {
                  $option .= sprintf(" size=\"%s\" ", $size);
                }
                
                if ($maxlength != "") {
                  $option .= sprintf(" maxlength=\"%s\" ", $maxlength);
                }
                $form = sprintf($format_temp, $name, $option, $this->get($name));
            }

            // ラジオボタンの作成
            else if (isset($sheet['type-radio']) && is_array($sheet['type-radio'])) {
                $form = "";
                $checkarray = array(); // 値が入力された時の処理用
                $checked = "";
                $i = 1;
                
                if(isset($sheet['interval']) && $sheet['interval'] > 0) {
                    $interval = $sheet['interval'];
                }
                
                // 初期値
                $get_val = $this->get($name);
                if (!isset($get_val) || $get_val == "") {
                    if ( isset($sheet['default']) && $sheet['default'] != "") {
                        $get_val = $sheet['default'];
                    }
                }
                
                // class
                $class_str = 'typeRadio ' . $name;
                if (isset($sheet['class'])) {
                    $class_str .= $sheet['class'];
                }
                
                $arr = $sheet['type-radio'];
                foreach ($arr as $value) {
                    if ($get_val != "") {
                        // 値が入力された時の処理
                        if ($value == $get_val) {
                            $checked = " checked ";
                        } else {
                            $checked = "";
                        }
                    }
                    
                    $form .= sprintf("<input type=\"radio\" class=\"%s\" name=\"%s\" value=\"%s\" %s /><span class=\"%s\">%s</span>\n", $class_str, $name, $value, $checked, $class_str, $value);
                    // $form .= sprintf("<input type=\"radio\" class=\"%s\" name=\"%s\" value=\"%s\" %s />%s\n", $class_str, $name, $value, $checked, $value);
                    if (($i % $interval) == 0) {
                        $form .= "<br />\n";
                    }
                    $i++;
                }
            }

            // ラジオボタンの作成-2 その他があるやつ
            else if (isset($sheet['type-radio-2']) && is_array($sheet['type-radio-2'])) {
                $form = "";
                $checkarray = array(); // 値が入力された時の処理用
                $checked = "";
                $i = 1;
                $arr = $sheet['type-radio-2'];
                if(isset($sheet['interval']) && $sheet['interval'] > 0) {
                    $interval = $sheet['interval'];
                }
                foreach ($arr as $value) {

                    if ($this->get($name)) {
                        // 値が入力された時の処理
                        if ($value == $this->get($name)) {
                            $checked = " checked ";
                        } else {
                            $checked = "";
                        }
                    }
                    
                    $form .= sprintf("<input type=\"radio\" name=\"%s\" value=\"%s\" %s />&nbsp;%s&nbsp;\n", $name, $value, $checked, $value);
                    if (($i % $interval) == 0) {
                        $form .= "<br />\n";
                    }
                    $i++;
                }
                $name_o = $name . "_o";
                $name_o_v = $this->get($name_o);
                $form .= sprintf("<input type=\"text\" name=\"%s\" value=\"%s\" size=\"30\" />\n", $name_o, $name_o_v);
                
            }

            // セレクトの作成
            else if (isset($sheet['type-select']) && is_array($sheet['type-select'])) {
                $form = "";
                $selectarray = array(); // 値が入力された時の処理用
                $selected = "";
                $arr = $sheet['type-select'];

                $form = sprintf("<select name=\"%s\" >", $name);

                if (isset($sheet['first-no-value-item']) && $sheet['first-no-value-item'] != '') {  
                    $form .= sprintf("<option value=\"\">%s</option>\n", $sheet['first-no-value-item']);;
                }
                foreach ($arr as $value) {

                    if ($this->get($name)) {
                        // 値が入力された時の処理
                        if ($value == $this->get($name)) {
                            $selected = " selected ";
                        } else {
                            $selected = "";
                        }
                    }
                    
                    $form .= sprintf("<option value=\"%s\" %s >%s</option>\n", $value, $selected, $value);
                }
                $form .= "</select>";
            }
            // チェックボタンの作成
            else if (isset($sheet['type-checkbox']) && is_array($sheet['type-checkbox'])) {
                $form = "";
                $checkarray = array(); // 値が入力された時の処理用
                $checked = "";
                $i = 1;
                $arr = $sheet['type-checkbox'];
                if(isset($sheet['interval']) && $sheet['interval'] > 0) {
                    $interval = $sheet['interval'];
                }
                $checkedname_arr = $this->get($name);

                $get_val = null;
                if (isset($sheet['default']) && $sheet['default'] != "") {
                    $get_val = $sheet['default'];
                }
                
                foreach ($arr as $value) {
                    if (is_array($checkedname_arr) && in_array($value, $checkedname_arr)) {
                        // 値が入力された時の処理
                        $checked = " checked ";
                    } else {
                        $checked = "";
                    }

                    if ($get_val != null && $get_val == $value) {
                        $checked = " checked ";
                    }
                    
                    if ($sheet['disp-type'] == 1) {
                        $form .= sprintf("<input type=\"checkbox\" name=\"%s[]\" value=\"%s\" %s />%nbsp;\n", $name, $value, $checked);
                    } else {
                        $form .= sprintf("<input type=\"checkbox\" name=\"%s[]\" value=\"%s\" %s />&nbsp;%s&nbsp;\n", $name, $value, $checked, $value);
                    }
                    if (($i % $interval) == 0) {
                        $form .= "<br />\n";
                    }
                    $i++;
                }
            }

            // チェックボタンの作成
            else if (isset($sheet['type-checkbox-2']) && is_array($sheet['type-checkbox-2'])) {
                $form = "";
                $checkarray = array(); // 値が入力された時の処理用
                $checked = "";
                $i = 1;
                $arr = $sheet['type-checkbox-2'];
                if(isset($sheet['interval']) && $sheet['interval'] > 0) {
                    $interval = $sheet['interval'];
                }
                $checkedname_arr = $this->get($name);
                foreach ($arr as $value) {
                    if (is_array($checkedname_arr) && in_array($value, $checkedname_arr)) {
                        // 値が入力された時の処理
                        $checked = " checked ";
                    } else {
                        $checked = "";
                    }
                    
                    $form .= sprintf("<input type=\"checkbox\" name=\"%s[]\" value=\"%s\" %s />&nbsp;%s&nbsp;\n", $name, $value, $checked, $value);
                    if ($i % $interval == 0) {
                        $form .= "<br />\n";
                    }
                    $i++;
                }
                $name_o = $name . "_o";
                $name_o_v = $this->get($name_o);
                $form .= sprintf("<input type=\"text\" name=\"%s\" value=\"%s\" />&nbsp;\n", $name_o, $name_o_v);
            }
            // 数字を指定したselect
            else if (isset($sheet['type-select-from-to'])) {
              $arr = $sheet['type-select-from-to'];
              $form = sprintf("<select name=\"%s\" >", $name);
              // 初期値
              $form .= "<option value=\"\">----</option>\n";
              $from = intval($arr[0]);
              $to = intval($arr[1]);
              for ($i = $from ; $i <= $to ; $i++) {
                if ($this->get($name)) {
                  // 値が入力された時の処理
                  if ($i == $this->get($name)) {
                    $selected = " selected ";
                  } else {
                    $selected = "";
                  }
                }
                $form .= sprintf("<option value=\"%s\" %s >%s</option>\n", $i, $selected, $i);
              }
              $form .= "</select>";
            }
            
            // テキストエリア
            else if ($sheet['type-textarea']) {
                $textform = $sheet['type-textarea'];

                $format_temp = '<textarea name="%s" %s >%s</textarea>';
                $option = "";
                if (is_array($textform) && count($textform) > 0) {
                    if (isset($textform['rows']) && $textform['rows'] != "") {
                        $option .= sprintf(" rows=\"%s\" ", $textform['rows']);
                    }
                    
                    if (isset($textform['cols']) && $textform['cols'] != "") {
                        $option .= sprintf(" cols=\"%s\" ", $textform['cols']);
                    }
                }
                $form = sprintf($format_temp, $name, $option, $this->get($name));
            } else {
                // 引っかからなかった時
                var_dump($sheet);
                throw new Exception("Error: not find type:" . $sheet['type']);
                exit;
            }
        }
        return $form;
    }

    
    
    
    

    
}

?>