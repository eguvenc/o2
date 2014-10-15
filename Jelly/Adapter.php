<?php

namespace Obullo\Jelly;

use RunTimeException,
    Controller,
    Closure,
    Obullo\Permissions\Rbac\User,
    Obullo\Jelly\View\View;

/**
 * Jelly
 * 
 * @category  Jelly
 * @package   Adapter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs
 */
Class Adapter
{
    /**
     * Form constants
     */
    const FORM_PRIMARY_KEY = 'form_id';
    const FORM_ID          = 'form_attr_id';
    const FORM_NAME        = 'form_attr_name';
    const FORM_RESOURCE_ID = 'form_resource_id';
    const FORM_ACTION      = 'form_attr_action';
    const FORM_METHOD      = 'form_attr_method';
    const FORM_ATTRIBUTE   = 'form_attr_extra';
    
    /**
     * Form element constants
     */
    const ELEMENT_PRIMARY_KEY      = 'form_element_id';
    const ELEMENT_FORM_ID          = 'forms_form_id';
    const ELEMENT_GROUP_ID         = 'form_groups_form_group_id';
    const ELEMENT_TYPE             = 'form_element_type';
    const ELEMENT_LABEL            = 'form_element_label';
    const ELEMENT_NAME             = 'form_element_name';
    const ELEMENT_VALUE_TYPE       = 'form_element_value_type';
    const ELEMENT_VALUE            = 'form_element_value';
    const ELEMENT_ATTRIBUTE        = 'form_element_attribute';
    const ELEMENT_TITLE            = 'form_element_title';
    const ELEMENT_RULES            = 'form_element_rules';
    const ELEMENT_CLASS            = 'form_element_class';
    const ELEMENT_ORDER            = 'form_element_order';
    const ELEMENT_ROLE             = 'form_element_role';
    const ELEMENT_WIDGET           = 'form_element_widget';
    const ELEMENT_DESCRIPTION      = 'form_element_description';
    const ELEMENT_DATABASE_TO_FORM = 'form_element_database_to_form';
    const ELEMENT_FORM_TO_DATABASE = 'form_element_form_to_database';

    /**
     * Form option constants
     */
    const OPTION_PRIMARY_KEY = 'form_option_id';
    const OPTION_FORM_ID     = 'forms_form_id';
    const OPTION_NAME        = 'form_option_name';
    const OPTION_VALUE       = 'form_option_value';

    /**
     * Form group constants
     */
    const GROUP_PRIMARY_KEY        = 'form_group_id';
    const GROUP_FORM_ID            = 'forms_form_id';
    const GROUP_NAME               = 'form_group_name';
    const GROUP_LABEL              = 'form_group_label';
    const GROUP_VALUE              = 'form_group_value';
    const GROUP_CLASS              = 'form_group_class';
    const GROUP_WIDGET             = 'form_group_widget';
    const GROUP_FORM_TO_DATABASE   = 'form_group_form_to_database';
    const GROUP_DATABASE_TO_FORM   = 'form_group_database_to_form';
    const GROUP_ORDER              = 'form_group_order';
    const GROUP_NUMBER_OF_ELEMENTS = 'form_group_number_of_elements';
    const GROUP_DESCRIPTION        = 'form_group_description';

    /**
     * Cache ( Redis ) constants
     */
    const CACHE_FORM            = 'jellyForm:form:';
    const CACHE_ALL_FORMS       = 'jellyForm:allForms:';
    const CACHE_FORM_ATTRIBUTES = 'jellyForm:formAttributes:';
    const CACHE_FORM_ELEMENT    = 'jellyForm:formElement:';
    const CACHE_FORM_ELEMENTS   = 'jellyForm:formElements:';
    const CACHE_ELEMENT_GROUP   = 'jellyForm:elementGroup:';
    const CACHE_ELEMENT_GROUPS  = 'jellyForm:elementGroups:';
    const CACHE_FORM_OPTION     = 'jellyForm:formOption:';
    const CACHE_FORM_OPTIONS    = 'jellyForm:formOptions:';

    /**
     * Append data
     * 
     * @var array
     */
    public $data = array();

    /**
     * Append data key
     * 
     * @var string
     */
    public $dataKey = '';

    /**
     * Values
     * 
     * @var array
     */
    public $values = array();

    /**
     * Order input
     * 
     * @var integer
     */
    public $order = 0;

    /**
     * Form identifier
     * 
     * @var string
     */
    public $formIdentifier = '';

    /**
     * Form id
     * 
     * @var integer
     */
    public $formId = 0;

    /**
     * Attributes
     * 
     * @var array
     */
    public $attributes = array();

    /**
     * Array data
     * 
     * @var array
     */
    public $builtArray = array();

    /**
     * Save values
     * 
     * @var array
     */
    public $saveValues = array();

    /**
     * Description div
     * 
     * @var string
     */
    public $descriptionDiv = '';

    /**
     * Form element div
     * 
     * @var string
     */
    public $elementDiv = '';

    /**
     * Form element group div
     * 
     * @var string
     */
    public $groupDiv = '';

    /**
     * Form element group parent div
     * 
     * @var string
     */
    public $groupParentDiv = '';

    /**
     * Group parent label
     * 
     * @var string
     */
    public $groupParentLabel = '';

    /**
     * Operation name
     * 
     * Operations:
     * -----------
     * 1. view // Don't send view.
     * 2. update
     * 3. delete
     * 4. insert
     * 5. save // Both update and insert process.
     * 
     * @var string
     */
    public $operationName = '';

    /**
     * Element attributes
     * 
     * @var array
     */
    protected $elementAttributes = array(
        self::ELEMENT_NAME       => 'Name',
        self::ELEMENT_LABEL      => 'Label',
        self::ELEMENT_VALUE      => 'Value',
        self::ELEMENT_ATTRIBUTE  => 'Attribute',
        self::ELEMENT_TITLE      => 'Title',
        self::ELEMENT_RULES      => 'Rules',
        self::ELEMENT_ORDER      => 'Order',
    );

    /**
     * Form attributes
     * 
     * @var array
     */
    protected $formAttributes = array(
        self::FORM_NAME        => 'Name',
        self::FORM_ID          => 'Form ID',
        self::FORM_RESOURCE_ID => 'Resource ID',
        self::FORM_ACTION      => 'Form Action',
        self::FORM_ATTRIBUTE   => 'Attribute'
    );

    /**
     * Form attributes
     * 
     * @var array
     */
    protected $optionAttributes = array(
        self::OPTION_VALUE => 'Value'
    );

    /**
     * Form attributes
     * 
     * @var array
     */
    protected $groupAttributes = array(
        self::GROUP_NAME             => 'Name',
        self::GROUP_LABEL            => 'Label',
        self::GROUP_CLASS            => 'Class',
        self::GROUP_VALUE            => 'Value',
        self::GROUP_FORM_TO_DATABASE => 'Form to database (Func)',
        self::GROUP_DATABASE_TO_FORM => 'Database to form (Func)',
        self::GROUP_DESCRIPTION      => 'Description',
        self::GROUP_ORDER            => 'Order'
    );

    /**
     * Input types
     * 
     * @var array
     */
    protected $inputTypes = array(
        'input'    => 'Text',
        'checkbox' => 'Checkbox',
        'email'    => 'Email',
        'upload'   => 'File',
        'hidden'   => 'Hidden',
        'image'    => 'Image',
        'password' => 'Password',
        'radio'    => 'Radio',
        'reset'    => 'Reset',
        'submit'   => 'Submit',
        'button'   => 'Button',
        'textarea' => 'Textarea',
        'dropdown' => 'Dropdown',
        'captcha'  => 'Captcha'
    );

    /**
     * Input types
     * 
     * @var array
     */
    protected $inputRoles = array(
        'input'  => 'input',
        'field'  => 'field',
        'widget' => 'widget',
    );

    /**
     * Option types
     * 
     * @var array
     */
    protected $optionTypes = array(
        'ajax' => 'Ajax',
    );

    /**
     * Is print form called?
     * 
     * @var boolean
     */
    public $isPrintOpen = false;

    /**
     * Is allowed
     * 
     * @var boolean
     */
    public $isAllowed = true;

    /**
     * Form values
     * 
     * @var array
     */
    public $formValues = array();

    /**
     * Group data
     * 
     * @var array
     */
    public $groupData = array();

    /**
     * Append attribute
     * 
     * @var array
     */
    public $appendAttributes = array(
        'name'        => self::ELEMENT_NAME,
        'group_id'    => self::ELEMENT_GROUP_ID,
        'type'        => self::ELEMENT_TYPE,
        'rules'       => self::ELEMENT_RULES,
        'role'        => self::ELEMENT_ROLE,
        'value_type'  => self::ELEMENT_VALUE_TYPE,
        'value'       => self::ELEMENT_VALUE,
        'title'       => self::ELEMENT_TITLE,
        'label'       => self::ELEMENT_LABEL,
        'attribute'   => self::ELEMENT_ATTRIBUTE,
        'description' => self::ELEMENT_DESCRIPTION,
        'order'       => self::ELEMENT_ORDER,
    );

    public $permissionAttributes = array(
        'permission_name'        => 'Permission Name',
        'permission_resource_id' => 'Resource ID',
    );

    /**
     * Value types
     * 
     * @var array
     */
    public $valueTypes = array(
        'string'  => 'String',
        'json'    => 'Json',
        'closure' => 'Closure'
    );

    /**
     * Number of elements
     * 
     * @var integer
     */
    public $numberOfElements = 0;

    /**
     * Group attributes names
     * 
     * @var array
     */
    public $groupAttributeNames = array();

    /**
     * Group closure data
     * 
     * @var array
     */
    public $groupClosureData = array();

    /**
     * Is group?
     * 
     * @var boolean
     */
    public $isGroup = false;

    /**
     * Group order
     * 
     * @var integer
     */
    public $increaseOrder = 0;

    /**
     * Is rules
     * 
     * @var boolean
     */
    public $isRules = false;

    /**
     * Rules data
     * 
     * @var array
     */
    public $rules = array();

    /**
     * Is hidden input
     * 
     * @var boolean
     */
    public $isGroupHidden = true;

    /**
     * Constructor
     * 
     * @param array $c      container
     * @param array $params connection parameters
     */
    public function __construct($c, $params = array())
    {
        $this->c = $c;
        $this->view   = new View($this->c);
        $this->user   = $this->c->load('service/rbac/user');
        $this->logger = $this->c->load('service/logger');
        $this->params = $params;
        
        $this->setElementDiv($params['tpl.elementDiv']);
        $this->setDescriptionDiv($params['tpl.descriptionDiv']);
        $this->setGroupDiv($params['tpl.groupElementDiv']['groupedDiv']);
        $this->setGroupParentDiv($params['tpl.groupElementDiv']['parentDiv']);
        $this->setGroupParentLabel($params['tpl.groupElementDiv']['parentLabel']);
    }

    /**
     * Set form
     * 
     * @param array $values form values
     * 
     * @return void
     */
    public function setValues($values = array())
    {
        $this->formValues = $values;
    }

    /**
     * Set rules
     * 
     * @param string $field input field name
     * @param string $label input label name
     * @param string $rules input rules
     * 
     * @return void
     */
    public function setRules($field, $label = '', $rules = '')
    {
        $this->isRules = true;
        $this->c->load('validator')->setRules($field, $label, $rules);
    }

    /**
     * Set id
     * 
     * @param string $id form identifier
     * 
     * @return void
     */
    public function setId($id)
    {
        $this->formIdentifier = $id;
    }

    /**
     * Set save values
     * 
     * @param array $values values data
     * 
     * @see getValues() function
     * 
     * @return void
     */
    protected function setFormValues($values = array())
    {
        $this->saveValues = $values;
    }

    /**
     * Set element div
     * 
     * @param string $element element div
     * 
     * @return void
     */
    public function setElementDiv($element)
    {
        $this->elementDiv = $element;
    }

    /**
     * Set description div
     * 
     * @param string $desc description div
     * 
     * @return void
     */
    public function setDescriptionDiv($desc)
    {
        $this->descriptionDiv = $desc;
    }

    /**
     * Set group div
     * 
     * @param string $groupDiv group div
     * 
     * @return void
     */
    public function setGroupDiv($groupDiv)
    {
        $this->groupDiv = $groupDiv;
    }

    /**
     * Set group parent div
     * 
     * @param string $parentDiv group parent div
     * 
     * @return void
     */
    public function setGroupParentDiv($parentDiv)
    {
        $this->groupParentDiv = $parentDiv;
    }

    /**
     * Set group parent div
     * 
     * @param string $parentLabel group parent label
     * 
     * @return void
     */
    public function setGroupParentLabel($parentLabel)
    {
        $this->groupParentLabel = $parentLabel;
    }

    /**
     * Get form identifier
     * 
     * @return string
     */
    public function getId()
    {
        return $this->formIdentifier;
    }

    /**
     * Is group hidden
     * 
     * @param boolean $groupHidden group hidden
     * 
     * @return boolean
     */
    public function isGroupHidden($groupHidden = true)
    {
        return $this->isGroupHidden = (boolean)$groupHidden;
    }

    /**
     * Get Post Values
     * 
     * @return array
     */
    public function getPostValues()
    {
        $post = $this->c->load('post');
        $formData      = $this->getFormAttributes();
        $formElements  = $this->getFormElements($formData[static::FORM_PRIMARY_KEY], array(static::ELEMENT_NAME, static::ELEMENT_GROUP_ID, static::ELEMENT_ROLE));
        $formValues    = array();
        $groupElements = array();
        foreach ($formElements as $val) {
            if ($val[static::ELEMENT_GROUP_ID] > 0) {
                $groupElements = $this->getFormGroup($val[static::ELEMENT_GROUP_ID], array(static::GROUP_PRIMARY_KEY, static::GROUP_NAME));
            }
            if (isset($post[$val[static::ELEMENT_NAME]])) {
                $formValues[$val[static::ELEMENT_NAME]] = $post[$val[static::ELEMENT_NAME]];
            } elseif (isset($this->formValues[$val[static::ELEMENT_NAME]])) {
                $formValues[$val[static::ELEMENT_NAME]] = $this->formValues[$val[static::ELEMENT_NAME]];
            } elseif ($val[static::ELEMENT_ROLE] == 'input'
                AND isset($groupElements[static::GROUP_PRIMARY_KEY])
                AND $val[static::ELEMENT_GROUP_ID] == $groupElements[static::GROUP_PRIMARY_KEY]
            ) {
                $formValues[$groupElements[static::GROUP_NAME]] = isset($this->formValues[$groupElements[static::GROUP_NAME]]) ? $this->formValues[$groupElements[static::GROUP_NAME]] : '';
            }
        }
        return $formValues;
    }

    /**
     * Input template
     * 
     * @param string $desc description
     * 
     * @return string template
     */
    public function getDescriptionDiv($desc)
    {
        return sprintf($this->descriptionDiv, $desc);
    }

    /**
     * Input template
     * 
     * @param string $element   element data
     * @param string $labelName label name
     * @param string $attribute attribute
     * 
     * @return string template
     */
    public function getElementDiv($element, $labelName = '', $attribute = '')
    {
        return sprintf($this->elementDiv, $attribute, $labelName, $element);
    }

    /**
     * Group template
     * 
     * @param string $element   element data
     * @param string $name      element name
     * @param string $labelName label name
     * 
     * @return string template
     */
    public function getGroupDiv($element, $name, $labelName = '')
    {
        return sprintf($this->groupDiv, $name, $labelName, $element);
    }

    /**
     * Group template
     * 
     * @return string template
     */
    public function getGroupParentLabel()
    {
        return $this->groupParentLabel;
    }

    /**
     * Group parent template
     * 
     * @param string $element   element data
     * @param string $colSm     col-sm
     * @param string $labelName label name
     * 
     * @return string template
     */
    public function getGroupParentDiv($element, $colSm = 3, $labelName = '')
    {
        $groupParentDiv = $this->groupParentDiv;
        if (strpos($this->groupParentDiv, '##label##') !== false) {
            $label = '%s';
            if ( ! empty($labelName)) {
                $colSm = 2;
                $label = $this->getGroupParentLabel();
            }
            $groupParentDiv = str_replace('##label##', $label, $this->groupParentDiv);
        }
        return sprintf($groupParentDiv, $labelName, $colSm, $element);
    }

    /**
     * Get save values
     * 
     * $this->db->where('user_id', $userId = 1);
     * $this->db->update('users', $this->jellyForm->getValues());
     * 
     * @return array
     */
    public function getValues()
    {
        return $this->saveValues;
    }

    /**
     * Get value db enum types.
     * array(
     *     'string' => 'String',
     *     'closure' => 'Closure'
     * )
     * 
     * @return array
     */
    public function getValueTypes()
    {
        return $this->valueTypes;
    }

    /**
     * Get element attributes
     * 
     * @return array
     */
    public function getDefaultFormAttributes()
    {
        return $this->formAttributes;
    }

    /**
     * Get element attributes
     * 
     * @return array
     */
    public function getDefaultElementAttributes()
    {
        return $this->elementAttributes;
    }

    /**
     * Get option attributes
     * 
     * @return array
     */
    public function getDefaultOptionAttributes()
    {
        return $this->optionAttributes;
    }

    /**
     * Get group attributes
     * 
     * @return array
     */
    public function getDefaultGroupAttributes()
    {
        return $this->groupAttributes;
    }

    /**
     * Get default append attributes
     * 
     * @return array
     */
    public function getDefaultAppendAttributes()
    {
        return $this->appendAttributes;
    }

    /**
     * Get default permission attributes
     * 
     * @return array
     */
    public function getDefaultPermissionAttributes()
    {
        return $this->permissionAttributes;
    }

    /**
     * Get input types
     * 
     * @return array
     */
    public function getInputTypes()
    {
        return $this->inputTypes;
    }

    /**
     * Get input roles
     * 
     * @return array
     */
    public function getInputRoles()
    {
        return $this->inputRoles;
    }

    /**
     * Get options
     * 
     * @return array
     */
    public function getOptionTypes()
    {
        return $this->optionTypes;
    }

    /**
     * Get form names
     * 
     * @param array $key  name key
     * @param array $data form data
     * 
     * @return array
     */
    public function getFormNames($key, $data)
    {
        $value = array();
        foreach ($data as $val) {
            $value[] = $val[$key];
        }
        return $value;
    }

    /**
     * Sort array
     * 
     * @param array $source source data
     * @param array $target target data
     * 
     * @return array
     */
    public function sort($source, $target)
    {
        if (count($target) === 0) {
            $this->isAllowed = false;
            // $this->logger->channel('jellyForm');
            // $this->logger->notice(
            //     $notice,
            //     array(
            //         'form'      => $formData[static::FORM_NAME],
            //         'operation' => $operationName,
            //         'field'     => $key,
            //         'data'      => $data,
            //     )
            // );
            return array('no permission');
        }
        $groupData = $this->getFormGroups($source['form'][static::FORM_PRIMARY_KEY], '*');
        foreach ($target as $val) {

            $order = $val[static::ELEMENT_ORDER];
            if (isset($this->data['override'][$val[static::ELEMENT_NAME]])) {
                foreach ($this->data['override'][$val[static::ELEMENT_NAME]] as $key => $override) {
                    $val[$key] = $override;
                }
            }
            if ($val[static::ELEMENT_GROUP_ID] > 0) {
                $elementsOfGroup = $this->getElementsOfGroup($groupData, $val[static::ELEMENT_GROUP_ID]);
                $order = $elementsOfGroup[static::GROUP_ORDER];
                $increasedOrder = $this->increaseAppendOrder($val[static::ELEMENT_NAME], $order);
                $values[$increasedOrder][] = $val;
            } else {
                $increasedOrder = $this->increaseAppendOrder($val[static::ELEMENT_NAME], $order);
                $values[$increasedOrder] = $val;
            }
        }
        $i = 0;
        ksort($values);
        foreach ($values as $elements) {
            if ( ! isset($elements[static::ELEMENT_ORDER])) {
                foreach ($elements as $v) {
                    $i++;
                    $newValues['elements'][$i] = $v;
                }
            } else {
                $i++;
                $newValues['elements'][$i] = $elements;
            }
        }
        $groups['groups'] = $groupData;
        return array_merge($source, $newValues, $groups);
    }

    /**
     * To json
     * 
     * @param array $data array data
     * 
     * @return json
     */
    public function toJson($data = null)
    {
        if (is_array($data)) {
            return json_encode($data);
        }
        return $data;
    }

    /**
     * To array
     * 
     * @param json    $data     json data
     * @param boolean $jsonType json type
     * 
     * @return If type of data is json return it as json type.
     */
    public function toArray($data = null, $jsonType = true)
    {
        if ($data == null) {
            return array();
        }
        if (is_array($data)) {
            return $data;
        }
        if (json_decode($data, $jsonType) !== null) {
            return json_decode($data, $jsonType);
        }
        if ($decoded = base64_decode($data)) {
            $data = $decoded;
        }
        return json_decode($data, $jsonType);
    }

    /**
     * Set option
     * 
     * @param array $option option data
     * @param array $form   form data
     * 
     * @return array
     */
    public function setOption($option, $form)
    {
        $values = array();
        foreach ($option as $val) {
            $values[$val[static::OPTION_NAME]] = $val[static::OPTION_VALUE];
        }
        return array_merge($form, $values);
    }

    /**
     * override data
     * 
     * @param int   $order order
     * @param array $data  override data
     * 
     * @return void
     */
    public function override($order, $data = array())
    {
        if ( ! isset($data['name'])) {
            throw new RunTimeException('Required: "name"');
        }
        foreach ($this->getDefaultAppendAttributes() as $key => $val) {
            if (isset($data[$key])) {
                $this->data['override'][$data['name']][$val] = $data[$key];
            }
        }
        $this->data['override'][$data['name']][static::ELEMENT_ORDER]    = $order;
        $this->data['override'][$data['name']][static::ELEMENT_GROUP_ID] = (isset($data['group_id'])) ? (string)$data['group_id'] : (string)0;
    }

    /**
     * Append data
     * 
     * @param int   $order order
     * @param array $data  append data
     * 
     * @return void
     */
    public function append($order, $data = array())
    {
        foreach ($this->getDefaultAppendAttributes() as $key => $val) {
            if (isset($data[$key])) {
                $this->data['append'][$data['name']][$val] = $data[$key];
            } else {
                $this->data['append'][$data['name']][$val] = '';
            }
        }
        $this->data['append'][$data['name']][static::ELEMENT_ORDER]    = (int)$order;
        $this->data['append'][$data['name']][static::ELEMENT_GROUP_ID] = (isset($data[static::ELEMENT_GROUP_ID])) ? $data[static::ELEMENT_GROUP_ID] : 0;
    }

    /**
     * If we have append request to order (5) it goes to (6) and other orders increased by 1.
     * We increase order numbers by one by.
     * 
     * @param string $elementName element name
     * @param int    $order       element order
     * 
     * @return int
     */
    public function increaseAppendOrder($elementName, $order)
    {
        if (isset($this->data['append']) AND ! isset($this->data['append'][$elementName])) {
            foreach ($this->data['append'] as $v) {
                if ($order >= $v[static::ELEMENT_ORDER]) {
                    $order++;
                }
            }
        }
        return $order;
    }

    /**
     * Append form data
     * 
     * @param array $formData form data
     * 
     * @return void
     */
    public function appendFormData($formData)
    {
        ksort($this->values['elements']);
        $keys  = array_keys($this->values['elements']);
        $order = end($keys) + 1;
        $this->values['elements'][$order]['input'][$this->order][static::ELEMENT_NAME]  = 'form_data';
        $this->values['elements'][$order]['input'][$this->order][static::ELEMENT_TYPE]  = 'hidden';
        $this->values['elements'][$order]['input'][$this->order][static::ELEMENT_VALUE] = base64_encode($this->toJson($formData));
        $this->values['elements'][$order]['extra'][static::ELEMENT_TYPE] = 'hidden';

        if (count($this->groupClosureData) > 0) {
            foreach ($this->groupClosureData as $key => $val) {
                foreach ($this->values['elements'][$key]['input'] as $k => $v) {
                    if ($v[static::ELEMENT_TYPE] === 'radio' OR $v[static::ELEMENT_TYPE] === 'checkbox') {
                        $this->values['elements'][$key]['input'][$k]['checked'] = '';
                        if ($v[static::ELEMENT_VALUE] == $val[$v[static::ELEMENT_NAME]]) {
                            $this->values['elements'][$key]['input'][$k]['checked'] = 1;
                        }
                    }
                    $this->values['elements'][$key]['input'][$k][static::ELEMENT_VALUE]  = $val[$v[static::ELEMENT_NAME]];
                }
            }
        }
        $formData['form'] = $formData;
        $this->values = array_merge($this->values, $formData);
    }

    // public function 

    /**
     * Build form array
     * 
     * @param array $formData    form data
     * @param array $elementData element data
     * 
     * @return array
     */
    public function renderArray($formData, $elementData)
    {
        if (isset($this->data['append'])) {
            foreach ($this->data['append'] as $value) {
                array_push($elementData, $value);
            }
        }
        return $this->builtArray = $this->buildArray($this->sort(array('form' => $formData), $elementData));
    }

    /**
     * Group array
     * 
     * @param array $groupData group data
     * @param int   $order     input order
     * 
     * @return void
     */
    public function elementExtraArray($groupData, $order)
    {
        foreach ($groupData as $key => $val) {
            $this->values['elements'][$order]['extra'][$key]  = $val;
        }
    }
    
    /**
     * Group order
     * 
     * @param array $groupData group data
     * @param int   $groupId   group primary key
     * 
     * @return integer
     */
    public function getElementsOfGroup($groupData, $groupId)
    {
        foreach ($groupData as $v) {
            if ($v[static::GROUP_PRIMARY_KEY] === $groupId) {
                return $v;
            }
        }
    }

    /**
     * Create group hidden inputs
     * 
     * @param array $elementsOfGroup group data
     * 
     * @return array
     */
    public function createGroupHiddenInputs($elementsOfGroup)
    {
        return array(
            static::ELEMENT_GROUP_ID    => $elementsOfGroup[static::GROUP_PRIMARY_KEY],
            static::ELEMENT_TYPE        => 'hidden',
            static::ELEMENT_VALUE_TYPE  => 'string',
            static::ELEMENT_VALUE       => '',
            static::ELEMENT_TITLE       => '',
            static::ELEMENT_ATTRIBUTE   => '',
            static::ELEMENT_LABEL       => '',
            static::ELEMENT_ROLE        => '',
            static::ELEMENT_RULES       => '',
            static::ELEMENT_DESCRIPTION => '',
            static::ELEMENT_NAME        => $elementsOfGroup[static::GROUP_NAME],
            static::ELEMENT_ORDER       => $elementsOfGroup[static::GROUP_ORDER],
        );
    }

    /**
     * Build array
     * 
     * @param array $data data
     * 
     * @return array
     */
    public function buildArray($data)
    {
        $formData = $data['form'];
        $groupData = $data['groups'];
        $elementData = $data['elements'];
        $formViewPermission = $this->user->hasObjectPermission($formData[static::FORM_NAME], 'view');
        $formOpPermission   = $this->user->hasObjectPermission($formData[static::FORM_NAME], $this->operationName);
        $formPermission     = $this->user->isPermitted($formData[static::FORM_NAME], $formViewPermission);
        // $groupData          = $this->getFormGroups($formData[static::FORM_PRIMARY_KEY], '*');

        // Reset element permission
        $elementViewPermissions = false;
        $elementOpPermissions   = false;
        $groupOpPermissions     = false;
        if ($formOpPermission === false) {
            $elementViewPermissions = $this->user->hasChildPermission($formData[static::FORM_NAME], $this->getFormNames(static::ELEMENT_NAME, $elementData), 'view');
            $elementOpPermissions   = $this->user->hasChildPermission($formData[static::FORM_NAME], $this->getFormNames(static::ELEMENT_NAME, $elementData), $this->operationName);
            $groupOpPermissions     = (count($groupData) > 0) ? $this->user->hasChildPermission($formData[static::FORM_NAME], $this->getFormNames(static::GROUP_NAME, $groupData), $this->operationName) : false;
        }
        $this->appendOrder = 0;
        $this->values      = array();
        $elementsOfGroup   = array();
        $submitTemp        = array();
        $postValues        = $this->toArray($this->getPostValues());

        foreach ($elementData as $val) {
            $order = (int)$val[static::ELEMENT_ORDER];
            $role  = isset($val[static::ELEMENT_ROLE]) ? $val[static::ELEMENT_ROLE] : '';
            $this->order = 0;
            if ($val[static::ELEMENT_GROUP_ID] > 0) { // Let's do order for grouped elements
                $this->order = $order;
                $elementsOfGroup = $this->getElementsOfGroup($groupData, $val[static::ELEMENT_GROUP_ID]);
                $order = (int)$elementsOfGroup[static::GROUP_ORDER];

                // if (($this->user->isPermitted($formData[static::FORM_NAME], $formOpPermission) === true
                //     OR $this->user->isPermitted($elementsOfGroup[static::GROUP_NAME], $groupOpPermissions) === true)
                //     AND $this->numberOfElements == (int)$elementsOfGroup[static::GROUP_NUMBER_OF_ELEMENTS] - (int)$elementsOfGroup[static::GROUP_NUMBER_OF_ELEMENTS]
                // ) {
                //     $appendOrder = $this->increaseAppendOrder($val[static::ELEMENT_NAME], $order);
                //     $increasedGroupOrder = $appendOrder + $this->increaseOrder;
                //     $this->increaseOrder++;
                //     if ($this->isGroupHidden === true) {
                //         $this->setElements($this->createGroupHiddenInputs($elementsOfGroup), $elementsOfGroup, $postValues, $increasedGroupOrder);
                //     }
                // }
                $this->numberOfElements++;
                $this->isGroup = true;
            }
            // Append data
            // If we have append request to order (5) it goes to (6) and other orders increased by 1.
            // We increase order numbers by one by.
            $increasedOrder = $this->increaseAppendOrder($val[static::ELEMENT_NAME], $order);
            // if ($this->isGroup === true AND $this->isGroupHidden === true) {
            //     $increasedOrder = $increasedOrder + $this->increaseOrder;
            // }
            if ($formPermission === true) {
                // If user doesn't have form or the input permission
                // and type "submit" is not,
                // We add "disabled" the input attribute.
                if ($this->user->isPermitted($formData[static::FORM_NAME], $formOpPermission) === false
                    AND $role !== 'widget'
                    AND $val[static::ELEMENT_TYPE] !== 'submit'
                    AND $this->user->isPermitted($val[static::ELEMENT_NAME], $elementOpPermissions) === false
                ) {
                    if ( ! isset($this->data['append'][$val[static::ELEMENT_NAME]])) {
                        $val[static::ELEMENT_RULES] = null;
                        $val[static::ELEMENT_ATTRIBUTE] = $val[static::ELEMENT_ATTRIBUTE] . ' disabled="disabled"';
                    }
                }
                $this->setElements($val, $elementsOfGroup, $postValues, $increasedOrder);

            } elseif (isset($this->data['append'][$val[static::ELEMENT_NAME]]) OR $this->user->isPermitted($val[static::ELEMENT_NAME], $elementViewPermissions) === true) {
            
                // If the user doesn't have any permissions, we are add "disabled" the input attribute.

                if ($this->user->isPermitted($val[static::ELEMENT_NAME], $elementOpPermissions) === false
                    AND $role !== 'widget'
                    AND $val[static::ELEMENT_TYPE] !== 'submit'
                ) {
                    if ( ! isset($this->data['append'][$val[static::ELEMENT_NAME]])) {
                        $val[static::ELEMENT_RULES] = null;
                        $val[static::ELEMENT_ATTRIBUTE] = $val[static::ELEMENT_ATTRIBUTE] . ' disabled="disabled"';
                    }
                }
                $this->setElements($val, $elementsOfGroup, $postValues, $increasedOrder);
            }
            if ($val[static::ELEMENT_TYPE] === 'submit') {  // Set temporary submit data.
                $submitTemp = $val;
                $submitTemp['group'] = $elementsOfGroup;
                $submitTempOrder = $increasedOrder;         // We have changed the "submit" of new order.
            }
        }
        if ( ! isset($this->values['elements'])) {
            return null;
        }
        if (isset($submitTempOrder)) {
            // If element type is "submit" and user doesn't have permissions,
            // We add "disabled" attribute.
            if ($formOpPermission === false AND $elementOpPermissions === false) {
                $submitTemp[static::ELEMENT_ATTRIBUTE] = $submitTemp[static::ELEMENT_ATTRIBUTE] . ' disabled="disabled"';
            }
            $this->order = $submitTemp[static::ELEMENT_ORDER];
            $this->setElements($submitTemp, $submitTemp['group'], $postValues, $submitTempOrder); // Set element submit button
            unset($submitTempOrder);
        }
        $this->appendFormData($formData);

        return $this->values;
    }

    /**
     * Set elements
     * 
     * @param array $data       element data
     * @param array $groupData  group data
     * @param array $postValues post values
     * @param int   $order      order
     * 
     * @todo Oluşturulan array bir fonksiyona dönüştürülecek.
     * @todo Switch fonksiyona dönüştürülecek.
     * 
     * @return void
     */
    public function setElements($data, $groupData, $postValues, $order)
    {
        $elementValueType = empty($data[static::ELEMENT_VALUE_TYPE]) ? 'string' : $data[static::ELEMENT_VALUE_TYPE];

        if ($data[static::ELEMENT_GROUP_ID] > 0 AND $data[static::ELEMENT_TYPE] != 'hidden') {
            array_push($this->groupAttributeNames, $data[static::ELEMENT_NAME]);
            if ($this->numberOfElements == $groupData[static::GROUP_NUMBER_OF_ELEMENTS]
                AND isset($postValues[$groupData[static::GROUP_NAME]])
                AND ! empty($postValues[$groupData[static::GROUP_NAME]])
                AND ! empty($groupData[static::GROUP_DATABASE_TO_FORM])
            ) {
                eval("\$func = " . $groupData[static::GROUP_DATABASE_TO_FORM] . ";");
                $closure = Closure::bind($func, Controller::$instance, 'Controller');
                $this->groupClosureData[$order] = $closure($this->c, $postValues[$groupData[static::GROUP_NAME]], $this->groupAttributeNames);
                $this->numberOfElements = 0;
                $this->groupAttributeNames = array();
            } elseif ($this->numberOfElements == $groupData[static::GROUP_NUMBER_OF_ELEMENTS]) {
                $this->numberOfElements = 0;
                $this->groupAttributeNames = array();
                $groupDescription = (isset($groupData[static::GROUP_DESCRIPTION])) ? $groupData[static::GROUP_DESCRIPTION] : '';
            }
            $extraArray = array(
                static::GROUP_FORM_TO_DATABASE   => $groupData[static::GROUP_FORM_TO_DATABASE],
                static::GROUP_DATABASE_TO_FORM   => $groupData[static::GROUP_DATABASE_TO_FORM],
                static::ELEMENT_NAME             => $groupData[static::GROUP_NAME],
                static::ELEMENT_LABEL            => $groupData[static::GROUP_LABEL],
                static::ELEMENT_CLASS            => $groupData[static::GROUP_CLASS],
                static::GROUP_NUMBER_OF_ELEMENTS => $groupData[static::GROUP_NUMBER_OF_ELEMENTS],
                static::ELEMENT_DESCRIPTION      => $data[static::ELEMENT_DESCRIPTION],
            );
        } else {
            $extraArray = array(
                static::GROUP_FORM_TO_DATABASE   => '',
                static::GROUP_DATABASE_TO_FORM   => '',
                static::ELEMENT_CLASS            => '',
                static::ELEMENT_NAME             => $data[static::ELEMENT_NAME],
                static::ELEMENT_LABEL            => $data[static::ELEMENT_LABEL],
                static::GROUP_NUMBER_OF_ELEMENTS => 0,
                static::ELEMENT_DESCRIPTION      => $data[static::ELEMENT_DESCRIPTION],
            );
        }
        if (isset($groupDescription)) {
            $extraArray[static::ELEMENT_DESCRIPTION] =  $groupDescription;
        }
        $this->elementExtraArray($extraArray, $order);
        foreach (
            array(
                static::ELEMENT_NAME,
                static::ELEMENT_TITLE,
                static::ELEMENT_RULES,
                static::ELEMENT_ATTRIBUTE,
                static::ELEMENT_TYPE,
                static::ELEMENT_LABEL,
                static::ELEMENT_ROLE
            ) as $val
        ) {
            $this->values['elements'][$order]['input'][$this->order][$val] = $data[$val];
        }
        $elementValue = (isset($postValues[$data[static::ELEMENT_NAME]])) ? $postValues[$data[static::ELEMENT_NAME]] : '';
        $name = static::ELEMENT_VALUE;
        if ($data[static::ELEMENT_TYPE] == 'dropdown') {
            $name = 'option';
            $this->values['elements'][$order]['input'][$this->order][static::ELEMENT_VALUE] = $elementValue;
        }
        $appendValues = 'appendValuesTo'. ucfirst($elementValueType);
        $this->$appendValues($name, $data, $elementValue, $order);

        if ($data[static::ELEMENT_TYPE] === 'radio' OR $data[static::ELEMENT_TYPE] === 'checkbox') {
            $this->values['elements'][$order]['input'][$this->order]['checked'] = '';
            if ($data[static::ELEMENT_VALUE] == $elementValue) {
                $this->values['elements'][$order]['input'][$this->order]['checked'] = 1;
            }
        }
    }

    /**
     * Convert to json
     * 
     * @param string $name         element name
     * @param array  $data         data
     * @param mix    $elementValue element value
     * @param mix    $order        order
     * 
     * @return void
     */
    public function appendValuesToJson($name, $data, $elementValue, $order)
    {
        $this->values['elements'][$order]['input'][$this->order][$name] = $this->toArray($data[static::ELEMENT_VALUE]);
    }

    /**
     * Convert to closure
     * 
     * @param string $name         element name
     * @param array  $data         data
     * @param mix    $elementValue element value
     * @param mix    $order        order
     * 
     * @return void
     */
    public function appendValuesToClosure($name, $data, $elementValue, $order)
    {
        $elementValue = '';
        if ( ! empty($data[static::ELEMENT_DATABASE_TO_FORM])) {
            eval("\$func = " . $data[static::ELEMENT_DATABASE_TO_FORM] . ";");
            $closure = Closure::bind($func, Controller::$instance, 'Controller');
            $elementValue = $closure($this->c, $this->formValues);
        }
        $this->values['elements'][$order]['input'][$this->order][$name] = $elementValue;
    }

    /**
     * Convert to string
     * 
     * @param string $name         element name
     * @param array  $data         data
     * @param mix    $elementValue element value
     * @param mix    $order        order
     * 
     * @return void
     */
    public function appendValuesToString($name, $data, $elementValue, $order)
    {
        if ( ! empty($data[static::ELEMENT_VALUE]) OR is_numeric($data[static::ELEMENT_VALUE])) {
            $defaultValue = $data[static::ELEMENT_VALUE];
        }
        $this->values['elements'][$order]['input'][$this->order][static::ELEMENT_VALUE] = (isset($defaultValue)) ? $defaultValue : $elementValue;
    }

    /**
     * Validate form
     * 
     * @param array $operationName operation name
     * 
     * @return boolean
     */
    public function validate($operationName)
    {
        $this->post      = $this->c->load('return post');
        $this->validator = $this->c->load('return validator');
        $this->jellyForm = $this->c->load('return jelly/form');

        $postData = $this->post[true];
        if ( ! isset($postData['form_data'])) {
            return false;
        }
        $formData = $this->toArray($postData['form_data']); // Get form attributes.
        $elementsData = $this->jellyForm->getFormElements(
            $formData[static::FORM_PRIMARY_KEY],
            array(
                static::ELEMENT_PRIMARY_KEY,
                static::ELEMENT_NAME,
                static::ELEMENT_GROUP_ID,
                static::ELEMENT_ROLE,
                static::ELEMENT_WIDGET,
                static::ELEMENT_LABEL,
                static::ELEMENT_VALUE_TYPE,
                static::ELEMENT_FORM_TO_DATABASE,
                static::ELEMENT_RULES
            )
        );
        $i = 0;
        $groupId = 0;
        $newValues = array();
        $groupData = array();
        $groupInputs = array();
        foreach ($elementsData as $v) {
            $groupId = ($groupId == $v[static::ELEMENT_GROUP_ID]) ? $groupId : $v[static::ELEMENT_GROUP_ID];
            $groupTempPostData = array();
            $groupTempClosureData = '';
            if ($v[static::ELEMENT_GROUP_ID] > 0 AND $groupId == $v[static::ELEMENT_GROUP_ID]) {
                $i++;
                $group = $this->getFormGroup($v[static::ELEMENT_GROUP_ID], array(static::GROUP_NAME, static::GROUP_FORM_TO_DATABASE, static::GROUP_NUMBER_OF_ELEMENTS, static::GROUP_WIDGET, static::GROUP_LABEL));
                $groups[$group[static::GROUP_NAME]]['input'][] = $v[static::ELEMENT_NAME];
                $groupData[$v[static::ELEMENT_NAME]] = $group[static::GROUP_NAME];

                if ($i == $group[static::GROUP_NUMBER_OF_ELEMENTS]
                    AND ! empty($group[static::GROUP_FORM_TO_DATABASE])
                ) {
                    eval("\$func = " . $group[static::GROUP_FORM_TO_DATABASE] . ";");
                    $groupInputs = $groups[$group[static::GROUP_NAME]]['input'];
                    if (is_array($groupInputs)) {
                        foreach ($groupInputs as $key => $val) {
                            if (isset($postData[$val])) {
                                $groupTempPostData[$groupInputs[$key]] = $postData[$groupInputs[$key]];
                            }
                        }
                    }
                    if ( ! empty($groupTempPostData)) {
                        $groupClosure = Closure::bind($func, Controller::$instance, 'Controller');
                        $groupTempClosureData = $groupClosure($this->c, $groupTempPostData);
                        $newValues[$group[static::GROUP_NAME]] = $groupTempClosureData;
                    }
                    $i = 0;
                } elseif ($i == $group[static::GROUP_NUMBER_OF_ELEMENTS] AND empty($group[static::GROUP_FORM_TO_DATABASE])) {
                    $i = 0;
                }
            }

            if ($v[static::ELEMENT_ROLE] === 'field'AND isset($postData[$v[static::ELEMENT_NAME]])) {
                $elementValue = $postData[$v[static::ELEMENT_NAME]];

                //-- Element closure start --//
                
                if ($v[static::ELEMENT_VALUE_TYPE] == 'closure' AND ! empty($v[static::ELEMENT_FORM_TO_DATABASE])) {
                    eval("\$func = " . $v[static::ELEMENT_FORM_TO_DATABASE] . ";");
                    $closure = Closure::bind($func, Controller::$instance, 'Controller'); // Closure function begins
                    $elementValue = $closure($this->c, $postData[$v[static::ELEMENT_NAME]]);
                }
                //-- Element closure end --//
                $newValues[$v[static::ELEMENT_NAME]] = $elementValue;
            }
            // $value = '';
            $value = (isset($postData[$v[static::ELEMENT_NAME]])) ? $postData[$v[static::ELEMENT_NAME]] : '';
            if ( ! empty($v[static::ELEMENT_RULES])) {
                if ($this->view->isTranslatorEnabled() === true) {
                    $v[static::ELEMENT_LABEL] = $this->c->load('translator')[$v[static::ELEMENT_LABEL]];
                }
                $value = (isset($postData[$v[static::ELEMENT_NAME]])) ? $postData[$v[static::ELEMENT_NAME]] : '';
                $this->rules[$v[static::ELEMENT_NAME]] = array(
                    'field' => $v[static::ELEMENT_NAME],
                    'label' => $v[static::ELEMENT_LABEL],
                    'rules' => $v[static::ELEMENT_RULES]
                );
            }
            if ($v[static::ELEMENT_GROUP_ID] > 0 AND ! empty($group[static::GROUP_WIDGET]) AND ! empty($groupTempClosureData)) {
                $value = $groupTempClosureData;
                $v[static::ELEMENT_NAME]   = $group[static::GROUP_NAME];
                $v[static::ELEMENT_WIDGET] = $group[static::GROUP_WIDGET];
            }
            if ($v[static::ELEMENT_ROLE] === 'widget' OR ! empty($v[static::ELEMENT_WIDGET]) AND ! empty($value)) {
                $widgetClass = $this->widget($v[static::ELEMENT_WIDGET]);
                $validate = $widgetClass->validate($value);
                if ($validate === false) {
                    $this->validator->setError($v[static::ELEMENT_NAME], $widgetClass->getError());
                }
            }
            // Set rules for all form elements.
            if (isset($this->rules[$v[static::ELEMENT_NAME]])) {
                // if ($this->view->isTranslatorEnabled() === true) {
                //     $v[static::ELEMENT_LABEL] = $this->c->load('translator')[$v[static::ELEMENT_LABEL]];
                // }
                // var_dump($v[static::ELEMENT_NAME]);
                $this->setRules($this->rules[$v[static::ELEMENT_NAME]]['field'], $this->rules[$v[static::ELEMENT_NAME]]['label'], $this->rules[$v[static::ELEMENT_NAME]]['rules']);
            }
        }
        $this->setGroupData($groupData); // Set group data for ajax forms.

        $formOpPermission   = $this->user->hasObjectPermission($formData[static::FORM_NAME], $operationName);   // Get form operation permission.
        $formPermission     = $this->user->isPermitted($formData[static::FORM_NAME], $formOpPermission);        // Check form permission.
        $elementsPermission = $this->user->hasObjectPermission(array_keys($newValues), $operationName);         // Elements permission.
        
        if ($formPermission === false) {
            $data = array();
            foreach ($newValues as $key => $val) {
                if ( ! isset($group[static::GROUP_NAME]) OR $group[static::GROUP_NAME] !== $key) {
                    $data = $postData[$key];
                }
                if ($this->user->isPermitted($key, $elementsPermission) === false) {
                    $notice = sprintf('You haven\'t got an %s permission for this field.', $operationName);
                    $this->validator->setError($key, $notice);
                    $this->logger->channel('jellyForm');
                    $this->logger->warning(
                        $notice,
                        array(
                            'form'      => $formData[static::FORM_NAME],
                            'operation' => $operationName,
                            'field'     => $key,
                            'data'      => $data,
                        )
                    );
                    // $this->logger->push(LOGGER_EMAIL);
                }
            }
        }
        if ($this->validator->isValid()) {
            $this->setFormValues($newValues);
            return true;
        }
        return false;
    }

    /**
     * Is allowed form.
     * 
     * @return boolean
     */
    public function isAllowed()
    {
        return $this->isAllowed;
    }

    /**
     * Set group data
     * 
     * @param array $data group data
     * 
     * @return void
     */
    public function setGroupData($data)
    {
        $this->groupData = $data;
    }

    /**
     * Get group data
     * 
     * @return array group data
     */
    public function getGroupData()
    {
        return $this->groupData;
    }

    /**
     * Print form open tag
     * 
     * @return string
     */
    public function printOpen()
    {
        $this->isPrintOpen = true;

        if (count($this->builtArray) == 0) {
            return 'Sorry, but there is no data to show for this query.';
        }
        $this->view->render($this->builtArray);

        return $this->view->open();
    }

    /**
     * Print form close tag
     * 
     * @return string
     */
    public function printClose()
    {
        return $this->view->close();
    }

    /**
     * Render html
     * 
     * @return string
     */
    public function printForm()
    {
        if ($this->isPrintOpen === false) {
            throw new RunTimeException('First you must call this function "printOpen()".');
        }
        return $this->view->form();
    }

    /**
     * Print submit button
     * 
     * @param boolean $isTemplate is template?
     * 
     * @return string
     */
    public function printSubmit($isTemplate = true)
    {
        if ($this->isPrintOpen === false) {
            throw new RunTimeException('First you must call this function "printOpen()".');
        }
        return $this->view->submit($isTemplate);
    }

    /**
     * Widget classes
     * 
     * @param string $class class name
     * 
     * @return object
     */
    public function widget($class)
    {
        $className = '\\Obullo\Jelly\Widget\\'. ucfirst($class);
        return new $className($this->c);
    }

    /**
     * Load html form classes
     * 
     * @param string $class  class name
     * @param int    $formId form id
     * 
     * @return void
     */
    public function load($class, $formId = '')
    {
        $this->formId           = (int)$formId;
        $className              = '\\Obullo\Jelly\Html\Form\\'. ucfirst($class);
        $formManager            = new $className($this->c, $this);
        $htmlFormClass          = lcfirst($class);
        $this->{$htmlFormClass} = $formManager;
    }
}

// END Adapter Class
/* End of file Adapter.php */

/* Location: .Obullo/Jelly/Adapter.php */