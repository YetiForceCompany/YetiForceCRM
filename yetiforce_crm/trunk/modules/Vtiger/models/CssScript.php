<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * CSS Script Model Class
 */
class Vtiger_CssScript_Model extends Vtiger_Base_Model {

	const DEFAULT_REL = 'stylesheet';
	const DEFAULT_MEDIA = 'screen';
	const DEFAULT_TYPE = 'text/css';

	const LESS_REL = 'stylesheet/less';

	/**
	 * Function to get the rel attribute value
	 * @return <String>
	 */
	public function getRel(){
		$rel = $this->get('rel');
		if(empty($rel)){
			$rel = self::DEFAULT_REL;
		}
		return $rel;
	}

	/**
	 * Function to get the media attribute value
	 * @return <String>
	 */
	public function getMedia(){
		$media = $this->get('media');
		if(empty($media)){
			$media = self::DEFAULT_MEDIA;
		}
		return $media;
	}

	/**
	 * Function to get the type attribute value
	 * @return <String>
	 */
	public function getType(){
		$type = $this->get('type');
		if(empty($type)){
			$type = self::DEFAULT_TYPE;
		}
		return $type;
	}

	/**
	 * Function to get the href attribute value
	 * @return <String>
	 */
	public function getHref() {
		$href = $this->get('href');
		if(empty($href)) {
			$href = $this->get('linkurl');
		}
		return $href;
	}

	/**
	 * Function to get the instance of CSS Script model from a given Vtiger_Link object
	 * @param Vtiger_Link $linkObj
	 * @return Vtiger_CssScript_Model instance
	 */
	public static function getInstanceFromLinkObject (Vtiger_Link $linkObj){
		$objectProperties = get_object_vars($linkObj);
		$linkModel = new self();
		foreach($objectProperties as $properName=>$propertyValue){
			$linkModel->$properName = $propertyValue;
		}
		return $linkModel->setData($objectProperties);
	}

}
