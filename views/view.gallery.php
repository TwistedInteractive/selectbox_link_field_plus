<?php
/**
 * (c) 2011
 * Author: Giel Berkers
 * Date: 10-10-11
 * Time: 14:19
 */

// The class name must be 'SBLPView_[filename - view. and .php (ucfirst)]':
Class SBLPView_Gallery
{
    /**
     * Return the name of this view
     * @return string
     */
    public function getName()
    {
        return __('Gallery');
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

        // Create the gallery:
        $gallery = new XMLElement('div', null, array('class'=>'sblp-gallery'));
        foreach($options as $optGroup)
        {
            $gallery->appendChild(new XMLElement('h3', $optGroup['label']));
            // Set the sectionname: this is required by the javascript-functions to edit or delete entries.
            $sectionName = General::createHandle($optGroup['label']);
            foreach($optGroup['options'] as $option)
            {
                $id       = $option[0];
                $value    = $option[2];
                $attr     = array('rel' => $id, 'class' => 'image');
                $xml = new SimpleXMLElement(html_entity_decode($value));
                $attr2 = $xml->attributes();
                $href = str_replace(URL.'/workspace/', '', $attr2['href']);
                $img = '<img src="/image/2/100/100/5/'.$href.'" alt="thumb" width="100" height="100" />';
                // Now this is where the thumbnail, including the edit- and delete buttons are rendered:
                // Please note that the edit- and delete-buttons use javascript functions provided by sbl+ to handle
                // this functionality. This is done to make sure this extension uses as much native Symphony
                // functionality as possible:
                $div = new XMLElement('div', '
                    <a href="#" class="edit" onclick="sblp_editEntry(\''.$viewName.'\',\''.$sectionName.'\','.$id.'); return false;">E</a>
                    <a href="#" class="delete" onclick="sblp_deleteEntry(\''.$viewName.'\',\''.$sectionName.'\','.$id.'); return false;">×</a>
                    <a href="#" title="'.$href.'" class="thumb">'.$img.'</a>', $attr);
                $gallery->appendChild($div);
            }
        }
        $viewWrapper->appendChild($gallery);

        // CSS:
        $viewWrapper->appendChild(new XMLElement('style', '
            div.sblp-gallery div.image { width: 100px; height: 100px; float: left;
                box-shadow: 0 2px 6px rgba(0, 0, 0, .5); margin-right: 5px; margin-bottom: 5px;
                font-size: 10px; cursor: pointer; position: relative;
            }
            div.sblp-gallery { border: 1px solid #eee; padding: 10px; padding-right: 0; float: left; margin-top: 5px;
                box-shadow: 2px 2px 6px rgba(0, 0, 0, 1) inset; background: #333; overflow: auto; max-height: 548px;
                border: 1px solid #ccc; }
            div.sblp-gallery h3 { font-size: 16px; margin-bottom: 10px; color: #fff; text-shadow: none; }
            div.sblp-gallery span { float: right; }
            div.sblp-gallery div.selected { outline: 5px solid #81b934; background: #81b934; }
            div.sblp-gallery div.selected img { opacity: .5; }
            div.sblp-gallery a.edit, div.sblp-gallery a.delete { position: absolute; background: #81b934;
                left: 0; top: 0; z-index: 2; width: 20px; height: 20px; text-align: center; text-decoration: none;
                font-size: 12px; font-weight: bold; color: #fff; display: none; }
            div.sblp-gallery a.delete { left: 80px; background: #b93434; }
            div.sblp-gallery div.image:hover a.edit, div.sblp-gallery div.image:hover a.delete { display: block; }
            #sblp-view-'.$parent->get('id').' select { display: none; }
        ', array('type'=>'text/css')));

        // JavaScript:
        // Javascript should be placed inside an initView()-function, to make sure it gets executed whenever the view
        // reloads with AJAX:
        $viewWrapper->appendChild(new XMLElement('script', '
            function initView()
            {
                var $ = jQuery;
                var multiple = '.($parent->get('allow_multiple_selection') == 'yes' ? 'true' : 'false').';
                $("div.sblp-gallery div.image a.thumb").click(function(e){
                    var id = $(this).parent().attr("rel");
                    if(multiple)
                    {
                        $(this).parent().toggleClass("selected");
                        if($(this).parent().hasClass("selected")) {
                            $("#sblp-view-'.$parent->get('id').' select option[value=" + id + "]").attr("selected", "selected");
                        } else {
                            $("#sblp-view-'.$parent->get('id').' select option[value=" + id + "]").removeAttr("selected");
                        }
                    } else {
                        $("#sblp-view-'.$parent->get('id').' div.sblp-gallery div.image").removeClass("selected");
                        $("#sblp-view-'.$parent->get('id').' select option").removeAttr("selected");
                        $("#sblp-view-'.$parent->get('id').' select option[value=" + id + "]").attr("selected", "selected");
                        $(this).parent().addClass("selected");
                    }
                    return false;
                });
                $("#sblp-view-'.$parent->get('id').' select option:selected").each(function(){
                    $("div.image[rel=" + $(this).val() + "]").addClass("selected");
                });
            }
        ', array('type'=>'text/javascript')));
    }
}
