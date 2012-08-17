<?php


	require_once(TOOLKIT.'/class.administrationpage.php');



	class contentExtensionSelectbox_link_field_plusIndex extends AdministrationPage
	{

		public function view(){

			// Get the sorting order:
			if( isset($_GET['get']) ){
				$id = intval($_GET['get']);
				if( $id == '' ){
					$id = 0;
				}

				$order = Symphony::Database()->fetchVar('related_field_id', 0, 'SELECT `related_field_id` FROM `tbl_sblp_sortorder` WHERE `entry_id` = '.$id);

				if( $order !== false ){
					$order = json_encode(unserialize($order));
				}
				else{
					$order = "{}";
				}

				echo $order;
			}

			die();
		}
	}
