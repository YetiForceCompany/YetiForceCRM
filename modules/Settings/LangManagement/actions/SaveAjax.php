<?php

/**
 * Settings LangManagement SaveAjax action class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public function addTranslation(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		try {
			$langs = $request->getArray('langs', 1);
			if (!$langs || array_diff($langs, array_keys(App\Language::getAll()))) {
				throw new \App\Exceptions\Security('ERR_LANGUAGE_DOES_NOT_EXIST');
			}
			if (!\in_array($request->getByType('type'), \App\Language::LANG_TYPE)) {
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
			}
			$mod = $request->getByType('mod');
			$type = $request->getByType('type');
			$variable = $request->getByType('variable', 'Text');
			$moduleModel = Settings_LangManagement_Module_Model::getInstance($moduleName);
			$data = $moduleModel->loadLangTranslation($langs, $mod);
			if (!isset($data[$type][$variable])) {
				foreach ($langs as $lang) {
					\App\Language::translationModify($lang, $mod, $type, $variable, $request->getForHtml($lang));
				}
				$result = ['success' => true, 'message' => \App\Language::translate('LBL_AddTranslationOK', $moduleName)];
			} else {
				$result = ['success' => false, 'message' => \App\Language::translate('LBL_KeyExists', $moduleName)];
			}
		} catch (\Exception $ex) {
			$result = ['success' => false];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Save translations.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\Security
	 */
	public function saveTranslation(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		try {
			if (!isset(App\Language::getAll()[$request->getByType('lang')])) {
				throw new \App\Exceptions\Security('ERR_LANGUAGE_DOES_NOT_EXIST');
			}
			if (!\in_array($request->getByType('type'), \App\Language::LANG_TYPE)) {
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
			}
			\App\Language::translationModify($request->getByType('lang'), $request->getByType('mod'), $request->getByType('type'), $request->getByType('variable', 'Text'), $request->getForHtml('val'));
			$result = ['success' => true, 'message' => \App\Language::translate('LBL_UpdateTranslationOK', $moduleName)];
		} catch (\Exception $ex) {
			$result = ['success' => false];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Remove translation.
	 *
	 * @param \App\Request $request
	 */
	public function deleteTranslation(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		try {
			$langs = $request->getArray('lang', 1);
			if (!$langs || array_diff($langs, array_keys(App\Language::getAll()))) {
				throw new \App\Exceptions\Security('ERR_LANGUAGE_DOES_NOT_EXIST');
			}
			if (!\in_array($request->getByType('type'), \App\Language::LANG_TYPE)) {
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
			}
			foreach ($langs as $lang) {
				\App\Language::translationModify($lang, $request->getByType('mod'), $request->getByType('type'), $request->getByType('langkey', 'Text'), '', true);
			}
			$result = ['success' => true, 'message' => \App\Language::translate('LBL_DeleteTranslationOK', $moduleName)];
		} catch (\Exception $ex) {
			$result = ['success' => false];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function added new language.
	 *
	 * @param \App\Request $request
	 */
	public function add(App\Request $request)
	{
		$params = [
			'label' => $request->getByType('label', 'Text'),
			'name' => $request->getByType('name', 'Text'),
			'prefix' => $request->getByType('prefix', 'Text'),
		];
		$saveResp = Settings_LangManagement_Module_Model::add($params);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $saveResp['success'],
			'message' => \App\Language::translate($saveResp['data'], $request->getModule(false)),
			'params' => $params
		]);
		$response->emit();
	}

	/**
	 * Delete language.
	 *
	 * @param \App\Request $request
	 */
	public function delete(App\Request $request)
	{
		$lang = $request->getByType('prefix');
		if (\in_array($lang, [\App\Config::main('default_language'), \App\Language::DEFAULT_LANG])) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
		}
		$saveResp = Settings_LangManagement_Module_Model::delete($lang);
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
	public function setAsDefault(App\Request $request)
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
