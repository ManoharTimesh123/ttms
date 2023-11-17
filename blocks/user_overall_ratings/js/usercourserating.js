$('#hidecourse').click(function () {
    $('#courseratingspopup').modal('hide');
    $('#courserating-details').html('');
    $.ajax ({
        method:"POST",
        url: M.cfg.wwwroot + '/blocks/user_overall_ratings/courserating_detail_ajax.php',
        success: function(response){
            $('#courseratingspopup').modal('show');
            $('#courserating-details').html(response);
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(thrownError);
        }
    });
});