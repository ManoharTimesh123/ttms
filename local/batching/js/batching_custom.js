$(document).ready(function() {
    $(document).on("click", ".facilitators .form-autocomplete-suggestions li", function() {
        var facilitator = $(this).attr('data-value');
        $.ajax({
            method: "POST",
            url: M.cfg.wwwroot + '/local/batching/training_count_ajax.php',
            data: {'facilitator': facilitator},
            success: function(response) {
                if (response == 1) {
                    alert('This user training limit reaches. Do you still want to continue?');
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError);
            }
        });
    });

    $(document).on("change", "#id_category", function() {

        var category_id = $(this).val();
        var batching = $('#id_batching').val();

        $.ajax({
            method: "POST",
            url: M.cfg.wwwroot + '/local/batching/financials_ajax.php?task=category',
            data: {
                'category_id': category_id,
                'batching': batching
            },
            success: function(response) {
                response = JSON.parse(response);
                if(response.category_details) {
                    //
                    // console.log(response);
                    // console.log(response.name);

                    $('#id_itemtitle').val(response.name);
                    $('#id_itemcost').val(response.price);
                    $('#id_itemunit').val(response.unit);
                    $('#category_details').html(response.category_details);

                } else {
                    $('#id_itemtitle').val('');
                    $('#id_itemcost').val('');
                    $('#id_itemunit').val('');
                    $('#category_details').html('');
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError);
            }
        });
    });

    $('.show-participants').click(function () {
        $('#participantsmodelpopup').modal('hide');
        $('#participants-list').html('');
        var batchid = $(this).attr('id');
        $.ajax ({
            method:"POST",
            url: M.cfg.wwwroot + '/local/batching/show_participants_ajax.php',
            data: {'batchid':batchid},
            success: function(response){
                $('#participantsmodelpopup').modal('show');
                $('#participants-list').html(response);
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError);
            }
        });
    });

    $(document).on("change", ".lunchitems", function() {

        // alert('in');

        var lunch_type = $('#id_lunch_type').find(":selected").val();
        var lunch_tea = $('[id^=id_lunch_tea]').is(':checked');
        var lunch_water = $('[id^=id_lunch_water]').is(':checked');

        if(lunch_tea) {
            var lunch_tea_id = $('[id^=id_lunch_tea]').attr('id').split('_').pop();
        } else {
            lunch_tea_id = 0;
        }

        if(lunch_water) {
            var lunch_water_id = $('[id^=id_lunch_water]').attr('id').split('_').pop();
        } else {
            lunch_water_id = 0;
        }

        var category_id = $('#id_category').find(":selected").val();
        $('#lunchoptions-list').html('');

        // console.log('items-' + lunch_type + '-' + lunch_tea + '-' + lunch_water);

        var batching = $('#id_batching').val();

        $.ajax ({
            method: "POST",
            url: M.cfg.wwwroot + '/local/batching/financials_ajax.php?task=getlunchdetail',
            data: {
                'category_id': category_id,
                'batching': batching,
                'lunch_type': lunch_type,
                'lunch_tea': lunch_tea_id,
                'lunch_water': lunch_water_id
            },
            success: function(response){
                response = JSON.parse(response);
                console.log(response);
                if(response.category_details) {
                    $('#id_itemtitle').val(response.name);
                    $('#id_itemcost').val(response.price);
                    $('#id_itemunit').val(response.unit);
                    $('#category_details').html(response.category_details);
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

    $('#venue_table').dataTable({
        'bSort': false,
    });

    $('#selectall').on('change', function() {
        if (this.checked) {
            $('#venue_table tbody input[type="checkbox"]').prop('checked', true);
        } else {
            $('#venue_table tbody input[type="checkbox"]').prop('checked', false);
        }
    });

});