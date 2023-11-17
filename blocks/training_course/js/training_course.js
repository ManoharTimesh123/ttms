jQuery(document).ready(function(){
    jQuery("ul.featured-tab li").on("click", function(){             
        let datatab = jQuery(this).attr('data-tab');
        jQuery("ul.featured-tab li").removeClass("active")
        jQuery(".course-box").hide();

        jQuery(this).addClass("active")          
        jQuery('#'+ datatab).show();
        //  console.log(datatab);
    });

    jQuery('.Course-component').click(function(){
       let storeoff = jQuery(this).offset().left;
    })
});