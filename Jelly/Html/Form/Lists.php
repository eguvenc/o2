<?php

namespace Obullo\Jelly\Html\Form;

use Obullo\Jelly\Form;

/**
 * Lists
 * 
 * @category  Jelly
 * @package   Html
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs
 */
Class Lists
{
    /**
     * Jelly Form object
     * 
     * @var object
     */
    public $jellyForm = null;

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
    }

    /**
     * Pring form list
     * 
     * @param mix $attributes attributes
     * 
     * @return array
     */
    public function printList($attributes = '')
    {
        $attr = $attributes;
        if (is_array($attributes)) {
            $attr = ''; // If attributes is array reset attr variable
            foreach ($attributes as $key => $val) {
                $attr .= $key .'="'. $val .'"';
            }
        }
        $table     = '<table '. $attr .'>';
        $tableAttr = $this->jellyForm->getDefaultFormAttributes();
        array_push($tableAttr, 'Method', 'Action');
        $table    .= $this->createTableHeader($tableAttr);
        $data      = $this->jellyForm->getAllForms(
            array(
                Form::FORM_PRIMARY_KEY,
                Form::FORM_NAME,
                Form::FORM_ID,
                Form::FORM_RESOURCE_ID,
                Form::FORM_ACTION,
                Form::FORM_ATTRIBUTE,
                Form::FORM_METHOD
            )
        ); // 'id,name,resource_id,action,attribute,method'
        $linkData  = array();
        $tableData = array();
        foreach ($data as $key => $val) {
            foreach ($val as $k => $v) {
                if ($k == Form::FORM_PRIMARY_KEY) {
                    $linkData[$key][$k] = $v;
                } else {
                    $tableData[$key][$k] = $v;
                }
            }
        }
        $printTable = $this->createTableBody($tableData);
        $table     .= $this->createLinks($printTable, $linkData, '/jelly/delete_form', '/jelly/edit_form');

        return $table.= '</table>';
    }

    /**
     * Print form element list
     * 
     * @param mix $attributes attributes
     * 
     * @return array
     */
    public function printElementList($attributes = '')
    {
        $attr = $attributes;
        if (is_array($attributes)) {
            $attr = ''; // If attributes is array reset attr variable
            foreach ($attributes as $key => $val) {
                $attr .= $key .'="'. $val .'"';
            }
        }
        $table     = '<table '. $attr .'>';
        $tableAttr = $this->jellyForm->getDefaultElementAttributes();
        array_unshift($tableAttr, 'type');
        array_push($tableAttr, 'Group ID', 'Role', 'Action');
        $table    .= $this->createTableHeader($tableAttr);
        $data      = $this->jellyForm->getFormElements(
            $this->jellyForm->formId,
            array(
                Form::ELEMENT_PRIMARY_KEY,
                Form::ELEMENT_TYPE,
                Form::ELEMENT_NAME,
                Form::ELEMENT_LABEL,
                Form::ELEMENT_VALUE,
                Form::ELEMENT_ATTRIBUTE,
                Form::ELEMENT_TITLE,
                Form::ELEMENT_RULES,
                Form::ELEMENT_ORDER,
                Form::ELEMENT_GROUP_ID,
                Form::ELEMENT_ROLE
            )
        ); // 'id,type,label,name,value,attribute,title,rules,order,group_id,role'
        $linkData  = array();
        $tableData = array();
        foreach ($data as $key => $val) {
            foreach ($val as $k => $v) {
                if ($val[Form::ELEMENT_TYPE] === 'input') {
                    $v = str_replace('input', 'text', $v);
                }
                if ($k === Form::ELEMENT_VALUE) {
                    $v = substr($v, 0, 40);
                }
                if ($k === Form::ELEMENT_PRIMARY_KEY) {
                    $linkData[$key][$k] = $v;
                } else {
                    $tableData[$key][$k] = $v;
                }
            }
        }
        $printTable = $this->createTableBody($tableData);
        $table     .= $this->createLinks($printTable, $linkData, '/jelly/delete_element', '/jelly/edit_element');

        return $table.= '</table>';
    }

    /**
     * Print form element group list
     * 
     * @param mix $attributes attributes
     * 
     * @return array
     */
    public function printGroupList($attributes = '')
    {
        $attr = $attributes;
        if (is_array($attributes)) {
            $attr = ''; // If attributes is array reset attr variable
            foreach ($attributes as $key => $val) {
                $attr .= $key .'="'. $val .'"';
            }
        }
        $table     = '<table '. $attr .'>';
        $header    = $this->jellyForm->getDefaultGroupAttributes();
        array_push($header, 'Action');
        $table    .= $this->createTableHeader($header);
        $data      = $this->jellyForm->getFormGroups(
            $this->jellyForm->formId,
            array(
                Form::GROUP_PRIMARY_KEY,
                Form::GROUP_NAME,
                Form::GROUP_LABEL,
                Form::GROUP_CLASS,
                Form::GROUP_VALUE,
                Form::GROUP_FORM_TO_DATABASE,
                Form::GROUP_DATABASE_TO_FORM,
                Form::GROUP_DESCRIPTION,
                Form::GROUP_ORDER
            )
        ); // 'id,name,label,class,value,func,desc,order'
        $linkData  = array();
        $tableData = array();
        foreach ($data as $key => $val) {
            foreach ($val as $k => $v) {
                if ($k === Form::GROUP_PRIMARY_KEY) {
                    $linkData[$key][$k] = $v;
                } else {
                    $tableData[$key][$k] = $v;
                }
            }
        }
        $printTable = $this->createTableBody($tableData);
        $table     .= $this->createLinks($printTable, $linkData, '/jelly/delete_group', '/jelly/edit_group');
        
        return $table.= '</table>';
    }

    /**
     * Print form option list
     * 
     * @param mix $attributes attributes
     * 
     * @return array
     */
    public function printOptionList($attributes = '')
    {
        $attr = $attributes;
        if (is_array($attributes)) {
            $attr = ''; // If attributes is array reset attr variable
            foreach ($attributes as $key => $val) {
                $attr .= $key .'="'. $val .'"';
            }
        }
        $table     = '<table '. $attr .'>';
        $table    .= $this->createTableHeader(array('Name', 'Value', 'Action'));
        $data      = $this->jellyForm->getFormOptions(
            $this->jellyForm->formId,
            array(
                Form::OPTION_PRIMARY_KEY,
                Form::OPTION_NAME,
                Form::OPTION_VALUE
            )
        ); // 'id,name,value'
        $linkData  = array();
        $tableData = array();
        foreach ($data as $key => $val) {
            foreach ($val as $k => $v) {
                if ($k === Form::OPTION_PRIMARY_KEY) {
                    $linkData[$key][$k] = $v;
                } else {
                    $tableData[$key][$k] = $v;
                }
            }
        }
        $printTable = $this->createTableBody($tableData);
        $table     .= $this->createLinks($printTable, $linkData, '/jelly/delete_option', '/jelly/edit_option');

        return $table.= '</table>';
    }

    /**
     * Create table header
     * 
     * @param array $data table data
     * 
     * @return string
     */
    public function createTableHeader($data)
    {
        $table = '
                <thead>
                <tr>';
        foreach ($data as $val) {
            $table .= '<th>' . ucfirst($val) . '</th>';
        }
        $table .= '
                </tr>
                </thead>';
                
        return $table;
    }

    /**
     * Create table
     * 
     * @param array $data table data
     * 
     * @return string
     */
    public function createTableBody($data = array())
    {
        $table = '';
        foreach ($data as $val) {
            $table .= '<tbody><tr>';
            foreach ($val as $v) {
                $table.= '<td>' . $v . '</td>';
            }
            $table.= '<td>
                        %s
                       </td>
                      </tr></tbody>';
        }
        return $table;
    }

    /**
     * Create links
     * 
     * @param string $tableData  table html data
     * @param array  $linkData   link data
     * @param string $removeLink remove link
     * @param string $editLink   edit link
     * 
     * @return string
     */
    public function createLinks($tableData = array(), $linkData = array(), $removeLink = '', $editLink = '')
    {
        if (count($linkData) == 0) {
            return false;
        }
        $links = array();
        foreach ($linkData as $val) {
            foreach ($val as $v) {
                $links[] = '<a href="'. $removeLink .'/'. $v . '"><span class="glyphicon glyphicon-remove"></span></a>
                            <a href="'. $editLink .'/'. $v . '"><span class="glyphicon glyphicon-edit"></span></a>';
            }
        }
        return vsprintf($tableData, $links);
    }

}

// END FormList Class
/* End of file FormList.php */

/* Location: .Obullo/Jelly/FormList.php */