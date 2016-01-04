<?php /* -*-mode:php; coding:utf-8-*- */
/*
Plugin Name: mycon
Plugin URI: http://www.sofplant.com/
Description: お問合用プラグイン
Version: 1.0.1
Author: Reji Sato
Author URI: http://www.sofplant.com/
*/


/*  Copyright 2008 Reiji Sato (email : sato@sofplant.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once('class/RequestForm.php');

/**
 * お問合せをシンプルに
 */
class myConForms {
    var $plugin_name = "myconforms";

    // 送信エンコーディング
    // var $mb_language = "ja";
    var $mb_language = "uni";
    
    var $request;
    var $sheet;

    var $options;
    
    /**
     * myContactコンストラクタ
     */
    public function __construct($dataFileName = '') {
        /* 付け加え */
        add_filter('the_content', array($this, 'mycontact_filter'));
        /* スタイルシート */
        add_action('wp_head', array($this, 'includeStyle'));
        /* 管理メニュー */
        add_action('admin_menu',array($this, 'settingMenu'));
        add_action('wp_enqueue_scripts',array($this, 'mycon_enqueue_script'));
        add_action('wp_enqueue_scripts',array($this, 'mycon_enqueue_style'));

        add_action('template_redirect', array($this, 'mycon_redirect_page'));
        
        $this->request = new RequestForm();

        // ファイル指定を可能に
        if ($dataFileName != '') {
            $this->dataFileName = $dataFileName;

            require_once($this->dataFileName);
            $opt = $initopt;
            if ($opt != '') {
                $sheet = $this->getSheetFromTemplate($opt['mycontact_template']);
                // チェックシートを読み込ませる
                $this->sheet = $sheet;
                $this->request->setCheckSheet($sheet);
                // 取得したオプションをセット
                $this->options = $opt;
            }
        }
    }


    /**
     * 初期パラメータ値をセットする（上記ファイルでの読み込みの代替）
     */
    function myConFormsSetInitOpt($initopt) {
        if(is_array($initopt) && count($initopt)) {
            $opt = $initopt;
            $sheet = $this->getSheetFromTemplate($opt['mycontact_template']);
            // チェックシートを読み込ませる
            $this->sheet = $sheet;
            $this->request->setCheckSheet($sheet);
            // 取得したオプションをセット
            $this->options = $opt;
        }
    }


    /**
     * request getter
     */
    function getRequest() {
      return $this->request;
    }
    
    /**
     * option getter
     */
    function getOptions() {
      return $this->options;
    }
    
    /**
     * contextのフィルターメソッド
     */
    function mycontact_filter($content) {
        if (preg_match('/\[mycon-(\d+)\]/', $content, $matches)) {
            $form_id = 'mycon-' . $matches[1];
            
            $form_tag = sprintf("[%s]", $form_id);
            $opt = $this->get_form_id_opt($form_id);
            if ($opt != '') {
                $sheet = $this->getSheetFromTemplate($opt['mycontact_template']);
                // print_r($sheet);
                // チェックシートを読み込ませる
                $this->sheet = $sheet;
                $this->request->setCheckSheet($sheet);

                // 取得したオプションをセット
                $this->options = $opt;
                
                $form1 = $this->cf_ctl();
                if ($_REQUEST['cfctl'] == 'done' || $_REQUEST['cfctl'] == 'confirm') {
                    $content = $form1;
                } else {
                    $content = str_replace($form_tag, $form1, $content);
                }
            }
        }
        return $content;
    }

    function get_form_id_opt($form_id) {
        $opt = '';
        $options = get_option($this->plugin_name);
        if (isset($options) && $options != "" && is_array($options)) {
            $index = -1;
            foreach ($options as $idx => $item) {
                if ($item['mycontact_form_id'] == $form_id) {
                    $opt = $item;
                    $index = $idx;
                    break;
                }
            }
        }
        return $opt;
    }
    
