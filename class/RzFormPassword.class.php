<?php /* -*- coding:utf-8 -*- */
/**
 * Passwordフォーム作成
 */
class RzFormPassword extends RzFormText {

    /**
     * 入力フォームを作成
     */
    public function getForm() {
        $form = "";

        $class_str = 'typePassword ' . $this->name;
        if (isset($this->sheet['class'])) {
            $class_str = $class_str . ' ' . $this->sheet['class'];
        }
        
        $format_temp = '<input type="password" id="%s" name="%s" %s value="%s" class="%s" />';
        $option = "";
        if ($this->size != "") {
            $option .= sprintf(" size=\"%s\" ", $this->size);
        }
        
        if ($this->maxlength != "") {
            $option .= sprintf(" maxlength=\"%s\" ", $this->maxlength);
        }
        $form = sprintf($format_temp, $this->name, $this->name, $option, $this->request->get($this->name), $class_str);
        
        return $form;
    }

}