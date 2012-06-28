<?php
/**
 * (c) 2011
 * Author: Giel Berkers
 * Date: 10-10-11
 * Time: 10:45
 */

require_once(EXTENSIONS.'/selectbox_link_field/extension.driver.php');

Class extension_selectbox_link_field_plus extends extension_selectbox_link_field
{

    /**
     * Get the subscribed delegates
     * @return array
     */
    public function getSubscribedDelegates()
    {
        return array(
            array(
                'page' => '/backend/',
                'delegate' => 'AdminPagePreGenerate',
                'callback' => '__appendAssets'
            ),
            array(
                'page' => '/publish/',
                'delegate' => 'Delete',
                'callback' => '__entryDelete'
            ),
	        array(
		        'page' => '/publish/new/',
		        'delegate' => 'EntryPostCreate',
		        'callback' => '__linkCreatedEntry'
	        )
        );
    }

    /**
     * Add some JavaScript and CSS to the header
     * @param $context
     * @return void
     */
    public function __appendAssets($context)
    {
        $callback = Symphony::Engine()->getPageCallback();

        // Append styles for publish area
        if ($callback['driver'] == 'publish') {
            Administration::instance()->Page->addScriptToHead(URL.'/extensions/selectbox_link_field_plus/assets/jquery-ui-1.8.16.custom.min.js');
            Administration::instance()->Page->addScriptToHead(URL.'/extensions/selectbox_link_field_plus/assets/sbl_plus.js');
            Administration::instance()->Page->addStylesheetToHead(URL.'/extensions/selectbox_link_field_plus/assets/sbl_plus.css');
        }
    }

    /**
     * When an entry is deleted, also delete it's sorting order data from the database:
     * @param $context
     * @return void
     */
    public function __entryDelete($context)
    {
        foreach($context['entry_id'] as $id)
        {
            // Delete sorting order information:
	        Symphony::Database()->delete('tbl_sblp_sortorder', '`entry_id` = '.$id);
	        // Delete references to created entries:
	        Symphony::Database()->delete('tbl_sblp_created', '`created_id` = '.$id);
	        Symphony::Database()->delete('tbl_sblp_created', '`entry_id` = '.$id);
        }
    }

	/**
	 * When an entry is created, check if there is a parent set and store it if so:
	 * @param $context
	 */
	public function __linkCreatedEntry($context)
	{
		$entry = $context['entry'];
		/* @var $entry Entry */
		if(isset($_POST['sblp_parent']))
		{
			Symphony::Database()->insert(
				array('entry_id' => intval($_POST['sblp_parent']), 'created_id' => $entry->get('id')), 'tbl_sblp_created'
			);
		}
	}

    /**
     * Install the extension
     * @return bool
     */
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
                  `view` VARCHAR(255) NOT NULL default '',
                  `show_created` int(1) NOT NULL default '0',
              PRIMARY KEY  (`id`),
              KEY `field_id` (`field_id`)
            )");

            Symphony::Database()->query("CREATE TABLE IF NOT EXISTS `tbl_sblp_sortorder` (
                  `id` int(11) unsigned NOT NULL auto_increment,
                  `entry_id` int(11) unsigned NOT NULL,
                  `related_field_id` TEXT NOT NULL,
              PRIMARY KEY  (`id`),
              KEY `entry_id` (`entry_id`)
            )");

			Symphony::Database()->query("CREATE TABLE IF NOT EXISTS `tbl_sblp_created` (
				`id` INT NOT NULL AUTO_INCREMENT,
				`entry_id` INT NOT NULL ,
				`created_id` INT NOT NULL,
			  PRIMARY KEY  (`id`),
              KEY `entry_id` (`entry_id`),
              KEY `created_id` (`created_id`)
			) ENGINE = MYISAM;");
        }
        catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Uninstall the extension
     * @return void
     */
    public function uninstall()
    {
        Symphony::Database()->query("DROP TABLE `tbl_fields_selectbox_link_plus`");
        Symphony::Database()->query("DROP TABLE `tbl_sblp_sortorder`");
		Symphony::Database()->query("DROP TABLE `tbl_sblp_created`");
    }

    /**
     * Update the extension
     * @param $previousVersion
     * @return void
     */
    public function update($previousVersion)
    {
        if(version_compare($previousVersion, '1.3', '<')){
			// Create the tables:
            Symphony::Database()->query("CREATE TABLE IF NOT EXISTS `tbl_sblp_sortorder` (
                  `id` int(11) unsigned NOT NULL auto_increment,
                  `entry_id` int(11) unsigned NOT NULL,
                  `related_field_id` TEXT NOT NULL,
              PRIMARY KEY  (`id`),
              KEY `entry_id` (`entry_id`)
            )");

			Symphony::Database()->query("CREATE TABLE IF NOT EXISTS `tbl_sblp_created` (
				`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`entry_id` INT NOT NULL ,
				`created_id` INT NOT NULL,
			  PRIMARY KEY  (`id`),
              KEY `entry_id` (`entry_id`),
              KEY `entry_id` (`created_id`)
			) ENGINE = MYISAM;");

			Symphony::Database()->query("ALTER TABLE  `tbl_fields_selectbox_link_plus` ADD `show_created` INT(1) NOT NULL");

			// By default, save all the current relations as being created by their parent entry (which is often the case):
			$field_ids = Symphony::Database()->fetchCol('field_id', 'SELECT `field_id` FROM `tbl_fields_selectbox_link_plus`;');
			foreach($field_ids as $field_id)
			{
				$results = Symphony::Database()->fetch(
					sprintf('SELECT * FROM `tbl_entries_data_%d`;', $field_id)
				);
				foreach($results as $result)
				{
					if(!is_null($result['relation_id']))
					{
						Symphony::Database()->insert(array(
							'entry_id' => $result['entry_id'],
							'created_id' => $result['relation_id']
						), 'tbl_sblp_created');
					}
				}
			}
        }

	    if(version_compare($previousVersion, '1.5', '<')){
		    Symphony::Database()->query("ALTER TABLE  `tbl_fields_selectbox_link_plus`
		        ADD `enable_create` INT(1) NOT NULL DEFAULT 1,
		        ADD `enable_edit` INT(1) NOT NULL DEFAULT 1,
		        ADD `enable_delete` INT(1) NOT NULL DEFAULT 1");
	    }
    }
}
 
