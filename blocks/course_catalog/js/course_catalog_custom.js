$(document).ready(function(){
    $(".course_catalog_category:first").addClass('active');
    $(document).on("click", ".course_catalog_category", function () {
        var categoryid = $(this).attr('id');
        $('.course_catalog_category').removeClass("active");
        $(this).addClass('active');
        require(['core/ajax'], function(ajax) {
            categoryid = categoryid;
            var promises = ajax.call([
                {
                    methodname: 'block_course_catalog_get_course_by_category',
                    args: { 'categoryid': categoryid }
                }
            ]);
            promises[0].done(function(response) {

                var coursehtml = '';
                if (response.processed === true) {
                    var data = JSON.parse(response.record);
                    for (var i = 0; i < data.length; i++) {
                        coursehtml += '<div class="col-md-4 col mb-4">';
                        coursehtml += '<a class="top_courses box-shadow p-0 border-0 h-100 top_courses_wrapper bg-white d-block" href="'+ data[i].course_url +'">';
                        coursehtml += '<div class="top_courses_img w-100"><img class="img-fluid w-100 h-100" src="' + data[i].course_image + '" /></div>';
                        coursehtml += '<div class="top_courses_content p-3"><h6>6 weeks</h6><p class="mb-1">' + data[i].category_name + '</p>';
                        coursehtml += '<h4 class="font-weight-bold">' + data[i].course_name + '</h4><div class="course-rating d-flex justify-content-between"><div>' + data[i].course_rating + '</div><div>493 <i class="fa fa-user" aria-hidden="true"></i></div></div></div>';
                        coursehtml += '<div class="course-join-btn font-weight-bold d-flex justify-content-center align-items-center w-100"><span class="px-3 py-2">Join course<i class="flaticon-right-arrow"></i></span></div>';
                        coursehtml += '</a>';
                        coursehtml += '</div>';
                    }
                } else {
                    coursehtml += '<div class="nodata_found d-flex justify-content-center align-items-center w-100"><h2>No record found.</h2></div>';
                }
                $('#course_catalog').html(coursehtml);

            }).fail(function(ex){
                console.log(ex);
            });
        });
    });
});