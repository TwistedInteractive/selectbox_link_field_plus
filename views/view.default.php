<?php
	/**
	 * (c) 2011
	 * Author: Giel Berkers
	 * Date: 10-10-11
	 * Time: 14:19
	 */


	require_once(EXTENSIONS.'/selectbox_link_field_plus/views/view.php');



	// The class name must be 'SBLPView_[filename - view. and .php (ucfirst)]':
	Class SBLPView_Default extends SBLPView
	{

		public function getName(){
			return __('Default View');
		}

		public function getHandle(){
			return 'default';
		}
	}
