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
    public function __construct(&$parent){
        parent::__construct($parent);
        $this->_name = __('Select Box Link +');
        $this->_required = true;
        $this->_showassociation = true;

        // Default settings
        $this->set('show_column', 'no');
        $this->set('show_association', 'yes');
        $this->set('required', 'yes');
        $this->set('limit', 20);
        $this->set('related_field_id', array());

        if(!isset(self::$em) && class_exists('EntryManager')) {
            self::$em = new EntryManager(Symphony::Engine());
        }
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

        if($this->get('required') != 'yes') $options[] = array(NULL, false, NULL);

        $states = $this->findOptions($entry_ids);
        if(!empty($states)){
            foreach($states as $s){
                $group = array('label' => $s['name'], 'options' => array());
                foreach($s['values'] as $id => $v){
                    $group['options'][] = array($id, in_array($id, $entry_ids), General::sanitize($v));
                }
                $options[] = $group;
            }
        }

        $fieldname = 'fields'.$prefix.'['.$this->get('element_name').']'.$postfix;
        if($this->get('allow_multiple_selection') == 'yes') $fieldname .= '[]';
        $label = Widget::Label($this->get('label'));

        // Generate the buttons the create entries in the related sections:
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

        $viewWrapper = new XMLElement('div', null, array('id'=>'sblp-view-'.$this->get('id')));

        // Load the correct View:
        require_once(EXTENSIONS.'/selectbox_link_field_plus/views/view.'.$this->get('view').'.php');
        $className = 'SBLPView_'.ucfirst($this->get('view'));
        $view = new $className;
        
        $view->generateView($viewWrapper, $fieldname, $options, $this);

        $label->appendChild($viewWrapper);

        if(!is_null($error)) {
            $wrapper->appendChild(Widget::wrapFormElementWithError($label, $error));
        }
        else $wrapper->appendChild($label);
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
        $label = Widget::Label(__('View'));
        $label->appendChild(Widget::Select('fields['.$this->get('sortorder').'][view]', $options));
        $wrapper->insertChildAt(4, $label);
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
        $fields['field_id'] = $id;
        if($this->get('related_field_id') != '') $fields['related_field_id'] = $this->get('related_field_id');
        $fields['allow_multiple_selection'] = $this->get('allow_multiple_selection') ? $this->get('allow_multiple_selection') : 'no';
        $fields['show_association'] = $this->get('show_association') == 'yes' ? 'yes' : 'no';
        $fields['limit'] = max(1, (int)$this->get('limit'));
        $fields['related_field_id'] = implode(',', $this->get('related_field_id'));
        $fields['view'] = $this->get('view');

        Symphony::Database()->query("DELETE FROM `tbl_fields_".$this->handle()."` WHERE `field_id` = '$id'");

        if(!Symphony::Database()->insert($fields, 'tbl_fields_' . $this->handle())) return false;

        $this->removeSectionAssociation($id);
        foreach($this->get('related_field_id') as $field_id){
            $this->createSectionAssociation(NULL, $id, $field_id, $this->get('show_association') == 'yes' ? true : false);
        }

        return true;
    }

}