   $(document).ready(function() {
        var data = $('.view-teacher h2').text().trim();

        var str = "";

        for(var i = 0; i < 11; i++) {
            str += data[i];
        }

      
        data = data.replace( str, '<span style="color:#000;">' + str + '</span>' );

        $('.view-teacher h2').html( data );
    });
