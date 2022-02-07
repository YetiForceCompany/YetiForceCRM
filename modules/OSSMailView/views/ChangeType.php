<?php

/**
 * Change type action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailView_ChangeType_View extends Vtiger_IndexAjax_View
{
	public function process(App\Request $request)
	{
		$module = $request->getModule();
		$type_list = OSSMailView_Record_Model::getMailType();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $module);
		$viewer->assign('TYPE_LIST', $type_list);
		$viewer->view('ChangeType.tpl', $module);
	}
}
