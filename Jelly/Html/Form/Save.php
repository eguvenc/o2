<?php

namespace Obullo\Jelly\Html\Form;

use Obullo\Jelly\Form,
    Obullo\Jelly\Html\Form\Menu;

/**
 * Form Save
 * 
 * @category  Jelly
 * @package   Html
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs
 */
Class Save
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
        $this->c = $c;
        $this->jellyForm   = $jellyForm;
        $this->formElement = $this->c->load('form/element');
        $this->validator   = $this->c->load('validator');
        $this->validator->setErrorDelimiters('<div style="color:red;">', '</div>');
    }

    /**
     * Create save form
     * 
     * @param string $action     form action
     * @param values $values     value of inputs
     * @param mixed  $attributes attributes
     * 
     * @return string
     */
    public function printSaveForm($action, $values = array(), $attributes = '')
    {
        $attr = $attributes; // temp attributes
        if (is_array($attributes)) {
            $attr = ''; // If attributes is array reset attr variable
            foreach ($attributes as $key => $val) {
                $attr .= $key .'="'. $val .'" ';
            }
        }
        $form  = '';
        $form .= $this->formElement->open($action, $attr); // Form start
        $formMenu = new Menu($this->c, $this->jellyForm);
        $form .= $formMenu->create(
            Form::FORM_METHOD,
            array(
                'POST' => 'POST',
                'GET'  => 'GET'
            ),
            'Method',
            '',
            'class="form-control"'
        );
        $form .= $this->createInputs($this->jellyForm->getDefaultFormAttributes(), array(), false);
        $form .= '<div class="col-sm-12">
                    <label class="label-control col-sm-3"></label>
                    <div class="form-group col-sm-9">
                        Do you want to add permission? <input type="checkbox" value="1" name="add_permission" onChange="addPermission(this)">
                    </div>
                </div>';
        $perms = $this->c->load('return service/rbac/perms');
        $option = array(0 => 'Select Permission');
        foreach ($perms->getPermissions() as $val) {
            $option[$val['rbac_permission_id']] = $val['rbac_permission_name'];
        }
        $form .= $formMenu->create(
            'permission_parent_id',
            $option,
            'Parent Permission',
            '',
            'class="form-control"'
        );
        $form .= $formMenu->create(
            'permission_type',
            array('object' => 'Object', 'page' => 'Page'),
            'Permission Type',
            '',
            'class="form-control"'
        );
        $form .= $this->createInputs($this->jellyForm->getDefaultPermissionAttributes());
        $form .= $this->formElement->close();

        return $form;
    }
    
    /**
     * Create element save form
     * 
     * @param string $action     form action
     * @param array  $values     value of inputs
     * @param mixed  $attributes attributes
     * @param string $selected   dropdown selected
     * 
     * @return string
     */
    public function printElementSaveForm($action, $values = array(), $attributes = '', $selected = '')
    {
        $attr = $attributes; // temp attributes
        if (is_array($attributes)) {
            $attr = ''; // If attributes is array reset attr variable
            foreach ($attributes as $key => $val) {
                $attr .= $key .'="'. $val .'" ';
            }
        }
        $formData = $this->jellyForm->getAllForms(array(Form::FORM_PRIMARY_KEY, Form::FORM_NAME));
        $forms = array('Select Form');
        foreach ($formData as $key => $val) {
            $forms[$val[Form::FORM_PRIMARY_KEY]] = $val[Form::FORM_NAME];
        }
        $groups = array('Select Group');
        $groupOrder = array();
        foreach ($this->jellyForm->getFormGroups($selected) as $v) {
            $groups[$v[Form::GROUP_PRIMARY_KEY]] = $v[Form::GROUP_NAME];
            $groupOrder[$v[Form::GROUP_PRIMARY_KEY]] = ' data="' . $v[Form::GROUP_ORDER] . '"';
        }
        $form  = '';
        $form .= $this->formElement->open($action, $attr); // Form start
        $formMenu = new Menu($this->c, $this->jellyForm);
        $form .= $formMenu->create(
            Form::ELEMENT_FORM_ID,
            $forms,
            'Forms',
            $selected,
            'class="form-control" onChange="this.options[this.selectedIndex].value && (window.location = \'/jelly/add_element/\' + this.options[this.selectedIndex].value);"'
        );
        $form .= $formMenu->create(
            Form::ELEMENT_TYPE,
            $this->jellyForm->getInputTypes(),
            'Input Type',
            '',
            'class="form-control" onChange="dropdownAction(this)"'
        );
        $form .= $formMenu->create(
            Form::ELEMENT_ROLE,
            $this->jellyForm->getInputRoles(),
            'Role',
            '',
            'class="form-control"'
        );
        $form .= $formMenu->create(
            Form::ELEMENT_GROUP_ID,
            $groups,
            'Group',
            '',
            'id="group_id" class="form-control" onChange="groupDropdown(this.options[this.selectedIndex])"',
            $groupOrder
        );
        $form .= $formMenu->create(
            Form::ELEMENT_VALUE_TYPE,
            $this->jellyForm->getValueTypes(),
            'Value Type',
            '',
            'class="form-control" onChange="valueTypeDropDown(this)"'
        );
        $form .= $formMenu->create(
            Form::ELEMENT_WIDGET,
            array('Select Widget', 'captcha' => 'Captcha', 'birthdate' => 'Birth Date', 'image' => 'Image'),
            'Widget',
            '',
            'class="form-control"'
        );
        $form .= $this->createInputs(array('group_order' => 'Group Order'), '', false, array('div' => 'id="groupOrder" style="display:none;"'));
        $form .= $this->createInputs(array('form_element_form_to_database' => 'Form to database'), '', false);
        $form .= $this->createInputs(array('form_element_database_to_form' => 'Database to form'), '', false);
        $form .= $this->createInputs($this->jellyForm->getDefaultElementAttributes(), $values, false);
        $form .= $this->createInputs(array('form_element_description' => 'Description'), '', false);
        $form .= '<div class="col-sm-12">
                    <label class="label-control col-sm-3"></label>
                    <div class="form-group col-sm-9">
                        Do you want to add permission? <input type="checkbox" value="1" onChange="addPermission(this);" name="add_permission">
                    </div>
                </div>';
        $perms = $this->c->load('return service/rbac/perms');
        $option = array(0 => 'Select Permission');
        foreach ($perms->getPermissions() as $val) {
            $option[$val['rbac_permission_id']] = $val['rbac_permission_name'];
        }
        $form .= $formMenu->create(
            'permission_parent_id',
            $option,
            'Parent Permission',
            '',
            'class="form-control"'
        );
        $form .= $formMenu->create(
            'permission_type',
            array('object' => 'Object', 'page' => 'Page'),
            'Permission Type',
            '',
            'class="form-control"'
        );
        $form .= $this->createInputs($this->jellyForm->getDefaultPermissionAttributes());
        $form .= $this->formElement->close();

        return $form;
    }

    /**
     * Create group save form
     * 
     * @param string $action     form action
     * @param array  $values     value of inputs
     * @param mixed  $attributes attributes
     * @param string $selected   dropdown selected
     * 
     * @return string
     */
    public function printGroupSaveForm($action, $values = array(), $attributes = '', $selected = '')
    {
        $attr = $attributes; // temp attributes
        if (is_array($attributes)) {
            $attr = ''; // If attributes is array reset attr variable
            foreach ($attributes as $key => $val) {
                $attr .= $key .'="'. $val .'" ';
            }
        }
        $formData = $this->jellyForm->getAllForms(array(Form::FORM_PRIMARY_KEY, Form::FORM_NAME));
        $forms = array('Select Form');
        foreach ($formData as $key => $val) {
            $forms[$val[Form::FORM_PRIMARY_KEY]] = $val[Form::FORM_NAME];
        }
        $form  = '';
        $form .= $this->formElement->open($action, $attr); // Form start
        $formMenu = new Menu($this->c, $this->jellyForm);
        $form .= $formMenu->create(
            Form::GROUP_FORM_ID,
            $forms,
            'Forms',
            $selected,
            'class="form-control" onChange="this.options[this.selectedIndex].value && (window.location = \'/jelly/add_group/\' + this.options[this.selectedIndex].value);"'
        );
        $form .= $formMenu->create(
            Form::GROUP_WIDGET,
            array('Select Widget', 'birthdate' => 'Birth Date', 'image' => 'Image'),
            'Widget',
            '',
            'class="form-control"'
        );
        $form .= $this->createInputs($this->jellyForm->getDefaultGroupAttributes(), $values);
        $form .= $this->formElement->close();

        return $form;
    }

    /**
     * Create option save form
     * 
     * @param string $action     form action
     * @param array  $values     value of inputs
     * @param mixed  $attributes attributes
     * @param string $selected   dropdown selected
     * 
     * @return string
     */
    public function printOptionSaveForm($action, $values = array(), $attributes = '', $selected = '')
    {
        $attr = $attributes; // temp attributes
        if (is_array($attributes)) {
            $attr = ''; // If attributes is array reset attr variable
            foreach ($attributes as $key => $val) {
                $attr .= $key .'="'. $val .'" ';
            }
        }
        $formData = $this->jellyForm->getAllForms(array(Form::FORM_PRIMARY_KEY, Form::FORM_NAME));
        $forms = array('Select Form');
        foreach ($formData as $key => $val) {
            $forms[$val[Form::FORM_PRIMARY_KEY]] = $val[Form::FORM_NAME];
        }
        $form  = '';
        $form .= $this->formElement->open($action, $attr); // Form start
        $formMenu = new Menu($this->c, $this->jellyForm);
        $form .= $formMenu->create(
            Form::OPTION_FORM_ID,
            $forms,
            'Forms',
            $selected,
            'class="form-control" onChange="this.options[this.selectedIndex].value && (window.location = \'/jelly/add_option/\' + this.options[this.selectedIndex].value);"'
        );
        $form .= $formMenu->create(
            Form::OPTION_NAME,
            $this->jellyForm->getOptionTypes(),
            'Option',
            '',
            'class="form-control"'
        );
        $form .= $this->createInputs($this->jellyForm->getDefaultOptionAttributes(), $values);
        $form .= $this->formElement->close();

        return $form;
    }

    /**
     * Create edit form
     * 
     * @param string $action     form action
     * @param values $values     value of inputs
     * @param mixed  $attributes attributes
     * 
     * @return string
     */
    public function printEditForm($action, $values = array(), $attributes = '')
    {
        $attr = $attributes; // temp attributes
        if (is_array($attributes)) {
            $attr = ''; // If attributes is array reset attr variable
            foreach ($attributes as $key => $val) {
                $attr .= $key .'="'. $val .'" ';
            }
        }
        $form  = '';
        $form .= $this->formElement->open($action, $attr); // Form start
        $formMenu = new Menu($this->c, $this->jellyForm);
        $form .= $formMenu->create(
            Form::FORM_METHOD,
            array(
                'POST' => 'POST',
                'GET' => 'GET'
            ),
            'Method',
            $values[Form::FORM_METHOD],
            'class="form-control"'
        );
        $form .= $this->createInputs($this->jellyForm->getDefaultFormAttributes(), $values);
        $form .= $this->formElement->close();

        return $form;
    }

    /**
     * Create element edit form
     * 
     * @param string $action     form action
     * @param array  $values     value of inputs
     * @param mixed  $attributes attributes
     * @param string $selected   dropdown selected
     * 
     * @return string
     */
    public function printElementEditForm($action, $values = array(), $attributes = '', $selected = '')
    {
        $attr = $attributes; // temp attributes
        if (is_array($attributes)) {
            $attr = ''; // If attributes is array reset attr variable
            foreach ($attributes as $key => $val) {
                $attr .= $key .'="'. $val .'" ';
            }
        }
        $formData = $this->jellyForm->getAllForms(array(Form::FORM_PRIMARY_KEY, Form::FORM_NAME));
        $forms = array('Select Form');
        foreach ($formData as $key => $val) {
            $forms[$val[Form::FORM_PRIMARY_KEY]] = $val[Form::FORM_NAME];
        }
        $groups = array('Select Group');
        $groupOrder = 0;
        foreach ($this->jellyForm->getFormGroups($selected) as $v) {
            $groups[$v[Form::GROUP_PRIMARY_KEY]] = $v[Form::GROUP_NAME];
            $groupOrder = $v[Form::GROUP_ORDER];
        }
        $form  = '';
        $form .= $this->formElement->open($action, $attr); // Form start
        $formMenu = new Menu($this->c, $this->jellyForm);
        $form .= $formMenu->create(
            Form::ELEMENT_FORM_ID,
            $forms,
            'Forms',
            $selected,
            'class="form-control"'
        );
        $form .= $formMenu->create(
            Form::ELEMENT_TYPE,
            $this->jellyForm->getInputTypes(),
            'Type',
            $values[Form::ELEMENT_TYPE],
            'class="form-control" onChange="dropdownAction(this)"'
        );
        $form .= $formMenu->create(
            Form::ELEMENT_ROLE,
            $this->jellyForm->getInputRoles(),
            'Roles',
            $values[Form::ELEMENT_ROLE],
            'class="form-control" onChange="dropdownAction(this)"'
        );
        $form .= $formMenu->create(
            Form::ELEMENT_GROUP_ID,
            $groups,
            'Groups',
            $values[Form::ELEMENT_GROUP_ID],
            'id="group_id" class="form-control" onChange="groupDropdown(this)" data="'. $groupOrder .'"'
        );
        $form .= $formMenu->create(
            Form::ELEMENT_VALUE_TYPE,
            $this->jellyForm->getValueTypes(),
            'Value Type',
            $values[Form::ELEMENT_VALUE_TYPE],
            'id="group_id" class="form-control" onChange="valueDropdown(this)" data="'. $groupOrder .'"'
        );
        $form .= $formMenu->create(
            Form::ELEMENT_WIDGET,
            array('Select Widget', 'captcha' => 'Captcha', 'birthdate' => 'Birth Date', 'image' => 'Image'),
            'Widget',
            $values[Form::ELEMENT_WIDGET],
            'class="form-control"'
        );
        $form .= $this->createInputs(array('group_order' => 'Group Order'), '', false, array('div' => 'id="groupOrder" style="display:none;"'));
        $form .= $this->createInputs(array('form_element_form_to_database' => 'Form to database'), $values, false);
        $form .= $this->createInputs(array('form_element_database_to_form' => 'Database to form'), $values, false);
        $form .= $this->createInputs($this->jellyForm->getDefaultElementAttributes(), $values, false);
        $form .= $this->createInputs(array('form_element_description' => 'Description'), $values);
        $form .= $this->formElement->close();

        return $form;
    }

    /**
     * Create option save form
     * 
     * @param string $action     form action
     * @param array  $values     value of inputs
     * @param mixed  $attributes attributes
     * @param string $selected   dropdown selected
     * 
     * @return string
     */ 
    public function printOptionEditForm($action, $values = array(), $attributes = '', $selected = '')
    {
        $attr = $attributes; // temp attributes
        if (is_array($attributes)) {
            $attr = ''; // If attributes is array reset attr variable
            foreach ($attributes as $key => $val) {
                $attr .= $key .'="'. $val .'" ';
            }
        }
        $formData = $this->jellyForm->getAllForms(array(Form::FORM_PRIMARY_KEY, Form::FORM_NAME));
        $forms = array('Select Form');
        foreach ($formData as $key => $val) {
            $forms[$val[Form::FORM_PRIMARY_KEY]] = $val[Form::FORM_NAME];
        }
        $form  = '';
        $form .= $this->formElement->open($action, $attr); // Form start
        $formMenu = new Menu($this->c, $this->jellyForm);
        $form .= $formMenu->create(
            Form::OPTION_FORM_ID,
            $forms,
            'Forms',
            $selected,
            'class="form-control"'
        );
        $form .= $formMenu->create(
            Form::OPTION_NAME,
            $this->jellyForm->getOptionTypes(),
            'Option',
            $selected,
            'class="form-control"'
        );
        $form .= $this->createInputs($this->jellyForm->getDefaultOptionAttributes(), $values);
        $form .= $this->formElement->close();

        return $form;
    }

    /**
     * Create group edit form
     * 
     * @param string $action     form action
     * @param array  $values     value of inputs
     * @param mixed  $attributes attributes
     * @param string $selected   dropdown selected
     * 
     * @return string
     */ 
    public function printGroupEditForm($action, $values = array(), $attributes = '', $selected = '')
    {
        $attr = $attributes; // temp attributes
        if (is_array($attributes)) {
            $attr = ''; // If attributes is array reset attr variable
            foreach ($attributes as $key => $val) {
                $attr .= $key .'="'. $val .'" ';
            }
        }
        $formData = $this->jellyForm->getAllForms(array(Form::FORM_PRIMARY_KEY, Form::FORM_NAME));
        $forms = array('Select Form');
        foreach ($formData as $key => $val) {
            $forms[$val[Form::FORM_PRIMARY_KEY]] = $val[Form::FORM_NAME];
        }
        $form  = '';
        $form .= $this->formElement->open($action, $attr); // Form start
        $formMenu = new Menu($this->c, $this->jellyForm);
        $form .= $formMenu->create(
            Form::GROUP_FORM_ID,
            $forms,
            'Forms',
            $selected,
            'class="form-control"'
        );
        $form .= $formMenu->create(
            Form::GROUP_WIDGET,
            array('Select Widget', 'birthdate' => 'Birth Date', 'image' => 'Image'),
            'Widget',
            $values[Form::GROUP_WIDGET],
            'class="form-control"'
        );
        $form .= $this->createInputs($this->jellyForm->getDefaultGroupAttributes(), $values);
        $form .= $this->formElement->close();

        return $form;
    }

    /**
     * Create inputs
     * 
     * @param array  $inputs    inputs data
     * @param array  $values    inputs elements values
     * @param bool   $submit    submit
     * @param string $attribute attribute
     * 
     * @return string
     */
    public function createInputs($inputs = array(), $values = array(), $submit = true, $attribute = array())
    {
        $attribute['div']   = (isset($attribute['div'])) ? $attribute['div'] : '';
        $attribute['input'] = (isset($attribute['input'])) ? $attribute['input'] : '';
        $post = $this->c->load('return post');
        $input   = '';
        foreach ($inputs as $key => $val) {
            $value = '';
            if (isset($post[$key])) {
                $value = $post[$key];
            } elseif (isset($values[$key])) {
                $value = $values[$key];
            }
            $labelName = ucfirst($val);
            $element   = $this->validator->getError($key);
            $element  .= $this->formElement->input($key, $value, ' class="form-control" ' . $attribute['input']);
            $input    .= $this->jellyForm->getElementDiv($element, $labelName, $attribute['div']);
        }
        if ($submit) {
            $element = $this->formElement->submit('submit', 'Submit', 'class="btn btn-info"');
            $input  .= $this->jellyForm->getElementDiv($element, '');
        }
        return $input;
    }

}

// END FormSave Class
/* End of file FormSave.php */

/* Location: .Obullo/Jelly/FormSave.php */