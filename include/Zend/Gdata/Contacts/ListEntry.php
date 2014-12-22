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

require_once 'Zend/Gdata/Entry.php';
require_once 'Zend/Gdata/Contacts/Extension/Name.php';
require_once 'Zend/Gdata/Contacts/Extension/Notes.php';
require_once 'Zend/Gdata/Contacts/Extension/Email.php';
require_once 'Zend/Gdata/Contacts/Extension/Im.php';
require_once 'Zend/Gdata/Contacts/Extension/PhoneNumber.php';
require_once 'Zend/Gdata/Contacts/Extension/StructuredPostalAddress.php';
require_once 'Zend/Gdata/Contacts/Extension/Organization.php';
require_once 'Zend/Gdata/Extension/ExtendedProperty.php';
require_once 'Zend/Gdata/Contacts/Extension/Category.php';
 
/**
 * Represents a contact entry.
 *
 */
class Zend_Gdata_Contacts_ListEntry extends Zend_Gdata_Entry
{
	
	protected $_entryClassName = 'Zend_Gdata_Contacts_ListEntry';

	protected $_addresses = null;
	protected $_categories= null;
	protected $_emails = null;
	protected $_extendedProperties = null;
	protected $_ims = null;
	protected $_name = null;
	protected $_notes = null;
	protected $_organization = null;
	protected $_phones = null;
        protected $_pobox=null; 
        protected $_country=null; 
        protected $_postcode=null; 
        protected $_city=null; 
        protected $_region=null; 
        protected $_street=null; 

	public function __construct($element = null) {
        $this->registerAllNamespaces(Zend_Gdata_Contacts::$namespaces);
        parent::__construct($element);
    }
	
