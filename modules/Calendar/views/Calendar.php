<?php

/**
 * Calendar view file.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author	Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Calendar view class.
 */
class Calendar_Calendar_View extends Vtiger_Calendar_View
{
	/** {@inheritdoc} */
	protected $filters = ['Events', 'Filter'];

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('ACTIVITY_STATE_LABELS', \App\Json::encode([
			'current' => Calendar_Module_Model::getComponentActivityStateLabel('current'),
			'history' => Calendar_Module_Model::getComponentActivityStateLabel('history'),
		]));
		parent::process($request);
	}

	/** {@inheritdoc} */
	public function postProcess(App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('SHOW_TYPE', true);
		$viewer->assign('CALENDAR_FILTERS', Calendar_CalendarFilters_Model::getCleanInstance());
		parent::postProcess($request);
	}
}
