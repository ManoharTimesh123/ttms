$(document).ready(function(){
    $('.fulltext').hide();
    $(document).on("click", ".commnet-box-bg .readmore", function (event) {
        event.preventDefault();
        var commentid = $(this).attr('id');
        $('#fulltext_'+commentid).slideToggle('slow');
        $(this).text($(this).text() == 'Read less...' ? 'Read more...' : 'Read less...');
    });
    $('.btn-comment').on('click', function () {
        var postid = $(this).attr('id');
        $('.commentbox').hide();
        $('.socialsharebox').hide();
        $("#commentbox"+postid).toggle();
    });

    $(document).on("click", ".btn-submit", function () {
        var buttonattributeid = $(this).attr('id');
        require(['core/ajax'], function(ajax) {
            //console.log(buttonattributeid);
            var postid = buttonattributeid.split('_');

            var comment = $("textarea#comment_content_"+postid[2]).val();

            if(comment == "") {
                alert('Please provide some content.');
                return false;
            }
            var promises = ajax.call([
                {
                    methodname: 'local_wall_create_post_comment',
                    args: {'data':{'postid': postid[2], 'description': comment} }
                }
            ]);
            promises[0].done(function(response) {
                $('.commentbox').hide();
                comment_html = '';
                comment_html += '<div class="d-flex flex-start mt-2" id="comment_row_'+response.data[0].id+'">';
                comment_html += '<img class="rounded-circle shadow-1-strong mr-3" src="'+response.data[0].user_profile_picture+'" alt="avatar" width="40" height="40" />';
                comment_html += '<div class="w-100 commnet-box-bg">';
                comment_html += '<div class="d-flex justify-content-between align-items-center mb-3">';
                comment_html += '<h6 class="text-primary fw-bold mb-0">'+response.data[0].comment_added_by+'</h6>';
                comment_html += response.data[0].comment_content;
                comment_html += '<p class="mb-0"><a onclick="deletepostcomment('+response.data[0].id+')" class="delete_comment"><i class="fa fa-trash"></i></a>'+response.data[0].created_at+'</p>';
                comment_html += '</div></div></div>';
                $('#post_comment_'+postid[2]).html('Comment '+response.data[0].comment_count);
                $('.user_comment_'+postid[2]).prepend(comment_html);
                $("#comment_content_"+postid[2]+'editable').html("");
            }).fail(function(ex){
                console.log(ex);
            });
        });
    });

    $(document).on("click", ".btn-like", function () {
        var postid = $(this).attr('id');
        require(['core/ajax'], function(ajax) {
           postid = postid;
            var promises = ajax.call([
                {
                    methodname: 'local_wall_post_like',
                    args: { 'data' : { 'postid' : postid }}
                }
            ]);
            promises[0].done(function(response) {
                if (response.data[0].like == true) {
                    vote_html = '<i class="fa fa-heart  mr-2" title="liked"></i> <p class="mb-0"> ' + response.data[0].total_like+  '</p>';
                } else {
                    vote_html = '<i class="fa fa-heart-o unlike mr-2" title="Unliked"></i> <p class="mb-0"> ' + response.data[0].total_like + '</p>';
                }
                $('.user_like_' + postid).html(vote_html);
            }).fail(function(ex){
                console.log(ex);
            });
        });
    });
    $(document).on("click", ".btn-share", function () {
        var postid = $(this).attr('id');
        $('.commentbox').hide();
        $('#socialsharebox'+postid).toggle();
    });

    $(document).on("click", ".shareurl", function (event) {
        event.preventDefault();
        var postid = $(this).attr('id');
        var targeturl = $(this).attr('href');
        require(['core/ajax'], function (ajax) {
            var data = postid.split('_');
            var promises = ajax.call([
                {
                    methodname: 'local_wall_post_share',
                    args: { 'data' : { 'postid': data[1], 'shareto': data[0]}}
                }
            ]);
            promises[0].done(function (response) {
                if (response.data[0].shareto) {
                    $('#post_share_' + data[1]).html('Share ' + response.data[0].sharedcount);
                    $('#socialsharebox' + data[1]).toggle();
                    setTimeout(function () {
                        window.open(targeturl, '_blank');
                    }, 2000);
                }
            }).fail(function (ex) {
                console.log(ex);
            });
        });
    });

});

function deletepostcomment(commentid) {

    if (confirm('Are you sure?')) {
        var commentid = commentid;
        require(['core/ajax'], function(ajax) {
            var promises = ajax.call([
                {
                    methodname: 'local_wall_delete_post_comment',
                    args: { 'data' : { 'commentid': commentid}}
                }
            ]);
            promises[0].done(function(response) {
                $("#comment_row_" + commentid).html("");
                $("#post_comment_" + response.data[0].record).html('Comment '+response.data[0].comment_count);
            }).fail(function(ex){
                console.log(ex);
            });
        });
    }
}


