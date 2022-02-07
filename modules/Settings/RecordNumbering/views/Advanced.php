<?php

/**
 * Modal for advanced record numbering.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_RecordNumbering_Advanced_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$this->pageTitle = \App\Language::translate('LBL_ADVANCED_RECORD_NUMBERING', $request->getModule(false));
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$supportedModules = Settings_RecordNumbering_Module_Model::getSupportedModules();
		$sourceModule = $request->getByType('sourceModule', 2);
		$valueParam = $request->getByType('picklist', \App\Purifier::TEXT);
		$moduleModel = \Vtiger_Module_Model::getInstance($sourceModule);
		$valueSequences = (new \App\Db\Query())->select(['prefix' => 'value', 'cur_id'])->from('u_#__modentity_sequences')->where(['tabid' => $moduleModel->getId()])->all();

		preg_match('/{{picklist:([a-z0-9_]+)}}/i', $valueParam, $picklistName);
		if ($sourceModule) {
			$defaultModuleModel = $supportedModules[\App\Module::getModuleId($sourceModule)];
		} else {
			$defaultModuleModel = reset($supportedModules);
		}

		if (!empty($picklistName[1]) && !\App\TextParser::isVaribleToParse($valueParam) && $moduleModel->getFieldByName($picklistName[1])) {
			$currentValues = array_column($valueSequences, 'cur_id', 'prefix');
			$valueSequences = [];
			foreach (\App\Fields\Picklist::getValues($picklistName[1]) as $value) {
				if (!empty($value['prefix'])) {
					$value['cur_id'] = $currentValues[$value['prefix']] ?? 1;
					$valueSequences[] = $value;
				}
			}
		}
		if (empty($valueSequences)) {
			$this->successBtn = '';
			$this->dangerBtn = '';
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('SUPPORTED_MODULES', $supportedModules);
		$viewer->assign('DEFAULT_MODULE_MODEL', $defaultModuleModel);
		$viewer->assign('PICKLISTS_VALUES', $valueSequences);
		$viewer->view('Advanced.tpl', $request->getModule(false));
	}
}
