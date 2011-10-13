<?php
/**
 * (c) 2011
 * Author: Giel Berkers
 * Date: 10-10-11
 * Time: 14:19
 */

// The class name must be 'SBLPView_[filename - view. and .php (ucfirst)]':
Class SBLPView_Default
{
    /**
     * Return the name of this view
     * @return string
     */
    public function getName()
    {
        return __('Default View');
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

        // The default view doesn't have any extras. See the gallery or the checkboxes-view for a more detailed example.
    }
}
