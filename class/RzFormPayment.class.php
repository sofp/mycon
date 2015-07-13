<?php
/**
 * Radioフォーム作成
 */
class RzFormPayment extends RzFormRadio {

    public function __construct($name, $sheet) {
        parent::__construct($name, $sheet);
    }

    /**
     * 入力フォームを作成
     */
    public function getForm() {
        $form = "";

        $item = $this->sheet['type-payment']; /* itemパラメータから取った方が良いのではという気が。。。 */

        $check_arr = array();
        foreach($item as $val) {
            $check_arr[$val] = "";
        }
        
        /*
          $check_arr = array("代引き（手数料無料）" => "",
             "カード決済" => "",
             "前振込（入金確認後、商品発送となります）" => "",
             "各月１０日締め、当月末振り込み" => "",
             "各月１０日締め、翌月１０日振り込み" => "",
             "各月２０日締め、当月末振り込み" => "",
             "各月２０日締め、翌月２０日振り込み" => "",
             "各月末締め、翌月末振り込み" => "");
        */
        $check_arr[$this->request->get($this->name)] = ' checked ';
        
        $form .= '
        <p>
      <input id="paymentId01" class="validate[required]" type="radio" name="payment" value="'.$item[0].'" ' . $check_arr[$item[0]] . '>
      <label for="paymentId01">'.$item[0].'</label>
    </p>
    <p>
      <input id="paymentId02" class="validate[required]" type="radio" name="payment" value="'.$item[1].'" ' . $check_arr[$item[1]] . '>
      <label for="paymentId02">'.$item[1].'<img src="/images/icon_card.jpg" alt="" style="margin-top: -3px;"> （「まとめ買い割引」の対象外）</label>
    </p>
    <p>
      <input id="paymentId03" class="validate[required]" type="radio" name="payment" value="'.$item[2].'" ' . $check_arr[$item[2]] . ' >
      <label for="paymentId03">'.$item[2].'</label>
    </p>
    <p>
      <input id="paymentId04" class="validate[required]" type="radio" name="payment" value="'.$item[3].'" ' . $check_arr[$item[3]] . ' >
      <label for="paymentId04">'.$item[3].'</label>
    </p>
    <p>
      <input id="paymentId05" class="validate[required]" type="radio" name="payment" value="'.$item[4].'" ' . $check_arr[$item[4]] . ' >
      <label for="paymentId05">'.$item[4].'</label>
    </p>
    <p>
      <input id="paymentId06" class="validate[required]" type="radio" name="payment" value="'.$item[4].'" ' . $check_arr[$item[5]] . ' >
      <label for="paymentId06">'.$item[5].'</label>
    </p>
    <p>
      <input id="paymentId07" class="validate[required]" type="radio" name="payment" value="'.$item[5].'" ' . $check_arr[$item[6]] . ' >
      <label for="paymentId07">'.$item[6].'</label>
    </p>
    <p>
      <input id="paymentId08" class="validate[required]" type="radio" name="payment" value="'.$item[6].'" ' . $check_arr[$item[7]] . ' >
      <label for="paymentId08">'.$item[7].'</label>
    </p>
        
        ';
        
        return $form;
    }

    /* 値を取得 */
    public function getValue($format = '') {
        // echo "test:[". $this->name . "]";

        $arr = $this->sheet['type-payment'];

        $val_arr = array();
        
        foreach ($arr as $value) {
            if (preg_match("/^(.*)\:(.*)$/", $value, $matches)) {
                $value = trim($matches[1]);
                $value_str = trim($matches[2]);

                $val_arr[$value] = $value_str;
            } else {
                $val_arr[$value] = $value;
            }
        }
        
        $get_val = $this->request->get($this->name);
        $v = $val_arr[$get_val];
        return $v;
    }
    
}