<?php
require_once(TOOLKIT.'/class.administrationpage.php');

class contentExtensionSelectbox_link_field_plusIndex extends AdministrationPage
{
    public function view()
    {
        // Store the AJAX-Call to store the sorting order:
        if(isset($_POST['id']))
        {
            $id = intval($_POST['id']);
            $order = $_POST['order'];
            if($id == '') { $id = 0; }
            Symphony::Database()->query('DELETE FROM `tbl_sblp_sortorder` WHERE `entry_id` = '.$id.';');
            if($id != 0) {
                Symphony::Database()->query('DELETE FROM `tbl_sblp_sortorder` WHERE `entry_id` = 0;');
            }
            Symphony::Database()->query('INSERT INTO `tbl_sblp_sortorder` (`entry_id`, `related_field_id`)
                VALUES ('.$id.', \''.General::sanitize($order).'\');');
        }
        // Get the sorting order:
        if(isset($_GET['get']))
        {
            $id = intval($_GET['get']);
            if($id == '') { $id = 0; }
            $order = Symphony::Database()->fetchVar('related_field_id', 0, 'SELECT `related_field_id` FROM
                `tbl_sblp_sortorder` WHERE `entry_id` = '.$id);
            if($order == false)
            {
                // Sortorder not found, probably saved as a new entry, so give the sortorder '0' instead:
                $order = Symphony::Database()->fetchVar('related_field_id', 0, 'SELECT `related_field_id` FROM
                    `tbl_sblp_sortorder` WHERE `entry_id` = 0;');
            }
            echo $order;
        }
        die();
    }
}