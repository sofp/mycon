<?php
/**
 * Textフォーム作成
 */
class RzFormDate extends RzFormBase {

    protected $name1, $name2, $name3;
    
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
        // はじめの年
        $sheet = $this->sheet;
        if(isset($sheet['set-year-from-to'])) {
            $y_arr = explode('#', $sheet['set-year-from-to']);
            $from_year = $y_arr[0];
            $to_year = $y_arr[1];
        } else {
            $from_year = 1940;
            $to_year = 2020;
        }

                
        if(isset($sheet['set-default-date'])) {
            if($sheet['set-default-date'] == 'today') {
                $d_day_arr = explode('-', date('Y-m-d'));
                if ($this->request->get($this->name1) == '') 
                    $this->request->add($this->name1, $d_day_arr[0]);
                if ($this->request->get($this->name2) == '') 
                    $this->request->add($this->name2, $d_day_arr[1]);
                if ($this->request->get($this->name3) == '') 
                    $this->request->add($this->name3, $d_day_arr[2]);
            }
        }

                
        $this->request->checksheet[$this->name1]['type-select-from-to'] = array($from_year, $to_year);
        $this->request->checksheet[$this->name1]['type'] = 'select-from-to';
        $this->request->checksheet[$this->name2]['type-select-from-to'] = array(1, 12);
        $this->request->checksheet[$this->name2]['type'] = 'select-from-to';
        $this->request->checksheet[$this->name3]['type-select-from-to'] = array(1, 31);
        $this->request->checksheet[$this->name3]['type'] = 'select-from-to';
        
        $format = "%s 年 %s 月 %s 日";
        $form = sprintf($format, 
                        $this->request->getForm($this->name1),
                        $this->request->getForm($this->name2),
                        $this->request->getForm($this->name3));
        return $form;
    }


    /* 入力をチェック */
    public function check() {
        $msg = '';

        $v1 = $this->request->get($this->name1);
        $v2 = $this->request->get($this->name2);
        $v3 = $this->request->get($this->name3);
        
        if (isset($this->sheet['check-must'])) {
            if ($v1 ==  '' || $v2 ==  '' || $v3 ==  '') {
                $msg = sprintf($this->request->errorfmt, "日付が選択されていません");
            }
        }
        
        return $msg;
    }


    /* 値を取得 */
    public function getValue($format = "%s 年 %s 月 %s 日") {
        if ($format == '') {
            $format = "%s 年 %s 月 %s 日";
        }
        $name1 = $this->name1;
        // echo "<p>[format: $format:$name1]  </p>";
        $string = sprintf($format,
                          $this->request->get($this->name1),
                          $this->request->get($this->name2),
                          $this->request->get($this->name3)
        );
        
        
        return $string;
    }
    /* hidden */
    public function getHiddenTag() {
        $v = "";
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

        add_post_meta( $post_id, $this->name1, $this->request->get($this->name1));
        add_post_meta( $post_id, $this->name2, $this->request->get($this->name2));
        add_post_meta( $post_id, $this->name3, $this->request->get($this->name3));

        return $form;
    }

    /**
     * ユーザーデータから取得
     */
    public function setValueFromWPUserdata($user_id) {
        $this->request->add($this->name1, get_usermeta($user_id, $this->name1));
        $this->request->add($this->name2, get_usermeta($user_id, $this->name2));
        $this->request->add($this->name3, get_usermeta($user_id, $this->name3));
    }

    /* 投稿データから値を取得し、requestにセットする（投稿編集用） */
    public function setValueFromWPPostdata($post_id) {
        /*
        printf( "<p>[date: %s: %d : %s-%s-%s]</p>",
                $this->name1, $post_id,
                get_post_meta($post_id, $this->name1, true),
                get_post_meta($post_id, $this->name2, true),
                get_post_meta($post_id, $this->name3, true));
        */
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