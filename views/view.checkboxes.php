<?php
	/**
	 * (c) 2011
	 * Author: Giel Berkers
	 * Date: 10-10-11
	 * Time: 14:19
	 */



	require_once(EXTENSIONS.'/selectbox_link_field_plus/views/view.php');



	// The class name must be 'SBLPView_[filename - view. and .php (ucfirst)]':
	Class SBLPView_Checkboxes extends SBLPView
	{
		private static $assets_loaded = false;



		public function getName(){
			return __("Checkboxes')");
		}

		public function getHandle(){
			return 'checkboxes';
		}

		public function generateView(XMLElement &$wrapper, $fieldname, $options, fieldSelectBox_Link_plus $field){
			parent::generateView($wrapper, $fieldname, $options, $field);

			// Show checkboxes:
			$checkboxes = new XMLElement('div', null, array('class' => 'sblp-checkboxes'));
			foreach( $options as $optGroup ){
				$container = new XMLElement('div', null, array('class' => 'container'));

				if( isset($optGroup['label']) ){
					$suffix = $field->get('allow_multiple_selection') == 'yes' ? ' <em>'.__('(drag to reorder)').'</em>' : '';
					$container->appendChild(new XMLElement('h3', $optGroup['label'].$suffix));
					$this->generateShowCreated($container);

					// Set the sectionname: this is required by the javascript-functions to edit or delete entries.
					$sectionName = General::createHandle($optGroup['label']);
					// In case of no multiple and not required:
					if( $field->get('allow_multiple_selection') == 'no' && $field->get('required') == 'no' ){
						$label = Widget::Label('<em>'.__('Select none').'</em>', Widget::Input('sblp-checked-'.$field->get('id'), '0', 'radio'));
						$container->appendChild($label);
					}

					foreach( $optGroup['options'] as $option ){
						$section = SectionManager::fetch($optGroup['id']);

						$id = $option[0];
						$value = strip_tags(html_entity_decode($option[2]));

						// item
						$label = Widget::Label();
						if( $field->get('allow_multiple_selection') == 'yes' ){
							$input = Widget::Input('sblp-checked-'.$field->get('id').'[]', (string) $id, 'checkbox');
						}
						else{
							$input = Widget::Input('sblp-checked-'.$field->get('id'), (string) $id, 'radio');
						}
						$label->setValue(__('%s <span class="text">%s</span>', array($input->generate(), $value)));
						$label->setAttributeArray(array(
							'title' => $value,
							'rel' => $id,
							'data-section' => $section->get('handle')
						));

						// edit & delete
						$actions = '';
						if( $field->get('enable_edit') == 1 ){
							$actions .= '<a href="javascript:void(0)" class="edit">Edit</a>';
						}
						if( $field->get('enable_delete') == 1 ){
							if( $actions !== '' ) $actions .= '|';
							$actions .= '<a href="javascript:void(0)" class="delete">Delete</a>';
						}

						if( $actions !== '' ){
							$label->appendChild(new XMLElement('span', $actions, array('class' => 'sblp-checkboxes-actions')));
						}

						$container->appendChild($label);

					}
				}
				$checkboxes->appendChild($container);
			}

			$wrapper->appendChild($checkboxes);

			// send some data to JS
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

				$page->addStylesheetToHead(URL."/extensions/selectbox_link_field_plus/assets/styles/view.checkboxes.css");
				$page->addScriptToHead(URL."/extensions/selectbox_link_field_plus/assets/libraries/sblpview_checkboxes.js");
				$page->addScriptToHead(URL."/extensions/selectbox_link_field_plus/assets/libraries/view.checkboxes.js");
			}
		}
	}
