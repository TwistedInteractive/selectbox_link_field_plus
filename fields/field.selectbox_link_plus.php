<?php
/**
 * (c) 2011
 * Author: Giel Berkers
 * Date: 10-10-11
 * Time: 10:46
 */


if(!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');

require_once(EXTENSIONS.'/selectbox_link_field/fields/field.selectbox_link.php');

Class fieldSelectBox_Link_plus extends fieldSelectBox_Link {

    private static $em = null;

    /**
     * Constructor
     * @param $parent
     */
    public function __construct(){
        parent::__construct();

        $this->_name = __('Select Box Link +');
        $this->_required = true;
        $this->_showassociation = true;
    }

	public function findDefaults(array &$settings){
		$settings['required'] = 'yes';
		$settings['allow_multiple_selection'] = 'no';
		$settings['show_column'] = 'no';
		$settings['show_association'] = 'yes';
		$settings['limit'] = 20;
		$settings['enable_create'] = 1;
		$settings['enable_edit'] = 1;
		$settings['enable_delete'] = 1;
		$settings['related_field_id'] = array();
	}

    /**
     * Display the publish panel
     * @param XMLElement $wrapper
     * @param null $data
     * @param null $error
     * @param null $prefix
     * @param null $postfix
     * @param null $entry_id
     * @return void
     */
    public function displayPublishPanel(XMLElement &$wrapper, $data = null, $error = null, $prefix = null, $postfix = null, $entry_id = null) {
        $entry_ids = array();
        $options = array();

        if(!is_null($data['relation_id'])){
            if(!is_array($data['relation_id'])){
                $entry_ids = array($data['relation_id']);
            }
            else{
                $entry_ids = array_values($data['relation_id']);
            }
        }

        if($this->get('required') != 'yes') $options[] = array(null, false, null);

        $fieldname = 'fields'.$prefix.'['.$this->get('element_name').']'.$postfix;
        if($this->get('allow_multiple_selection') == 'yes') $fieldname .= '[]';
        $label = Widget::Label($this->get('label'));

        // Generate the buttons the create entries in the related sections:
        $viewWrapper = new XMLElement('div', null, array('id'=>'sblp-view-'.$this->get('id')));

        // edge case when new entry, required field but can't select entries
        if( $this->get('show_created') === '1'
            && $this->get('required') === 'yes'
            && is_null($entry_id)
        ){
            $viewWrapper->appendChild(new XMLElement('p', __('This field will be enabled after you create the entry.'), array('class' => 'help')));
        }
        else{
	        // Create button
	        if( $this->get('enable_create') == 1 ){
		        $related_fields = $this->get('related_field_id');
		        $related_sections = array();
		        foreach($related_fields as $id)
		        {
			        // get the section:
			        $related_sections[] = Symphony::Database()->fetchRow(0, 'SELECT A.`name`, A.`id`, A.`handle` FROM
                `tbl_sections` A, `tbl_fields` B  WHERE A.`id` = B.`parent_section` AND B.`id` = '.$id.';');
		        }

		        $buttons = new XMLElement('span', null, array('class'=>'sblp-buttons'));
		        foreach($related_sections as $section)
		        {
			        $buttons->appendChild(Widget::Anchor(sprintf(__('Create new entry in "%s"'), $section['name']),
				        URL.'/symphony/publish/'.$section['handle'].'/new/', null,
				        'create button sblp-add'));
		        }
		        $label->appendChild($buttons);
	        }

	        // Find entries
	        $states = $this->findOptions($entry_ids);

	        if(!empty($states)){
		        foreach($states as $s){
			        $group = array('label' => $s['name'], 'options' => array());
			        foreach($s['values'] as $id => $v){
				        if($this->get('show_created') == 1)
				        {
					        // Check if this entry is created by it's parent:
					        if(Symphony::Database()->fetchVar('count', 0, sprintf('SELECT COUNT(*) AS `count` FROM
                            `tbl_sblp_created` WHERE `entry_id` = %d AND `created_id` = %d;', $entry_id, $id)) == 0)
					        {
						        // skip this one:
						        continue;
					        }
				        }
				        $group['options'][] = array($id, in_array($id, $entry_ids), General::sanitize($v));
			        }
			        $options[] = $group;
		        }
	        }

            // Load the correct View:
            require_once(EXTENSIONS.'/selectbox_link_field_plus/views/view.'.$this->get('view').'.php');
            $className = 'SBLPView_'.ucfirst($this->get('view'));
            $view = new $className;

            $view->generateView($viewWrapper, $fieldname, $options, $this);
        }

        $label->appendChild($viewWrapper);

        if(!is_null($error)) {
            $wrapper->appendChild(Widget::Error($label, $error));
        }
        else{
	        $wrapper->appendChild($label);
        }
    }

    public function checkPostFieldData($data, &$message, $entry_id = null){
        $result = parent::checkPostFieldData($data, $message, $entry_id);

        // if new entry, required field && show_created, bypass required option
        if( $this->get('show_created') === '1'
            && $result === self::__MISSING_FIELDS__
            && is_null($entry_id)
        ){
            $message = '';
            return self::__OK__;
        }

        return self::__OK__;
    }

	/**
	 * Get an array with ID's of entries created by the parent entry
	 *
	 * @return array
	 */
	public function getCreatedEntryIds()
	{
		$ids = array();
		$context = Administration::instance()->getPageCallback();
		if(isset($context['context']['entry_id']))
		{
			$ids = Symphony::Database()->fetchCol('id',
				sprintf('SELECT `created_id` AS `id` FROM `tbl_sblp_created` WHERE `entry_id` = %d;', $context['context']['entry_id']));
		}
		return $ids;
	}

    /**
     * Display the settings panel
     * @param $wrapper
     * @param null $errors
     * @return void
     */
	public function displaySettingsPanel(&$wrapper, $errors=NULL){
		// Just load the regular settings panel:
		parent::displaySettingsPanel($wrapper, $errors);

		// Add the view-picker:
		$options = array();
		$files = glob(EXTENSIONS.'/selectbox_link_field_plus/views/*.php');
		foreach($files as $file)
		{
			$handle    = str_replace(array('view.', '.php'), '', basename($file));
			$className = 'SBLPView_'.ucfirst($handle);
			require_once($file);
			$view = new $className;
			$options[] = array($handle, $this->get('view') == $handle, $view->getName());
		}
		$fieldset = new XMLElement('div', null, array('class'=>'two columns'));

		$label = Widget::Label(null, null, 'column');
		$input = Widget::Input('fields['.$this->get('sortorder').'][show_created]', 'yes', 'checkbox');
		if ($this->get('show_created') == 1) $input->setAttribute('checked', 'checked');
		$label->setValue(__('<br />%s Only show entries created by the parent entry', array($input->generate())));
		$fieldset->appendChild($label);

		$label = Widget::Label(__('View'), null, 'column');
		$label->appendChild(Widget::Select('fields['.$this->get('sortorder').'][view]', $options));
		$fieldset->appendChild($label);

		$wrapper->insertChildAt(4, $fieldset);

		$fieldset = new XMLElement('div', null, array('class' => 'three columns'));

		$label = Widget::Label(null, null, 'column');
		$input = Widget::Input('fields['.$this->get('sortorder').'][enable_create]', 'yes', 'checkbox');
		if ($this->get('enable_create') == 1) $input->setAttribute('checked', 'checked');
		$label->setValue(__('<br />%s Enable Create button', array($input->generate())));
		$fieldset->appendChild($label);

		$label = Widget::Label(null, null, 'column');
		$input = Widget::Input('fields['.$this->get('sortorder').'][enable_edit]', 'yes', 'checkbox');
		if ($this->get('enable_edit') == 1) $input->setAttribute('checked', 'checked');
		$label->setValue(__('<br />%s Enable Edit button', array($input->generate())));
		$fieldset->appendChild($label);

		$label = Widget::Label(null, null, 'column');
		$input = Widget::Input('fields['.$this->get('sortorder').'][enable_delete]', 'yes', 'checkbox');
		if ($this->get('enable_delete') == 1) $input->setAttribute('checked', 'checked');
		$label->setValue(__('<br />%s Enable Delete button', array($input->generate())));
		$fieldset->appendChild($label);

		$wrapper->insertChildAt(5, $fieldset);
	}

    /**
     * Save the settings panel
     * @return bool
     */
	public function commit(){
		if(!parent::commit()) return false;

		$id = $this->get('id');

		if($id === false) return false;

		$fields = array();
		// $fields['field_id'] = $id;
		if($this->get('related_field_id') != '') $settings['related_field_id'] = $this->get('related_field_id');
		$settings['allow_multiple_selection'] = $this->get('allow_multiple_selection') ? $this->get('allow_multiple_selection') : 'no';
		$settings['show_association'] = $this->get('show_association') == 'yes' ? 'yes' : 'no';
		$settings['limit'] = max(1, (int)$this->get('limit'));
		$settings['related_field_id'] = implode(',', $this->get('related_field_id'));
		$settings['view'] = $this->get('view');
		$settings['show_created'] = $this->get('show_created') == 'yes' ? 1 : 0;
		$settings['enable_create'] = $this->get('enable_create') == 'yes' ? 1 : 0;
		$settings['enable_edit'] = $this->get('enable_edit') == 'yes' ? 1 : 0;
		$settings['enable_delete'] = $this->get('enable_delete') == 'yes' ? 1 : 0;

		FieldManager::saveSettings($id, $settings);

		SectionManager::removeSectionAssociation($id);

		foreach($this->get('related_field_id') as $field_id){
			SectionManager::createSectionAssociation(null, $id, $field_id, $this->get('show_association') == 'yes' ? true : false);
		}

		return true;
	}

    /**
     * Override the appendFormattedElement to put the entries in the datasource in the same order as they were stored
     * for sorting purposes.
     * @param $wrapper
     * @param $data
     * @param bool $encode
     * @return
     */
    public function appendFormattedElement(&$wrapper, $data, $encode=false){
        if(!is_array($data) || empty($data) || is_null($data['relation_id'])) return;

        $list = new XMLElement($this->get('element_name'));

        if(!is_array($data['relation_id'])) {
            $data['relation_id'] = array($data['relation_id']);
        }

        $related_values = $this->findRelatedValues($data['relation_id']);

        // This is the only adjustment from it's native function:
        $new_related_values = array();
        foreach($related_values as $relation)
        {
            $new_related_values[$relation['id']] = $relation;
        }
        $related_values = array();
        foreach($data['relation_id'] as $id)
        {
            $related_values[] = $new_related_values[$id];
        }
        // End of the only adjustment //

        foreach($related_values as $relation) {
            $item = new XMLElement('item');
            $item->setAttribute('id', $relation['id']);
            $item->setAttribute('handle', Lang::createHandle($relation['value']));
            $item->setAttribute('section-handle', $relation['section_handle']);
            $item->setAttribute('section-name', General::sanitize($relation['section_name']));
            $item->setValue(General::sanitize($relation['value']));
            $list->appendChild($item);
        }

        $wrapper->appendChild($list);
    }

}
