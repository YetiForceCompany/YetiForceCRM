<?php

/**
 * TimeLineModal View Class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_TimeLineModal_View extends Vtiger_BasicModal_View
{
	/**
	 * Checking permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$moduleName = $request->getModule();
		if (!\App\Privilege::isPermitted($moduleName, 'TimeLineList') || !\App\Privilege::isPermitted($moduleName, 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * The initial process.
	 *
	 * @param \App\Request $request
	 * @param type         $display
	 */
	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request);
		echo '<div class="modal-header">
				<h5 class="modal-title">' . \App\Language::translate('LBL_TIMELINE', $request->getModule()) . ' </h5>
				<button type="button" class="close" data-dismiss="modal" title="' . \App\Language::translate('LBL_CLOSE') . '">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">';
	}

	/**
	 * The final process.
	 *
	 * @param \App\Request $request
	 * @param mixed        $display
	 */
	public function postProcess(App\Request $request, $display = true)
	{
		parent::postProcess($request);
		echo '</div>';
	}

	/**
	 * Proceess.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$request->set('limit', App\Config::module('ModTracker', 'TIMELINE_IN_LISTVIEW_LIMIT'));
		$request->set('type', Vtiger_HistoryRelation_Widget::getActions());
		$request->set('noMore', true);

		$viewClassName = Vtiger_Loader::getComponentClassName('View', 'Detail', $moduleName);
		$instance = new $viewClassName();
		$instance->record = Vtiger_DetailView_Model::getInstance($moduleName, $request->getInteger('record'));

		$this->preProcess($request);
		echo $instance->showRecentRelation($request);
		$this->postProcess($request);
	}
}
