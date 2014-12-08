<?php

/**
 * https://github.com/prasad83/Zend-Gdata-Contacts
 * @author prasad
 * 
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Contacts
 */
require_once 'Zend/Gdata/Contacts/Extension.php';

class Zend_Gdata_Contacts_Extension extends Zend_Gdata_Extension {

    protected $_rootNamespace = 'gd';
    protected $_rootElement = '';
    protected $_value = null;
	
	protected $_valueAttrName = 'value';

    public function __construct($value = null) {
        $this->registerAllNamespaces(Zend_Gdata_Contacts::$namespaces);
        parent::__construct();
        $this->_value = $value;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null) {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_value != null) {
            $element->setAttribute('value', $this->_value);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute) {
        switch ($attribute->localName) {
        case $this->_valueAttrName:
            $this->_value = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getValue() {
        return $this->_value;
    }

    public function setValue($value) {
        $this->_value = $value;
        return $this;
    }

    public function __toString() {
        return $this->getValue();
    }

}

class Zend_Gdata_Contacts_ExtensionElement extends Zend_Gdata_Extension {
	
	public function __construct($value = null) {
        $this->registerAllNamespaces(Zend_Gdata_Contacts::$namespaces);
        parent::__construct();
        $this->_text = $value;
    }
	
	public function getValue() {
        return $this->_text;
    }

}