    /**
     * コントロール
     */
    function cf_ctl() {
        if (isset($_REQUEST['cfctl'])) {
            $cfctl = $_REQUEST['cfctl'];
            if ($cfctl == 'confirm') 
                $form = $this->cf_confirm(); // 入力確認
            else if ($cfctl == 'reedit')
                $form = $this->cf_form(); // 再編集画面
            else if ($cfctl == 'done')
                $form = $this->cf_done(); // メール送信
        } else {
            $form = $this->cf_form(); // 入力画面
        }

        return $form;
    }

    /**
     * cf_form
     */
    function cf_form() {
        // echo "<p>cf_form()</p>";
        $request = $this->request;
        // 資料
        $form = '';
        $form .= '<div id="cf-inquiry">';

        if ($this->isAttachFile()) {
            $form .= '
<form method="post" id="registerform" action="'. $_SERVER['REQUEST_URI'] .'" enctype="multipart/form-data">
';
        } else {
            $form .= '
<form method="post" id="registerform" action="'. $_SERVER['REQUEST_URI'] .'" class="form-horizontal">
';
        }
        if (isset($this->options['mycontact_form_text']) && $this->options['mycontact_form_text'] != "") {
            $form .= stripcslashes($this->options['mycontact_form_text']);
                
            $pattern = array();
            $replace = array();
            foreach ($this->sheet as $name => $val) {
                $pattern[] = sprintf("/\[name:%s\]/", $name);
                $replace[] = $request->getItemname($name);
                
                $pattern[] = sprintf("/\[form:%s\]/", $name);
                $replace[] = $request->getForm($name);
            }
            $form = preg_replace($pattern, $replace, $form);
        } else {
            // print_r($this->sheet);
            $form .= '<div class="table-inquiry-type2">';
            foreach ($this->sheet as $name => $val) {
                $form .= '<div class="item-tr form-group">';
                if (isset($val['type-hidden'])) {
                    ;
                } else if (isset($val['type-title'])) {
                    $form .= sprintf("<div class=\"ctitle\">%s</div>", $request->getItemname($name));
                } else {
                    $form .= sprintf("<label class=\"c1 control-label col-sm-3\">%s</label>", $request->getItemname($name));
                    $form .= sprintf("<div class=\"c2 col-sm-9\">%s</div>",  $request->getForm($name));
                }
                $form .= '</div>';
            }

            if (isset($this->options['mycontact_form_add_text']) && $this->options['mycontact_form_add_text'] != "") {
                $form .= $this->options['mycontact_form_add_text'];
            }
            
            $form .= '</div>';
        }

        $form .= '
<div class="confirmArea form-group text-center">
<input type="hidden" name="cfctl" value="confirm" />
<button type="submit" id="confirm-button" class="btn btn-default">送信項目を確認</button>
</div>
</form>
</div>';
        
        return $form;
    }

    function cf_confirm() {
        $form = "";
        $request = $this->request;

        $err_msges = array();
        $err_flag = 0;

        // print "<p>-- check fase --</p>";
        foreach ($this->sheet as $name => $val) {
            $value = $request->get($name);
            $msg = $request->check($name, $value);

            if ($err_flag < $request->errflag) {
                // $request->errflag++;
                $err_flag = $request->errflag;
                $err_msges[] = $msg;
            }
            
        }
        // print "<p>-- end of check fase --</p>";
        if($request->getErrorCount() == 0) {
            $form = $this->cf_confirm_sub();
        } else {
            $form .= $this->getErrorMessgaeTag($err_msges);
            $form .= $this->cf_form();
        }
        
        return $form;
    }
    
