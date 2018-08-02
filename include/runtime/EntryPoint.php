<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
Vtiger_Loader::includeOnce('include.http.Response');
Vtiger_Loader::includeOnce('include.runtime.Viewer');
Vtiger_Loader::includeOnce('include.runtime.Theme');
Vtiger_Loader::includeOnce('include.runtime.JavaScript');
Vtiger_Loader::includeOnce('include.runtime.Cache');
Vtiger_Loader::includeOnce('include.runtime.Layout');

abstract class Vtiger_EntryPoint
{
	/**
	 * Login data.
	 */
	protected $login = false;

	/**
	 * Get login data.
	 */
	public function getLogin()
	{
		return $this->login;
	}

	/**
	 * Set login data.
	 */
	public function setLogin()
	{
		if ($this->login) {
			throw new \App\Exceptions\AppException('Login is already set.');
		}
		$this->login = true;
	}

	/**
	 * Check if login data is present.
	 */
	public function hasLogin()
	{
		return $this->getLogin();
	}

	abstract public function process(\App\Request $request);
}
