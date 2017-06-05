<?php

/**
 * OSSMailView detail view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMailView_Detail_View extends Vtiger_Detail_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showSummary');
	}

	public function isAjaxEnabled($recordModel)
	{
		return false;
	}

	public function showSummary(\App\Request $request)
	{
		$record = $request->get('record');
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $record);
	}
}

?>