    function cf_confirm_sub() {
        $request = $this->request;
        $form = "";
        $form .= '
        <div id="cf-inquiry">

<h3>入力情報の確認</h3>
<p class="confirmDescription">確認のうえよろしければ「送信」をクリックしてください。</p>';

        if (isset($this->options['mycontact_confirm_text']) && $this->options['mycontact_confirm_text'] != "") {
            $confirm_text = stripcslashes($this->options['mycontact_confirm_text']);
        } else if (isset($this->options['mycontact_form_text']) && $this->options['mycontact_form_text'] != "") {
            $confirm_text = stripcslashes($this->options['mycontact_form_text']);
        }
        if (isset($confirm_text)) {
            $form .= $confirm_text;
            $pattern = array();
            $replace = array();
            foreach ($this->sheet as $name => $val) {
                $pattern[] = sprintf("/\[name:%s\]/", $name);
                $replace[] = $request->getItemname($name);
                $pattern[] = sprintf("/\[form:%s\]/", $name);
                $replace[] = $request->getValue($name);
            }
            $form = preg_replace($pattern, $replace, $form);
        } else {
            $form .= '<div class="table-inquiry-type2 form-horizontal">';
            foreach ($this->sheet as $name => $val) {
                $form .= '<div class="item-tr form-group">';
                if (isset($val['type-title'])) {
                    $form .= sprintf("<div class=\"ctitle\">%s</div>", $request->getItemname($name));
                } else {
                    $form .= sprintf("<label class=\"c1 control-label col-sm-3\">%s</label>", $request->getItemname($name));
                    $form .= sprintf("<div class=\"c2 col-sm-9\">%s</div>",  $request->getValue($name));
                }
                $form .= '</div>';
            }
            $form .= '</div>';
            
        }

        // 以下、確認と修正
        $form .= '
<div class="confirmArea">

  <form method="post" action="'. $_SERVER['REQUEST_URI'] .'">
      <input type="hidden" name="cfctl" value="reedit" />';
        $form .= $request->getHiddenValues();
        $form .= '
      <p class="submit">
        <input type="submit" value="&laquo;&nbsp;修正 " id="edit-button" name="edit" />
      </p>
  </form>
';
        if ($request->getErrorCount() == 0) {
            $form .= '
  <form method="post" action="'. $_SERVER['REQUEST_URI'] .'">
      <input type="hidden" name="cfctl" value="done" />
';
            $form .= $request->getHiddenValues();
            $form .= '
      <p class="submit">
        <input type="submit" value="送信 &raquo;" id="send-button" name="submit" />
      </p>
  </form>';
        }
        $form .= '
</div>
</div>';
        
        return $form;
        
    }

