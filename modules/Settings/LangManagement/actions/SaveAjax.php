<?php

/**
 * Settings LangManagement SaveAjax action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_LangManagement_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('addTranslation');
		$this->exposeMethod('saveTranslation');
		$this->exposeMethod('deleteTranslation');
		$this->exposeMethod('add');
		$this->exposeMethod('save');
		$this->exposeMethod('delete');
		$this->exposeMethod('setAsDefault');
	}

	/**
	 * Add translation.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\Security
	 */
	public function addTranslation(\App\Request $request)
	{
		$params = [
			'mod' => $request->getByType('mod', 2),
			'type' => $request->getByType('type'),
			'langkey' => $request->getByType('variable', 'Text'),
		];
		foreach ($request->getArray('langs') as $lang) {
			if (!isset(App\Language::getAll()[$lang])) {
				throw new \App\Exceptions\Security('LBL_LANGUAGE_DOES_NOT_EXIST');
			}
			$params['lang'] = $lang;
			$params['val'] = $request->getForHtml($lang);
			$saveResp = Settings_LangManagement_Module_Model::addTranslation($params);
			if ($saveResp['success'] === false) {
				break;
			}
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $saveResp['success'],
			'message' => \App\Language::translate($saveResp['data'], $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Save translations.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\Security
	 */
	public function saveTranslation(\App\Request $request)
	{
		if (!isset(App\Language::getAll()[$request->getByType('lang')])) {
			throw new \App\Exceptions\Security('ERR_LANGUAGE_DOES_NOT_EXIST');
		}
		$params = [
			'lang' => $request->getByType('lang'),
			'mod' => $request->getByType('mod', 2),
			'type' => $request->getByType('type'),
			'langkey' => $request->getByType('langkey', 'Text'),
			'val' => $request->getForHtml('val'),
			'is_new' => $request->getBoolean('is_new'),
		];
		$saveResp = Settings_LangManagement_Module_Model::saveTranslation($params);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $saveResp['success'],
			'message' => \App\Language::translate($saveResp['data'], $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Remove translation.
	 *
	 * @param \App\Request $request
	 */
	public function deleteTranslation(\App\Request $request)
	{
		$params = [
			'langkey' => $request->getByType('langkey', 'Text'),
			'mod' => $request->getByType('mod', 2),
			'lang' => $request->getArray('lang', 1),
		];
		$saveResp = Settings_LangManagement_Module_Model::deleteTranslation($params);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $saveResp['success'],
			'message' => \App\Language::translate($saveResp['data'], $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Function added new language.
	 *
	 * @param \App\Request $request
	 */
	public function add(\App\Request $request)
	{
		$params = [
			'label' => $request->getByType('label', 'Text'),
			'name' => $request->getByType('name', 'Text'),
			'prefix' => $request->getByType('prefix'),
		];
		$saveResp = Settings_LangManagement_Module_Model::add($params);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $saveResp['success'],
			'message' => \App\Language::translate($saveResp['data'], $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Delete language.
	 *
	 * @param \App\Request $request
	 */
	public function delete(\App\Request $request)
	{
		$saveResp = Settings_LangManagement_Module_Model::delete($request->getByType('prefix'));
		$response = new Vtiger_Response();
		if ($saveResp) {
			$response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_DeleteDataOK', $request->getModule(false))]);
		} else {
			$response->setResult(['success' => false]);
		}
		$response->emit();
	}

	/**
	 * Function to set language as default.
	 *
	 * @param \App\Request $request
	 */
	public function setAsDefault(\App\Request $request)
	{
		$saveResp = Settings_LangManagement_Module_Model::setAsDefault($request->getByType('prefix'));
		$response = new Vtiger_Response();
		if ($saveResp['success']) {
			$response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_SaveDataOK', $request->getModule(false)), 'prefixOld' => $saveResp['prefixOld']]);
		} else {
			$response->setResult(['success' => false]);
		}
		$response->emit();
	}
}
