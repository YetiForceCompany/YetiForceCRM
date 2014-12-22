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

require_once 'Zend/Gdata/Contacts/Extension/FormattedAddress.php';
require_once 'Zend/Gdata/Contacts/Extension/Street.php';
require_once 'Zend/Gdata/Contacts/Extension/Postcode.php';
require_once 'Zend/Gdata/Contacts/Extension/Region.php';
require_once 'Zend/Gdata/Contacts/Extension/Country.php';
require_once 'Zend/Gdata/Contacts/Extension/City.php';
require_once 'Zend/Gdata/Contacts/Extension/Pobox.php';

class Zend_Gdata_Contacts_Extension_StructuredPostalAddress extends Zend_Gdata_Contacts_Extension {
	protected $_rootElement = 'structuredPostalAddress';
	
	protected $_rel;
	protected $_formattedAddress, $_street,$_postcode,$_city,$_pobox,$_region,$_country; 
	
	public function __construct($value = null, $rel = 'work') {
        parent::__construct();
		$this->_rel = $rel;
		$this->_formattedAddress = new Zend_Gdata_Contacts_Extension_FormattedAddress($value);
                $this->_city = new Zend_Gdata_Contacts_Extension_City($value);
                $this->_country = new Zend_Gdata_Contacts_Extension_Country($value);
                $this->_pobox = new Zend_Gdata_Contacts_Extension_Pobox($value);
                $this->_postcode = new Zend_Gdata_Contacts_Extension_Postcode($value);
                $this->_region = new Zend_Gdata_Contacts_Extension_Region($value);
    }
	
	public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null) {
		$element = parent::getDOM($doc, $majorVersion, $minorVersion);
		$element->setAttribute("rel", $this->lookupNamespace("gd").'#'.$this->_rel);
		if ($this->_formattedAddress != null) {
			$element->appendChild($this->_formattedAddress->getDOM($element->ownerDocument));
		}
		if ($this->_street != null) {
			$element->appendChild($this->_street->getDOM($element->ownerDocument));
		}
                if ($this->_postcode != null) {
                    
			$element->appendChild($this->_postcode->getDOM($element->ownerDocument));
		}
                if ($this->_pobox!= null) {
			$element->appendChild($this->_pobox->getDOM($element->ownerDocument));
		}
                if ($this->_city != null) {
			$element->appendChild($this->_city->getDOM($element->ownerDocument));
		}
                if ($this->_region != null) {
			$element->appendChild($this->_region->getDOM($element->ownerDocument));
		}
                if ($this->_country != null) {
			$element->appendChild($this->_country->getDOM($element->ownerDocument));
		}
		return $element;
	}
	
	/**
     * Creates individual Entry objects of the appropriate type and
     * stores them as members of this entry based upon DOM data.
     *
     * @param DOMNode $child The DOMNode to process
     */
    protected function takeChildFromDOM($child) {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
		$gdNamespacePrefix = $this->lookupNamespace('gd') . ':';

        switch ($absoluteNodeName) {
            case $gdNamespacePrefix . 'formattedAddress';
                $formattedAddress = new Zend_Gdata_Contacts_Extension_FormattedAddress();
                $formattedAddress->transferFromDOM($child);
                $this->_formattedAddress = $formattedAddress;
                break;
			case $gdNamespacePrefix . 'street';
                $street = new Zend_Gdata_Contacts_Extension_Street();
                $street->transferFromDOM($child);
                $this->_street = $street;
                break;
            case $gdNamespacePrefix . 'pobox';
                $pobox = new Zend_Gdata_Contacts_Extension_Pobox();
                $pobox->transferFromDOM($child);
                $this->_pobox = $pobox;
                break;
            case $gdNamespacePrefix . 'city';
                $city = new Zend_Gdata_Contacts_Extension_City();
                $city->transferFromDOM($child);
                $this->_city = $city;
                break;
            case $gdNamespacePrefix . 'country';
                $country = new Zend_Gdata_Contacts_Extension_Country();
                $country->transferFromDOM($child);
                $this->_country = $country;
                break;
            case $gdNamespacePrefix . 'region';
                $region = new Zend_Gdata_Contacts_Extension_Region();
                $region->transferFromDOM($child);
                $this->_region = $region;
                break;
            case $gdNamespacePrefix . 'postcode';
                $postcode = new Zend_Gdata_Contacts_Extension_Postcode();
                $postcode->transferFromDOM($child);
                $this->_postcode = $postcode;
                break;
        }
    }
	
	public function getValue() {
		return $this->_formattedAddress->getValue();
	}
	
	public function __toString() {
		$string = $this->_formattedAddress->__toString();
		if ($this->_street != null) $string .= "\n" . $this->_street->__toString();
		return trim($string);
	}


	public function setFormattedAddress($value) {
		$this->_formattedAddress = $value;
		return $this;
	}
	
	public function getFormattedAddress() {
		return $this->_formattedAddress;
	}
	
	public function setStreet($value) {
		$this->_street = $value;
		return $this;
	}
	
	public function getStreet() {
		return $this->_street;
	}
        
        public function getPobox() {
		return $this->_pobox;
	}
        public function setCity($value) {
		$this->_city = $value;
		return $this;
	}
	
	public function getCity() {
		return $this->_city;
	}
        public function setCountry($value) {
		$this->_country = $value;
		return $this;
	}
	
	public function getCountry() {
		return $this->_country;
	}
         public function setRegion($value) {
		$this->_region = $value;
		return $this;
	}
	
	public function getRegion() {
		return $this->_region;
	}
         public function setPostCode($value) {
		$this->_postcode = $value;
		return $this;
	}
	
	public function getPostCode() {
		return $this->_postcode;
	}
}