<?php

/**
 * TimeLineModal View Class
 * @package YetiForce.Modal
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_TimeLineModal_View extends Vtiger_BasicModal_View
{

	/**
	 * Checking permission
	 * @param Vtiger_Request $request
	 * @throws \Exception\NoPermittedToRecord
	 */
	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		if (!\App\Privilege::isPermitted($moduleName, 'TimeLineList') || !\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId)) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}

	/**
	 * The initial process
	 * @param Vtiger_Request $request
	 * @param type $display
	 */
	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request);
		echo '<div class="modal-header">
				<button class="close" data-dismiss="modal" title="' . \App\Language::translate('LBL_CLOSE') . '">x</button>
				<h3 class="modal-title">' . \App\Language::translate('LBL_TIMELINE', $request->getModule()) . ' </h3>
			</div>
			<div class="modal-body">';
	}

	/**
	 * The final process
	 * @param Vtiger_Request $request
	 */
	public function postProcess(Vtiger_Request $request)
	{
		parent::postProcess($request);
		echo '</div>';
	}

	/**
	 * Proceess
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$request->set('limit', AppConfig::module('ModTracker', 'TIMELINE_IN_LISTVIEW_LIMIT'));
		$request->set('type', Vtiger_HistoryRelation_Widget::getActions());
		$request->set('noMore', true);

		$viewClassName = Vtiger_Loader::getComponentClassName('View', 'Detail', $moduleName);
		$instance = new $viewClassName();

		$this->preProcess($request);
		echo $instance->showRecentRelation($request);
		$this->postProcess($request);
	}
}