    function cf_done() {
        $request = $this->request;
        
        $form = "";
        $options = $this->options;
        
        $mailto = $request->get('email');
        $mailfrom = $options['mycontact_from_addr'];

        
        $fullname = trim($options['mycontact_from_fullname']);

        mb_language($this->mb_language) ;
        $fullname = mb_encode_mimeheader($fullname);
        if ($fullname != '') {
            $headers = "From: \"$fullname\" <$mailfrom>";
        } else {
            $headers = "From: $mailfrom";
        }
        if($options['mycontact_cc_addr'] != '') {
            $headers .= "\nCc: " . $options['mycontact_cc_addr'];
        }
        if($options['mycontact_bcc_addr'] != '') {
            $headers .= "\nBcc: " . $options['mycontact_bcc_addr'];
        }

        $mailsubject = $options['mycontact_mail_subject']; // 件名

        $mailbody = $options['mycontact_mail_text'];

        // 入力者用文面オプションがある場合
        if ($options['mycontact_customer_mail_text'] != '') {
            $mailbody = $options['mycontact_customer_mail_text'];
        }
        
        $mailbody = $request->getItemNamePatterns($mailbody);

        // エラーは管理者に送信する
        $sendmail_params = "-f" . $options['mycontact_send_addr'];
        
        // emailの指定がある場合、入力者に送る
        if ($mailto != '') {
            $this->mycon_mail($mailto, $mailsubject, $mailbody, $headers, $sendmail_params);
        }

        // 同じ内容で管理者に送る
        if (! isset($options['mycontact_send_addr_not_email_send']) || $options['mycontact_send_addr_not_email_send'] != 'NoSend') {
            $mailto = $options['mycontact_send_addr'];
        
            // 入力者用文面オプションがある場合
            if ($options['mycontact_customer_mail_text'] != '') {
                $mailbody = $options['mycontact_mail_text'];
                $mailbody = $request->getItemNamePatterns($mailbody);
            }

        
            $bodypre = '

[下記の内容にて入力フォームから受け付けました。]

';
        
            $mailbody = $bodypre . $mailbody;
        
            if ($this->isAttachFile()) {
                $files = array();
                foreach ($this->sheet as $name => $val) {
                    if (isset($val['type-file']) && $request->get($name) != '') {
                        $filename = $request->get($name);
                        $upload_dirs = wp_upload_dir();
                        $upload_dir = $upload_dirs['basedir'] . '/' . MYCONFORM_UPLOAD_FILE_DIR;
                        $upfile = $upload_dir . "/" . $filename;
                        if(file_exists($upfile))
                            $files[] = $upfile;
                    }
                }
            
                $this->mycon_mail($mailto, $mailsubject, $mailbody, $headers, $sendmail_params, $files);

                // 最後は削除
                foreach ($files as $del_file) {
                    if(file_exists($del_file))
                        unlink($del_file);
                }
            } else {
                // 管理者に送信
                $this->mycon_mail($mailto, $mailsubject, $mailbody, $headers, $sendmail_params);
            }
        
        }
        
        // ページに表示するメッセージ
        $done_page_text = '
<h3>送信完了</h3>

<strong>お問合せ頂き有り難うございました。</strong>

<div style="margin-left: 150px;">
<a href="' . get_bloginfo('home') . '">トップへ戻る</a>
</div>';
        
        if($options['mycontact_done_page_text'] != '') {
            $done_page_text = stripcslashes($options['mycontact_done_page_text']);
        }

        $form .= $done_page_text;
        return $form;
    }

    function isAttachFile() {
        $file_flag = false;
        foreach ($this->sheet as $name => $val) {
            if (isset($val['type-file'])) {
                $file_flag = true;
            }
        }
        return $file_flag;
    }

    function getSheetFromTemplate($template) {
        $fields = $this->get_inistr_to_array($template);
        
        // print_r($fields);
        $sheet = array();

        $vals_names_arr = array('check-must', // 必須事項設定
                                'remark', // 注意事項等
                                'interval', // radio, checkboxの改行指定
                                'default',  // デフォルト値指定
                                'class', // class指定
                                'check-mail', // メールアドレスが正しいかどうか
                                'check-mail-exist', // 既に登録されているか？
                                'check-mail-again', // 
                                'check-mail-err',
                                'check-same-again', // 同じ値かどうかをチェック
                                'check-same-err',   // 上記のエラーメッセージ
                                'check-nodisp',
                                'set-year-from-to', // type-date用・年のレンジを決める
                                'set-default-date',  // type-date用・日付のデフォルト
                                'first-no-value-item', // select用,最初の値のない選択項目
                                'category-name',
                                'refine-search', /* 絞り込み検索 */
            );
        
        foreach ($fields as $field) {
            foreach($field as $key => $vals) {
                if (isset($vals['name'])) {
                    $sheet[$key]['item-name'] = $vals['name'];
                }
                if (isset($vals['type'])) {
                    $sheet[$key]['type'] = $vals['type']; /* type キーに type名を追加 */
                    switch($vals['type']) {
                    case 'type-text':
                    case 'type-password':
                      
                        $sheet[$key][$vals['type']]['size'] = '30'; // default
                        if (isset($vals['size'])) {
                            $sheet[$key][$vals['type']]['size'] = $vals['size'];
                        }

                        $sheet[$key][$vals['type']]['maxlength'] = '100'; // default
                        if (isset($vals['maxlength'])) {
                            $sheet[$key][$vals['type']]['maxlength'] = $vals['maxlength'];
                        }
                        break;
                    case 'type-radio':
                    case 'type-select':
                    case 'type-checkbox':
                    case 'type-checkbox-2':
                    case 'type-select-from-to':
                    case 'type-payment':
                        if (isset($vals['item'])) {
                            $sheet[$key][$vals['type']] = explode( '#', $vals["item"] );
                        }
                        break;
                    case 'type-textarea':
                        $sheet[$key][$vals['type']]['rows'] = '5'; // default
                        if (isset($vals['rows'])) {
                            $sheet[$key][$vals['type']]['rows'] = $vals['rows'];
                        }
                        $sheet[$key][$vals['type']]['cols'] = '40'; // default
                        if (isset($vals['cols'])) {
                            $sheet[$key][$vals['type']]['cols'] = $vals['cols'];
                        }
                        break;
                    default:
                        $sheet[$key][$vals['type']] = '1';
                    }
                }
                foreach($vals_names_arr as $vals_name) {
                    if (isset($vals[$vals_name])) {
                        $sheet[$key][$vals_name] = $vals[$vals_name];
                    }
                }
            }
        }
        return $sheet;
    }

