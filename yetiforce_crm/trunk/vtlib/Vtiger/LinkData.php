<?php

/* +*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 * ******************************************************************************* */

/**
 * @author MAK
 */

/**
 * Description of LinkData
 *
 * @author MAK
 */
class Vtiger_LinkData {
	protected $input;
	protected $link;
	protected $user;
	protected $module;

	public function __construct($link, $user, $input = null) {
		global $currentModule;
		$this->link = $link;
		$this->user = $user;
		$this->module = $currentModule;
		if(empty($input)) {
			$this->input = $_REQUEST;
		} else {
			$this->input = $input;
		}
	}

	public function getInputParameter($name) {
		return $this->input[$name];
	}

	/**
	 *
	 * @return Vtiger_Link
	 */
	public function getLink() {
		return $this->link;
	}

	/**
	 *
	 * @return Users 
	 */
	public function getUser() {
		return $this->user;
	}

	public function getModule() {
		return $this->module;
	}

}

?>