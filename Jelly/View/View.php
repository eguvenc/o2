<?php

namespace Obullo\Jelly\View;

use Obullo\Jelly\Form,
    Obullo\Jelly\View\Elements;
    

/**
 * Jelly View
 * 
 * @category  Jelly
 * @package   View
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/jelly
 */
Class View
{
    /**
     * Form elements type
     * 
     * Types: ("input", "text", "dropdown", "textarea" ..)
     * 
     * @var string
     */
    public $type = '';

    /**
     * Form data
     * 
     * @var array
     */
    public $formData = array();

    /**
     * Form is ajax?
     * 
     * @var boolean
     */
    public $isAjax = false;
    
    /**
     * Elements is group?
     * This variable uses to 'repeatedDiv'.
     * 
     * 'tpl.groupedElementDiv' => array(
     *     'groupedDiv' => '<div class="form-group">
     *         <label class="col-sm-2 control-label">%s</label>
     *         %s
     *     </div>',
     *     'repeatedDiv' => '<div class="col-sm-2">%s</div>'
     * )
     * 
     * @var boolean
     */
    public $isGroup = false;

    /**
     * Elements is grouped?
     * This variable uses to 'groupedDiv'.
     * 
     * 'tpl.groupedElementDiv' => array(
     *     'groupedDiv' => '<div class="form-group">
     *         <label class="col-sm-2 control-label">%s</label>
     *         %s
     *     </div>',
     *     'repeatedDiv' => '<div class="col-sm-2">%s</div>'
     * )
     * 
     * @var boolean
     */
    public $isGrouped = false;

    /**
     * Temporary data to group elements.
     * 
     * @var string
     */
    public $groupElementsTemp = '';

    /**
     * Form elements extra data
     * Array
     * (
     *     [label] => Username
     *     [type] => input
     *     [func] => 
     *     [group] => 0
     * )
     * 
     * @var array
     */
    public $fieldExtraData = array();

    /**
     * Form elements data
     * 
     * Array
     * (
     *     [name] => username
     *     [title] => Username
     *     [rules] => required|min(3)|max(15)
     *     [attribute] =>  id="username"
     *     [type] => input
     *     [value] =>
     * )
     * 
     * @var array
     */
    public $fieldData = array();

    /**
     * 'parentDiv' => '<div class="form-group col-sm-%d">%s</div>'
     * 
     * @var integer
     */
    public $colSm = 3;

    /**
     * Form html
     * 
     * @var string
     */
    public $form = '';

    /**
     * Submit button
     * 
     * @var string
     */
    public $submit = '';

    /**
     * Form open tag
     * 
     * @var string
     */
    public $open = '';

    /**
     * Close tag
     * 
     * @var string
     */
    public $close = '';

    /**
     * Constructor
     * 
     * @param array $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
        $this->formElement = $c->load('return form/element');
        $this->validator   = $c->load('return validator');
    }

    /**
     * Set submit
     * 
     * @param string $submit submit
     * 
     * @return void
     */
    public function setSubmit($submit)
    {
        $this->submit = $submit;
    }

    /**
     * Form is ajax?
     * 
     * @return boolean
     */
    public function isAjax()
    {
        return (boolean)$this->isAjax;
    }

    /**
     * Elements is group?
     * 
     * @return boolean
     */
    public function isGroup()
    {
        return (boolean)$this->isGroup;
    }

    /**
     * Elements is grouped?
     * 
     * @return boolean
     */
    public function isGrouped()
    {
        return (boolean)$this->isGrouped;
    }

    /**
     * Label is translate?
     * 
     * @return boolean
     */
    public function isTranslatorEnabled()
    {
        if (class_exists('\\Obullo\Translation\Translator', false) === true) {
            return true;
        }
        return false;
    }

    /**
     * Get group elements temporary data.
     * 
     * @return string
     */
    public function getGroupElementsTemp()
    {
        return $this->groupElementsTemp;
    }

    /**
     * Get field data
     * 
     * @return array
     */
    public function getFieldData()
    {
        return $this->fieldData;
    }

    /**
     * Get field extra data
     * 
     * @return array
     */
    public function getFieldExtraData()
    {
        return $this->fieldExtraData;
    }

    /**
     * Get col-sm
     * 
     * @return integer
     */
    public function getColSm()
    {
        return $this->colSm;
    }

    /**
     * Form open tag
     * 
     * @return html
     */
    public function open()
    {
        return $this->open;
    }

    /**
     * Get submit
     * 
     * @param boolean $isTemplate is template?
     * 
     * @return string
     */
    public function submit($isTemplate = true)
    {
        if ($isTemplate) {
            return $this->c->load('service/jelly/form')->getElementDiv($this->submit);
        }
        return $this->submit;
    }

    /**
     * Form close tag
     * 
     * @return html
     */
    public function close()
    {
        return $this->formElement->close();
    }

    /**
     * Render
     * 
     * @param array $data form data
     * 
     * @return string
     */
    public function render($data = array())
    {
        if ($this->c->load('service/jelly/form')->isAllowed() === false) {
            echo 'No permission or no data.';
            return;
        }
        $this->form = '';
        $attributes = ''; // reset attributes variable
        foreach ($data['form'] as $k => $v) {
            if ($k !== Form::FORM_ATTRIBUTE
                AND $k !== Form::FORM_ACTION
                AND $k !== Form::FORM_PRIMARY_KEY
                AND $k !== Form::FORM_ID
            ) {
                $attributes .= $k .'="'. $v .'" ';
            }
        }
        $attributes    .= 'id="'. $data['form'][Form::FORM_ID] . '"';
        $this->isAjax   = (isset($data['form']['ajax'])) ? $data['form']['ajax'] : 0;
        $this->formData = $data['form'];

        $this->open = $this->formElement->open($data['form'][Form::FORM_ACTION], $attributes . $data['form'][Form::FORM_ATTRIBUTE]);
        foreach ($data['elements'] as $val) {

            $this->fieldExtraData = $val['extra'];
            /**
             * $numberOfElements Total number of group elements
             * 
             * The "$numberOfElements" in form_groups
             * table database field : 'number_of_elements'.
             * 
             * @var integer
             */
            $numberOfElements = isset($this->fieldExtraData[Form::GROUP_NUMBER_OF_ELEMENTS]) ? (int)$this->fieldExtraData[Form::GROUP_NUMBER_OF_ELEMENTS] : 0;
            // resets
            $i = 0;
            $this->fieldData = array();
            $this->isGroup   = false;
            $this->isGrouped = false;
            $this->groupElementsTemp = '';
            foreach ($val['input'] as $v) {
                $i++;
                if (isset($v[Form::ELEMENT_TYPE]) AND ! empty($v[Form::ELEMENT_TYPE])) {
                    $attr = isset($v[Form::ELEMENT_ATTRIBUTE]) ? $v[Form::ELEMENT_ATTRIBUTE] : '';

                    $this->fieldData = $v;
                    $this->fieldData[Form::ELEMENT_ATTRIBUTE] = $attr;
                    $Element = 'Obullo\Jelly\View\Elements\\'. ucfirst($v[Form::ELEMENT_TYPE]); // E.g. Obullo\Jelly\View\Elements\Input
                    $this->element = new $Element($this->c, $v[Form::ELEMENT_NAME]);            // Initialized element class

                    if ($numberOfElements > 0) {        // If the input group
                        $this->isGroup = true;          // Set is group true

                        if ($v[Form::ELEMENT_TYPE] != 'radio') {
                            $this->colSm = ($numberOfElements === $i) ? ceil(3 / $numberOfElements) * 3 : 3;
                        }
                        $this->groupElementsTemp .= $this->element->render($this, $this->colSm);

                        if ($numberOfElements === $i) {  // $numberOfElements ve anahtar birbirine eşit ise
                                                         // biz bu inputların gruplama işleminin tamamladığını anlıyoruz.
                            $this->isGrouped = true;     // isGroup değişkenini "true" olarak set ederek render() 'ın
                                                         // ['tpl.groupedElementDiv']['groupedDiv'] kullanarak ana div içine almasını sağlıyoruz.
                            $this->form .= $this->element->render($this);
                        }

                    } else {
                        $this->form .= $this->element->render($this);
                    }
                }
            }
        }
    }

    /**
     * Create hidden input
     * 
     * @param string $name element name
     * 
     * @return html hidden input
     */
    public function createHiddenInput($name)
    {
        return $this->formElement->hidden($name);
    }

    /**
     * Form
     * 
     * @return string form
     */
    public function form()
    {
        return $this->form;
    }
}

// END View Class
/* End of file View.php */

/* Location: .Obullo/Jelly/View.php */