    function get_inistr_to_array($str) {
        $result_arr = array();
    
        $section = "";
        $section_arr = array();

        $i = 0;
        for($line = strtok($str,"\r\n") ; $line != null ; $line = strtok("\r\n")) {
            $line = trim($line);    // delete first space

            if(preg_match('/^;/', $line))
                continue;

            if(preg_match('/^\[([\w_-]+)\]$/', $line, $matches)) {
                if (count($section_arr) > 0) {
                    $result_arr[$i] = $section_arr; // save section
                    $section_arr = array(); // clear old section
                    $i++;
                }

                $section_name = $matches[1];
                $section_arr[$section_name] = array();
            } else if(preg_match('/^([\w_-]+)\=(.*)$/', $line, $matches)) {
                $section_arr[$section_name][$matches[1]] = $matches[2];
            }
        
        }
        if (count($section_arr) > 0) 
            $result_arr[$i] = $section_arr;

        // print "\n-----\n";
        return $result_arr;
    
    }

    
    function sanitize_name( $name ) {
		$name = sanitize_title( $name );
		$name = str_replace( '-', '_', $name );
		
		return $name;
	}

    function getDefaultFormTemplate() {
        $returndata = "";
        $returndata .= '';
        return $returndata;
    }
    
    /**
     * メール文の初期化
     */
    function getDefaultMailText() {
        $mailtext = "";
        $mailtext .= '';
        return $mailtext;
    }

    // テンプレートオプションの初期状態を返す
    function getInitOpt($mycontact_form_id = 'mycon-001') {
        $initopt = array();
        $initopt['mycontact_form_id'] = $mycontact_form_id;
        $initopt['mycontact_form_description'] = '';
        $initopt['mycontact_send_addr'] = get_option('admin_email');
        $initopt['mycontact_from_fullname'] = get_bloginfo('name');
        $initopt['mycontact_from_addr'] = get_option('admin_email');
        $initopt['mycontact_cc_addr'] = '';
        $initopt['mycontact_bcc_addr'] = '';
        $initopt['mycontact_template'] = $this->getDefaultFormTemplate();
        $initopt['mycontact_mail_subject'] = 'お問合せを承りました';
        $initopt['mycontact_mail_text'] = $this->getDefaultMailText();
        $initopt['mycontact_customer_mail_text'] = '';
        $initopt['mycontact_done_page_text'] = '
<h3>送信完了</h3>

<strong>お問い合わせ頂き有り難うございました。</strong>

<div style="margin-left: 150px;">
<a href="' . get_bloginfo('home') . '">トップへ戻る</a>
</div>';
        $initopt['mycontact_form_text'] = ''; // html
        $initopt['mycontact_confirm_text'] = ''; // html
        $initopt['mycontact_thanks_page_url'] = '';
        
        return $initopt;
    }
    
