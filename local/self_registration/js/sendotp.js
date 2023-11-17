$(document).ready(function(){
  startOTPTimer();
  // AJAX POST Request
    $.ajax({
        url: 'resend_otp.php', // URL of the API endpoint
        type: 'get', // HTTP method (e.g. GET, POST, PUT, DELETE)
        dataType: 'json', // Expected data type of the response
        data: {"resend": 1}, // Data to be sent in the request body
        success: function(data) {
        // Success callback function
        if(data)
        {
          showOTPSentNotification('OTP sent successfully!','alert-success');
        }
        else
        {
          showOTPSentNotification('Something is invalid!','alert-danger');

        }
        // Handle the response data here
        }
    });

  
  });

  // Function to start the OTP timer
function startOTPTimer() {
    // Set the initial time to 60 seconds (1 minute)
    var timeLeft = 60;
  
    // Disable the "Resend OTP" button initially
    $("#id_resendotp").prop("disabled", true); // Disable the resend button
  
    // Update the timer every second
    var timer = setInterval(function() {
      // Update the remaining time
      timeLeft--;
  
      // Display the remaining time on the timer element
      $('#id_resendotp').text('00 : '+timeLeft);
      $('#id_resendotp').css({    "background": "none",
        "color": "inherit",
        "border": "none",
        "padding": 0,
        "font": "inherit",
        "cursor": "pointer",
        "outline": "inherit"});
      // If the time is up, enable the "Resend OTP" button and clear the timer
      if (timeLeft <= 0) {
        $('#id_resendotp').removeAttr('style');
        $('#id_resendotp').text('Resend OTP');
        $('#id_resendotp').prop('disabled', false);
        clearInterval(timer);
      }
    }, 1000);
  }

    // Function to reset the OTP timer
    function resetOTPTimer() {
        startOTPTimer(); // Start the timer again
      }
  

  
  // Call the startOTPTimer function to start the timer


// Example usage for the "Resend OTP" button click event
$('#id_resendotp').on('click', function() {
  // Call the resetOTPTimer function to reset the timer
  startOTPTimer();
  // AJAX POST Request
    $.ajax({
        url: 'resend_otp.php', // URL of the API endpoint
        type: 'get', // HTTP method (e.g. GET, POST, PUT, DELETE)
        dataType: 'json', // Expected data type of the response
        data: {"resend": 1}, // Data to be sent in the request body
        success: function(data) {
        // Success callback function
        if(data)
        {
          showOTPSentNotification('OTP sent successfully!','alert-success');
        }
        else
        {
          showOTPSentNotification('Something is invalid!','alert-danger');

        }
        // Handle the response data here
        }
    });

});



$('#id_complete').on('click', function() {
  // AJAX POST Request
  var otp=$('#id_otp').val();
  $.ajax({
        url: 'resend_otp.php', // URL of the API endpoint
        type: 'get', // HTTP method (e.g. GET, POST, PUT, DELETE)
        dataType: 'json', // Expected data type of the response
        data: {"submit": 1, 'otp':otp}, // Data to be sent in the request body
        success: function(data) {
        // Success callback function

        if(data)
        {
          showOTPSentNotification('OTP is Invalid!','alert-danger');
        }
        else
        {
          $("form").submit();
        }
        // Handle the response data here
        }
    });
    
  });


  function showOTPSentNotification(text, className) {
    // Create a new element for the notification
    var notification = $('<div>', {
      class: 'otp-notification alert '+className,
      text: text
    });
    // Append the notification element to the body
    $('form').prepend(notification);
  
    // Fade in the notification
    notification.fadeIn();
  
    // Set a timeout to automatically fade out and remove the notification after 3 seconds
    setTimeout(function() {
      notification.fadeOut(function() {
        notification.remove();
      });
    }, 5000);
  }