<?php

/**
 * OSSMailView list view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailView_List_View extends Vtiger_List_View
{
	public function isEditable($moduleName)
	{
		return false;
	}
}
