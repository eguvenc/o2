<?php

namespace Obullo\Jelly\View\Elements;

use Obullo\Jelly\Form,
    Obullo\Jelly\View\View;

/**
 * Jelly Hidden
 * 
 * @category  Jelly
 * @package   Elements
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/jelly
 */
Class Hidden implements ElementsInterface
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
        return $this->formElement->hidden($data[Form::ELEMENT_NAME], $data[Form::ELEMENT_VALUE], $data[Form::ELEMENT_ATTRIBUTE]);
        // return $this->jellyForm->getElementDiv($element, '', '');
    }
}

// END Hidden Class
/* End of file Hidden.php */

/* Location: .Obullo/Jelly/Hidden.php */