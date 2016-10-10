<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Vtiger_Basic_Widget
{

	public $Module = false;
	public $Record = false;
	public $Config = [];
	public $moduleModel = false;
	public $dbParams = [];
	public $allowedModules = [];

	public function __construct($Module = false, $moduleModel = false, $Record = false, $widget = [])
	{
		$this->Module = $Module;
		$this->Record = $Record;
		$this->Config = $widget;
		$this->Config['tpl'] = 'Basic.tpl';
		$this->Data = isset($widget['data']) ? $widget['data'] : [];
		$this->moduleModel = $moduleModel;
	}

	public function getConfigTplName()
	{
		return 'BasicConfig';
	}

	public function getWidget()
	{
		return $this->Config;
	}
}
