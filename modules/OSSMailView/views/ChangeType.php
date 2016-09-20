<?php

/**
 * Change type action class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailView_ChangeType_View extends Vtiger_IndexAjax_View
{

	public function process(Vtiger_Request $request)
	{
		$module = $request->getModule();
		$type_list = OSSMailView_Record_Model::getMailType();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $module);
		$viewer->assign('TYPE_LIST', $type_list);
		$viewer->view('ChangeType.tpl', $module);
	}
}
