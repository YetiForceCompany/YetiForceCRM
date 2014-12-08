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
 * Vtiger Image Model Class
 */
class Vtiger_Image_Model extends Vtiger_Base_Model {

	/**
	 * Function to get the title of the Image
	 * @return <String>
	 */
	public function getTitle(){
		return $this->get('title');
	}

	/**
	 * Function to get the alternative text for the Image
	 * @return <String>
	 */
	public function getAltText(){
		return $this->get('alt');
	}

	/**
	 * Function to get the Image file path
	 * @return <String>
	 */
	public function getImagePath(){
		return Vtiger_Theme::getImagePath($this->get('imagename'));
	}

	/**
	 * Function to get the Image file name
	 * @return <String>
	 */
	public function getImageFileName(){
		return $this->get('imagename');
	}

}