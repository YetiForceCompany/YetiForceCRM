<?php

/**
 * EditFieldByModal View Class for Assets
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Assets_EditFieldByModal_View extends Vtiger_EditFieldByModal_View
{

	protected $showFields = ['assetname', 'parent_id', 'serialnumber', 'datesold', 'assetstatus', 'dateinservice', 'assigned_user_id', 'created_user_id', 'shownerid'];

}
