<?php
/**
* @author Martin Kollerup
* @package Youtube embed
* @copyright Copyright (C) www.kmweb.dk. All rights reserved.
* @license http://www.gnu.org, see LICENSE.php
*/
defined( '_JEXEC' ) or die;

jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.html.parameter' );
class plgContentYoutubeEmbed extends JPlugin
{
    public function plgContentYoutubeEmbed( &$subject, $params ){
        parent::__construct( $subject, $params );
    }

	public function onContentPrepare($context, &$article, &$params, $limitstart=0) {

        if (strstr($article->text, 'http://www.youtube.com/watch') === false && strstr( $article->text, 'http://www.youtu.be') === false) {
            return false;
        }

        //We find the youtube url in the article text
        $urls = array(
          '/http:\/\/www.youtube.com\/watch\?v=([a-zA-Z0-9_-]+)([%&=#a-zA-Z0-9_-])*/',
          '/http:\/\/www.youtube.com\/watch#\!v=([a-zA-Z0-9_-]+)([%&=#a-zA-Z0-9_-])*/',
          '/http:\/\/youtu.be\/([a-zA-Z0-9_-]+)/'
        );
        $article->text= preg_replace($urls, $this->youtubeEmbedCode('$1','$2'), $article->text);

    return true;
    }
    
    public function youtubeEmbedCode($video, $start = 0){

        $plugin         = JPluginHelper::getPlugin('content', 'youtubecodecustom');
        $params         = new JParameter( $plugin->params );
        $type           = $this->params->get('type',1);
        $responsive     = $this->params->get('responsive', 1);
        $ratio          = $this->params->get('ratio', 1.6);
        $width          = $this->params->get('width', 425);
        $height         = $this->params->get('height', 344);

        $autohide       = $this->params->get('autohide', 0);
        $autoplay       = $this->params->get('autoplay', 0);
        $fs             = $this->params->get('fs', 0);
        $fullscreen     = ($fs == 0) ? "false" : "true";
        $color          = ($this->params->get('color', 0) == 1) ? "white" : "red";
        $loop           = $this->params->get('loop', 0);
        $rel            = $this->params->get('rel', 1);
        $showinfo       = $this->params->get('showinfo', 1);
        $theme          = ($this->params->get('theme', 1) == 1) ? "dark" : "light";

        $cc_load_policy = $this->params->get('cc_load_policy', 0);
        $iv_load_policy = $this->params->get('iv_load_policy', 1);
        $modestbranding = $this->params->get('modestbranding', 0);
        $custom         = $this->params->get('custom', "");

        $embed          = true;
        $url            = 'autohide='.$autohide.'&autoplay='.$autoplay.'&fs='.$fs.'&color='.$color.'&loop='.$loop.'&rel='.$rel.'&showinfo='.$showinfo.'&theme='.$theme.'&cc_load_policy='.$cc_load_policy.'&iv_load_policy='.$iv_load_policy.'&modestbranding='.$modestbranding.$custom;

        //including responsive design - automatical adjusting the size depending on the main div
        if($responsive == 1) :
            $document = JFactory::getDocument();
            $js = ' /*youtube embed - responsive*/
                    window.addEvent(\'load\',function() {
                        var vombieYoutubeEmbed = document.id("youtubeEmbed").parentNode.getSize().x;
                        var VombieYoutubeEmbedHeight = vombieYoutubeEmbed / '.$ratio.';
                        $$(".VombieYoutubeEmbed").set("width", vombieYoutubeEmbed);
                        $$(".VombieYoutubeEmbed embed").set("width", vombieYoutubeEmbed);
                        $$(".VombieYoutubeEmbed").set("height", VombieYoutubeEmbedHeight);
                        $$(".VombieYoutubeEmbed embed").set("height", VombieYoutubeEmbedHeight);
                    });
                ';
           // $js = preg_replace('/\s+/', ' ', $js);
            $document->addScriptDeclaration($js);
        endif;

        switch($type){
            case "0":
                $embed = '
                <object width="'.$width.'" height="'.$height.'" id="youtubeEmbed">
                    <param name="movie" value="https://www.youtube.com/v/'.$video.'?version=3&'.$url.'"></param>
                    <param name="allowFullScreen" value="'.$fullscreen.'"></param>
                    <param name="allowScriptAccess" value="always"></param>
                    <embed class="VombieYoutubeEmbed" src="https://www.youtube.com/v/'.$video.'?version=3&'.$url.'" type="application/x-shockwave-flash" allowscriptaccess="always" width="'.$width.'" height="'.$height.'" allowfullscreen="'.$fullscreen.'"></embed>
                </object>
                ';
            break;

            case "1":
                $embed = '<iframe id="youtubeEmbed" class="VombieYoutubeEmbed" type="text/html" width="'.$width.'" height="'.$height.'" src="http://www.youtube.com/embed/'.$video.'?'.$url.'" frameborder="0"></iframe>';
            break;
        }

        
        return $embed;
	}

}
