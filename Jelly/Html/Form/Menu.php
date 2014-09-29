<?php

namespace Obullo\Jelly\Html\Form;

use Obullo\Jelly\Form,
    Obullo\Form\Element;

/**
 * Form Menu
 * 
 * @category  Jelly
 * @package   Html
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs
 */
Class Menu
{
    /**
     * Jelly Form object
     * 
     * @var object
     */
    public $jellyForm = null;

    /**
     * Form element
     * 
     * @var object
     */
    public $formElement = null;

    /**
     * Validator object
     * 
     * @var object
     */
    public $validator = null;

    /**
     * Constructor
     * 
     * @param array  $c         container
     * @param object $jellyForm Jelly Form object
     */
    public function __construct($c, Form $jellyForm)
    {
        $this->jellyForm   = $jellyForm;
        $this->formElement = $c->load('form/element');
        $this->validator   = $c->load('validator');
    }

    /**
     * Create dropdown menu
     * 
     * @param string $name      input name
     * @param string $options   option data
     * @param string $labelName label name
     * @param string $selected  dropdown selected
     * @param string $extra     extra data
     * @param string $data      option extra data
     * 
     * @return string
     */
    public function create($name, $options, $labelName, $selected = '', $extra = '', $data = array())
    {
        $element = $this->validator->getError($name);
        $element.= $this->formElement->dropdown($name, $options, $selected, $extra, $data);
        return $this->jellyForm->getElementDiv($element, $labelName);
    }

    /**
     * Input template
     * 
     * @param string $element   element data
     * @param string $inputName input name
     * @param string $labelName label name
     * 
     * @return string template
     */
    public function inputTemplate($element, $inputName, $labelName)
    {
        $form  = '';
        $form .= '<div class="form-group">
                        <label class="col-sm-2 control-label">'. $labelName .'</label>';
        $form .= '<div class="col-sm-8">
                '.$this->validator->getError($inputName);
        $form .= $element;
        $form .='</div></div>';
        return $form;
    }
}


// END FormMenu Class
/* End of file FormMenu.php */

/* Location: .Obullo/Jelly/FormMenu.php */