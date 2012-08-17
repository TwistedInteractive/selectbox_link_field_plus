<?php
	/**
	 * (c) 2011
	 * Author: Giel Berkers
	 * Date: 10-10-11
	 * Time: 14:19
	 */

	// The class name must be 'SBLPView_[filename - view. and .php (ucfirst)]':
	abstract class SBLPView
	{

		/**
		 * Return the name of this view
		 *
		 * @return string
		 */
		abstract public function getName();

		/**
		 * Return the handle of this view
		 *
		 * @return string
		 */
		abstract public function getHandle();

		/**
		 * Generates the create new functionality.
		 *
		 * @param XMLElement               $wrapper
		 *	 The XMLElement wrapper in which the view is placed
		 * @param fieldSelectBox_Link_plus $field
		 *	 The field instance
		 */
		public function generateCreate(XMLElement &$wrapper, fieldSelectBox_Link_plus $field){
			if( $field->get('enable_create') == 1 ){
				$related_sections = $field->findRelatedSections();

				usort($related_sections, function($a, $b){
					return strcasecmp($a->get('name'), $b->get('name'));
				});

				$create_options = array();

				$buttons = new XMLElement('span', __('Create new entry in'), array('class' => 'sblp-buttons'));
				foreach( $related_sections as $idx => $section ){
					/** @var $section Section */
					$create_options[] = array(URL.'/symphony/publish/'.$section->get('handle').'/new/', $idx == 0, $section->get("name"));
				}

				$buttons->appendChild(Widget::Select('sblp_section_selector_'.$field->get('id'), $create_options, array('class' => 'sblp-section-selector')));
				$buttons->appendChild(Widget::Anchor(__("Go"), URL.'/symphony/publish/'.$related_sections[0]->get('handle').'/new/', null, 'create button sblp-add'));

				$wrapper->appendChild($buttons);
			}
		}

		/**
		 * Appends the show_created input.
		 *
		 * @param XMLElement $wrapper
		 */
		public function generateShowCreated(XMLElement &$wrapper){
			$div = new XMLElement('span', null, array('class' => 'hide-others'));
			$input = Widget::Input('show_created', null, 'checkbox');
			$div->setValue(__('%s hide others', array($input->generate())));
			$wrapper->appendChild($div);
		}

		/**
		 * Generates the view on the publish page
		 *
		 * @param XMLElement               $wrapper
		 *	 The XMLElement wrapper in which the view is placed
		 * @param string                   $fieldname
		 *	 The name of the field
		 * @param array                    $options
		 *	 The options
		 * @param fieldSelectBox_Link_plus $field
		 *	 The field instance
		 *
		 * @return void
		 */
		public function generateView(XMLElement &$wrapper, $fieldname, $options, fieldSelectBox_Link_plus $field){
			$attributes['class'] = 'target';
			if( $field->get('allow_multiple_selection') )
				$attributes['multiple'] = 'multiple';

			$wrapper->appendChild(
				Widget::Select($fieldname, $options, $attributes)
			);
		}
	}
