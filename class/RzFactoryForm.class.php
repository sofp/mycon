<?php

// 以下 autoloadしたい。。
require_once 'RzFormBase.class.php';

require_once 'RzFormAddress.class.php';
require_once 'RzFormCategory.class.php';
require_once 'RzFormCheckbox.class.php';
require_once 'RzFormFile.class.php';
require_once 'RzFormGmapxy.class.php';
require_once 'RzFormText.class.php';
require_once 'RzFormTextarea.class.php';
require_once 'RzFormHidden.class.php';
require_once 'RzFormPhone.class.php';
require_once 'RzFormPassword.class.php';
require_once 'RzFormRadio.class.php';
require_once 'RzFormSelect.class.php';
require_once 'RzFormDate.class.php';
require_once 'RzFormZip.class.php';

class RzFactoryForm {
    
    public function create($name, $sheet) {
        $formObj = $this->_createForm($name, $sheet);

        return $formObj;
    }

    private function _createForm($name, $sheet)  {
        // type-xxのキーを捜す
        $key = $this->_getTypeKey($sheet);

        $cname = '';

        // $cname = 'Categoryform';
        $cname = $this->_key2class($key); /* クラス名に変換 */

        // echo $cname;
        return new $cname($name, $sheet);
    }


    // type-で始まるkeyを取得
    private function _getTypeKey($sheet) {
        return $sheet['type'];
        /* 旧式 "type" に type-keyを入れるので下記は不要になった。
        $keys = array_keys($sheet);
        foreach($keys as $k) {
            if(preg_match("/^type\-(\w+)$/", $k, $match)) {
                return $k;
            }
        }
        */
    }

    /**
     * typeキーをクラス名に変換する
     */
    private function _key2class($key) {
        if(preg_match("/^type\-(\w+)$/", $key, $match)) {
            return "RzForm" . ucfirst($match[1]);
        }
    }
   
}

?>