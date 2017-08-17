/**
 * Created by admin on 2017/8/17.
 */
jQuery(document).ready( function($) {
    $(".zl_comment_thumb_do_like").click( function() {
        var comment_id =$(this).attr('data-comment-id');
        $.ajax({
            type: "POST",
            data: {
                action: 'do_user_thumb_up',
                comment_id: comment_id,
                user_id: ajax_object.user_id
            },
            url: ajax_object.url,
            success : function( data ) {
                console.log(data);
            },
            error: function(data) {
                console.log('AJAX发送失败----' + comment_id);
            }
        });
    });
});
