<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_PickListDependency_IndexAjax_View extends Settings_PickListDependency_Edit_View
{
	use \App\Controller\ExposeMethod,
	 App\Controller\ClearProcess;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getDependencyGraph');
	}
}
