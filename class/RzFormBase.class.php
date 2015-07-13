<?php
/**
 * フォームのインターフェース
 */
abstract class RzFormBase {
    protected $name;
    protected $request;
    protected $sheet;

    protected $errorfmt = '<span class="err">%s</span>';
    
    public function __construct($name, $sheet) {
        $this->name = $name;
        $this->sheet = $sheet;
    }

    public function setRequest($request) {
        $this->request = $request;
    }

    /* フォームを htmlで取得 */
    abstract public function getForm();

    /* 入力をチェック */
    abstract public function check();

    /**
     * チェックしてエラーがあれば、メッセージを、なければnullを返す
     * errorフラグはカウントしない
     * value: フィルターを通した値を入れる
     */
    protected function _pre_check($name, $value) {
        $msg = null;

        $chk = $this->sheet[$this->name];
            
        // 入力チェック、値が入ってなければエラー
        if ($value == '') {
            // echo "[not input value]";
            // print_r($chk);
            if(isset($chk['check-must'])) {
                $msg = sprintf($this->errorfmt, $chk['check-must']);
            }
        } else {
            // echo "[value:$value]";
        }
        // echo "[[$msg]]";
        return $msg;
    }
    
    /* 値を取得 */
    abstract public function getValue($format = '');

    /* hidden */
    abstract public function getHiddenTag();

    /* 投稿データへ登録 */
    abstract public function postRegistByPid($post_id);

    /* WordPrssのユーザーデータからしてrequestにセット */
    abstract public function setValueFromWPUserdata($user_id);

    /* 投稿データから値を取得し、requestにセットする（投稿編集用） */
    abstract public function setValueFromWPPostdata($post_id);

    /* 指定された投稿IDでアップデートする */
    abstract public function updateValueWPPostByPid($post_id);

}
?>