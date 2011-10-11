<?php
/**
 * (c) 2011
 * Author: Giel Berkers
 * Date: 10-10-11
 * Time: 14:19
 */

Class SBLPView_Default
{
    public function getName()
    {
        return __('Default View');
    }

    public function generateView(&$viewWrapper, $fieldname, $options, $parent)
    {
        // Add the selectbox:
        $viewWrapper->appendChild(
            Widget::Select($fieldname, $options, ($parent->get('allow_multiple_selection') == 'yes' ? array(
                'multiple' => 'multiple') : NULL
            ))
        );

        // The default view doesn't have any extras. See the gallery-view for a more detailed example.
    }
}
