<?php

/**
 * ActivityStateModal view Class.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Calendar_ActivityStateModal_View extends Vtiger_BasicModal_View
{
	/**
	 * Get tpl path file.
	 *
	 * @return string
	 */
	protected function getTpl()
	{
		return 'ActivityStateModal.tpl';
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$this->record = $request->isEmpty('record', true) ? null : Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		if (!$this->record || !$this->record->isEditable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('LINKS', $this->getLinks());
		$viewer->assign('RECORD', $this->record);
		$viewer->assign('SCRIPTS', $this->getScripts($request));
		$viewer->view($this->getTpl(), $moduleName);
	}

	/** {@inheritdoc} */
	public function getScripts(App\Request $request)
	{
		return $this->checkAndConvertJsScripts([
			'modules.' . $request->getModule() . '.resources.ActivityStateModal'
		]);
	}

	/**
	 * Gets links.
	 *
	 * @return array
	 */
	public function getLinks(): array
	{
		$links = [];
		if ($this->record->isEditable() && \App\Mail::checkInternalMailClient()) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_SEND_CALENDAR',
				'linkdata' => ['url' => "index.php?module={$this->record->getModuleName()}&view=SendInvitationModal&record={$this->record->getId()}"],
				'linkicon' => 'yfi-send-invitation',
				'linkclass' => 'btn-outline-dark btn-sm js-show-modal',
			]);
		}
		if ($this->record->isViewable()) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_SHOW_COMPLETE_DETAILS',
				'linkurl' => $this->record->getDetailViewUrl(),
				'linkicon' => 'fas fa-th-list',
				'linkclass' => 'btn-sm btn-default',
			]);
		}
		if ($this->record->isEditable()) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_EDIT',
				'linkurl' => $this->record->getEditViewUrl(),
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn-sm btn-default',
			]);
		}
		return $links;
	}
}
