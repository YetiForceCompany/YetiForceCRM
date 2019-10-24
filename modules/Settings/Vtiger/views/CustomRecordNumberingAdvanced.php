<?php

/**
 * Modal for advanced record numbering.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Settings_Vtiger_CustomRecordNumberingAdvanced_View extends Settings_Vtiger_BasicModal_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$this->preProcess($request);
		$moduleName = $request->getModule(false);
		$supportedModules = Settings_Vtiger_CustomRecordNumberingModule_Model::getSupportedModules();
		$sourceModule = $request->getByType('sourceModule', 2);
		$moduleModel = \Vtiger_Module_Model::getInstance($sourceModule);
		$picklistSequences = (new \App\Db\Query())->select(['value', 'cur_id'])->from('u_#__modentity_sequences')->where(['tabid' => $moduleModel->getId()])->createCommand()->queryAllByGroup(0);
		$picklistName = $picklist = [];
		preg_match('/{{picklist:([a-z0-9_]+)}}/i', $request->getByType('picklist', 'Text'), $picklistName);
		if ($sourceModule) {
			$defaultModuleModel = $supportedModules[\App\Module::getModuleId($sourceModule)];
		} else {
			$defaultModuleModel = reset($supportedModules);
		}
		if ($moduleModel->getFieldByName($picklistName[1]) && !empty($picklistName[1])) {
			foreach (\App\Fields\Picklist::getValues($picklistName[1]) as $value) {
				if (!empty($value['prefix'])) {
					if (isset($picklistSequences[$value['prefix']])) {
						$value['cur_id'] = $picklistSequences[$value['prefix']];
					} else {
						$value['cur_id'] = 1;
					}
					$picklist[] = $value;
				}
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('SUPPORTED_MODULES', $supportedModules);
		$viewer->assign('DEFAULT_MODULE_MODEL', $defaultModuleModel);
		$viewer->assign('PICKLISTS_VALUES', $picklist);
		$viewer->view('CustomRecordNumberingAdvanced.tpl', $moduleName);
		$this->postProcess($request);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getModalScripts(App\Request $request)
	{
		return array_merge(parent::getModalScripts($request), $this->checkAndConvertJsScripts([
			'modules.Settings.Vtiger.resources.CustomRecordNumberingAdvanced',
		]));
	}
}
