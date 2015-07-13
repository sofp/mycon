<?php
/**
 * Textフォーム作成
 */

define('MYCONFORM_UPLOAD_FILE_DIR', 'myconform');
define('MYCONFORM_UPLOAD_FILE_SIZE', '2M');


class RzFormFile extends RzFormBase {
    var $name;
    var $request;
    var $sheet;
    
    public function __construct($name, $sheet) {
        $this->name = $name;
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

        $form = '';
        // 画像アップ処理
        // 既にアップされていた場合。
        $get_val = $this->request->get($this->name);
        $old_file = $this->getUploadDir() . '/' . $get_val;
        if (isset($get_val) && $get_val != '' && file_exists($old_file)) {
            $form .= sprintf('<input type="hidden" name="%s-tmp" value="%s"/>',
                             $this->name, $get_val);
            $form .= "<strong>File: $get_val</span><br />";
                    
        }
                
        $form .= sprintf("<input type=\"file\" name=\"%s\" size=\"30\" />", $this->name);
        return $form;
    }


    /* 入力をチェック */
    public function check() {
        $v = "";

        require_once( ABSPATH . 'wp-admin/includes/file.php' );
            
        $msg = $_FILES[$this->name]["name"]; /* 予めいれておく。。 */

        $upload_overrides = array( 'test_form' => false );
            
        $movefile = wp_handle_upload($_FILES[$this->name], $upload_overrides);
            
        if ($movefile) {
            if (!isset($movefile['error']) && isset($movefile['file']) && isset($movefile['url'])) {
                $_REQUEST[$this->name . '-file'] = $movefile['file'];
                $_REQUEST[$this->name . '-url'] =  $movefile['url'];
            }
        } else {
            $tmpname = $this->name . '-tmp';
            $upload_filename = $this->get($tmpname);
            if (isset($upload_filename) && $upload_filename != '') {
                $msg = $upload_filename;
            } else {
                // 添付ファイル指定がなかった場合
                if(isset($sheet['check-must']) && $sheet['check-must'] != '') {
                    // $msg = sprintf($this->errorfmt, $sheet['check-must']);
                    // $this->errflag++;
                    // 何かエラーを返すべきか。。
                }
            }
        }
        
        return $v;
    }


    /* 値を取得 */
    public function getValue($format = '') {
        $v = "";
        $name_url = sprintf("%s-url", $this->name);
        $val = $_REQUEST[$name_url];

        // echo "<p>[" . $_REQUEST[$name_url] . "]</p>";
        
        // $v .= sprintf("<img src=\"%s\" alt=\"%s\" />", $val, $this->name);
        $v .= $val;
        return $v;
    }

    /* hidden */
    public function getHiddenTag() {
        $v = "";
        // '-url'を追加
        $name_url = sprintf("%s-url", $this->name);
        $val = $_REQUEST[$name_url];

        $v .= $this->request->getHiddenTag($name_url, $val);
        return $v;
    }

    public function postRegistByPid($post_id) {
        $form = "";
        $name_url = sprintf("%s-url", $this->name);
        $val = $_REQUEST[$name_url];

        add_post_meta( $post_id, $this->name, $val, true);
        
        return $form;
    }


    /* フォルダを返す */
    function getUploadDir() {
        $upload_dirs = wp_upload_dir();
        $upload_dir = $upload_dirs['basedir'] . '/' . MYCONFORM_UPLOAD_FILE_DIR;
        return $upload_dir;
    }

    public function setValueFromWPUserdata($user_id) {
        // echo $user_id;
    }

    /* 投稿データから値を取得し、requestにセットする（投稿編集用） */
    public function setValueFromWPPostdata($post_id) {
        
    }

    /* 指定された投稿IDでアップデートする */
    public function updateValueWPPostByPid($post_id) {
        
    }
}