<?php

global $CFG;
require_once($CFG->dirroot. '/theme/edumy/ccn/block_handler/ccn_block_handler.php');
class block_image_gallery extends block_base
{
    // Declare first
    public function init()
    {
        $this->title = get_string('image_gallery', 'block_image_gallery');
    }

    /**
     * Block Content
     * 
     * @return object
     */
    public function hide_header() 
    {
      return true;
    }

    // Declare second
    public function specialization()
    {
        global $CFG;
        include($CFG->dirroot . '/theme/edumy/ccn/block_handler/specialization.php');
    }
    public function get_content(){
        global $CFG, $DB;
        require_once($CFG->libdir . '/filelib.php');
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content         =  new \stdClass();
        if(!empty($this->config->title)){$this->content->title = $this->config->title;}

        $fs = get_file_storage();
        $files = $fs->get_area_files($this->context->id, 'block_image_gallery', 'content');
        $this->content->image = '';
        $imagescollection = [];

        foreach ($files as $file) {
            $filename = $file->get_filename();
              if ($filename <> '.') {
                $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), null, $file->get_filepath(), $filename);

                $this->content->image = '
                <div class="items-first">
                   <div class="items-first-border">
                      <img  src="' . $url . '" alt="' . $filename . '">
                   </div>
                </div>';

                $imagescollection[] = $this->content->image;
            }
        }

        $imagescount = 1;
        $gallery_images = '';
        $neededElements = 8;
        foreach($imagescollection as $images)
        {
            $gallery_images .= $imagescollection[$imagescount];
            if ($imagescount == $neededElements) {
              break;
            }
            $imagescount++;

        }
        $this->content->text = '
                   <section class="section-gallary">
                      <div class="container">
                        <div class="row">
                          <div class="col-md-3">
                              <h2><span> ' . get_string('image', 'block_image_gallery') . ' <br> <em>' . get_string('gallery', 'block_image_gallery') . ' </em></span></h2>
                          </div>
                          <div class="col-md-9">
                              <div class="image-outer" id="randomImageGallery">
                                '. $gallery_images .'
                              </div>
                          </div>
                        </div>
                      </div>
                   </section>
                 ';

        $this->page->requires->js('/blocks/image_gallery/js/gallery.js');
        $this->page->requires->js_init_call('get_random_images', array('img_collection' => $imagescollection, 'neededElements' => $neededElements));

        return $this->content;
    }

    /**
     * Allow multiple instances in a single course?
     *
     * @return bool True if multiple instances are allowed, false otherwise.
     */
    public function instance_allow_multiple()
    {
        return true;
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    function has_config()
    {
        return true;
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
     function applicable_formats()
     {
       $ccnBlockHandler = new ccnBlockHandler();
       return $ccnBlockHandler->ccnGetBlockApplicability(array('all'));
     }

     public function html_attributes()
     {
       global $CFG;
       $attributes = parent::html_attributes();
       include($CFG->dirroot . '/theme/edumy/ccn/block_handler/attributes.php');
       return $attributes;
     }

}
