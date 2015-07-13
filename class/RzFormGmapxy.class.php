<?php
/**
 * カテゴリを選択するフォーム作成
 */
class RzFormGmapxy extends RzFormBase {

    var $name;
    var $category_name;
    var $request;
    var $sheet;
    var $refine_search;
    
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

        $form .= "<input type=\"button\" id=\"setGeocodeXYbutton\" value=\"上記の住所で緯度経度を取得\"  />";
        $form .= '
  <div id="map_canvas" style="width: 520px; height: 400px;"></div>

<script type="text/javascript">

var google_map;
google.maps.event.addDomListener(window, "load", function() {
var mapdiv = document.getElementById("map_canvas");
var myOptions = {
zoom: 12,
center: new google.maps.LatLng(35.65865941, 139.7453293),
mapTypeId: google.maps.MapTypeId.ROADMAP,
scaleControl: true
};
google_map = new google.maps.Map(mapdiv, myOptions);

var marker = new google.maps.Marker({
position: new google.maps.LatLng(35.65865941, 139.7453293),
map: google_map, 
title: ""
});

google.maps.event.addListener(google_map, "idle", function() {
drawMarker();
});
google.maps.event.addListener(google_map, "drag", function() {
drawMarker();
});


// 地図の中心にマークを常に描画する
function drawMarker(){
marker.setPosition(google_map.getCenter());
}

});

//中心の座標を取得して情報を表示する
function getMapXY(){
zahyo = google_map.getCenter();
document.getElementById("mapX").value = zahyo.lng();
document.getElementById("mapY").value = zahyo.lat();
}

//中心の座標を指定して移動する
function setMapXY(lat, lon){
google_map.setCenter(new google.maps.LatLng(lat, lon), 12);
}

</script>
';
        $form .= sprintf('Lng:<input type="text" name="%s_mapX" id="mapX" value="" />', $this->name);
        $form .= sprintf('Lat:<input type="text" name="%s_mapY" id="mapY" value="" />', $this->name);
        
        return $form;
    }


    /* 入力をチェック */
    public function check() {
        // $v = "地図";
        return "";
    }


    /* 値を取得 */
    public function getValue($format = '') {
        $v = "";

        $v .= '
  <div id="map_canvas" style="width: 520px; height: 400px;"></div>

<script type="text/javascript">

var google_map;
google.maps.event.addDomListener(window, "load", function() {
var mapdiv = document.getElementById("map_canvas");
var myOptions = {
zoom: 12,
center: new google.maps.LatLng(35.65865941, 139.7453293),
mapTypeId: google.maps.MapTypeId.ROADMAP,
scaleControl: true
};
google_map = new google.maps.Map(mapdiv, myOptions);

var marker = new google.maps.Marker({
position: new google.maps.LatLng(35.65865941, 139.7453293),
map: google_map, 
title: ""
});

google.maps.event.addListener(google_map, "idle", function() {
drawMarker();
});
google.maps.event.addListener(google_map, "drag", function() {
drawMarker();
});


// 地図の中心にマークを常に描画する
function drawMarker(){
marker.setPosition(google_map.getCenter());
}

});

</script>
';        
        return $v;
    }
    /* hidden */
    public function getHiddenTag() {
        $v = "";
        $name_x = sprintf("%s_mapX", $this->name);
        $name_y = sprintf("%s_mapY", $this->name);

        $v .= $this->request->getHiddenTag($name_x,
                                           $this->request->get($name_x));

        $v .= $this->request->getHiddenTag($name_y,
                                           $this->request->get($name_y));
        
        return $v;
    }

    public function postRegistByPid($post_id) {
        // echo "<p>RzFormGmapxy::postRegistByPid[" . $post_id . "]</p>";
        $form = "";
        $name_x = sprintf("%s_mapX", $this->name);
        $name_y = sprintf("%s_mapY", $this->name);
        

        add_post_meta( $post_id, $name_x, $this->request->get($name_x));
        add_post_meta( $post_id, $name_y, $this->request->get($name_y));

        return $form;
    }

    public function setValueFromWPUserdata($user_id) {
    }

    /* 投稿データから値を取得し、requestにセットする（投稿編集用） */
    public function setValueFromWPPostdata($post_id) {
    }

    /* 指定された投稿IDでアップデートする */
    public function updateValueWPPostByPid($post_id) {
        $name_x = sprintf("%s_mapX", $this->name);
        $name_y = sprintf("%s_mapY", $this->name);
        

        update_post_meta( $post_id, $name_x, $this->request->get($name_x));
        update_post_meta( $post_id, $name_y, $this->request->get($name_y));
    }
}
?>