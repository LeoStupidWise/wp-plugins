/**
 * Created by admin on 2017/8/17.
 */
jQuery(document).ready( function($) {
    $(".zl_comment_thumb_do_like").click( function() {
        var comment_id =$(this).attr('data-comment-id');
        var img = $(this);
        $.ajax({
            type: "POST",
            data: {
                action: 'do_user_thumb_up',
                comment_id: comment_id,
                user_id: ajax_object.user_id
            },
            url: ajax_object.url,
            success : function( data ) {
                var data_obj    =  eval('(' + data + ')');
                if (data_obj.code == 2000) {
                    //console.log(img.attr('src'));
                    //console.log('点赞成功');
                    img.attr('src', data_obj.img);
                }
            },
            error: function(data) {
                console.log('AJAX发送失败----' + comment_id);
            }
        });
    });

    $(".zl_comment_thumb_do_judge").click( function() {
        var comment_id    =  $(this).attr('data-comment-id');
        var score         =  $(this).attr('data-judge-id');
        var img           =  $(this);
        $.ajax({
            type: "POST",
            data: {
                action: 'do_user_thumb_up_judge',
                comment_id: comment_id,
                score: score,
                user_id: ajax_object.user_id
            },
            url: ajax_object.url,
            success : function( data ) {
                var data_obj = eval('(' + data + ')');
                $(".zl_comment_thumb_do_judge").each(function (index) {
                    if (index < score) {
                        $(this).attr('src', data_obj.img);
                    } else {
                        $(this).remove();
                    }
                }),
              console.log(data);
                //var data_obj    =  eval('(' + data + ')');
                //if (data_obj.code == 2000) {
                    //console.log(img.attr('src'));
                    //console.log('点赞成功');
                    //img.attr('src', data_obj.img);
                //}
            },
            error: function(data) {
                console.log('AJAX发送失败----' + comment_id);
            }
        });
    });
});
