<?php

namespace Obullo\Jelly\View\Elements;

use Obullo\Jelly\Form,
    Obullo\Jelly\View\View;

/**
 * Jelly Blockquote
 * 
 * @category  Jelly
 * @package   Elements
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/jelly
 */
Class Blockquote implements ElementsInterface
{
    /**
     * Constructor
     * 
     * @param array $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
        $this->validator   = $c->load('validator');
        $this->formElement = $c->load('form/element');
        $this->jellyForm   = $c->load('jelly/form');
    }

    /**
     * Render
     * 
     * @param object $view View object
     * 
     * @return string
     */
    public function render(View $view)
    {
        $data  = $view->getFieldData();
        $extra = $view->getFieldExtraData();

        if (strpos($data[Form::ELEMENT_ATTRIBUTE], '{') !== false) {
            if (preg_match('#(?<key>{.*?})#', $data[Form::ELEMENT_ATTRIBUTE], $match)) {
                $callBack = preg_replace_callback(
                    $match['key'],
                    function ($val) use ($data, $view) {
                        $values = $view->getFormValues();
                        return isset($values[$val[0]]) ? $values[$val[0]] : $data[Form::ELEMENT_ATTRIBUTE];
                    },
                    $data[Form::ELEMENT_ATTRIBUTE]
                );
                $data[Form::ELEMENT_ATTRIBUTE] = str_replace(array('{', '}'), '', $callBack);
            }
        }
        
        $element = $this->validator->getError($data[Form::ELEMENT_NAME]);
        $element.= vsprintf(
            '<blockquote %s>%s</blockquote>',
            array(
                $data[Form::ELEMENT_ATTRIBUTE],
                $data[Form::ELEMENT_VALUE]
            )
        );
        if ($view->isTranslatorEnabled() === true) {
            $this->c->load('return translator');
            $data[Form::ELEMENT_LABEL]       = $this->c['translator'][$data[Form::ELEMENT_LABEL]];
            $extra[Form::ELEMENT_LABEL]      = $this->c['translator'][$extra[Form::ELEMENT_LABEL]];
            $data[Form::ELEMENT_DESCRIPTION] = $this->c['translator'][$data[Form::ELEMENT_DESCRIPTION]];
        }
        if ($view->isGroup() === true) {

            if ($view->isGrouped() === true) {

                $elementTemp = $view->createHiddenInput($extra[Form::ELEMENT_NAME]) . $view->getGroupElementsTemp();
               
                return $this->jellyForm->getGroupDiv(
                    $elementTemp,
                    $extra[Form::GROUP_NAME],
                    $extra[Form::GROUP_LABEL],
                    $extra[Form::GROUP_CLASS]
                );
            }
            $element = $this->jellyForm->getGroupParentDiv($element, $view->getColSm(), $data[Form::ELEMENT_LABEL]);

            if ($view->isGroupDescription()) {
                $element.= $this->jellyForm->getGroupDescriptionDiv($data[Form::ELEMENT_DESCRIPTION], '');
            }
            return $element;
        }
        $element = $this->jellyForm->getElementDiv($element, $extra[Form::ELEMENT_LABEL]);
        $element.= $this->jellyForm->getDescriptionDiv($data[Form::ELEMENT_DESCRIPTION], '');
        return $element;
        // return $this->jellyForm->getElementDiv($element, $extra[Form::ELEMENT_LABEL]);
    }
}

// END Input Class
/* End of file Input.php */

/* Location: .Obullo/Jelly/Input.php */