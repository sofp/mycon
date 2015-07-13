// 下記を入れないとWordPress内で干渉する
jQuery(document).ready(function($){


$(function() { 
    $("select.refine_search").change(function() {
        var selected_id = $(this).attr("id");
        // alert("id=" + selected_id);

        var cid = $(this).val();

        var last_id_arr  = selected_id.match(/_(\d+)$/);
        // alert(last_id_arr);
        var last_id_val = Number(last_id_arr[1]);
        last_id_val = last_id_val + 1; // 一つ番号をインクリメント
        var target_id = selected_id.replace(/\_\d$/, "_" + String(last_id_val));
        $.getJSON("/wp-content/plugins/mycon/catchild_ajax.php",
                  {"cid": cid},
                  function(json) {
                      var cnum = json.length;
                      // alert("target_id: " + target_id + "[" + cnum + "]");
                      $("#" + target_id).empty();
                      for (var idx in json) {
                          var c2name = json[idx].name;
                          var c2id = json[idx].term_id;
                          $("#" + target_id).append($('<option>').html(c2name).val(c2id));
                      }
                      
                      
                  });
                  
    });
});

$("#setGeocodeXYbutton").click(function() {
    var select_val = $("#jinfo_area_search_1").find(':selected').text();
    var select_val2 = $("#jinfo_area_search_2").find(':selected').text();
    var add_sub = $("#jinfo_area_text").val();

    if(!select_val || select_val == '選択して下さい')
        alert('都道府県を選択して下さい。');
    else if(!select_val2 || select_val2 == '選択して下さい')
        alert('市区町村を選択して下さい。');
    else if(!add_sub)
        alert('以降の住所を入力して下さい。');
    else
        {
            var add_text = select_val + select_val2 + add_sub;

            alert(add_text);
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({
                address: add_text
            },
                             function(data, status){
                                 if (status == google.maps.GeocoderStatus.OK) {
                                     $("#mapX").val(data[0].geometry.location.lat());
                                     $("#mapY").val(data[0].geometry.location.lng());
                                     setMapXY(data[0].geometry.location.lat(),
                                              data[0].geometry.location.lng());
                                 } else {
                                     alert("Google map APIでエラーが発生しました: " + status);
                                 }
                             });
        }
});


$('input#delall').click(function() {
    if ($(this).is(':checked')) { 
        $('input.delitem').attr('checked', 'checked');
    } else {
        $('input.delitem').removeAttr('checked');
    }
        
    
});


$("select.jinfo_diplay_month").change(function () {
    $("span#mailcount").text($(this).val() * 10);
    
    });



}); // jQuery(document).....

