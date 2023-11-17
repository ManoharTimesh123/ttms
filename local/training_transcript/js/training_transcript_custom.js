$(document).ready(function() {
    $('.training-transcript').click(function () {
        $('#trainingtranscriptmodelpopup').modal('hide');
        $('#training-detail').html('');
        var trainingid = $(this).attr('id');
        var userid = $('#userid').val();
        $.ajax ({
            method:"POST",
            url: M.cfg.wwwroot + '/local/training_transcript/training_detail_ajax.php',
            data: {'trainingid':trainingid, 'userid':userid},
            success: function(response){
                $('#trainingtranscriptmodelpopup').modal('show');
                $('#training-detail').html(response);
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError);
            }
        });
    });

    $(document).on("click", "input[type='checkbox'].enable-date", function() {
        var selectdatetime = $(this).attr('id');
        var datetimeclender = selectdatetime.split("_");
        var datepickerelement = datetimeclender[0]+'_'+datetimeclender[1];
        var day = datepickerelement+'_day';
        var month = datepickerelement+'_month';
        var year = datepickerelement+'_year';
        if (this.checked) {
            $('#'+day).prop('disabled', false);
            $('#'+month).prop('disabled', false);
            $('#'+year).prop('disabled', false);
        } else {
            $('#'+day).prop('disabled', true);
            $('#'+month).prop('disabled', true);
            $('#'+year).prop('disabled', true);
        }

    });
});