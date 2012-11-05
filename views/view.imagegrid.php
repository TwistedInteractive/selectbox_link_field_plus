<?php
/**
 * (c) 2012
 * Author: Shaw
 * Date: 2012-10-30
 * Time: 01:45
 */

	require_once(EXTENSIONS.'/selectbox_link_field_plus/views/view.php');


	// The class name must be 'SBLPView_[filename - view. and .php (ucfirst)]':
	Class SBLPView_ImageGrid extends SBLPView
	{
		private static $assets_loaded = false;

		public function getName(){
			return __("ImageGrid (select a field of the type 'upload')");
		}

		public function getHandle(){
			return 'imagegrid';
		}

		public function generateView(XMLElement &$wrapper, $fieldname, $options, fieldSelectBox_Link_plus $field){
			parent::generateView($wrapper, $fieldname, $options, $field);

			$alert = false;
			$thumbSize = 100;

			// text options
			$editbtn_text = __('Edit Selection');
			$editbtn_text_blank = __('Select Existing');
			$editbtn_text_close = __('Done');
			$createbtn_text = __('Create New');
			$footer_text_blank = __('None');
			$footer_text_drag = __('Drag to reorder');
			$grid_columns = 6; // CSS is set up to accept 1-10 here, perhaps this could be defined in field settings
			$option_count = count($options[1]['options']); // print_r($options, TRUE);
			// set some default classes
			$attributes = $wrapper->getAttribute('class');
			$wrapper->setAttribute('class', $attributes . ' closed has' . $option_count);

			// Create the imagegrid:
			$imagegrid = new XMLElement('div', null, array('class' => 'sblp-imagegrid col' . $grid_columns));

			foreach( $options as $optGroup ){
				$container = new XMLElement('div', null, array('class' => 'container'));

				if( isset($optGroup['label']) ){

					foreach( $optGroup['options'] as $option ){
						$section = SectionManager::fetch($optGroup['id']);

						$id = $option[0];
						$value = $option[2];
						$attr = array(
							'rel' => $id,
							'class' => 'image',
							'data-section' => $section->get('handle')
						);

						// item
						preg_match('/<*a[^>]*href*=*["\']?([^"\']*)/', html_entity_decode($value), $matches);
						$href = str_replace(URL.'/workspace/', '', $matches[1]);
						if( empty($href) ){
							// If no href could be found, the field selected for the relation probably isn't of the type 'upload':
							// In this case, show a message to the user:
							$alert = true;
						}
						$img = '<img src="'.URL.'/image/2/'.$thumbSize.'/'.$thumbSize.'/5/'.$href.'" alt="thumb" width="'.$thumbSize.'" height="'.$thumbSize.'" />';

						// edit & delete
						$actions = '';
						if( $field->get('enable_edit') == 1 ){
							$actions .= '<a href="javascript:void(0)" class="edit" title="'.__('Edit this item').'">'.__('Edit').'</a>';
						}
						if( $field->get('enable_delete') == 1 ){
							$actions .= '<a href="javascript:void(0)" class="delete" title="'.__('Delete this item').'">×</a>';
						}
						$actions .= '<div class="corner"></div>';
						$actions .= '<a href="javascript:void(0)" title="'.$href.'" class="thumb">'.$img.'</a>';

						$container->appendChild(new XMLElement('div', $actions, $attr));
					}
				}
				$imagegrid->appendChild($container);
			}
			$wrapper->appendChild($imagegrid);


			$imagegridfooter = new XMLElement('div', null, array('class' => 'sblp-imagegrid-footer'));

			/* Add button */
			if( $field->get('enable_create') == 1 ){
				$related_sections = $field->findRelatedSections();

				usort($related_sections, function($a, $b){
					return strcasecmp($a->get('name'), $b->get('name'));
				});
			}
			
			if ( $option_count >= 1 ) {
				$imagegridfooter->appendChild(Widget::Anchor($editbtn_text, 'javascript:void(0)', null, 'edit button sblp-edit'));
			}
			if( $field->get('enable_create') == 1 ) {
				$imagegridfooter->appendChild(Widget::Anchor($createbtn_text, URL.'/symphony/publish/'.$related_sections[0]->get('handle').'/new/', null, 'create button sblp-add'));
			}
			/* Just to make the button text values accessible to JS*/
			$imagegridfooter->appendChild(Widget::Input('editbtn_text', $editbtn_text, 'hidden'));
			$imagegridfooter->appendChild(Widget::Input('editbtn_text_blank', $editbtn_text_blank, 'hidden'));
			$imagegridfooter->appendChild(Widget::Input('editbtn_text_close', $editbtn_text_close, 'hidden'));

			$dragtext = $field->get('allow_multiple_selection') == 'yes' ? '<em>'.$footer_text_drag.'</em>' : '';
			$imagegridfooter->appendChild(new XMLElement('p', $dragtext, array('class' => 'dragtext')));

			$wrapper->appendChild($imagegridfooter);

			// send some data to JS
			$wrapper->setAttribute('data-alert', $alert ? 'true' : 'false');
			$wrapper->setAttribute('data-multiple', $field->get('allow_multiple_selection') == 'yes' ? 'true' : 'false');

			// append assets only once
			self::appendAssets();
		}



		public static function appendAssets(){
			if( self::$assets_loaded === false
				&& class_exists('Administration')
				&& Administration::instance() instanceof Administration
				&& Administration::instance()->Page instanceof HTMLPage
			){
				self::$assets_loaded = true;

				$page = Administration::instance()->Page;

				$page->addStylesheetToHead(URL."/extensions/selectbox_link_field_plus/assets/styles/view.imagegrid.css");
				$page->addScriptToHead(URL."/extensions/selectbox_link_field_plus/assets/libraries/sblpview_imagegrid.js");
				$page->addScriptToHead(URL."/extensions/selectbox_link_field_plus/assets/libraries/view.imagegrid.js");
			}
		}
	}

