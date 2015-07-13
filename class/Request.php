<?php
/**
 * Requestクラスファイル
 */

/**
 * Requestクラス:
 * GET/POSTを扱うクラス
 */
class Request {
    protected $params = Array();
    // protected $title = "";
  
    public function __construct() {
        // 通常のリクエストパラメータも同様に扱えるように
        if (is_array($_REQUEST) ) {
            foreach ( $_REQUEST as $name => $value) {
                $this->add($name, $value);
            }
        }
    }

    /**
     *
     */
    public function add($name, $data) {
        $this->params[$name] = $data;
    }

    /**
     * 
     */
    public function get($name) {
        if (!is_array(@$this->params[$name])) {
            // arrayでなければ trimとstrip_tagsを行う
            $param = $this->translate(@$this->params[$name]);
        } else {
            // arrayの場合なにもしない
            $param = array();
            $param_array = @$this->params[$name];
            foreach ($param_array as $p) {
                $p = $this->translate($p);
                $param[] = $p;
            }
        }
        return $param;
    }

    /**
     * 変換
     */
    public  function translate($param) {
        if (is_string($param)) {
            $p = trim($param);
            $p = strip_tags($p);
            $p = htmlspecialchars($p); /* for xss */
            $p = mb_convert_encoding($p, "UTF-8", "ASCII,UTF-8,SJIS,EUC-JP");
        } else {
            $p = $param;
        }
        return $p;
    }
}
?>
