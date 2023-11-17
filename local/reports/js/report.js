$(document).ready(function() {
    $(document).on("click", ".attendancereport .form-autocomplete-suggestions li", function() {
        var courseid = $(this).attr('data-value');
        $.ajax({
            url: M.cfg.wwwroot + '/local/reports/report_ajax.php',
            type: 'get',
            dataType: 'json',
            data: {
                "courseid": courseid,
                "reportmodule" : "attendance"
            },
            success: function(data) {
                var html = '';
                if(data) {
                    for (var key of Object.keys(data)) {
                        html += '<option value="'+key+'">'+data[key]+'</option>';
                    }
                } else {
                    html += '<option selected="selected" value="">No Attendance available</option>';
                }
                $("#id_attendance").html(html);
            }
        });
    });
    $(document).on("click", ".certificatereport .form-autocomplete-suggestions li", function() {
        var courseid = $(this).attr('data-value');
        $.ajax({
            url: M.cfg.wwwroot + '/local/reports/report_ajax.php',
            type: 'get',
            dataType: 'json',
            data: {
                "courseid": courseid,
                "reportmodule" : "certificate"
            },
            success: function(data) {
                var html = '';
                if(data) {
                    for (var key of Object.keys(data)) {
                        html += '<option value="'+key+'">'+data[key]+'</option>';
                    }
                } else {
                    html += '<option selected="selected" value="">No Certificate available</option>';
                }
                $("#id_certificate").html(html);
            }
        });
    });
    $(document).on("click", ".questionnairereport .form-autocomplete-suggestions li", function() {
        var courseid = $(this).attr('data-value');
        $.ajax({
            url: M.cfg.wwwroot + '/local/reports/report_ajax.php',
            type: 'get',
            dataType: 'json',
            data: {
                "courseid": courseid,
                "reportmodule" : "questionnaire"
            },
            success: function(data) {
                var html = '';
                if(data) {
                    for (var key of Object.keys(data)) {
                        html += '<option value="'+key+'">'+data[key]+'</option>';
                    }
                } else {
                    html += '<option selected="selected" value="">No feedback available</option>';
                }
                $("#id_questionnaire").html(html);
            }
        });
    });
});