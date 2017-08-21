/**
 * Created by admin on 2017/8/21.
 */
jQuery(document).ready( function($) {
    $("#floatTrigger").click( function() {
        if($("#online_qq_layer").attr("show")) {
            $("#online_qq_layer").animate({right:"-140px"});
            $("#online_qq_layer").removeAttr("show");
        } else {
            $("#online_qq_layer").animate({right:"0px"});
            $("#online_qq_layer").attr("show","1")
        };
    });

    $(window).scroll(function(){
        var top = $(window).scrollTop() + 200;
        //var left = $(window).scrollLeft() + 320;
        $("#online_qq_layer").css({
            //left: left + 'px',
            top: top + 'px'
        });
    });
});

/*
* 显示状态：style="right: 0px; display: block;"
* 藏状态：style="right: -140px; display: block;"
* id="online_qq_layer"
* */