<?php

/**
 * OSSMailView list view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailView_List_View extends Vtiger_List_View
{

	public function isEditable($moduleName)
	{
		return false;
	}
}
