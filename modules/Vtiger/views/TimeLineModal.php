<?php

/**
 * TimeLineModal View Class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_TimeLineModal_View extends Vtiger_BasicModal_View
{

	/**
	 * Checking permission
	 * @param \App\Request $request
	 * @throws \Exception\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		if (!\App\Privilege::isPermitted($moduleName, 'TimeLineList') || !\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId)) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}

	/**
	 * The initial process
	 * @param \App\Request $request
	 * @param type $display
	 */
	public function preProcess(\App\Request $request, $display = true)
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
	 * @param \App\Request $request
	 */
	public function postProcess(\App\Request $request)
	{
		parent::postProcess($request);
		echo '</div>';
	}

	/**
	 * Proceess
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
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
