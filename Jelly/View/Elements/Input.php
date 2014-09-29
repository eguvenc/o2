<?php

namespace Obullo\Jelly\View\Elements;

use Obullo\Jelly\Form,
    Obullo\Jelly\View\View;

/**
 * Jelly Input
 * 
 * @category  Jelly
 * @package   Elements
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/jelly
 */
Class Input implements ElementsInterface
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
        
        $element = $this->validator->getError($data[Form::ELEMENT_NAME]);
        // $element = ($view->isGroup() === false) ? $this->validator->getError($data[Form::ELEMENT_NAME]) : '';
        $element.= $this->formElement->input($data[Form::ELEMENT_NAME], $data[Form::ELEMENT_VALUE], $data[Form::ELEMENT_ATTRIBUTE]);

        if ($view->isTranslatorEnabled() === true) {
            $this->c->load('return translator');
            $data[Form::ELEMENT_LABEL] = $this->c['translator'][$data[Form::ELEMENT_LABEL]];
            $extra[Form::ELEMENT_LABEL] = $this->c['translator'][$extra[Form::ELEMENT_LABEL]];
            $extra[Form::ELEMENT_DESCRIPTION] = $this->c['translator'][$extra[Form::ELEMENT_DESCRIPTION]];
        }
        if ($view->isGroup() === true) {
            if ($view->isGrouped() === true) {

                $elementTemp = $view->createHiddenInput($extra[Form::ELEMENT_NAME]) . $view->getGroupElementsTemp();
                
                return $this->jellyForm->getGroupDiv(
                    $elementTemp,
                    $extra[Form::ELEMENT_NAME],
                    $extra[Form::ELEMENT_LABEL]
                );
            }
            return $this->jellyForm->getGroupParentDiv($element, $view->getColSm(), $data[Form::ELEMENT_LABEL]);
        }
        $element = $this->jellyForm->getElementDiv($element, $extra[Form::ELEMENT_LABEL]);
        $element.= $this->jellyForm->getDescriptionDiv($extra[Form::ELEMENT_DESCRIPTION], '');
        return $element;
        // return $this->jellyForm->getElementDiv($element, $extra[Form::ELEMENT_LABEL]);
    }
}

// END Input Class
/* End of file Input.php */

/* Location: .Obullo/Jelly/Input.php */