	public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null) {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
		if ($this->_addresses != null) {
			foreach ($this->_addresses as $address) {
				$element->appendChild($address->getDOM($element->ownerDocument));
			}
		}
		if ($this->_categories != null) {
			foreach ($this->_categories as $category) {
				$element->appendChild($category->getDOM($element->ownerDocument));
			}
		}
		if ($this->_emails != null) {
			foreach ($this->_emails as $email) {
				$element->appendChild($email->getDOM($element->ownerDocument));
			}
		}
		if ($this->_extendedProperties != null) {
			foreach ($this->_extendedProperties as $extendedProperty) {
				$element->appendChild($extendedProperty->getDOM($element->ownerDocument));
			}
		}
		if ($this->_ims != null) {
			foreach ($this->_ims as $im) {
				$element->appendChild($im->getDOM($element->ownerDocument));
			}
		}
		if ($this->_name != null) {
			$element->appendChild($this->_name->getDOM($element->ownerDocument));
		}
		if ($this->_notes != null) {
			$element->appendChild($this->_notes->getDOM($element->ownerDocument));
		}
		if ($this->_organization != null) {
			$element->appendChild($this->_organization->getDOM($element->ownerDocument));
		}
		if ($this->_phones != null) {
			foreach ($this->_phones as $phone) {
				$element->appendChild($phone->getDOM($element->ownerDocument));
			}
		}
		return $element;
	}
	
	protected function takeChildFromDOM($child) {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
		
		$gdNamespacePrefix = $this->lookupNamespace('gd') . ':';
		
        switch ($absoluteNodeName) {
        case $gdNamespacePrefix . 'structuredPostalAddress':
            $address = new Zend_Gdata_Contacts_Extension_StructuredPostalAddress();
            $address->transferFromDOM($child);
            $this->_addresses[] = $address;
            break;
		case $gdNamespacePrefix . 'category':
            $category = new Zend_Gdata_Contacts_Extension_Category();
            $category->transferFromDOM($child);
            $this->_categories[] = $category;
            break;
		case $gdNamespacePrefix . 'email':
            $email = new Zend_Gdata_Contacts_Extension_Email();
            $email->transferFromDOM($child);
            $this->_emails[] = $email;
            break;
		case $gdNamespacePrefix . 'extendedproperty':
            $extendedProperty = new Zend_Gdata_Contacts_Extension_ExtendedProperty();
            $extendedProperty->transferFromDOM($child);
            $this->_extendedProperties[] = $extendedProperty;
            break;
		case $gdNamespacePrefix . 'im':
            $im = new Zend_Gdata_Contacts_Extension_Im();
            $im->transferFromDOM($child);
            $this->_ims[] = $im;
            break;
        case $gdNamespacePrefix . 'name':
            $name = new Zend_Gdata_Contacts_Extension_Name();
            $name->transferFromDOM($child);
            $this->_name = $name;
            break;
		//case $gdNamespacePrefix . 'notes':
		case $this->lookupNamespace('atom') . ':' . 'notes';
            $notes = new Zend_Gdata_Contacts_Extension_Notes();
            $notes->transferFromDOM($child);
            $this->_notes = $notes;
            break;
		case $gdNamespacePrefix . 'organization':
            $organization = new Zend_Gdata_Contacts_Extension_Organization();
            $organization->transferFromDOM($child);
            $this->_organization = $organization;
            break;
		case $gdNamespacePrefix . 'phoneNumber':
            $phoneNumber = new Zend_Gdata_Contacts_Extension_PhoneNumber();
            $phoneNumber->transferFromDOM($child);
            $this->_phones[] = $phoneNumber;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }
 
    /**
     * Retrieves the name of this contact
     *
     * @return Zend_Gdata_Contacts_Extension_Name 
     */
    public function getName() {
		return $this->_name;
	}
     
    /**
     * @param Zend_Gdata_Contacts_Extension_Name $value
     */
    public function setName($value) {
		$this->_name = $value;
		return $this;
	}
     
    /**
     * Retrieves the text of any notes associated with this contact.
     *
     * @return Zend_Gdata_Contacts_Extension_Notes Note text
     */
    public function getNotes() {
		return $this->_notes;
	}
    /**
     * @param Zend_Gdata_Contacts_Extension_Notes $value
     */
    public function setNotes($value) {
		$this->_notes = $value;
		return $this;
	}
    /**
     * Retrieves a list of Zend_Gdata_Contacts_Extension_Email items.
     *
     * @todo return primary first, if any
     * @return array An array of Zend_Gdata_Contacts_Extension_Email objects
     */
    public function getEmails() {
		return $this->_emails;
	}
    /**
     * @param array $values Array of Zend_Gdata_Contacts_Extension_Email items
     * @return Zend_Gdata_Extension_ListEntry or else FALSE on error
     */
    public function setEmails($values) {
		$this->_emails = $values;
		return $this;
	}
     
    /**
     * Retrieves a list of Zend_Gdata_Contacts_Extension_Im items.
     *
     * @todo return primary first, if any
     * @return array An array of Zend_Gdata_Contacts_Extension_Im objects
     */
    public function getIms() {
		return $this->_ims;
	}
    /**
     * @param array $values Array of Zend_Gdata_Contacts_Extension_Im items
     * @return Zend_Gdata_Extension_ListEntry or else FALSE on error
     */
    public function setIms($values) {
		$this->_ims = $values;
		return $this;
	}
    /**
     * Retrieves a list of Zend_Gdata_Contacts_Extension_PhoneNumber items.
     *
     * @todo return primary first, if any
     * @return array An array of Zend_Gdata_Contacts_Extension_PhoneNumber objects
     */
    public function getPhones() {
		return $this->_phones;
	}
    /**
     * @param array $values Array of Zend_Gdata_Contacts_Extension_PhoneNumber items
     * @return Zend_Gdata_Extension_ListEntry or else FALSE on error
     */
    public function setPhones($values) {
		$this->_phones = $values;
	}
 
    /**
     * Sets the "primary" flag on the given object, and unsets it on all
     * sibling objects.
     *
     * @param Zend_Gdata_Contacts_Extension_Primary $object
     * @return boolean True on success, false on failure.
     */
    public function setPrimary(Zend_Gdata_Contacts_Extension_Primary $object) {
		
	}
     
    /**
     * Retrieves a list of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item.
     *
     * @todo return primary first, if any
     * @return List of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item.
     */
    public function getAddresses() {
		return $this->_addresses;
	}
     
    /**
     * @param mixed $value List of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item
     */
    public function setAddresses($values) {
		$this->_addresses = $values;
		return $this;
	}
     
    /**
     * Retrieves a list of Zend_Gdata_Contacts_Extension_Organization items.
     * 
     * @todo return primary first, if any
     * @return array An array of Zend_Gdata_Contacts_Extension_Organization objects
     */
    public function getOrgs() {
		return $this->_orgs;
	}
     
    /**
     * @param array $values Array of Zend_Gdata_Contacts_Extension_Organization items
     * @return Zend_Gdata_Extension_ListEntry or else FALSE on error
     */
    public function setOrgs($values) {
		$this->_orgs = $values;
		return $this;
	}
 
    /**
     * Retrieves a list of Zend_Gdata_Extension_ExtendedProperty items.
     *
     * @return array An array of Zend_Gdata_Extension_ExtendedProperty objects
     */
    public function getExtendedProperties() {
		return $this->_extendedProperties;
	}
 
    /**
     * Will fail if there are duplicate ExtendedProperty keys.
     * @param array $values Array of Zend_Gdata_Extension_ExtendedProperty items
     * @return Zend_Gdata_Contacts_ListEntry or else FALSE on error
     */
    public function setExtendedProperties($values) {
		$this->_extendedProperties = $values;
		return $this;
	}
     
    /**
     * Returns all detected categories for elements
     *
     * @return array Array of string labels
     */
    public function getCategories() {
		return $this->_categories;
	}
 
    /**
     * Returns all categorizable elements of a specific type (e.g. "work", "other", "MyCategory")
     *
     * @param string $name
     * @param string $caseSensitive
     * @return array Array of Zend_Gdata_Extension objects
     */
    public function getByCategory($name,$caseSensitive = true) {
		$this->_categories;
	}
        
     /**
     * Retrieves a list of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item.
     *
     * @todo return primary first, if any
     * @return List of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item.
     */
    public function getCity() {
        return $this->_city;
    }

    /**
     * @param mixed $value List of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item
     */
    public function setCity($values) {
        $this->_city = $values;
        return $this;
    }

    /**
     * Retrieves a list of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item.
     *
     * @todo return primary first, if any
     * @return List of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item.
     */
    public function getPobox() {
        return $this->_pobox;
    }

    /**
     * @param mixed $value List of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item
     */
    public function setPobox($values) {
        $this->_pobox = $values;
        return $this;
    }

    /**
     * Retrieves a list of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item.
     *
     * @todo return primary first, if any
     * @return List of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item.
     */
    public function getCountry() {
        return $this->_country;
    }

    /**
     * @param mixed $value List of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item
     */
    public function setCountry($values) {
        $this->_country = $values;
        return $this;
    }

    /**
     * Retrieves a list of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item.
     *
     * @todo return primary first, if any
     * @return List of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item.
     */
    public function getPostcode() {
        return $this->_postcode;
    }

    /**
     * @param mixed $value List of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item
     */
    public function setPostcode($values) {
        $this->_postcode = $values;
        return $this;
    }

    /**
     * Retrieves a list of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item.
     *
     * @todo return primary first, if any
     * @return List of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item.
     */
    public function getRegion() {
        return $this->_region;
    }

    /**
     * @param mixed $value List of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item
     */
    public function setRegion($values) {
        $this->_region = $values;
        return $this;
    }

    /**
     * Retrieves a list of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item.
     *
     * @todo return primary first, if any
     * @return List of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item.
     */
    public function getStreet() {
        return $this->_street;
    }

    /**
     * @param mixed $value List of Zend_Gdata_Contacts_Extension_StructuredPostalAddress item
     */
    public function setStreet($values) {
        $this->_street = $values;
        return $this;
    }

}