    /**
     * デザイン
     * file: mycontact.cssを編集する
     */
    function includeStyle() {
        ?>
<script>
// ページ情報読み込めてからチェック項目について設定する
     jQuery(document).ready(function(){
         jQuery("#registerform").validationEngine('attach', {promptPosition: "topLeft"});
     });
</script>
<?php
    }

    /**
     * 設定メニュー
     */
    function settingMenu() {
        if ( !function_exists( 'add_options_page' ) ) return;
        add_options_page( 'MyConForms', 'MyConForms', 9, __FILE__, array(&$this,  'settingPage'));
    }

    function settingPage() {
        
        $base_uri = get_bloginfo('home') . "/wp-admin/options-general.php?page=mycon/mycon.php";
        
        $display_form_id = "";
        
        if (isset($_REQUEST['mycontact_form_id']) && isset($_REQUEST['mycontact_save'])) {
            // 保存
            $display_form_id = $_REQUEST['mycontact_form_id'];
            $options = get_option($this->plugin_name);
            if (isset($options) && $options != "" && is_array($options)) {
                $opt = '';
                $index = -1;
                foreach ($options as $idx => $item) {
                    if ($item['mycontact_form_id'] == $display_form_id) {
                        $opt = $item;
                        $index = $idx;
                        break;
                    }
                }
                if ($opt != '' && is_array($opt) && $index != -1) {
                    // 新しく項目追加できるようにデフォルトの項目を利用する
                    $defaultOpt = $this->getInitOpt();
                    foreach ($defaultOpt as $opt_name => $opt_val) {
                        if (isset($_REQUEST[$opt_name])) {
                            $opt[$opt_name] = $_REQUEST[$opt_name];
                        }
                    }
                    $options[$index] = $opt;
                    update_option($this->plugin_name, $options);
                    echo '<div id="message" class="updated fade"><p><strong>設定を保存しました。</strong></p></div>';
                }
            }
        } else if (isset($_REQUEST['mycontact_form_id']) && isset($_REQUEST['mycontact_remove'])) {
            $display_form_id = $_REQUEST['mycontact_form_id'];
            $options = get_option($this->plugin_name);
            if (isset($options) && is_array($options) && count($options) > 0) {
                $new_options = array();
                foreach ($options as $item) {
                    if($item['mycontact_form_id'] != $display_form_id) {
                        $new_options[] = $item;
                    }
                }
                update_option($this->plugin_name, $new_options);
            }
        } else if (isset($_REQUEST['mycontact_form_id'])) {
            // 通常のセレクト時
            $display_form_id = $_REQUEST['mycontact_form_id'];
            $opt = $this->get_form_id_opt($display_form_id);
        } else if(isset($_REQUEST['addform'])) {
            $options = get_option($this->plugin_name);
            if (isset($options) && $options != "" && is_array($options)) {

                // 全IDを配列に入れておく
                $exit_ids = array();
                foreach ($options as $item) {
                    $exit_ids[] = $item['mycontact_form_id'];
                }

                // 存在しない一番最新の数字を決める
                $add_mycontact_form_id = '';
                for ($i = 1; $i < 999 ; $i++ ){
                    $id_name = sprintf("mycon-%03d", $i);
                    if(!in_array($id_name, $exit_ids)) {
                        $add_mycontact_form_id = $id_name;
                        break;
                    }
                }
                // optionsにセットする
                if ($add_mycontact_form_id != '') {
                    $opt = $this->getInitOpt($add_mycontact_form_id); // 初期化
                    $options[] = $opt; // 追加
                }
            } else {
                $options = array();
                $opt = $this->getInitOpt(); // 初期化
                $options[] = $opt; // 追加
                $display_form_id = $opt['mycontact_form_id'];
            }
            update_option($this->plugin_name, $options);
        } else if (isset($_REQUEST['mycontact_datainit'])) {
            // 全初期化
            delete_option($this->plugin_name);
        } else {
            $options = get_option($this->plugin_name);
        }
        ?>

<div class="wrap">
  <div id="icon-options-general" class="icon32"><br /></div>
   <h2><?php echo $this->plugin_name; ?>の設定</h2>
   <div class=""><a href="<?php echo $base_uri?>&addform">フォームテンプレートの新規追加</a></div>
   
<?php
                                            // print_r($options);
        $options = get_option($this->plugin_name);
        // 表示テンプレートの選択
        if (isset($options) && $options != "" && is_array($options)) {
            ?>
<h3>表示するテンプレートを選択してください</h3>
<ul>
            <?php
            foreach($options as $opt_item) {
                ?>
                <li><a href="<?php echo $base_uri?>&mycontact_form_id=<?php echo $opt_item['mycontact_form_id']?>"><?php echo $opt_item['mycontact_form_id']?></a> <?php echo $opt_item['mycontact_form_description']?></li>
                <?php
            }
        }
?>
</ul>
<?php
if ($display_form_id != "" && $opt != '') {

?>
  <h3>フォームテンプレート設定 [<?php echo $display_form_id?>]</h3>

  <form method="post">
   <input type="hidden" name="mycontact_save" value="save" />

   <table class="form-table">
     <tr>
       <th scope="row">
         フォームID
       </th>
       <td>
         <input type="text" value="<?php echo '[' . $opt['mycontact_form_id'] . ']'; ?>" onfocus="this.select();" readonly="readonly" /><input type="hidden" name="mycontact_form_id" value="<?php echo $opt['mycontact_form_id']?>" /><br />
         ※ [<?php echo $opt['mycontact_form_id']?>]を記事に貼り付けてご利用ください。
       </td>
     </tr>
     <tr>
       <td>このフォームの説明<br />(省略可 フォーム数が多い時の管理用)</td>
       <td><input tyhpe="text" name="mycontact_form_description" value="<?php echo $opt['mycontact_form_description']?>" size="50" /></td>
     </tr>
     <tr>
       <td>送信先アドレス（必須）</td>
       <td><input tyhpe="text" name="mycontact_send_addr" value="<?php echo $opt['mycontact_send_addr']?>" />（※ デフォルト：管理人アドレス）</td>
     </tr>
     <tr>
       <th scope="row">
        差出人 From: （必須）
       </th>
       <td>
          差出人名: <input tyhpe="text" name="mycontact_from_fullname" value="<?php echo $opt['mycontact_from_fullname']?>" />（※ デフォルト:サイト名）<br /> 
          E-Mail:<input tyhpe="text" name="mycontact_from_addr" value="<?php echo $opt['mycontact_from_addr']?>" /> （※ デフォルト：管理人アドレス）
       </td>
     </tr>
     <tr>
       <th scope="row">CC(カーボンコピー)アドレス</th>
       <td><input tyhpe="text" name="mycontact_cc_addr" value="<?php echo $opt['mycontact_cc_addr']?>"/></td>
     </tr>
     <tr>
       <th scope="row">Bcc(ブラインドカーボンコピー)アドレス</th>
       <td><input tyhpe="text" name="mycontact_bcc_addr" value="<?php echo $opt['mycontact_bcc_addr']?>"/></td>
     </tr>

     <tr>
       <th scope="row">フォームテンプレート（必須）</th>
      <td><textarea name="mycontact_template" rows="10" cols="50" class="large-text code"><?php echo $opt['mycontact_template']?></textarea></td>
     </tr>
     
     <tr>
       <th scope="row">送信文の件名（必須）</th>
       <td><input tyhpe="text" size="50" name="mycontact_mail_subject" value="<?php echo $opt['mycontact_mail_subject']?>"/></td>
     </tr>
     
     <tr>
       <th scope="row">メールの送信文（必須）</th>
       <td>
         <textarea name="mycontact_mail_text" rows="10" cols="50" class="large-text code"><?php echo $opt['mycontact_mail_text']?></textarea>
       </td>
     </tr>


     <tr>
       <th scope="row">ユーザー（フォーム入力者）宛メールの送信文（空の場合同じ文面）</th>
       <td>
         <textarea name="mycontact_customer_mail_text" rows="10" cols="50" class="large-text code"><?php echo $opt['mycontact_customer_mail_text']?></textarea>
       </td>
     </tr>

     <tr>
      <th scope="row">入力フォームHTMLテンプレート(空の場合自動生成されます)</th>
      <td><textarea name="mycontact_form_text" rows="8" cols="50" class="large-text code"><?php echo stripcslashes($opt['mycontact_form_text'])?></textarea></td>
     </tr>
     <tr>
      <th scope="row">入力フォームHTML確認テンプレート(空の場合自動生成されます)</th>
      <td><textarea name="mycontact_confirm_text" rows="8" cols="50" class="large-text code"><?php echo stripcslashes($opt['mycontact_confirm_text'])?></textarea></td>
     </tr>

     <tr>
      <th scope="row">送信完了ページ表示メッセージ</th>
      <td><textarea name="mycontact_done_page_text" rows="8" cols="50" class="large-text code"><?php echo stripcslashes($opt['mycontact_done_page_text'])?></textarea></td>
     </tr>

      <tr>
      <th scope="row">thanksページリダイレクトURL</th>
      <td><input type="text" name="mycontact_thanks_page_url" value="<?php echo $opt['mycontact_thanks_page_url']; ?>" /></td>
     </tr>
     
   </table>
   
   <input type="submit" class="button button-primary" value="設定を保存" />
  </form>
  <h3>上記のフォームテンプレートの削除</h3>
  <form method="post" action="<?php echo $base_uri?>">
    <input type="hidden" name="mycontact_remove" value="remove" />
    <input type="hidden" name="mycontact_form_id" value="<?php echo $opt['mycontact_form_id']?>" />
    <input type="submit" class="button button-primary" value="このフォームを削除" />
  </form>
  
<?php } ?>

</div><!-- .wrap -->
<?php
    }

