//Saving user query using ajax call
$(document).on('submit','#userForm',function(e){

    e.preventDefault();

    $.ajax ({   
        method:"POST",
        url: M.cfg.wwwroot + '/blocks/user_query/user_query.php',
        data: $(this).serialize(),
        success: function(data){
            $('#userForm').trigger("reset");
            $('#userformmsg').append("<div class='alert alert-success'><a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a><i class='fa fa-check'></i> Thank you for your query!</div>");
        }
    });
});