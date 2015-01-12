<?php

namespace Obullo\Jelly\View\Elements;

use Obullo\Jelly\Form,
    Obullo\Jelly\View\View;

/**
 * Jelly Submit
 * 
 * @category  Jelly
 * @package   Elements
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/jelly
 */
Class Submit implements ElementsInterface
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
     * @todo line 71 change to Form\Element\Button
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

        if ($view->isAjax() === true) {
            $formId = "'". (string)$view->formData[Form::FORM_ID] ."'";
            $data[Form::ELEMENT_ATTRIBUTE] = ' onClick="'. sprintf($this->jellyForm->params['ajax.function'], $formId) .'"'. $data[Form::ELEMENT_ATTRIBUTE];
        }
        $value = $data[Form::ELEMENT_VALUE];
        if ($view->isTranslatorEnabled() === true) {
            $value = $this->c->load('translator')[$data[Form::ELEMENT_VALUE]];
        }
        
        $element = $this->validator->getError($data[Form::ELEMENT_NAME]);
        $element.= $this->formElement->button(array('type' => 'submit'), $data[Form::ELEMENT_VALUE], $data[Form::ELEMENT_ATTRIBUTE]);
        // $element.= '<button name="'. $data[Form::ELEMENT_NAME] .'" type="submit"'. $data[Form::ELEMENT_ATTRIBUTE] .'>'. $value .'</button>';
        // $element.= $this->formElement->submit($data[Form::ELEMENT_NAME], $value, $data[Form::ELEMENT_ATTRIBUTE]);

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
        $view->setSubmit($element);
        return;
    }
}

// END Submit Class
/* End of file Submit.php */

/* Location: .Obullo/Jelly/Submit.php */