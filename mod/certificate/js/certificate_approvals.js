/* INTG Customization Start : Javascript for the change in the group field based on the course selected on the certificate approval page. */
$(document).ready(function() {
    $(document).on("click", ".certificateapproval_course .form-autocomplete-suggestions li", function() {
        var courseid = $(this).attr('data-value');

        get_course_groups_ajax ('filterdata', courseid);
    });
});

function certificate_status_comment (action, participantid, participantname, courseid, groupid) {
    $("#certificateapprovalpopup").modal('show');
    $("#modalusername").html(action + ' certificate for <span class="h3">'+participantname+'</h3>');

    $("#popupsubmit").on("click", function(e) {
        var statuscomment = $("#certificatecomment").val();
        $.ajax({
            url: M.cfg.wwwroot + '/mod/certificate/approval_ajax.php',
            type: 'post',
            dataType: 'json',
            data: {
                "type": 'updatedata',
                "action": action,
                "userid": participantid,
                "course": courseid,
                "group": groupid,
                "statuscomment": statuscomment
            },
            success: function(data) {
                if (data) {
                    $("#certificatecomment").val('');
                    $("#modalusername").html('');
                    $("#certificateapprovalpopup").modal('hide');
                    $("#user-notifications").html('<div class="alert alert-success">Certificate ' + action + ' for ' + participantname + '</div>');

                    var delay = 1000;
                    setTimeout(function(){ window.location = M.cfg.wwwroot + '/mod/certificate/approvals.php?course='+courseid+'&group='+groupid; }, delay);

                } else {
                    console.log('Error: Error is updating the user certificate status.');
                }

            }
        });

    });
}

function get_course_groups_ajax (type = 'filterdata', courseid = 0, groupid = 0) {
    if (courseid > 0 ) {
        $.ajax({
            url: M.cfg.wwwroot + '/mod/certificate/approval_ajax.php',
            type: 'post',
            dataType: 'json',
            data: {
                'type': type,
                'course': courseid
            },
            success: function(data) {
                var html = '';
                if (data) {
                    for (var key of Object.keys(data)) {
                        var selected = '';
                        if (key == groupid){
                            selected = 'selected';
                        }
                        html += '<option value='+key+' '+selected+' >'+data[key]+'</option>';
                    }
                } else {
                    html += '<option selected=\'selected\' value=\'0\'>No groups available</option>';
                }
                $('#id_group').html(html);
            }
        });
    } else {
        return false;
    }
}
/* INTG Customization End */
