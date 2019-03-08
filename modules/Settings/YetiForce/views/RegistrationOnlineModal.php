<?php

/**
 * YetiForce registration modal view class file.
 *
 * @package   Modules
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Online registration modal view class.
 */
class Settings_YetiForce_RegistrationOnlineModal_View extends \App\Controller\ModalSettings
{
	/**
	 * The name of the send button.
	 *
	 * @var string
	 */
	public $successBtn = 'LBL_SEND';

	/**
	 * Set modal title.
	 *
	 * @param \App\Request $request
	 */
	public function preProcessAjax(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$this->modalIcon = 'fas fa-globe';
		$this->pageTitle = \App\Language::translate('YetiForce', $qualifiedModuleName) . ' - ' . \App\Language::translate('LBL_REGISTRATION_ONLINE_MODAL', $qualifiedModuleName);
		parent::preProcessAjax($request);
	}

	/**
	 * Process user request.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('REGISTER_COMPANIES', $this->prepareCompanies());
		$viewer->view('RegistrationOnlineModal.tpl', $request->getModule(false));
	}

	/**
	 * Generate array with companies grouped by types.
	 *
	 * @return array
	 */
	public function prepareCompanies(): array
	{
		$data = [];
		foreach (\App\Company::getAll() as $company) {
			$key = \Settings_Companies_Record_Model::TYPES[$company['type']];
			$data[$key][] = $company;
		}
		return $data;
	}
}
