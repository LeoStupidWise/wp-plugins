/**
 * Created by admin on 2017/8/28.
 */
jQuery(document).ready( function($) {
    $("#banner_button_add").click( function() {
        //var input = $('#zoe_plugin_banner_form p').last().prev().children('input');
        var p_last = $('#zoe_plugin_banner_form div').last().children('p').last();
        var div_last = $('#zoe_plugin_banner_form div').last();
        var input = p_last.children('input');
        console.log(input);
        console.log(input.attr('data-num'));
        var hidden_num = parseInt($("#zoe_plugin_banner_hidden").attr("value"));
        var num_now = parseInt(input.attr('data-num')) + 1;

        var div_value = "div_banner_image_" + num_now;
        var url_value = "banner_image_" + num_now + "_url";
        var order_value = "banner_image_" + num_now + "_order";
        var name_value = "banner_image_" + num_now + "_name";
        var link_value = "banner_image_" + num_now + "_link";
        var delete_value = "banner_button_delete_" + num_now;
        var add_content = "<div id="+ div_value +" ><p>图片"+ num_now +"</p><p>图片地址：<input style='width: 600px' type='text' name='" + url_value +"' data-num=" + num_now + "></p><p>图片显示顺序：<input type='text' name='" + order_value +"' data-num=" + num_now + "></p><p>图片标题：<input type='text' name='" + name_value +"' data-num=" + num_now + "></p><p>图片跳转链接：<input style='width: 600px' type='text' name='" + link_value +"' data-num=" + num_now + "></p><p><input class='banner_button_delete' type='button' value='删除该图片' id='"+ delete_value +"' data-num="+ num_now +"></p></div>";
        $(add_content).insertAfter(div_last);
        $("#zoe_plugin_banner_hidden").attr("value", parseInt(hidden_num)+1);
    });

    $("body").on("click", ".banner_button_delete", function() {
        // 删除当前图片 div
        var num = $(this).attr('data-num');
        var hidden_num = parseInt($("#zoe_plugin_banner_hidden").attr("value"));
        // 当前页面图片数量
        if (hidden_num == 1) {
        //    alert("只剩最后一张图片了");
            return;
        }
        var div_delete_id = "#div_banner_image_" + num;
        var div_delete = $(div_delete_id);
        div_delete.remove();
        $("#zoe_plugin_banner_hidden").attr("value", parseInt(hidden_num) - 1);
    });
});