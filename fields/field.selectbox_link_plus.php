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

    /**
     * Constructor
     * @param $parent
     */
    public function __construct(&$parent){
        parent::__construct($parent);
        $this->_name = __('Select Box Link +');

        $this->set('filter', array());
    }
	
    public function set($field, $value){
    	if($field == 'related_field_id' && !is_array($value)){
			$value = explode(',', $value);
		}
    	if($field == 'filter' && !is_array($value)){
    		$value = explode(',', $value);
    	}
    	$this->_fields[$field] = $value;
    }
    
    protected function findRelatedValues(array $relation_id = array()) {
    	$relation_id = array_unique($relation_id);
		
    	// make sure we're editing Backend
    	if( Symphony::Engine() instanceof Administration ){
    		$callback = Symphony::Engine()->getPageCallback();
    		
    		$state = 'no filter';
    		
    		if( $callback['driver'] == 'publish' ){
    			if( $callback['context']['page'] == 'edit' ){
    				if( $this->get('use_filter') == 'yes' ){
    					$state = 'apply filters';
    				}
    			}
//     			elseif( $callback['context']['page'] == 'new' ){
//     				if( $this->get('use_filter') == 'yes' ){
//     					$state = 'return empty';
//     				}
//     			}
    		}
    		elseif( $callback['driver'] == 'preferences' ){
    			if( $this->get('use_filter') == 'yes' ){
    				$state = 'apply filters';
    			}
    		}
    		
    		switch( $state ){
    			case 'return empty':
    				return array();
    			
    			case 'apply filters':
    				$filters = $this->get('filter');
    				$filters = array_filter($filters);
    				
    				// if filters exist, refine $relation_ids (entries)
    				if( !empty($filters) ){
    					$entry_id = null;
    				
    					if( $callback['driver'] == 'publish'){
    						$entry_id = $callback['context']['entry_id'];
    					}
    					elseif( $callback['driver'] == 'preferences'  ){
    						$section_id = empty($callback['context'][0]) ? 1 : $callback['context'][0];
    						$em = new EntryManager(Symphony::Engine());
    						$entry = $em->fetch(null, $section_id, 1);
    				
    						$entry_id = $entry[0]->get('id');
    					}
    				
    					if( !empty($entry_id) ){
    						$filtered_entries = array();
    				
    						foreach( $filters as $filter_id ){
    							// get all entries from B that have `relation_id` set to current entry from A
    							$query = sprintf("
    									SELECT `entry_id`
    									FROM `tbl_entries_data_%d`
    									WHERE `relation_id` = '%d'
    									ORDER BY `entry_id` ASC
    									", $filter_id, $entry_id
    							);
    				
    							try {
    								$entries_by_relation = Symphony::Database()->fetchCol('entry_id', $query);
    							} catch (Exception $e) {
    							}
    				
    							$filtered_entries = array_merge($filtered_entries, $entries_by_relation);
    						}
    				
    						foreach( $relation_id as $key => $rel_id ){
    							if( !in_array($rel_id, $filtered_entries) ){
    								unset($relation_id[$key]);
    							}
    						}
    					}
    					else{
    						return array();
    					}
    				}
    				// no filters set, nothing to display
    				else{
    					return array();
    				}
    				break;
    				
    			case 'no filter':
    			default:
    				break;
    		}
    	}
    	
    	if( empty($relation_id) ) return array();
    	
    	// fetch related data as usual
    	return parent::findRelatedValues($relation_id);
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

        $buttons = new XMLElement('span', null, array('class'=>'sblp-buttons'));
        
        /* If new Entry (publish -> new) and SBL+ has a filter, disable create related entries buttons
         * because one cannot set visibility of an Image to current Album since current Album doesn't exist yet.
         */
        $callback = Symphony::Engine()->getPageCallback();
        if( ($callback['driver'] == 'publish') && ($callback['context']['page'] == 'new') && ($this->get('filter') != null) ){
        	$buttons->appendChild(Widget::Anchor(__('This button is available after saving the entry'), 'javascript:void(0)', null, 'create button'));
        }
        // Generate the buttons that create entries in the related sections:
        else{
	        $related_fields = $this->get('related_field_id');
	        $related_sections = array();
	        foreach($related_fields as $id)
	        {
	            // get the section:
	            $related_sections[] = Symphony::Database()->fetchRow(0, 'SELECT A.`name`, A.`id`, A.`handle` FROM
	                `tbl_sections` A, `tbl_fields` B  WHERE A.`id` = B.`parent_section` AND B.`id` = '.$id.';');
	        }
	        foreach($related_sections as $section)
	        {
	            $buttons->appendChild(Widget::Anchor(sprintf(__('Create new entry in "%s"'), $section['name']),
	                URL.'/symphony/publish/'.$section['handle'].'/new/', null, 'create button sblp-add'));
	        }
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
     * Process data from an entry. If filter exists, for each related entry, set visibility to this entry.
     *
     * @see fieldSelectBox_Link::processRawFieldData()
     */
    public function processRawFieldData($data, &$status, $simulate=false, $entry_id=NULL){
    	$result = parent::processRawFieldData($data, $status, $simulate, $entry_id);
    	
    	$filters = $this->get('filter');
    	
    	// if this field has a filter applied to entries, set related Entries visibility to this Entry
    	if( is_array($filters) && !empty($filters) ){
    		
    		foreach( $filters as $filter ){
    			
    			if( !empty($filter) ){
    				// get the related_field_id for filter field
    				$related_section_id = Symphony::Database()->fetchVar('parent_section', 0, sprintf("SELECT `parent_section` FROM `sym_fields` WHERE `id` = '%d' LIMIT 1", $filter));
    				
    				$em = new EntryManager(Symphony::Engine());
    				$related_entries = $em->fetch($result['relation_id'], $related_section_id, null, null, null, null, true, true);
    				
    				foreach( $related_entries as $entry ){
    					// get current visibility relations
    					$related_field_data = $entry->getData($filter);
    					
    					// add this entry to the relations
    					$new_field_data = array();
    					
    					if( !empty($related_field_data) ){
    						if( !is_array($related_field_data['relation_id']) ){
    							$new_field_data['relation_id'][] = $related_field_data['relation_id'];
    						}
    						else{
    							$new_field_data = $related_field_data;
    						}
    					}
    					else{
    						$new_field_data['relation_id'] = array();
    					}
    					
    					if( !in_array($entry_id, $new_field_data['relation_id']) ){
    						$new_field_data['relation_id'][] = $entry_id;
    						$entry->setData($filter, $new_field_data);
    						$entry->commit();
    					}
    				}
    			}
    		}
    	}
    	
    	return $result;
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
        
        
        // Add the checkbox for using filters
        $div = new XMLElement('div', null, array('class' => 'compact'));
        
        $label = Widget::Label();
        $input = Widget::Input('fields['.$this->get('sortorder').'][use_filter]', 'yes', 'checkbox');
        if($this->get('use_filter') == 'yes') $input->setAttribute('checked', 'checked');
        $label->setValue($input->generate() . ' ' . __('Enable filters'));
        if( $this->get('use_filter') == 'yes' ){
        	$message = __('Filters are enabled.');
        }
        else{
        	$message = __('Check this to enable filters for entries. Set filters after saving the Section.');
        }
        $label->appendChild(new XMLElement('p', $message, array('class' => 'help', 'style' => 'margin: 5px 0 0 0;')));
        $div->appendChild($label);
        
        // Add the filter for values
        $options = array();
        
        // Set options only if relation_id field is set
        $related_field_ids = $this->get('related_field_id');
        if( !empty($related_field_ids) ){
        	
        	// Fetch related Sections. Filter field must be part in one of these sections.
        	foreach( $related_field_ids as $key => $related_field_id ){
        		$related_field_ids[$key] = sprintf("`id` = '%s'", $related_field_id);
        	}
        	
        	try{
        		$related_sections_ids = Symphony::Database()->fetchCol('parent_section', "SELECT `parent_section` FROM tbl_fields WHERE ".implode(' OR ', $related_field_ids));
        		
        		$filter_fields = array();
        		
        		/*
        		 * Fetch candidate filter fields (field_id + field_label + section_name)
        		 *
        		 * These fields are SBL+ and must meet conditions:
        		 * - parent_section of `related_field_id` => equal to $this_section_id
        		 * - parent_section of `field_id` => in_array($related_sections_ids)
        		 */
        		try{
        			$query = sprintf("
        				SELECT t_sblp.`field_id`, t_fs.`label`, t_sblp.`related_field_id`, t_ss.`name`
        					
        				FROM (`sym_fields_selectbox_link_plus` AS t_sblp
        					LEFT JOIN `sym_fields` AS t_fs
        					ON t_sblp.`field_id` = t_fs.`id`)
        						LEFT JOIN `sym_sections` AS t_ss
        						ON t_fs.`parent_section` = t_ss.`id`
        					
        				WHERE t_sblp.`field_id` IN (
        					SELECT `id` FROM `sym_fields` WHERE `parent_section` IN (%s)
        				)
        				",
        				implode(',', $related_sections_ids)
        			);
        			
        			$filter_fields = Symphony::Database()->fetch($query);
        		}catch( Exception $e ){}
        		
        		$filters_by_section = array();
        		foreach( $filter_fields as $data ){
        			$filters_by_section[$data['name']][] = $data;
        		}
        		
        		$filters = $this->get('filter');
        		
        		foreach( $filters_by_section as $section_name => $fields ){
        			$group = array();
        			
        			foreach( $fields as $key => $data){
        				if( $this->_matchSectionsFromRelatedFields($data['related_field_id']) ){
        					$group[] = array($data['field_id'], in_array($data['field_id'], $filters), $data['label']);
        				}
        			}
        			
        			if( !empty($group) ){
        				$options[] = array( 'label' => $section_name, 'options' => $group );
        			}
        		}
        	} catch( Exception $e ) {}
        }
        
        $select_attributes = array('multiple' => 'multiple');
        if($this->get('use_filter') != 'yes'){
        	$select_attributes['disabled'] = 'disabled';
        }
        
        $label = Widget::Label(__('Filters for Values'));
        $label->appendChild(Widget::Select('fields['.$this->get('sortorder').'][filter][]', $options, $select_attributes));
        if( empty($related_field_ids) ){
        	$message = __('Options are available only after setting the Values field above and saving the Section.');
        }
        else{
        	$message = __('These filters will determine selectable values. If none selected, nothing will be displayed.');
        }
        $label->appendChild(new XMLElement('p', $message, array('class' => 'help', 'style' => 'margin: 5px 0 0 0;')));
        $div->appendChild($label);
        $wrapper->insertChildAt(6, $div);
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
        $fields['allow_multiple_selection'] = $this->get('allow_multiple_selection') ? $this->get('allow_multiple_selection') : 'no';
        $fields['show_association'] = $this->get('show_association') == 'yes' ? 'yes' : 'no';
        $fields['limit'] = max(1, (int)$this->get('limit'));
        if($this->get('related_field_id') != '') $fields['related_field_id'] = $this->get('related_field_id');
        $fields['related_field_id'] = implode(',', $this->get('related_field_id'));
        $fields['view'] = $this->get('view');
        $fields['use_filter'] = $this->get('use_filter') == 'yes' ? 'yes' : 'no';
        if($fields['use_filter']=='yes'){
        	if($this->get('filter') != '') $fields['filter'] = $this->get('filter');
        	$fields['filter'] = implode(',', $this->get('filter'));
        }
        else{
        	$fields['filter'] = '';
        }

        Symphony::Database()->query("DELETE FROM `tbl_fields_".$this->handle()."` WHERE `field_id` = '$id'");

        if(!Symphony::Database()->insert($fields, 'tbl_fields_' . $this->handle())) return false;

        $this->removeSectionAssociation($id);
        foreach($this->get('related_field_id') as $field_id){
            $this->createSectionAssociation(NULL, $id, $field_id, $this->get('show_association') == 'yes' ? true : false);
        }

        return true;
    }
	
    
    
    /**
     * Check if any of related_field_id's parent section is the same as current section.
     *
     * @param string $related_field_id - related fields ids
     *
     * @return boolean - true if at least one related field id's parent section is the same as current section.
     */
    private function _matchSectionsFromRelatedFields($related_field_id){
    	$this_section_id = Administration::instance()->Page->_context[1];
    	$related_fields_ids = explode(',', $related_field_id);
    	
    	foreach( $related_fields_ids as $field_id ){
	    	$query = sprintf("
	    		SELECT `id` FROM `sym_fields`
	    		WHERE `id` = '%s' AND `parent_section` = '%s'
	    		LIMIT 1", $field_id, $this_section_id
	    	);
	    	
	    	$validate = Symphony::Database()->fetchVar('id', 0, $query);
	    	
	    	if( !empty($validate) ) return true;
    	}
    	
    	return false;
    }
}
