<?php if(!defined('IS_CMS')) die();

/***************************************************************
*
* Plugin fuer moziloCMS, welches RSS Feeds laden und anzeigen kann 
* by blacknight - Daniel Neef
* 
***************************************************************/
class RSSReader extends Plugin {
	
	const RSSREADER_DIR_NAME               = 'RSSReader/';
	const SETTING_CACHE_DURATION           = 'cache_duration';
	const SETTING_ORDER_BY_DATE            = 'order_by_date';
	const SETTING_SHOW_MAX_ITEMS           = 'show_max_items';
	const SETTING_SHOW_RSS_TITLE           = 'show_title';
	const SETTING_SHOW_RSS_ITEM_TITLE      = 'show_item_title';
	const SETTING_SHOW_RSS_ITEM_TITLE_LINK = 'show_item_title_link';	
	const SETTING_SHOW_RSS_ITEM_DATE       = 'show_item_date';
	const SETTING_SHOW_RSS_ITEM_DES        = 'show_item_des';

    /***************************************************************
    * 
    * Gibt den HTML-Code zurueck, mit dem die Plugin-Variable ersetzt 
    * wird.
    * 
    ***************************************************************/
    function getContent($value) {
    	$dir = PLUGIN_DIR_REL.self::RSSREADER_DIR_NAME;
    	require_once($dir.'simplepie/autoloader.php');
    	
    	global $syntax;
    	$syntax->insert_in_head($this->getHead());
    	    	
    	$result = '';
    	$feed = new SimplePie();
    	if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())    
    		$value = stripslashes($value);    
    	$feed->set_feed_url($value);
    	$feed->set_cache_location($dir.'cache/');
    	if ($this->settings->get(self::SETTING_CACHE_DURATION) <> 3600) {
    		$feed->set_cache_duration($this->settings->get(self::SETTING_CACHE_DURATION));
    	}
    	$feed->enable_order_by_date($this->getBoolean($this->settings->get(self::SETTING_ORDER_BY_DATE)));
    	$feed->init();
    	
    	if ($feed->error()) {
    		$result .= '<div class="RSSReaderError">'.htmlspecialchars($feed->error()).'</div>';
    	}else{
    		if ($this->getBoolean($this->settings->get(self::SETTING_SHOW_RSS_TITLE))) {
		    	$result .= '<div class="RSSReaderTitle">';
		    	$result .= '<h1><a href="'.$feed->get_permalink().'">'.$feed->get_title().'</a></h1>';
		    	$result .= '<div class="RSSReaderTitleDescription">'.$feed->get_description().'</div>';
		    	$result .= '</div>';    	
    		}
	    	$iCount = 0;
	    	foreach ($feed->get_items() as $item) {
	    		if ($this->settings->get(self::SETTING_SHOW_MAX_ITEMS) !== '' and $this->settings->get(self::SETTING_SHOW_MAX_ITEMS) <= $iCount) {
	    			break;
	    		}
	    		$result .= '<div class="RSSReaderItem">';
	    		if ($this->getBoolean($this->settings->get(self::SETTING_SHOW_RSS_ITEM_TITLE))) {
		    		if ($this->getBoolean($this->settings->get(self::SETTING_SHOW_RSS_ITEM_TITLE_LINK)))
		    			$result .= '<div class="RSSReaderItemTitle"><a href="'.$item->get_permalink().'">'.$item->get_title().'</a></div>';
		    		else
		    			$result .= '<div class="RSSReaderItemTitle">'.$item->get_title().'</div>';
	    		}	    			
	    		if ($this->getBoolean($this->settings->get(self::SETTING_SHOW_RSS_ITEM_DATE))) 
	    			$result .= '<div class="RSSReaderItemDate">'.$item->get_date('d.m.Y G:i:s').'</div>';
	    		if ($this->getBoolean($this->settings->get(self::SETTING_SHOW_RSS_ITEM_DES)))
	    			$result .= '<div class="RSSReaderItemDescription">'.$item->get_description().'</div>';    		
	    		$result .= '</div>';
	    		$iCount = $iCount + 1;
	    	}
    	}

