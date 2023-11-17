
function display_certificate(selected){
    var imagename = selected;
    $("#certificate_display").remove();
    if (imagename != '') {
        var imagepath = M.cfg['wwwroot']+'/mod/certificate/pix/borders/'+imagename;
        var sampleimagepath = M.cfg['wwwroot']+'/mod/certificate/pix/sample_borders/'+imagename;

        var image = '<div id="certificate_display"><div class="d-inline pull-left w-50 text-center"><img alt="'+imagename+'" class="col-10 m-2 img-thumbnail img-responsive" src="'+imagepath+'"><div>Without text</div></div><div class="d-inline pull-left w-50 text-center"><img alt="'+imagename+'" class="col-10 m-2 img-thumbnail img-responsive" src="'+sampleimagepath+'"><div>With text</div></div></div>';
        $("#fitem_id_certificatetemplate .felement").append(image);
    }
}
