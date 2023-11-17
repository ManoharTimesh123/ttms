$(document).on("click", ".left-menu-toggle-desktop", function () {
    var slidebar = $(".ccn_dashboard_scroll_drawer").hasClass("show");
    $.ajax({
        url: M.cfg.wwwroot + '/blocks/left_menu/check_user_preference.php', // URL of the API endpoint
        type: 'get', // HTTP method (e.g. GET, POST, PUT, DELETE)
        dataType: 'json', // Expected data type of the response
        data: {"slidebar": slidebar}, // Data to be sent in the request body
        success: function(data) {

        }
    });
});

$(document).ready(function(){
  var getslidebar = 1; 
  $.ajax({
    url: M.cfg.wwwroot + '/blocks/left_menu/check_user_preference.php', // URL of the API endpoint
    type: 'get', // HTTP method (e.g. GET, POST, PUT, DELETE)
    dataType: 'json', // Expected data type of the response
    data: {"getslidebar": getslidebar}, // Data to be sent in the request body
    success: function(data) {
      if(data == true) {
        $(".ccn_dashboard_scroll_drawer").addClass('show');
        $(".dashboard_main_content").addClass('show');
        $(".dashboard_sidebars").removeClass('collapse-menu');
        $(".mobile-menu-left-icon").addClass('flaticon-back');
        
        
      } else {
        $(".ccn_dashboard_scroll_drawer").removeClass('show');
        $(".dashboard_sidebars").addClass('collapse-menu');
        $(".dashboard_main_content").removeClass('show');
        $(".mobile-menu-left-icon").removeClass('flaticon-back');

      }
    }
});
});