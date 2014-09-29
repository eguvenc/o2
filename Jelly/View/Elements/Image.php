<?php

namespace Obullo\Jelly\View\Elements;

use Obullo\Jelly\Form,
    Obullo\Jelly\View\View;

/**
 * Jelly Radio
 * 
 * @category  Jelly
 * @package   Elements
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/jelly
 */
Class Image implements ElementsInterface
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
     * Image
     *
     * Generates an <img /> element
     *
     * @param mixed  $src        folder image path via filename
     * @param string $attributes attributes
     * @param string $assetPath  asset path
     * 
     * @return   string
     */
    public function img($src = '', $attributes = '', $assetPath = true)
    {
        if (!is_array($src)) {
            $src = array('src' => $src);
        }
        $img = '<img';
        foreach ($src as $k => $v) {
            $v = ltrim($v, '/');   // remove first slash
            if ($k == 'src' AND strpos($v, '://') === false) {
                if ($assetPath === true) {
                    $img .= ' src="' . $this->getAssetPath($v, 'images') . '" ';
                } else {
                    $img .= ' src="' . $v . '" ';
                }
            } else {
                $img .= " $k=\"$v\" ";   // for http://
            }
        }
        $img .= $attributes . ' />';
        return $img;
    }

    /**
     * Get assets directory path
     *
     * @param string $file      url
     * @param string $extraPath extra path
     * @param string $ext       extension ( css or js )
     * 
     * @return   string | false
     */
    public function getAssetPath($file, $extraPath = '', $ext = '')
    {
        $paths = array();
        if (strpos($file, '/') !== false) {
            $paths = explode('/', $file);
            $file = array_pop($paths);
        }
        $subPath = '';
        if (count($paths) > 0) {
            $subPath = implode('/', $paths) . '/'; // .assets/css/sub/welcome.css  sub dir support
        }
        $folder = $ext . '/';
        if ($extraPath != '') {
            $extraPath = trim($extraPath, '/') . '/';
            $folder = '';
        }
        $assetsUrl = str_replace(DS, '/', ASSETS);
        $assetsUrl = str_replace(ROOT, '', ASSETS);
        return $this->c->load('uri')->getAssetsUrl('', false) . $assetsUrl . $extraPath . $folder . $subPath . $file;
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
        if ($data[Form::ELEMENT_ROLE] === 'widget') {
            $assetPath = false;
            if (strpos($data[Form::ELEMENT_VALUE], 'http://') === false) {
                $assetPath = true;
            }
            $element .= $this->img($data[Form::ELEMENT_VALUE], $data[Form::ELEMENT_ATTRIBUTE], $assetPath);
        } else {
            $element.= $this->formElement->input($data[Form::ELEMENT_NAME], $data[Form::ELEMENT_VALUE], '', $data[Form::ELEMENT_ATTRIBUTE]);
        }

        if ($view->isTranslatorEnabled() === true) {
            $this->c->load('return translator');
            $data[Form::ELEMENT_LABEL] = $this->c['translator'][$data[Form::ELEMENT_LABEL]];
            $extra[Form::ELEMENT_LABEL] = $this->c['translator'][$extra[Form::ELEMENT_LABEL]];
            $extra[Form::ELEMENT_DESCRIPTION] = $this->c['translator'][$extra[Form::ELEMENT_DESCRIPTION]];
        }
        if ($view->isGroup() === true) {
            if ($view->isGrouped() === true) {

                $elementTemp = $view->createHiddenInput($extra[Form::ELEMENT_NAME]) . $view->getGroupElementsTemp();
                
                return $this->jellyForm->getGroupDiv(
                    $elementTemp,
                    $extra[Form::ELEMENT_NAME],
                    $extra[Form::ELEMENT_LABEL]
                );
            }
            return $this->jellyForm->getGroupParentDiv($element, $view->getColSm(), $data[Form::ELEMENT_LABEL]);
        }
        $element = $this->jellyForm->getElementDiv($element, $extra[Form::ELEMENT_LABEL]);
        $element.= $this->jellyForm->getDescriptionDiv($extra[Form::ELEMENT_DESCRIPTION], '');
        return $element;
    }
}

// END Radio Class
/* End of file Radio.php */

/* Location: .Obullo/Jelly/Radio.php */