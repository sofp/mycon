<?php
//================================================
// 住所 → 緯度/経度変換
//================================================
function strAddrToLatLng( $strAddr )
{
    $input_str = 'http://maps.google.com/maps/api/geocode/json'
         . '?address=' . urlencode( mb_convert_encoding( $strAddr, 'UTF-8' ) )
                       . '&sensor=false';
    
    $strRes = file_get_contents($input_str);
         
    
    echo $input_str;
    $aryGeo = json_decode( $strRes, TRUE );
    if ( !isset( $aryGeo['results'][0] ) )
        return '';


    $strLat = (string)$aryGeo['results'][0]['geometry']['location']['lat'];
    $strLng = (string)$aryGeo['results'][0]['geometry']['location']['lng'];
    return $strLat . ',' . $strLng;
}


function get_gps_from_address( $address='' ){
    $res = array();
    $req = 'http://maps.google.com/maps/api/geocode/xml';
    $req .= '?address='.urlencode($address);
    $req .= '&sensor=false';    
    $xml = simplexml_load_file($req) or die('XML parsing error');
    if ($xml->status == 'OK') {
        $location = $xml->result->geometry->location;
        $res['lat'] = (string)$location->lat[0];
        $res['lng'] = (string)$location->lng[0];
    }
    return $res;
}


// echo strAddrToLatLng( '東京都青梅市' );
print_r(get_gps_from_address( '東京都青梅市' ));
?>
