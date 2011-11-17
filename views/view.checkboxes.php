<?php
/**
 * (c) 2011
 * Author: Giel Berkers
 * Date: 13-10-11
 * Time: 20:30
 */

// The class name must be 'SBLPView_[filename - view. and .php (ucfirst)]':
Class SBLPView_Checkboxes
{
    /**
     * Return the name of this view
     * @return string
     */
    public function getName()
    {
        return __('Checkboxes');
    }

    /**
     * This function generates the view on the publish page
     * @param $viewWrapper  The XMLElement wrapper in which the view is placed
     * @param $fieldname    The name of the field
     * @param $options      The options
     * @param $parent       The parent element (this is the Field itself)
     * @return void
     */
    public function generateView(&$viewWrapper, $fieldname, $options, $parent)
    {
        // Add the selectbox:
        $viewWrapper->appendChild(
            Widget::Select($fieldname, $options, ($parent->get('allow_multiple_selection') == 'yes' ? array(
                'multiple' => 'multiple') : NULL
            ))
        );

        // Set the viewname: this is required by the javascript-functions to edit or delete entries.
        $viewName = 'sblp-view-'.$parent->get('id');

        // Show checkboxes:
        $checkboxes = new XMLElement('div', null, array('class'=>'sblp-checkboxes'));
        $table = new XMLElement('table');
        foreach($options as $optGroup)
        {
            if(isset($optGroup['label']))
            {
                $tr = new XMLElement('tr');
                $tr->appendChild(new XMLElement('th', $optGroup['label'], array('colspan'=>2)));
                $table->appendChild($tr);
                // Set the sectionname: this is required by the javascript-functions to edit or delete entries.
                $sectionName = General::createHandle($optGroup['label']);
                // In case of no multiple and not required:
                if($parent->get('allow_multiple_selection') == 'no' && $parent->get('required') == 'no')
                {
                    $tr = new XMLElement('tr');
                    $td = new XMLElement('td', null, array('colspan'=>2));
                    $label = Widget::Label('<em>'.__('Select none').'</em>', Widget::Input('sblp-checked-'.$parent->get('id'), 0, 'radio'));
                    $td->appendChild($label);
                    $tr->appendChild($td);
                    $table->appendChild($tr);
                }
                foreach($optGroup['options'] as $option)
                {
                    $id       = $option[0];
                    $value    = $option[2];
                    // Now this is where the name of the item, including the edit- and delete buttons are rendered:
                    // Please note that the edit- and delete-buttons use javascript functions provided by sbl+ to handle
                    // this functionality. This is done to make sure this extension uses as much native Symphony
                    // functionality as possible:
                    $tr = new XMLElement('tr');
                    $td = new XMLElement('td', null, array('width' => '78%'));
                    if($parent->get('allow_multiple_selection') == 'yes')
                    {
                        $label = Widget::Label($value, Widget::Input('sblp-checked-'.$parent->get('id').'[]', $id, 'checkbox'));
                    } else {
                        $label = Widget::Label($value, Widget::Input('sblp-checked-'.$parent->get('id'), $id, 'radio'));
                    }
                    $td->appendChild($label);
                    $tr->appendChild($td);
                    $tr->appendChild(new XMLElement('td', '
                        <a href="#" class="edit" onclick="sblp_editEntry(\''.$viewName.'\',\''.$sectionName.'\','.$id.'); return false;">Edit</a>
                        <a href="#" class="delete" onclick="sblp_deleteEntry(\''.$viewName.'\',\''.$sectionName.'\','.$id.'); return false;">Delete</a>',
                        array('class' => 'sblp-checkboxes-actions', 'width' => '22%')
                    ));
                    $table->appendChild($tr);
                }
            }
        }
        $checkboxes->appendChild($table);
        $viewWrapper->appendChild($checkboxes);

        // CSS:
        $viewWrapper->appendChild(new XMLElement('style', '
            div.sblp-checkboxes { max-height: 332px; overflow: auto; border: 1px solid #ccc; margin-top: 5px; }
            div.sblp-checkboxes table { table-layout: auto; }
            div.sblp-checkboxes table label { margin: 0; }
            div.sblp-checkboxes table td input { float: left; margin-right: 19px; }
            div.sblp-checkboxes table td a { display: none; }
            div.sblp-checkboxes table tr:hover a { display: inline; }
            div.sblp-checkboxes table td.sblp-checkboxes-actions { text-align: right; }
            #sblp-view-'.$parent->get('id').' select { display: none; }
        ', array('type'=>'text/css')));

        // Javascript should be placed inside an sblp_initview[$viewName]()-function, to make sure it gets executed whenever the view
        // reloads with AJAX. This happens when an entry gets added, edited or deleted:
        $viewWrapper->appendChild(new XMLElement('script', '
            sblp_initview["'.$viewName.'"] = function()
            {
                var $ = jQuery;
                $("#'.$viewName.' select option:selected").each(function(){
                    $("#'.$viewName.' div.sblp-checkboxes input[value=" + $(this).val() + "]").attr("checked", "checked");
                });
                $("#'.$viewName.' div.sblp-checkboxes input").change(function(e){
                    $("#'.$viewName.' select option").removeAttr("selected");
                    $("#'.$viewName.' input:checked").each(function(){
                        var id = $(this).val();
                        $("#'.$viewName.' select option[value=" + id + "]").attr("selected", "selected");
                    });
                });
            };
        ', array('type'=>'text/javascript')));


    }
}
