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

namespace vtlib;

/**
 * Description of LinkData.
 *
 * @author MAK
 */
class LinkData
{
	protected $link;
	protected $user;
	protected $module;

	public function __construct($link)
	{
		$this->link = $link;
		$this->module = \App\Request::_getModule();
	}

	public function getInputParameter($name)
	{
		return \App\Request::_get($name);
	}

	/**
	 * @return vtlib\Link
	 */
	public function getLink()
	{
		return $this->link;
	}

	public function getModule()
	{
		return $this->module;
	}
}
