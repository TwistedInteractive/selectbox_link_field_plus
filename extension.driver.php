<?php
/**
 * (c) 2011
 * Author: Giel Berkers
 * Date: 24-11-2011
 * Time: 10:45
 */

require_once(EXTENSIONS.'/selectbox_link_field/extension.driver.php');

Class extension_selectbox_link_field_plus extends extension_selectbox_link_field
{

    public function about()
    {
        return array(
            'name' => 'Field: Select Box Link +',
            'version' => '1.3',
            'release-date' => '2011-11-24',
            'author' => array(
            	array(
	                'name' => 'Giel Berkers',
	                'website' => 'http://www.gielberkers.com',
	                'email' => 'info@gielberkers.com'
	            ),
            	array(
            		'name' => 'Vlad Ghita',
            		'website' => 'http://www.xandergroup.ro',
            		'email' => 'vlad.ghita@xandergroup.ro'
            	)
            )
        );
    }

    public function getSubscribedDelegates()
    {
        return array(
            array(
                'page' => '/backend/',
                'delegate' => 'AdminPagePreGenerate',
                'callback' => '__appendAssets'
            )
        );
    }

    public function __appendAssets($context)
    {
        $callback = Symphony::Engine()->getPageCallback();

        // Append styles for publish area
        if ($callback['driver'] == 'publish') {
            Administration::instance()->Page->addScriptToHead(URL.'/extensions/selectbox_link_field_plus/assets/sbl_plus.js');
            Administration::instance()->Page->addStylesheetToHead(URL.'/extensions/selectbox_link_field_plus/assets/sbl_plus.css');
        }
    }

    public function install()
    {
        try {
            Symphony::Database()->query("CREATE TABLE IF NOT EXISTS `tbl_fields_selectbox_link_plus` (
                  `id` int(11) unsigned NOT NULL auto_increment,
                  `field_id` int(11) unsigned NOT NULL,
                  `allow_multiple_selection` enum('yes','no') NOT NULL default 'no',
                  `show_association` enum('yes','no') NOT NULL default 'yes',
                  `related_field_id` VARCHAR(255) NOT NULL,
                  `limit` int(4) unsigned NOT NULL default '20',
                  `view` VARCHAR(255) NOT NULL ,
            	  `filter` VARCHAR(255) NOT NULL ,
              PRIMARY KEY  (`id`),
              KEY `field_id` (`field_id`)
            )");
        }
        catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        Symphony::Database()->query("DROP TABLE `tbl_fields_selectbox_link_plus`");
    }
    
    public function update($previous_version){
    	if( version_compare($previous_version, '1.3', '<') ){
    		try {
    			Symphony::Database()->query("ALTER TABLE `tbl_fields_selectbox_link_plus` ADD `filter` VARCHAR(255) NULL DEFAULT NULL");
    		} catch (Exception $e) {
    			return false;
    		}
    	}
    	
    	return true;
    }

}
 
