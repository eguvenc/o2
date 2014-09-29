<?php

namespace Obullo\Jelly\View\Elements;

use Obullo\Jelly\Form,
    Obullo\Jelly\View\View;

/**
 * Jelly Captcha
 * 
 * @category  Jelly
 * @package   Elements
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/jelly
 */
Class Captcha implements ElementsInterface
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
        $this->captcha     = $c->load('service/captcha');
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
        $element.= $this->formElement->input($data[Form::ELEMENT_NAME], $data[Form::ELEMENT_VALUE], $data[Form::ELEMENT_ATTRIBUTE]);
        $tmp     = $this->jellyForm->getGroupParentDiv($element);
        $captcha = '<img src="/index.php/widgets/captcha/create" id="captchaImg">';
        $tmp    .= $this->jellyForm->getGroupParentDiv($captcha);
        $tmp    .= '<a href="javascript:void(0);" onclick="document.getElementById(\'captchaImg\').src=\'/index.php/widgets/captcha/create/\'+Math.random();" id="image">Refresh</a> ';

        return $this->jellyForm->getGroupDiv(
            $tmp,
            $extra['label']
        );
    }
}

// END Captcha Class
/* End of file Captcha.php */

/* Location: .Obullo/Jelly/Captcha.php */