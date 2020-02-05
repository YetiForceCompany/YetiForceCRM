<?php

/**
 * Reservations RightPanel view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Reservations_RightPanel_View extends Calendar_RightPanel_View
{
	/**
	 * {@inheritdoc}
	 */
	protected function getTpl(string $tplFile)
	{
		return "Calendar/{$tplFile}";
	}

	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getTypesList');
		$this->exposeMethod('getUsersList');
		$this->exposeMethod('getGroupsList');
	}

	/**
	 * Gets template.
	 *
	 * @param App\Request $request
	 */
	public function getTypesList(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('ALL_ACTIVETYPES_LIST', Reservations_Calendar_Model::getCalendarTypes());
		$viewer->view($this->getTpl('RightPanel.tpl'), $request->getModule());
	}
}
