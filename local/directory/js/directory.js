$(document).ready(function() {
  $("#search-directory").on("click", function() {
    // Get the search input value       
    var searchTerm = $('#directory-search-field').val().toLowerCase();
    // Filter the table rows based on the search input value
        var url = window.location.href; 
        var params = {search: searchTerm};

        // Check if URL already contains parameters
        if (url.indexOf('?') !== -1) {
          // URL already has parameters, append new parameter using '&'
          url += '&' + $.param(params);
        } else {
          // URL does not have parameters, append new parameter using '?'
          url += '?' + $.param(params);
        }
        location.replace(url);
 
    
  });

  $(".relative").click(function(e) {
        $(this).children().show(e);
        e.stopPropagation();
  });
    $("body").click(function() {
        $(".absolute").hide(e);
        e.stopPropagation();
  })
  
});