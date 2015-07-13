<?php
/**
 * 住所フォーム作成
 */
class RzFormAddress extends RzFormBase {

    protected $name1, $name2, $name3;
    protected $prefecture = array("北海道", "青森県", "岩手県", "宮城県", "秋田県", "山形県",
                         "福島県", "茨城県", "栃木県", "群馬県", "埼玉県", "千葉県",
                         "東京都", "神奈川県", "新潟県", "富山県", "石川県",
                         "福井県", "山梨県", "長野県", "岐阜県", "静岡県", "愛知県",
                         "三重県", "滋賀県", "京都府", "大阪府", "兵庫県",
                         "奈良県", "和歌山県", "鳥取県", "島根県", "岡山県",
                         "広島県", "山口県", "徳島県", "香川県", "愛媛県", "高知県",
                         "福岡県", "佐賀県", "長崎県", "熊本県", "大分県",
                         "宮崎県", "鹿児島県", "沖縄県");
    
    public function __construct($name, $sheet) {
        parent::__construct($name, $sheet);

        $name = $this->name;
        
        $this->name1 = $name . '-1';
        $this->name2 = $name . '-2';
        $this->name3 = $name . '-3';
    }
    
    
    /**
     * 入力フォームを作成
     */
    public function getForm() {

        $class_str = 'typeAddress ' . $this->name;
        if (isset($this->sheet['class'])) {
            $class_str = $class_str . ' ' . $this->sheet['class'];
        }
        
        $format  = "都道府県: %s<br />\n";
        $format .= "市区町村・番地等:<br />%s<br />\n";
        $format .= "ビル名・階数等.:<br /> %s\n";
        
        $this->request->checksheet[$this->name1]['type-select'] = $this->prefecture;
        $this->request->checksheet[$this->name1]['type'] = 'type-select'; /* typeも入れる */
        $this->request->checksheet[$this->name1]['first-no-value-item'] = "選択してください";
        
        $form = sprintf($format,
                        $this->request->getForm($this->name1),
                        $this->request->getFormText($this->name2, 50 ,100, $class_str),
                        $this->request->getFormText($this->name3, 50 ,100, $class_str));
        
        return $form;
    }


    /* 入力をチェック */
    public function check() {
        $msg = "";


        $v1 = $this->request->get($this->name1);
        $msg1 = $this->_pre_check($this->name, $v1);
        
        $v2 = $this->request->get($this->name2);

        $msg2 = $this->_pre_check($this->name, $v2);
        
        $v3 = $this->request->get($this->name3);
        $msg3 = $this->_pre_check($this->name, $v3);

        if($msg1) $msg = $msg1;
        if($msg2) $msg = $msg2;
        if($msg3) $msg = $msg3;

        // echo "[$msg]";
        return $msg;
    }


    /* 値を取得 */
    public function getValue($format = '') {
        $format = "%s %s %s";
        
        $string = sprintf($format,
                          $this->request->get($this->name1),
                          $this->request->get($this->name2),
                          $this->request->get($this->name3));
        return $string;
    }
    /* hidden */
    public function getHiddenTag() {
        $v = "";
        $v .= $this->request->getHiddenTag($this->name,
                                           $this->request->get($this->name));
        $v .= $this->request->getHiddenTag($this->name1,
                                           $this->request->get($this->name1));
        $v .= $this->request->getHiddenTag($this->name2,
                                           $this->request->get($this->name2));
        $v .= $this->request->getHiddenTag($this->name3,
                                           $this->request->get($this->name3));
        return $v;
    }

    public function postRegistByPid($post_id) {
        $form = "";
        
        add_post_meta($post_id, $this->name1, $this->request->get($this->name1));
        add_post_meta($post_id, $this->name2, $this->request->get($this->name2));
        add_post_meta($post_id, $this->name3, $this->request->get($this->name3));
        
        
        return $form;
    }

    public function setValueFromWPUserdata($user_id) {
        $this->request->add($this->name1, get_usermeta($user_id, $this->name1));
        $this->request->add($this->name2, get_usermeta($user_id, $this->name2));
        $this->request->add($this->name3, get_usermeta($user_id, $this->name3));
    }

    /* 投稿データから値を取得し、requestにセットする（投稿編集用） */
    public function setValueFromWPPostdata($post_id) {
        $this->request->add($this->name1, get_post_meta($post_id, $this->name1, true));
        $this->request->add($this->name2, get_post_meta($post_id, $this->name2, true));
        $this->request->add($this->name3, get_post_meta($post_id, $this->name3, true));
    }

    /* 指定された投稿IDでアップデートする */
    public function updateValueWPPostByPid($post_id) {
        
        update_post_meta($post_id, $this->name1, $this->request->get($this->name1));
        update_post_meta($post_id, $this->name2, $this->request->get($this->name2));
        update_post_meta($post_id, $this->name3, $this->request->get($this->name3));

    }
    
}