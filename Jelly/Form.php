<?php

namespace Obullo\Jelly;

use RunTimeException,
    Obullo\Jelly\Html\Form\Permission\Save;

/**
 * Json Form
 * 
 * @category  Jelly
 * @package   Form
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs
 */
Class Form extends Adapter
{
    /**
     * Tablename constants
     */
    const FORM_TABLENAME    = 'db.form_tablename';
    const GROUP_TABLENAME   = 'db.group_tablename';
    const OPTION_TABLENAME  = 'db.option_tablename';
    const ELEMENT_TABLENAME = 'db.element_tablename';
    
    /**
     * Tablename
     * 
     * @var string
     */
    public $formTableName = 'forms';

    /**
     * Data tablename
     * 
     * @var string
     */
    public $dataTableName = 'form_data';

    /**
     * Element table column form id
     * (foreign key for form primary key)
     * 
     * @var string
     */
    public $columnFormId = 'form_id';

    /**
     * Column name
     * 
     * @var string
     */
    public $columnFormName = 'name';

    /**
     * Column order
     * 
     * @var string
     */
    public $columnOrder = 'order';

    /**
     * Redis cache expiration
     * 
     * @var integer
     */
    public $expiration = 7200;

    /**
     * Constructor
     * 
     * @param array $c      container
     * @param array $params parameters
     */
    public function __construct($c, $params = array())
    {
        $this->db = $c->load('return service/crud');
        $this->cache = $c->load('service/cache');

        parent::__construct($c, $params);
        $this->c = $c;
        
        if (count($params) > 0) {
            $this->formTableName = $params[static::FORM_TABLENAME];
            $this->groupTableName = $params[static::GROUP_TABLENAME];
            $this->optionTableName = $params[static::OPTION_TABLENAME];
            $this->elementTableName = $params[static::ELEMENT_TABLENAME];
        }
    }

    /**
     * Get form data to database
     * 
     * @param int   $formId form id primary key
     * @param mixed $select select fields
     * 
     * @return array
     */
    public function getForm($formId = 0, $select = array(self::FORM_PRIMARY_KEY, self::FORM_NAME, self::FORM_RESOURCE_ID))
    {
        if (empty($formId)) {
            return array();
        }
        if (is_array($select)) {
            $select = implode(',', $select);
        }
        $key = static::CACHE_FORM . $formId .':'. md5($select);
        $resultArray = $this->cache->get($key);
        if ($resultArray === false) { // If not exist in the cache
            $this->db->select($select);
            $this->db->where(static::FORM_PRIMARY_KEY, $formId);
            $this->db->get($this->formTableName);
            $resultArray = $this->db->rowArray();
            $this->cache->set($key, $resultArray, $this->expiration);
        }
        return $resultArray;
    }

    /**
     * Get form data to database
     * 
     * @param mixed $select select fields
     * 
     * @return array
     */
    public function getFormAttributes($select = array(self::FORM_PRIMARY_KEY, self::FORM_NAME, self::FORM_RESOURCE_ID, self::FORM_ACTION, self::FORM_ATTRIBUTE))
    {
        if (is_array($select)) {
            $select = implode(',', $select);
        }
        $key = static::CACHE_FORM_ATTRIBUTES . $this->formIdentifier .':'. md5($select);
        $resultArray = $this->cache->get($key);

        if ($resultArray === false) { // If not exist in the cache
            $this->db->select($select);
            $this->db->where(static::FORM_NAME, $this->formIdentifier);
            $this->db->get($this->formTableName);
            $resultArray = $this->db->rowArray();
            $this->cache->set($key, $resultArray, $this->expiration);
        }
        return $resultArray;
    }

    /**
     * Get form element to database
     * 
     * @param int   $primaryKey form id primary key
     * @param mixed $select     select fields
     * 
     * @return array
     */
    public function getFormElement($primaryKey = 0, $select = array(self::ELEMENT_PRIMARY_KEY, self::ELEMENT_NAME))
    {
        if (empty($primaryKey)) {
            return array();
        }
        if (is_array($select)) {
            $select = implode(',', $select);
        }
        $key = static::CACHE_FORM_ELEMENT . $primaryKey .':'. md5($select);
        $resultArray = $this->cache->get($key);
        if ($resultArray === false) { // If not exist in the cache
            $this->db->select($select);
            $this->db->where(static::ELEMENT_PRIMARY_KEY, $primaryKey);
            $this->db->get($this->elementTableName);
            $resultArray = $this->db->rowArray();
            $this->cache->set($key, $resultArray, $this->expiration);
        }
        return $resultArray;
    }

    /**
     * Get form data to database
     * 
     * @param int   $formId form id primary key
     * @param mixed $select select fields
     * 
     * @return array
     */
    public function getFormElements($formId = 0, $select = array(self::ELEMENT_PRIMARY_KEY, self::ELEMENT_NAME))
    {
        if (empty($formId)) {
            return array();
        }
        if (is_array($select)) {
            $select = implode(',', $select);
        }
        $key = static::CACHE_FORM_ELEMENTS . $formId .':'. md5($select);
        $resultArray = $this->cache->get($key);
        if ($resultArray === false) { // If not exist in the cache
            $this->db->select($select);
            $this->db->where(static::ELEMENT_FORM_ID, $formId);
            $this->db->orderBy(static::ELEMENT_PRIMARY_KEY, 'ASC');
            $this->db->get($this->elementTableName);
            $resultArray = $this->db->resultArray();
            $this->cache->set($key, $resultArray, $this->expiration);
        }
        return $resultArray;
    }

    /**
     * Get element group data
     * 
     * @param int   $primaryKey group id primary key
     * @param mixed $select     select fields
     * 
     * @return array
     */
    public function getFormGroup($primaryKey = 0, $select = array(self::GROUP_PRIMARY_KEY, self::GROUP_NAME))
    {
        if (empty($primaryKey)) {
            return array();
        }
        if (is_array($select)) {
            $select = implode(',', $select);
        }
        $key = static::CACHE_ELEMENT_GROUP . $primaryKey .':'. md5($select);
        $resultArray = $this->cache->get($key);
        if ($resultArray === false) { // If not exist in the cache
            $this->db->select($select);
            $this->db->where(static::GROUP_PRIMARY_KEY, $primaryKey);
            $this->db->get($this->groupTableName);
            $resultArray = $this->db->rowArray();
            $this->cache->set($key, $resultArray, $this->expiration);
        }
        return $resultArray;
    }

    /**
     * Get element groups data
     * 
     * @param int   $formId form id
     * @param mixed $select select fields
     * 
     * @return array
     */
    public function getFormGroups($formId = 0, $select = array(self::GROUP_PRIMARY_KEY, self::GROUP_NAME, self::GROUP_ORDER))
    {
        if (is_array($select)) {
            $select = implode(',', $select);
        }
        $key = static::CACHE_ELEMENT_GROUPS . $formId .':'. md5($select);
        $resultArray = $this->cache->get($key);

        if ($resultArray === false) {    // If not exist in the cache
            $this->db->select($select);
            $this->db->where(static::GROUP_FORM_ID, $formId);
            $this->db->get($this->groupTableName);
            $resultArray = $this->db->resultArray();

            $this->cache->set($key, $resultArray, $this->expiration);
        }
        return $resultArray;
    }

    /**
     * Get option fields
     * 
     * @param int   $primaryKey option id primary key
     * @param mixed $select     select fields
     * 
     * @return array
     */
    public function getFormOption($primaryKey = 0, $select = array(self::OPTION_PRIMARY_KEY, self::OPTION_NAME))
    {
        if (empty($primaryKey)) {
            return array();
        }
        if (is_array($select)) {
            $select = implode(',', $select);
        }
        $key = static::CACHE_FORM_OPTION . $primaryKey .':'. md5($select);
        $resultArray = $this->cache->get($key);

        if ($resultArray === false) {    // If not exist in the cache
            $this->db->select($select);
            $this->db->where(static::OPTION_PRIMARY_KEY, $primaryKey);
            $this->db->get($this->optionTableName);
            $resultArray = $this->db->rowArray(true);

            $this->cache->set($key, $resultArray, $this->expiration);
        }
        return $resultArray;
    }

    /**
     * Get options fields
     * 
     * @param int   $formId option form id
     * @param mixed $select select fields
     * 
     * @return array
     */
    public function getFormOptions($formId = 0, $select = array(self::OPTION_NAME, self::OPTION_VALUE))
    {
        if (empty($formId)) {
            return array();
        }
        if (is_array($select)) {
            $select = implode(',', $select);
        }
        $key = static::CACHE_FORM_OPTIONS . $formId .':'. md5($select);
        $resultArray = $this->cache->get($key);
        if ($resultArray === false) { // If not exist in the cache
            $this->db->select($select);
            $this->db->where(static::OPTION_FORM_ID, $formId);
            $this->db->get($this->optionTableName);
            $resultArray = $this->db->resultArray(true);
            $this->cache->set($key, $resultArray, $this->expiration);
        }
        return $resultArray;
    }

    /**
     * Get database fields
     * 
     * @param mixed $select select fields
     * 
     * @return array
     */
    public function getAllForms($select = array(self::FORM_PRIMARY_KEY, self::FORM_NAME, self::FORM_RESOURCE_ID, self::FORM_ACTION, self::FORM_ATTRIBUTE))
    {
        if (is_array($select)) {
            $select = implode(',', $select);
        }
        $key = static::CACHE_ALL_FORMS . md5($select);
        $resultArray = $this->cache->get($key);
        if ($resultArray === false) { // If not exist in the cache
            $this->db->select($select);
            $this->db->get($this->formTableName);
            $resultArray = $this->db->resultArray();
            $this->cache->set($key, $resultArray, $this->expiration);
        }
        return $resultArray;
    }

    /**
     * Add permission
     * 
     * @param array $data save data
     * 
     * @return void
     */
    public function addPermission($data)
    {
        $permSave = new Save($this->c);
        $permSave->add($data);
    }

    /**
     * Save form
     * 
     * @param array $data form data
     * 
     * @return void
     */
    public function insertForm($data)
    {
        $this->db->insert($this->formTableName, $data);
        $this->deleteCache(static::CACHE_ALL_FORMS);
        $this->deleteCache(static::CACHE_FORM_ATTRIBUTES);
    }
    
    /**
     * Save form element
     * 
     * @param array $data element data
     * 
     * @return void
     */
    public function insertFormElement($data)
    {
        // If value type array, we'll change it with the type of JSON.
        if (isset($data[static::ELEMENT_VALUE]) AND is_array($data[static::ELEMENT_VALUE])) {
            $data[static::ELEMENT_VALUE] = $this->toJson($data[static::ELEMENT_VALUE]);
        }
        if ($data[static::ELEMENT_GROUP_ID] > 0) {
            $groupData = array(
                static::GROUP_PRIMARY_KEY        => $data[static::ELEMENT_GROUP_ID],
                static::GROUP_FORM_ID            => $data[static::ELEMENT_FORM_ID],
                static::GROUP_NUMBER_OF_ELEMENTS => '+1'
            );
            $this->updateFormElementCount($groupData);
        }
        $this->updateFormGroupOrder($data, '1');
        $this->updateFormElementOrder($data, '1');
        $this->db->insert($this->elementTableName, $data);

        $this->deleteCache(static::CACHE_FORM_ELEMENTS . $data[static::ELEMENT_FORM_ID]);
    }

    /**
     * Save form option
     * 
     * @param array $data option data
     * 
     * @return void
     */
    public function insertFormOption($data)
    {
        $this->db->insert($this->optionTableName, $data);
        $this->deleteCache(static::CACHE_FORM_OPTIONS . $data[static::OPTION_FORM_ID]);
    }

    /**
     * Save form group
     * 
     * @param array $data group data
     * 
     * @return void
     */
    public function insertFormGroup($data)
    {
        $this->db->insert($this->groupTableName, $data);
        $this->deleteCache(static::CACHE_ELEMENT_GROUPS . $data[static::GROUP_FORM_ID]);
    }

    /**
     * Update form
     * 
     * @param array $data form data
     * 
     * @return void
     */
    public function updateForm($data)
    {
        $this->db->where(static::FORM_PRIMARY_KEY, $data[static::FORM_PRIMARY_KEY]);
        $this->db->update($this->formTableName, $data);

        $this->deleteCache(static::CACHE_FORM . $data[static::FORM_PRIMARY_KEY]);
        $this->deleteCache(static::CACHE_ALL_FORMS);
        $this->deleteCache(static::CACHE_FORM_ATTRIBUTES);
    }

    /**
     * Update form element
     * 
     * @param array $data form element data
     * 
     * @return void
     */
    public function updateFormElement($data)
    {
        $oldElementsData = $this->getFormElement($data[static::ELEMENT_PRIMARY_KEY], array(static::ELEMENT_FORM_ID, static::ELEMENT_GROUP_ID)); // Old elements data.
        if (( ! empty($oldElementsData[static::ELEMENT_GROUP_ID]) OR ! empty($data[static::ELEMENT_GROUP_ID]))
            AND $oldElementsData[static::ELEMENT_GROUP_ID] != $data[static::ELEMENT_GROUP_ID]
        ) {
            if ($oldElementsData[static::ELEMENT_GROUP_ID] > 0) {
                $reduceGroup = array( // Reduce group (-1)
                    static::GROUP_PRIMARY_KEY => $oldElementsData[static::ELEMENT_GROUP_ID],
                    static::GROUP_FORM_ID => $oldElementsData[static::ELEMENT_FORM_ID],
                    static::GROUP_NUMBER_OF_ELEMENTS => '-1'
                );
                $this->updateFormElementCount($reduceGroup);
            }
            if ($data[static::ELEMENT_GROUP_ID] > 0) {
                $increaseGroup = array( // Increase group (+1)
                    static::GROUP_PRIMARY_KEY => $data[static::ELEMENT_GROUP_ID],
                    static::GROUP_FORM_ID => $data[static::ELEMENT_FORM_ID],
                    static::GROUP_NUMBER_OF_ELEMENTS => '+1'
                );
                $this->updateFormElementCount($increaseGroup);
            }
        }
        // If value type array, we'll change it with the type of JSON.
        if (isset($data[static::ELEMENT_VALUE]) AND is_array($data[static::ELEMENT_VALUE])) {
            $data[static::ELEMENT_VALUE] = $this->toJson($data[static::ELEMENT_VALUE]);
        }
        $this->db->where(static::ELEMENT_PRIMARY_KEY, $data[static::ELEMENT_PRIMARY_KEY]);
        $this->db->update($this->elementTableName, $data);
        $this->deleteCache(static::CACHE_FORM_ELEMENT . $data[static::ELEMENT_PRIMARY_KEY]);
        $this->deleteCache(static::CACHE_FORM_ELEMENTS . $data[static::ELEMENT_FORM_ID]);
        $this->deleteCache(static::CACHE_FORM_ELEMENTS . $oldElementsData[static::ELEMENT_FORM_ID]);
    }

    /**
     * Update form option
     * 
     * @param array $data form option data
     * 
     * @return void
     */
    public function updateFormOption($data)
    {
        $this->db->where(static::OPTION_PRIMARY_KEY, $data[static::OPTION_PRIMARY_KEY]);
        $this->db->update($this->optionTableName, $data);

        $this->deleteCache(static::CACHE_FORM_OPTION . $data[static::OPTION_PRIMARY_KEY]);
        $this->deleteCache(static::CACHE_FORM_OPTIONS . $data[static::OPTION_FORM_ID]);
    }

    /**
     * Update form group
     * 
     * @param array $data group data
     * 
     * @return void
     */
    public function updateFormGroup($data)
    {
        $oldData = $this->getFormGroup($data[static::GROUP_PRIMARY_KEY], static::GROUP_FORM_ID);

        $this->db->where(static::GROUP_PRIMARY_KEY, $data[static::GROUP_PRIMARY_KEY]);
        $this->db->update($this->groupTableName, $data);

        $this->deleteCache(static::CACHE_ELEMENT_GROUP . $data[static::GROUP_PRIMARY_KEY]);
        $this->deleteCache(static::CACHE_ELEMENT_GROUPS . $data[static::GROUP_FORM_ID]);
        $this->deleteCache(static::CACHE_ELEMENT_GROUPS . $oldData[static::GROUP_FORM_ID]);
    }

    /**
     * Update form group
     * 
     * @param array $data   group data
     * @param int   $amount amount
     * 
     * @return void
     */
    public function updateFormGroupOrder($data, $amount)
    {
        $this->db->where(static::GROUP_ORDER . ' >=', $data[static::ELEMENT_ORDER]);
        $this->db->where(static::GROUP_FORM_ID, $data[static::ELEMENT_FORM_ID]);
        $this->db->set(static::GROUP_ORDER, static::GROUP_ORDER . '+'. $amount, false);
        $this->db->update($this->groupTableName);

        $this->deleteCache(static::CACHE_ELEMENT_GROUP . $data[static::ELEMENT_GROUP_ID]);
        $this->deleteCache(static::CACHE_ELEMENT_GROUPS . $data[static::ELEMENT_FORM_ID]);
    }

    /**
     * Update form group
     * 
     * @param array $data   group data
     * @param int   $amount amount
     * 
     * @return void
     */
    public function updateFormElementOrder($data, $amount)
    {
        $this->db->where(static::ELEMENT_ORDER . ' >=', $data[static::ELEMENT_ORDER]);
        $this->db->where(static::ELEMENT_GROUP_ID, $data[static::ELEMENT_GROUP_ID]);
        $this->db->where(static::ELEMENT_FORM_ID, $data[static::ELEMENT_FORM_ID]);
        $this->db->set(static::ELEMENT_ORDER, static::ELEMENT_ORDER . '+'. $amount, false);
        $this->db->update($this->elementTableName);

        $this->deleteCache(static::CACHE_FORM_ELEMENTS . $data[static::ELEMENT_FORM_ID]);
    }

    /**
     * Update form group
     * 
     * @param array $data group data
     * 
     * @return void
     */
    public function updateFormElementCount($data)
    {
        $this->db->where(static::GROUP_PRIMARY_KEY, $data[static::GROUP_PRIMARY_KEY]);
        $this->db->set(static::GROUP_NUMBER_OF_ELEMENTS, static::GROUP_NUMBER_OF_ELEMENTS . $data[static::GROUP_NUMBER_OF_ELEMENTS], false);
        $this->db->update($this->groupTableName);

        $this->deleteCache(static::CACHE_ELEMENT_GROUP . $data[static::GROUP_PRIMARY_KEY]);
        $this->deleteCache(static::CACHE_ELEMENT_GROUPS . $data[static::GROUP_FORM_ID]);
    }

    /**
     * Delete Form
     * 
     * @param int $formId primaryKey form id
     * 
     * @return void
     */
    public function deleteForm($formId)
    {
        // We will delete first child's table.
        // The first process form elements table.
        $this->db->where(static::ELEMENT_FORM_ID, $formId);
        $this->db->delete($this->elementTableName);

        // The second process form options table.
        $this->db->where(static::OPTION_FORM_ID, $formId);
        $this->db->delete($this->optionTableName);

        // The most recent form table.
        $this->db->where(static::FORM_PRIMARY_KEY, $formId);
        $this->db->delete($this->formTableName);
        
        // Element cache
        $this->deleteCache(static::CACHE_FORM_ELEMENTS . $formId);
        
        // Option cache
        $this->deleteCache(static::CACHE_FORM_OPTIONS . $formId);

        // Form cache
        $this->deleteCache(static::CACHE_FORM . $formId);
        $this->deleteCache(static::CACHE_ALL_FORMS);
        $this->deleteCache(static::CACHE_FORM_ATTRIBUTES);
    }

    /**
     * Delete form element
     * 
     * @param int $primaryKey element id
     * 
     * @return void
     */
    public function deleteFormElement($primaryKey)
    {
        $data = $this->getFormElement($primaryKey, array(static::ELEMENT_FORM_ID, static::ELEMENT_GROUP_ID, static::ELEMENT_ORDER));
        if ($data[static::ELEMENT_GROUP_ID] > 0) {
            $groupData = array(
                static::GROUP_PRIMARY_KEY => $data[static::ELEMENT_GROUP_ID],
                static::GROUP_FORM_ID => $data[static::ELEMENT_FORM_ID],
                static::GROUP_NUMBER_OF_ELEMENTS => '-1'
            );
            $this->updateFormElementCount($groupData);
        }
        $this->updateFormGroupOrder($data, '-1');
        $this->updateFormElementOrder($data, '-1');
        $this->db->where(static::ELEMENT_PRIMARY_KEY, $primaryKey);
        $this->db->delete($this->elementTableName);

        $this->deleteCache(static::CACHE_FORM_ELEMENT . $primaryKey);
        $this->deleteCache(static::CACHE_FORM_ELEMENTS . $data[static::ELEMENT_FORM_ID]);
    }

    /**
     * Delete form option
     * 
     * @param int $primaryKey option id
     * 
     * @return void
     */
    public function deleteFormOption($primaryKey)
    {
        $data = $this->getFormOption($primaryKey, self::OPTION_FORM_ID);

        $this->db->where(static::OPTION_PRIMARY_KEY, $primaryKey);
        $this->db->delete($this->optionTableName);

        $this->deleteCache(static::CACHE_FORM_OPTION . $primaryKey);
        $this->deleteCache(static::CACHE_FORM_OPTIONS . $data[static::OPTION_FORM_ID]);
    }

    /**
     * Delete group
     * 
     * @param int $primaryKey group id
     * 
     * @return void
     */
    public function deleteFormGroup($primaryKey)
    {
        $data = $this->getFormGroup($primaryKey, self::GROUP_FORM_ID);

        $this->db->where(static::GROUP_PRIMARY_KEY, $primaryKey);
        $this->db->delete($this->groupTableName);

        $this->deleteCache(static::CACHE_ELEMENT_GROUP . $primaryKey);
        $this->deleteCache(static::CACHE_ELEMENT_GROUPS . $data[static::GROUP_FORM_ID]);
    }

    /**
     * Delete cache
     * 
     * @param string $key redis key
     * 
     * @return void
     */
    public function deleteCache($key)
    {
        $keys = $this->cache->getAllKeys(rtrim($key, ':') . ':*');
        return $this->cache->delete($keys);
    }

    /**
     * Render
     * 
     * @param string $operationName operations name
     * 
     * Operations:
     * -----------
     * 1. view // Don't send view.
     * 2. update
     * 3. delete
     * 4. insert
     * 5. save // Both update and insert process.
     * 
     * @return void
     */
    public function render($operationName = '')
    {
        if ($operationName == 'view') {
            throw new RunTimeException('"View" already required in render and Jelly Form always use it when generating forms. If you want to just view operation, you should not send any value.');
        }
        $this->operationName = $operationName;
        $formData = $this->getFormAttributes(array(static::FORM_PRIMARY_KEY, static::FORM_ID, static::FORM_ATTRIBUTE, static::FORM_ACTION, static::FORM_NAME));
        if ($formData == false) {
            // set error message
            // $this->setMessage('form data');
            return;
        }
        $optionData  = $this->getFormOptions($formData[static::FORM_PRIMARY_KEY], '*');
        $elementData = $this->getFormElements($formData[static::FORM_PRIMARY_KEY], '*');
        
        return $this->renderArray($this->setOption($optionData, $formData), $elementData);
    }
}

// END Form Class
/* End of file Form.php */

/* Location: .Obullo/Jelly/Form.php */