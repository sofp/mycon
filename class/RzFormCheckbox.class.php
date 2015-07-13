<?php
/**
 * Textフォーム作成
 */
class RzFormCheckbox extends RzFormBase {
    var $size;
    var $maxlength;
    
    /**
     * 入力フォームを作成
     */
    public function getForm() {
        $form = "";
        
        $checkarray = array(); // 値が入力された時の処理用
        $checked = "";
        $sheet = $this->sheet;

        $class_str = 'typeCheckbox ' . $this->name;
        if (isset($this->sheet['class'])) {
            $class_str = $class_str . ' ' . $this->sheet['class'];
        }
        
        // echo "checkbox!!";
        $interval = 3;          /* default */
        
        $i = 1;
        $arr = $sheet['type-checkbox'];
        if(isset($sheet['interval']) && $sheet['interval'] > 0) {
            $interval = $sheet['interval'];
        }
        $checkedname_arr = $this->request->get($this->name);

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

            $id_str = sprintf("%s-%d", $this->name, $i);
            
            if (isset($sheet['disp-type']) && $sheet['disp-type'] == 1) {
                $form .= sprintf("<input type=\"checkbox\" name=\"%s[]\" value=\"%s\" %s />%nbsp;\n", $this->name, $value, $checked);
            } else {
                $form .= sprintf("<input type=\"checkbox\" id=\"%s\" name=\"%s[]\" value=\"%s\" %s class=\"%s\" />&nbsp;<label for=\"%s\">%s</label>&nbsp;\n",
                                 $id_str,
                                 $this->name,
                                 $value,
                                 $checked,
                                 $class_str,
                                 $id_str,
                                 $value);
            }
            if (($i % $interval) == 0) {
                $form .= "<br />\n";
            }
            $i++;
        }
        
        return $form;
    }


    /* 入力をチェック */
    public function check() {
        $v = "";
        return $v;
    }


    /* 値を取得 */
    public function getValue($format = '%s ') {
        $string = "";

        $v = $this->request->get($this->name);

        if($format == '') {
            $format = '%s ';
        }
        
        if(is_array($v) ){
            foreach ($v as $vitem) {
                $string .= sprintf($format, $vitem);
            }
        }
        return $string;
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

        // print_r(get_usermeta($user_id, $this->name));
        
        $this->request->add($this->name, get_usermeta($user_id, $this->name));
    }

    /* 投稿データから値を取得し、requestにセットする（投稿編集用） */
    public function setValueFromWPPostdata($post_id) {
        $this->request->add($this->name, get_post_meta($post_id, $this->name, true));
    }

    /* 指定された投稿IDでアップデートする */
    public function updateValueWPPostByPid($post_id) {
        update_post_meta($post_id, $this->name, $this->request->get($this->name));
    }

    
}