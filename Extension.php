<?php

namespace Bolt\Extension\Breizhtorm\FlickrGallery;

use Bolt\Application;
use Bolt\BaseExtension;


class Extension extends BaseExtension
{
	const NAME = 'FlickrGallery';

    public function getName()
    {
        return Extension::NAME;
    }

    public function initialize() {
        //$this->addCss('assets/extension.css');
        //$this->addJavascript('assets/start.js', true);

	    if ($this->app['config']->getWhichEnd() == 'frontend') {
	        $this->addTwigFunction('flickrgallery', 'twigFlickrGallery');
	    }
    }

    /**
     * Render the gallery
     */
    public function twigFlickrGallery() {

        if ( $this->config['flickr_api_key'] == '' || $this->config['flickr_secret'] == '' ) {
            error_log("[Bolt/".Extension::NAME."] Flickr API Key not found");
            return;
        }

        if ( $this->config['flickr_user_id'] == '' ) {
            error_log("[Bolt/".Extension::NAME."] A Flicker user id must be set");
            return;
        }

        $images = array();

        ini_set('include_path', __DIR__ . '/lib/');
        require_once __DIR__ . '/lib/phpFlickr/phpFlickr.php';

        try{
            $f = new \phpFlickr($this->config['flickr_api_key'], $this->config['flickr_secret']);

            $response = $f->people_getPublicPhotos($this->config['flickr_user_id'],1,$this->config['flickr_image_count']);

            //error_log(print_r($response, true));

            if (!empty($response)){
                foreach($response['photos']['photo'] as $photo){
                    $image = array(
                        "url" => 'http://farm'.$photo['farm'].'.staticflickr.com/'.$photo['server'].'/'.$photo['id'].'_'.$photo['secret'].'_'.$this->config['flickr_image_size'].'.jpg',
                        "title" => $photo['title'],
                        "link" => 'http://www.flickr.com/photos/'.$this->config['flickr_user_id'].'/'.$photo['id'],
                    );
                    array_push($images, $image);
                }
            }else{
                echo $this->config['error_message']."\n";
            }

        } catch (Exception $e) {
            error_log("[Bolt/".Extension::NAME."] Error loading images from Flickr : " . $e->getMessage());
            echo $this->config['error_message']."\n";
            return;
        }

        $template = $this->config['template'];

        $this->app['twig.loader.filesystem']->addPath(__DIR__);
        $html = $this->app['render']->render($template, array(
            'images'=>$images,
        ));
        return new \Twig_Markup($html, 'UTF-8');
    }
    
    protected function getDefaultConfig()
    {
        return array(
            'flickr_api_key' => '',
            'flickr_secret' => '',
            'flickr_user_id' => '',
            'flickr_image_size' => 'q',
            'flickr_image_count' => '10',
            'template' => '',
            'error_message' => '',
        );
    }

}






