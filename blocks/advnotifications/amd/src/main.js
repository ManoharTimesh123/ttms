$(document).ready(function() {
    $('.districts').on('change', function () {
        require(['core/ajax'], function(ajax) {
            const ids = [];
            $("#add_notification_districts :selected").each(function() {
                ids.push(this.value);
            });
            $('.diet-block').hide();
            $('.school-block').hide();
            $('#add_notification_diet').html("");
            $('#add_notification_school').html("");
            let district_ids = ids.join(',');
            var promises = ajax.call([
                {
                    methodname: 'block_advnotifications_get_zone',
                    args: {'district_ids': district_ids}
                }
            ]);
            promises[0].done(function(response) {
                if (response.data) {
                    $('.zone-block').show();
                    $('#add_notification_zones').html(response.data);
                } else {
                    alert('No matched record found with current selection.');
                    $('.zone-block').hide();
                }
            }).fail(function(ex){
                console.log(ex);
            });
        });
    });
    $(document).on("change", ".zones", function () {
        require(['core/ajax'], function(ajax) {
            const ids = [];
            $("#add_notification_zones :selected").each(function() {
                ids.push(this.value);
            });
            $('.school-block').hide();
            $('#add_notification_school').html("");
            let zone_ids = ids.join(',');
            var promises = ajax.call([
                {
                    methodname: 'block_advnotifications_get_diet',
                    args: {'zone_ids': zone_ids}
                }
            ]);
            promises[0].done(function(response) {
                if (response.data) {
                    $('.diet-block').show();
                    $('#add_notification_diet').html(response.data);
                } else {
                    alert('No matched record found with current selection.');
                    $('.diet-block').hide();
                }
            }).fail(function(ex){
                console.log(ex);
            });
        });
    });

    $(document).on("change", ".diets", function () {
        require(['core/ajax'], function(ajax) {
            const ids = [];
            $("#add_notification_diet :selected").each(function() {
                ids.push(this.value);
            });
            let diet_ids = ids.join(',');
            var promises = ajax.call([
                {
                    methodname: 'block_advnotifications_get_school',
                    args: {'diet_ids': diet_ids}
                }
            ]);
            promises[0].done(function(response) {
                if (response.data) {
                    $('.school-block').show();
                    $('#add_notification_school').html(response.data);
                } else {
                    alert('No matched record found with current selection.');
                    $('.school-block').hide();
                }
            }).fail(function(ex){
                console.log(ex);
            });
        });
    });

    $( "#add_notification_global" ).click(function() {
        if(this.checked){
            $('#organiation-hirarchy').hide();
            $(this).val(1);
        }
        if(!this.checked){
            $('#organiation-hirarchy').show();
            $('.zone-block').css('display', 'none');
            $('.diet-block').css('display', 'none');
            $('.school-block').css('display', 'none');
            $(this).val(0);
        }
    });
});