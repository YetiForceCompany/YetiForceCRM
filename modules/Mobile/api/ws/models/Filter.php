<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
include_once 'modules/CustomView/CustomView.php';

class Mobile_WS_FilterModel
{

	var $filterid, $moduleName;
	var $user;
	protected $customView;

	public function __construct($moduleName)
	{
		$this->moduleName = $moduleName;
		$this->customView = new CustomView($moduleName);
	}

	public function setUser($userInstance)
	{
		$this->user = $userInstance;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function query()
	{
		$listquery = getListQuery($this->moduleName);
		$query = $this->customView->getModifiedCvListQuery($this->filterid, $listquery, $this->moduleName);
		return $query;
	}

	public function queryParameters()
	{
		return false;
	}

	static function modelWithId($moduleName, $filterid)
	{
		$model = new Mobile_WS_FilterModel($moduleName);
		$model->filterid = $filterid;
		return $model;
	}
}
