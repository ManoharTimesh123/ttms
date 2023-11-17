$(document).ready(function() {
    $('.choose-training').change(function () {
        $('.response').html('');
        var trainingid = $(this).val();
        if (trainingid) {
            $.ajax ({
                method:"POST",
                url: M.cfg.wwwroot + '/local/need_based_trainings/training_ajax.php?task=gettraining',
                data: {
                    'trainingid':trainingid,
                },
                success: function(response){
                    response = JSON.parse(response);
                    if (response.id) {
                        $('.trainingdetail').css('display', 'block');
                        $('#type').html(response.coursetype);
                        $('#startdate').html(response.startdate);
                        $('#enddate').html(response.enddate);
                        $('#description').html(response.summary);
                        $('#image').html(response.image);
                    } else {
                        $('.trainingdetail').css('display', 'none');
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError);
                }
            });
        } else {
            $('.trainingdetail').css('display', 'none');
        }
    });

    $('#jointraining').on('click', function() {
        $('.response').html('');
        if (this.checked) {
            var trainingid = $("#trainingid").html();
            var topicid = $("#topicid").html();
            $.ajax ({
                method:"POST",
                url: M.cfg.wwwroot + '/local/need_based_trainings/training_ajax.php?task=alreadyadded',
                data: {
                    'trainingid':trainingid,
                    'topicid':topicid
                },
                success: function(response){
                    if (!response) {
                        $('.showreasonarea').css('display', 'block');
                    } else {
                        $('.showreasonarea').css('display', 'none');
                        $('.response').html('<div class="alert alert-info mt-2">You are already requested to the resource.</div>');
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError);
                }
            });
        } else {
            $('.showreasonarea').css('display', 'none');
        }
    });

    $('#needbasedtrainingfrom').submit(function(event) {
        $('.response').html('');
        $('.error').html('');
        event.preventDefault();
        var trainingid = $("#trainingid").html();
        var topicid = $("#topicid").html();
        var reason = $('textarea#reason').val();
        if (reason == "") {
            $('.error').html('<div class="alert alert-danger mt-2">This filed is required</div>');
            return false;
        }
        $.ajax ({
            method:"POST",
            url: M.cfg.wwwroot + '/local/need_based_trainings/training_ajax.php?task=recordinsert',
            data: {
                'trainingid':trainingid,
                'topicid':topicid,
                'reason':reason
            },
            success: function(response){
                $('.showreasonarea').css('display', 'none');
                if (response) {
                    $('.response').html('<div class="alert alert-success mt-2">Resource requested successfully.</div>');
                } else {
                    $('.showreasonarea').css('display', 'block');
                    $('.response').html('<div class="alert alert-danger mt-2">Something went wrong. Please try again</div>');
                }
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