        return $result;
    } // function getContent
    
    
    
    /***************************************************************
    * 
    * Gibt die Konfigurationsoptionen als Array zurueck.
    * 
    ***************************************************************/
    function getConfig() {
        global $ADMIN_CONF;        
        $dir = PLUGIN_DIR_REL.self::RSSREADER_DIR_NAME;
        $language = $ADMIN_CONF->get("language");
        $lang_admin = new Properties($dir."sprachen/admin_language_".$language.".txt",false);

        $config = array();
        $config[self::SETTING_CACHE_DURATION] = array(
        		"type" => "text",
        		"description" => $lang_admin->get("config_RSSReader_CacheDuration"),
        		"maxlength" => "4",
        		"regex" => "/^[1-9][0-9]?/",
        		"regex_error" => $lang_admin->get("config_RSSReader_number_regex_error")
        );  
        $config[self::SETTING_ORDER_BY_DATE] = array(
        		"type" => "checkbox",
        		"description" => $lang_admin->get("config_RSSReader_OrderByDate")
        );
        $config[self::SETTING_SHOW_MAX_ITEMS] = array(
        		"type" => "text",
        		"description" => $lang_admin->get("config_RSSReader_ShowMaxItems"),
        		"maxlength" => "2",
        		"regex" => "/^[1-9][0-9]?/",
        		"regex_error" => $lang_admin->get("config_RSSReader_number_regex_error")
        );
        $config[self::SETTING_SHOW_RSS_TITLE] = array(
        		"type" => "checkbox",
        		"description" => $lang_admin->get("config_RSSReader_RSSTitle")
        );
        $config[self::SETTING_SHOW_RSS_ITEM_TITLE] = array(
        		"type" => "checkbox",
        		"description" => $lang_admin->get("config_RSSReader_RSSItemTitle")
        );        
        $config[self::SETTING_SHOW_RSS_ITEM_TITLE_LINK] = array(
        		"type" => "checkbox",
        		"description" => $lang_admin->get("config_RSSReader_RSSItemTitleLink")
        );        
        $config[self::SETTING_SHOW_RSS_ITEM_DATE] = array(
        		"type" => "checkbox",
        		"description" => $lang_admin->get("config_RSSReader_RSSItemDate")
        );
        $config[self::SETTING_SHOW_RSS_ITEM_DES] = array(
        		"type" => "checkbox",
        		"description" => $lang_admin->get("config_RSSReader_RSSItemDes")
        );        
        return $config;            
    } // function getConfig
    
    
    
    /***************************************************************
    * 
    * Gibt die Plugin-Infos als Array zurueck. 
    * 
    ***************************************************************/
    function getInfo() {
        global $ADMIN_CONF;        
        $dir = PLUGIN_DIR_REL.self::RSSREADER_DIR_NAME;
        $language = $ADMIN_CONF->get("language");
        $lang_admin = new Properties($dir."sprachen/admin_language_".$language.".txt",false);        
        $info = array(
            // Plugin-Name
            "<b>".$lang_admin->get("config_RSSReader_plugin_name")."</b> \$Revision: 1 $",
            // CMS-Version
            "2.0",
            // Kurzbeschreibung
            $lang_admin->get("config_RSSReader_plugin_desc"),
            // Name des Autors
           "black-night",
            // Download-URL
            array("http://software.black-night.org","Software by black-night"),
            # Platzhalter => Kurzbeschreibung
            array('{RSSReader|}' => $lang_admin->get("config_RSSReader_show")            	              		
                 )
            );
            return $info;        
    } // function getInfo
    
    /***************************************************************
    *
    * Interne Funktionen
    *
    ***************************************************************/

    private function getHead() {
    	$head = '<style type="text/css"> @import "'.URL_BASE.PLUGIN_DIR_NAME.'/RSSReader/plugin.css"; </style>';
		return $head;
    } //function getHead 

    private function getBoolean($value) {
    	return (strtoupper($value)=="TRUE");
    } //function getBoolean
    
} // class RSSReader

?>