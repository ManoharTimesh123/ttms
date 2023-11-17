$(document).ready(function(){
    $(document).on("click", ".update-venue-request", function () {
        if (confirm('Are you sure?')) {
           var venuerequestid = $(this).attr('id');
            require(['core/ajax'], function(ajax) {
                var promises = ajax.call([
                    {
                        methodname: 'block_venue_requests_update_venue_request',
                        args: { 'venuerequestid': venuerequestid}
                    }
                ]);
                promises[0].done(function(response) {
                    if (response.processed === true) {
                        location.reload();
                    }
                }).fail(function(ex){
                    console.log(ex);
                });
            });
        }
    });
});