    function mycon_mail($to, $subject, $message, $headers = '', $params = null, $attachments = array() ) {
        if (is_array($attachments) && count($attachments) > 0) {
            wp_mail($to, $subject, $message, $headers, $attachments);
        }else {
            mb_language($this->mb_language) ;
            mb_internal_encoding("UTF-8") ;
            mb_send_mail($to, $subject, $message, $headers, $params);
        }
    }


    /**
     *
     */
    function getErrorMessgaeTag($messages) {
        $form = '<div class="errorMessgaeBox">' . "\n";
        $form .= '<ul class="errorMessgaeTag">' . "\n";
        foreach ($messages as $msg) {
            if (trim($msg) != '') { 
                $form .= sprintf("<li>%s</li>\n", $msg);
            }
        }
            
        $form .= '</ul>';
        $form .= '</div>';
        return $form;
    }

    /**
     * 設定があった場合、リダイレクト処理
     */
    public function mycon_redirect_page() {
        if (isset($_REQUEST['cfctl']) && $_REQUEST['cfctl'] == 'done') {
            $this_post_obj = get_post();
            $this->mycontact_filter($this_post_obj->post_content); /* check this page conent has mycon-form tag */
            // exit;
            $redirect_url = $this->options['mycontact_thanks_page_url'];
            if ($redirect_url != '') {
                header("Location: $redirect_url");
                exit;
            }
        }
    }
    
    function mycon_enqueue_script() {
        wp_enqueue_script( 'validationEngine', plugins_url('js/jquery.validationEngine.js', __FILE__), array( 'jquery' ) );
        wp_enqueue_script( 'validationEngine-ja', plugins_url('js/jquery.validationEngine-ja.js', __FILE__), array( 'jquery' ) );

    }
    
    function mycon_enqueue_style() {
        wp_enqueue_style('mycon', plugins_url('mycontact.css', __FILE__), false, null);
        wp_enqueue_style('validationEngine', plugins_url('js/validationEngine.jquery.css', __FILE__), false, null);
    }
    
}

new myConForms();



?>
