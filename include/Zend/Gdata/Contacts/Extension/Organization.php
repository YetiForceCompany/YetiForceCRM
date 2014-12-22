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

require_once 'Zend/Gdata/Contacts/Extension/OrgName.php';
require_once 'Zend/Gdata/Contacts/Extension/OrgTitle.php';

class Zend_Gdata_Contacts_Extension_Organization extends Zend_Gdata_Contacts_Extension {
	protected $_rootElement = 'organization';

	protected $_rel;
	protected $_orgName, $_orgTitle;
	
	public function __construct($name = null, $title = null, $rel = 'other') {
		parent::__construct();
		$this->_rel = $rel;
		if ($name != null) {
			$this->_orgName = new Zend_Gdata_Contacts_Extension_OrgName($name);
		}
		if ($title != null) {
			$this->_orgTitle= new Zend_Gdata_Contacts_Extension_OrgTitle($title);
		}
	}
	
	public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null) {
		$element = parent::getDOM($doc, $majorVersion, $minorVersion);
		$element->setAttribute('rel', $this->lookupNamespace('gd').'#'.$this->_rel);
		if ($this->_orgName != null) {
			$element->appendChild($this->_orgName->getDOM($element->ownerDocument));
		}
		if ($this->_orgTitle != null) {
			$element->appendChild($this->_orgTitle->getDOM($element->ownerDocument));
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
            case $gdNamespacePrefix . 'orgName';
                $orgName = new Zend_Gdata_Contacts_Extension_OrgName();
                $orgName->transferFromDOM($child);
                $this->_orgName = $orgName;
                break;
			case $gdNamespacePrefix . 'orgTitle';
                $orgTitle = new Zend_Gdata_Contacts_Extension_OrgTitle();
                $orgTitle->transferFromDOM($child);
                $this->_orgTitle = $orgTitle;
                break;
        }
    }
	
	public function getValue() {
		return $this->_orgName->getValue();
	}
	
}