<?php

namespace Obullo\Form;

/**
 * Element Class
 * 
 * @category  Form
 * @package   Element
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/form
 */
Class Element
{
    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
        $this->logger = $c['logger'];
        $this->translator = $c['translator'];

        $this->logger->debug('Form Element Class Initialized');
    }

    /**
     * Form Button
     *
     * @param mixed  $data    data
     * @param string $content content
     * @param string $extra   extra
     * 
     * @return string
     */
    public function button($data = '', $content = '', $extra = '')
    {
        $defaults = array('name' => (( ! is_array($data)) ? $data : ''), 'type' => 'button');
        if ( is_array($data) AND isset($data['content'])) {
            $content = $data['content'];
            unset($data['content']); // content is not an attribute
        }
        return "<button ".$this->parseFormAttributes($data, $defaults).$extra.">".$this->translator[$content]."</button>";
    }
    
    /**
     * Checkbox Field
     * 
     * @param mixed   $data    data
     * @param string  $value   value
     * @param boolean $checked checked
     * @param string  $extra   extra
     * 
     * @return string
     */
    public function checkbox($data = '', $value = '', $checked = false, $extra = '')
    {
        if (is_object($value)) { // $_POST & Db value schema sync
            $value = $this->getRowValue($checked, $data); 
        }
        $defaults = array('type' => 'checkbox', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);
        if (is_array($data) AND array_key_exists('checked', $data)) {
            $checked = $data['checked'];
            if ($checked == false) {
                unset($data['checked']);
            } else {
                $data['checked'] = 'checked';
            }
        }
        if ($checked == true) {
            $defaults['checked'] = 'checked';
        } else {
            unset($defaults['checked']);
        }
        $type = 'checkbox';
        if (isset($data['type']) AND $data['type'] == 'radio') {
            $type = 'radio';
        }
        return "<input " . $this->parseFormAttributes($data, $defaults) . $extra . " />";
    }
   
    /**
     * Form close tag
     * 
     * @param string $extra extra
     * 
     * @return string
     */
    public function close($extra = '')
    {
        return "</form>" . $extra;
    }

    /**
     * Drop-down Menu
     * 
     * @param string $name     name
     * @param mixed  $options  options
     * @param array  $selected selected
     * @param string $extra    extra data
     * @param string $data     extra option data
     * 
     * @return string
     */
    public function dropdown($name = '', $options = '', $selected = array(), $extra = '', $data = array())
    {
        if (is_object($selected)) { // $_POST & Db value
            $selected = $this->getRowValue($selected, $name);
        }
        if ($selected === false) { // False == "0" bug fix, false is not an Integer.
            $selected_option = array_keys($options);
            $selected        = $selected_option[0];
        }
        if ( ! is_array($selected)) {
            $selected = array($selected);
        }
        if (sizeof($selected) === 0) { // If no selected state was submitted we will attempt to set it automatically
            if (isset($_POST[$name])) { // If the form name appears in the $_POST array we have a winner !
                $selected = array($_POST[$name]);
            }
        }
        if ($extra != '') {
            $extra = ' '.$extra;
        }
        $multiple  = (sizeof($selected) > 1 AND strpos($extra, 'multiple') === false) ? ' multiple="multiple"' : '';
        $selectTag = '<select name="'.$name.'"'.$extra.$multiple.">\n";
        foreach ($options as $key => $val) {
            $key = (string) $key;
            if (is_array($val)) {
                $selectTag .= '<optgroup label="'.$key.'">'."\n";
                foreach ($val as $optgroup_key => $optgroup_val) {
                    $extra = (isset($data[$optgroup_key])) ? $data[$optgroup_key] : '';
                    $sel = (in_array($optgroup_key, $selected, true)) ? ' selected="selected"' : '';
                    $selectTag .= '<option value="'.$optgroup_key.'"'.$sel.' '. $extra .'>'.(string) $optgroup_val."</option>\n";
                }
                $selectTag .= '</optgroup>'."\n";
            } else {

                $extra = (isset($data[$key])) ? $data[$key] : '';
                $sel = (in_array($key, $selected, true)) ? ' selected="selected"' : '';
                $selectTag .= '<option value="'.$key.'"'.$sel.' '. $extra .'>'.(string) $val."</option>\n";
            }
        }
        return $selectTag .= '</select>';
    }
   
    /**
     * Fieldset Tag
     * 
     * Used to produce <fieldset><legend>text</legend>.  To close fieldset
     * use form_fieldset_close()
     * 
     * @param string $legend_text the legend text
     * @param array  $attributes  additional attributes
     * 
     * @return string
     */
    public function fieldset($legend_text = '', $attributes = array())
    {
        $fieldset = "<fieldset";
        $fieldset.= $this->attributesToString($attributes, false);
        $fieldset.= ">\n";
        if ($legend_text != '') {
            $fieldset .= "<legend>$legend_text</legend>\n";
        }
        return $fieldset;
    }
   
    /**
     * Fieldset Close Tag
     * 
     * @param string $extra extra
     * 
     * @return string
     */
    public function fieldsetClose($extra = '')
    {
        return "</fieldset>" . $extra;
    }

    /**
     * Hidden Input Field
     * 
     * Generates hidden fields.  You can pass a simple key/value string or an associative
     * array with multiple values.
     * 
     * @param mixed   $name      name
     * @param string  $value     value
     * @param string  $extra     extra data
     * @param boolean $recursing recursing
     * 
     * @return string
     */
    public function hidden($name, $value = '', $extra = '', $recursing = false)
    {
        static $hiddenTag;
        if (is_object($value)) { // $_POST & Db value
            $value = $this->getRowValue($value, $name); 
        }
        if ($recursing === false) {
            $hiddenTag = "\n";
        }
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                $this->hidden($key, $val, '', true);
            }
            return $hiddenTag;
        }
        if ( ! is_array($value)) {
            $hiddenTag .= '<input type="hidden" name="' . $name . '" value="'. $this->prep($value, $name) .'" '.  trim($extra) . '/>' . "\n";
        } else {
            foreach ($value as $k => $v) {
                $k = (is_int($k)) ? '' : $k;
                $this->hidden($name.'['.$k.']', $v, '', true);
            }
        }
        return $hiddenTag;
    }
   
    /**
     * Text Input Field
     * 
     * @param string $data  data
     * @param string $value value
     * @param string $extra extra data
     * 
     * @return string
     */
    public function input($data = '', $value = '', $extra = '')
    {
        if (is_object($value)) { // $_POST & Db value schema sync
            $value = $this->getRowValue($value, $data); 
        }
        $defaults = array('type' => 'text', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);
        $inputElement = "<input " . $this->parseFormAttributes($data, $defaults) . $extra . " />";
        if (strpos($inputElement, 'type="text"') > 0) {
            return $inputElement;
        }
        return $inputElement;
    }

    /**
     * Form label
     * 
     * @param string $label_text The text to appear onscreen
     * @param string $id         the id the label applies to
     * @param string $attributes additional attributes
     * 
     * @return string
     */
    public function label($label_text = '', $id = '', $attributes = "")
    {
        $label = '<label';
        $label_text = $this->translator[$label_text];
        if (empty($id)) {
            $id = mb_strtolower($label_text);
        }
        $label .= " for=\"$id\"";
        if (is_array($attributes) AND count($attributes) > 0) {
            foreach ($attributes as $key => $val) {
                $label .= ' '.$key.'="'.$val.'"';
            }
        } else {
            $label .= ' '.ltrim($attributes);
        }
        return $label .= ">$label_text</label>";
    }

    /**
    * Form Declaration
    * Creates the opening portion of the form.
    *
    * @param string $action     the URI segments of the form destination
    * @param array  $attributes a key/value pair of attributes
    * @param array  $hidden     a key/value pair hidden data
    * @param array  $protection csrf protection
    * 
    * @return   string
    */
    public function open($action = '', $attributes = '', $hidden = array(), $protection = true)
    {
        if ($attributes == '') {
            $attributes = 'method="post"';
        }
        $action = ( strpos($action, '://') === false) ? $this->c['uri']->getSiteUrl($action) : $action;
        $form  = '<form action="'.$action.'"';
        $form .= $this->attributesToString($attributes, true);
        $form .= '>';

        $security = $this->c['config']->load('security');
        $form = str_replace(array('"method=\'get\'"', "method=\'GET\'"), 'method="get"', $form);

        // Add CSRF field if enabled, but leave it out for GET requests and requests to external websites

        if ($security['csrf']['protection'] && $protection && ! stripos($form, 'method="get"')) {
            $hidden[$this->c['csrf']->getTokenName()] = $this->c['csrf']->getToken();
        }
        if (is_array($hidden) AND count($hidden) > 0) {
            $form .= sprintf("<div style=\"display:none\">%s</div>", $this->hidden($hidden));
        }
        return $form;
    }

    /**
     * Form Declaration - Multipart type
     * Creates the opening portion of the form, but with "multipart/form-data".
     * 
     * @param string $action     the "uri" segments of the form destination
     * @param array  $attributes a key/value pair of attributes
     * @param array  $hidden     a key/value pair hidden data
     * 
     * @return string
     */
    public function openMultipart($action, $attributes = array(), $hidden = array())
    {
        if (is_string($attributes)) {
            $attributes .= ' enctype="multipart/form-data"';
        } else {
            $attributes['enctype'] = 'multipart/form-data';
        }
        return $this->open($action, $attributes, $hidden);
    }
   
    /**
     * Password Field
     * Identical to the input function but adds the "password" type
     * 
     * @param mixed  $data  data
     * @param string $value value
     * @param string $extra extra
     * 
     * @return string
     */
    public function password($data = '', $value = '', $extra = '')
    {
        if (is_object($value)) { // $_POST & Db value schema sync
            $value = $this->getRowValue($value, $data); 
        }
        if ( ! is_array($data)) {
            $data = array('name' => $data);
        }
        $data['type'] = 'password';
        return $this->input($data, $value, $extra);
    }
    
    /**
     * Form Prep
     * Formats text so that it can be safely placed in a form field in the event it has HTML tags.
     * 
     * @param string $str        str
     * @param string $field_name field name
     * 
     * @return string
     */
    public function prep($str = '', $field_name = '')
    {
        static $preppedFields = array();
        if (is_array($str)) { // if the field name is an array we do this recursively
            foreach ($str as $key => $val) {
                $str[$key] = $this->prep($val);
            }
            return $str;
        }
        if ($str === '') {
            return '';
        }
        // we've already prepped a field with this name
        // @todo need to figure out a way to namespace this so
        // that we know the *exact* field and not just one with
        // the same name
        if (isset($preppedFields[$field_name])) {
            return $str;
        }
        $str = htmlspecialchars($str);
        // In case htmlspecialchars misses these.
        $str = str_replace(array("'", '"'), array("&#39;", "&quot;"), $str);
        if ($field_name != '') {
            $preppedFields[$field_name] = $field_name;
        }
        return $str;
    }

    /**
     * Radio button
     * 
     * @param mixed   $data    data
     * @param string  $value   value
     * @param boolean $checked checked
     * @param string  $extra   extra data
     * 
     * @return string
     */
    public function radio($data = '', $value = '', $checked = false, $extra = '')
    {
        if ( ! is_array($data)) {
            $data = array('name' => $data); 
        }
        $data['type'] = 'radio';
        return $this->checkbox($data, $value, $checked, $extra);
    }
   
    /**
     * Reset button
     * 
     * @param mixed  $data  data
     * @param string $value value
     * @param string $extra extra data
     * 
     * @return string
     */
    public function reset($data = '', $value = '', $extra = '')
    {
        $defaults = array('type' => 'reset', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);
        return '<input ' . $this->parseFormAttributes($data, $defaults) . $extra ." />";
    }

    /**
     * Submit button
     * 
     * @param mixed  $data  data
     * @param string $value value
     * @param string $extra extra data
     * 
     * @return string
     */
    public function submit($data = '', $value = '', $extra = '')
    {
        $defaults = array('type' => 'submit', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $this->c['translator'][$value]);
        return '<input ' . $this->parseFormAttributes($data, $defaults) . $extra . ' />';
    }
   
    /**
     * Textarea filed
     * 
     * @param mixed  $data  data
     * @param string $value value
     * @param string $extra extra data
     * 
     * @return string
     */
    public function textarea($data = '', $value = '', $extra = '')
    {
        if (is_object($value)) { // $_REQUEST & Db value sync with schema
            $value = $this->getRowValue($value, $data);
        }
        $defaults = array('name' => (( ! is_array($data)) ? $data : ''), 'cols' => '90', 'rows' => '12');
        if ( ! is_array($data) OR ! isset($data['value'])) {
            $val = $value;
            if (strpos($extra, 'rows') !== false OR strpos($extra, 'cols') !== false) {
                $defaults = array('name' => ( ! is_array($data)) ? $data : '');
            }
        } else {
            $val = $data['value']; 
            unset($data['value']); // textareas don't use the value attribute
        }
        $name     = (is_array($data)) ? $data['name'] : $data;
        $textarea = '<textarea '. $this->parseFormAttributes($data, $defaults) . $extra . ">" . $this->prep($val, $name) . '</textarea>';
        return $textarea;
    }

    /**
     * Upload field
     * Identical to the input function but adds the "file" type
     * 
     * @param string $data  data
     * @param string $value value
     * @param string $extra extra data
     * 
     * @return string
     */
    public function upload($data = '', $value = '', $extra = '')
    {
        if ( ! is_array($data)) {
            $data = array('name' => $data);
        }
        $data['type'] = 'file';
        return $this->input($data, $value, $extra);
    }

    /**
     * Parse the form attributes
     * Helper function used by some of the form helpers
     * 
     * @param array $attributes attributes
     * @param array $default    default
     * 
     * @return string
     */
    public function parseFormAttributes($attributes, $default)
    {
        if (is_array($attributes)) {
            foreach ($default as $key => $val) {
                if (isset($attributes[$key])) {
                    $default[$key] = $attributes[$key];
                    unset($attributes[$key]);
                }
            }
            if (count($attributes) > 0) {
                $default = array_merge($default, $attributes);
            }
        }
        $att = '';
        foreach ($default as $key => $val) {
            if ($key == 'value') {
                $val = $this->prep($val, $default['name']);
            }
            $att .= $key . '="' . $val . '" ';
        }
        return $att;
    }

    /**
     * Attributes To String
     * Helper function used by some of the form helpers
     * 
     * @param mixed $attributes attributes
     * 
     * @return string
     */
    public function attributesToString($attributes)
    {
        if (is_string($attributes) AND strlen($attributes) > 0) {
            $attributes = str_replace('\'', '"', $attributes); // convert to double quotes.
            if (strpos($attributes, 'method=') === false) {
                $attributes.= ' method="post"';
            }
            if (strpos($attributes, 'accept-charset=') === false) {
                $attributes.= ' accept-charset="'.strtolower($this->c['config']['locale']['charset']).'"';
            }
            return ' '.ltrim($attributes);
        }
        if (is_object($attributes) AND count($attributes) > 0) {
            $attributes = (array)$attributes;
        }
        if (is_array($attributes) AND count($attributes) > 0) {
            $atts = '';
            if ( ! isset($attributes['method'])) {
                $atts.= ' method="post"';
            }
            if ( ! isset($attributes['accept-charset'])) {
                $atts.= ' accept-charset="'.strtolower($this->c['config']['locale']['charset']).'"';
            }
            foreach ($attributes as $key => $val) {
                $atts.= ' '.$key.'="'.$val.'"';
            }
            return $atts;
        }
    }
    
    /**
     * Get $_REQUEST value from
     * $_POST data or database $row 
     * using valid schema comparison.
     * 
     * @param object $row   object row
     * @param string $field field
     * 
     * @return string
     */
    public function getRowValue($row = null, $field = '')
    {
        if (is_array($field)) {
            $field = $field['name'];
        }
        $value = (isset($_REQUEST[$field])) ? $this->setValue($field) : '';
        if ( ! isset($_REQUEST[$field])) { // If POST data not available use Database $row
            if (is_object($row) AND isset($row->{$field})) { // If field available in database $row Object
                $value = $row->{$field};
            } elseif (is_array($row) AND isset($row[$field])) { // If field available in database $row Array
                $value = $row[$field];   
            }
        }
        return $value;
    }
}


// END Element class
/* End of file Element.php */

/* Location: .Obullo/Form/Element.php */