<?php

namespace Obullo\Form;

use Controller;

/**
 * Form Class
 * 
 * @category  Form
 * @package   Form
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/form
 */
Class Form
{
    /**
     * Static form operation constants
     */
    const NOTICE  = 'notice';
    const STATUS  = 'status';
    const MESSAGE = 'message';
    const SUCCESS = 'success';
    const ERRORS  = 'errors';

    /**
     * Container
     *
     * @var object
     */
    public $c;

    /**
     * Store form notification and errors
     *
     * @var array
     */
    public $messages = array();

    /**
     * Config Parameters
     * 
     * @var array
     */
    public $params = array();

    /**
     * Constructor
     * 
     * @param string $c      container
     * @param string $params parameters
     */
    public function __construct($c, $params = array())
    {
        $this->c = $c;
        $this->params = (count($params) == 0) ? $c['config']->load('form') : $params;
        $this->logger = $this->c->load('logger');
        $this->logger->debug('Form Class Initialized');
    }

    /**
     * Set success message and set status to "1".
     * 
     * @param string $message success message
     * 
     * @return void
     */
    public function success($message)
    {
        $this->messages[static::MESSAGE] = (string)$message;
        $this->messages[static::SUCCESS] = NOTICE_SUCCESS;
    }

    /**
     * Set errors message and set status to "0".
     * 
     * @param string $message success message
     * 
     * @return void
     */
    public function error($message)
    {
        $this->messages[static::MESSAGE] = (string)$message;
        $this->messages[static::SUCCESS] = NOTICE_ERROR;
    }

    /**
     * Set info message
     * 
     * @param string $message success message
     * 
     * @return void
     */
    public function info($message)
    {
        $this->messages[static::MESSAGE] = (string)$message;
        $this->messages[static::SUCCESS] = NOTICE_INFO;
    }

    /**
     * Set warning message
     * 
     * @param string $message success message
     * 
     * @return void
     */
    public function warning($message)
    {
        $this->messages[static::MESSAGE] = (string)$message;
        $this->messages[static::SUCCESS] = NOTICE_WARNING;
    }

    /**
     * Sets form status
     * 
     * @param integer $status 1 or 0
     * 
     * @return void
     */
    public function status($status = 0)
    {
        $this->messages[static::SUCCESS] = (int)$status;
    }

    /**
     * Returns to form status
     * 
     * @return void
     */
    public function getStatus()
    {
        return $this->messages[static::SUCCESS];
    }

    /**
     * Set key for json_encode().
     * 
     * Set success, message, errors and any custom key.
     * 
     * @param string $key error key
     * @param string $val error value
     *
     * @return void
     */
    public function setKey($key, $val)
    {
        $this->messages[$key] = $val;
    }

    /**
     * Set validator errors array to form e.g. : array('field' => 'error', 'field2' => 'error' )
     * 
     * @param mixed $errors error array or validator object 
     *
     * @return void
     */
    public function setErrors($errors)
    {
        if (is_object($errors)) {
            $errors = $errors->getErrors();  // Get "Validator" object errors
        }
        if (is_array($errors) AND count($errors) > 0) {
            $this->messages[static::SUCCESS] = 0;
        }
        $this->messages[static::ERRORS]  = $errors;
    }

    /**
     * Set notification message and status
     * 
     * @param string $message form message
     * @param int    $status  status
     * 
     * @return void
     */
    public function setMessage($message, $status = NOTICE_ERROR)
    {
        $this->status($status);
        $this->messages[static::MESSAGE] = (string)$message;
    }

    /**
     * Get notification message for valid post.
     * 
     * @param string $notice notice message
     * @param int    $status status
     * 
     * @return string
     */
    public function getMessage($notice = '', $status = 0)
    {
        if ( ! empty($notice) AND is_string($notice)) {
            $this->setMessage($notice, $status);
        }
        $message = isset($this->messages[static::MESSAGE]) ? $this->messages[static::MESSAGE] : '';
        if (empty($message)) {
            return '';
        }
        $array = $this->getValidTemplate();
        return $this->messages[static::MESSAGE] = str_replace(
            array('{class}','{icon}','{message}'), 
            array($array['class'], $array['icon'], $message),
            $this->params[NOTICE_MESSAGE]
        );
    }

    /**
     * Get current status template from configuration.
     * 
     * @return string error status
     */
    public function getValidTemplate()
    {
        if ( ! isset($this->messages[static::SUCCESS])) {
            return 'empty';
        }
        return $this->params[$this->messages[static::SUCCESS]];
    }

    /**
     * Get all outputs of the form 
     * 
     * @return array
     */
    public function outputArray()
    {
        return $this->messages;
    }

    /**
     * Get error
     * 
     * @param string $field  fieldname
     * @param string $prefix error html tag start
     * @param string $suffix error html tag end
     * 
     * @return mixed string or null
     */
    public function getError($field, $prefix = '', $suffix = '')
    {
        if ($this->c->exists('validator')) {  // If we have validator object
            return $this->c->load('validator')->getError($field, $prefix, $suffix);
        }
    }

    /**
     * Get filtered value from validator data
     *
     * Permits you to repopulate a form field with the value it was submitted
     * with, or, if that value doesn't exist, with the default
     *
     * @param string $field   the field name
     * @param string $default value
     * 
     * @return mixed string or null
     */    
    public function getValue($field = '', $default = '')
    {
        if ($this->c->exists('validator')) { // If we have validator object
            return $this->c->load('validator')->getValue($field, $default);
        }
        return $default;
    }

    /**
     * Alias of getValue
     * 
     * @param string $field   the field name
     * @param string $default value
     * 
     * @return void
     */    
    public function setValue($field = '', $default = '')
    {
        return $this->getValue($field, $default);
    }

    /**
     * Set Select
     *
     * Enables pull-down lists to be set to the value the user
     * selected in the event of an error
     * 
     * @param string  $field          fieldname
     * @param string  $value          value
     * @param boolean $default        default value
     * @param string  $selectedString selected string text
     *
     * @return void
     */
    public function setSelect($field = '', $value = '', $default = false, $selectedString = ' selected="selected"')
    {
        $validator = $this->c->load('validator');
        if ( ! isset($validator->fieldData[$field]) OR ! isset($validator->fieldData[$field]['postdata'])) {
            if ($default === true AND count($validator->fieldData) === 0) {
                return $selectedString;
            }
            return '';
        }
        $field = $validator->fieldData[$field]['postdata'];
        if (is_array($field)) {
            if ( ! in_array($value, $field)) {
                return '';
            }
        } else {
            if (($field == '' OR $value == '') OR ($field != $value)) {
                return '';
            }
        }
        return $selectedString;  // ' selected="selected"'
    }

    /**
     * Set Checkbox
     *
     * Enables checkboxes to be set to the value the user
     * selected in the event of an error
     *
     * @param string  $field   fieldname
     * @param string  $value   value
     * @param boolean $default default value
     *
     * @return void
     */    
    public function setCheckbox($field = '', $value = '', $default = false)
    {
        return $this->setSelect($field, $value, $default, ' checked="checked"');
    }

    /**
     * Set Radio
     *
     * Enables radio buttons to be set to the value the user
     * selected in the event of an error
     *
     * @param string  $field   fieldname
     * @param string  $value   value
     * @param boolean $default default value
     *
     * @return void
     */    
    public function setRadio($field = '', $value = '', $default = false)
    {
        return $this->setSelect($field, $value, $default, ' checked="checked"');
    }

}

// END Form class
/* End of file Form.php */

/* Location: .Obullo/Form/Form.php */