<?php

/**
 * YetiForce registration modal view class file.
 *
 * @package   Modules
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Online registration modal view class.
 */
class Settings_YetiForce_RegistrationOnlineModal_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-full';

	/** @var string The name of the send button. */
	public $successBtn = 'LBL_SEND';

	/**
	 * Set modal title.
	 *
	 * @param \App\Request $request
	 */
	public function preProcessAjax(App\Request $request)
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
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('REGISTER_COMPANIES', $this->prepareCompanies());
		$viewer->assign('LICENSE', $this->getLicense());
		$viewer->view('RegistrationOnlineModal.tpl', $request->getModule(false));
	}

	/**
	 * Generate array with companies grouped by types.
	 *
	 * @return array
	 */
	private function prepareCompanies(): array
	{
		$data = [];
		foreach (\App\Company::getAll() as $company) {
			$key = \Settings_Companies_Record_Model::TYPES[$company['type']];
			$data[$key][] = $company;
		}
		return $data;
	}

	/**
	 * Get License details.
	 *
	 * @return array
	 */
	private function getLicense(): array
	{
		$lang = strtoupper(\App\Language::getShortLanguageName());
		$fileName = 'LicenseEN.txt';
		if (file_exists(ROOT_DIRECTORY . "/licenses/License{$lang}.txt")) {
			$fileName = "License{$lang}.txt";
		}
		$text = '';
		foreach (file(ROOT_DIRECTORY . "/licenses/{$fileName}") as $line) {
			if (\in_array(substr($line, 0, 2), ['b)', 'c)'])) {
				$text .= $line;
			}
		}
		return [
			'fileName' => $fileName,
			'text' => $text,
		];
	}
}
