<?php

/**
 * EditFieldByModal View Class
 * @package YetiForce.ModalView
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class SQuoteEnquiries_EditFieldByModal_View extends Vtiger_EditFieldByModal_View
{

	protected $restrictItems = ['PLL_CANCELLED' => 'btn-danger', 'PLL_COMPLETED' => 'btn-success'];

	public function getConditionToRestricts($moduleName, $ID)
	{
		return Users_Privileges_Model::isPermitted($moduleName, 'CloseRecord', $ID);
	}
}
