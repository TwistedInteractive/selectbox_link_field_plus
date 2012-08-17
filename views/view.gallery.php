<?php
	/**
	 * (c) 2011
	 * Author: Giel Berkers
	 * Date: 10-10-11
	 * Time: 14:19
	 */



	require_once(EXTENSIONS.'/selectbox_link_field_plus/views/view.php');



	// The class name must be 'SBLPView_[filename - view. and .php (ucfirst)]':
	Class SBLPView_Gallery extends SBLPView
	{
		private static $assets_loaded = false;



		public function getName(){
			return __("Gallery (select a field of the type 'upload')");
		}

		public function getHandle(){
			return 'gallery';
		}

		public function generateView(XMLElement &$wrapper, $fieldname, $options, fieldSelectBox_Link_plus $field){
			parent::generateView($wrapper, $fieldname, $options, $field);

			$alert = false;
			$thumbSize = 60;

			// Create the gallery:
			$gallery = new XMLElement('div', null, array('class' => 'sblp-gallery'));
			$this->generateShowCreated($gallery);
			foreach( $options as $optGroup ){
				$container = new XMLElement('div', null, array('class' => 'container'));

				if( isset($optGroup['label']) ){
					$suffix = $field->get('allow_multiple_selection') == 'yes' ? ' <em>'.__('(drag to reorder)').'</em>' : '';
					$container->appendChild(new XMLElement('h3', $optGroup['label'].$suffix));

					foreach( $optGroup['options'] as $option ){
						$section = SectionManager::fetch($optGroup['id']);

						$id = $option[0];
						$value = $option[2];
						$attr = array(
							'rel' => $id,
							'class' => 'image',
							'style' => "width:{$thumbSize}px; height:{$thumbSize}px;",
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
							$actions .= '<a href="javascript:void(0)" class="edit" title="'.__('Edit this item').'">E</a>';
						}
						if( $field->get('enable_delete') == 1 ){
							$actions .= '<a href="javascript:void(0)" class="delete" title="'.__('Delete this item').'" style="left:'.($thumbSize-20).'px;">×</a>';
						}
						$actions .= '<a href="javascript:void(0)" title="'.$href.'" class="thumb">'.$img.'</a>';

						$container->appendChild(new XMLElement('div', $actions, $attr));
					}
				}
				$gallery->appendChild($container);
			}
			$wrapper->appendChild($gallery);

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

				$page->addStylesheetToHead(URL."/extensions/selectbox_link_field_plus/assets/styles/view.gallery.css");
				$page->addScriptToHead(URL."/extensions/selectbox_link_field_plus/assets/libraries/sblpview_gallery.js");
				$page->addScriptToHead(URL."/extensions/selectbox_link_field_plus/assets/libraries/view.gallery.js");
			}
		}
	}
