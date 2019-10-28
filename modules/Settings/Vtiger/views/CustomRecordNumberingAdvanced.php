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
class Settings_Vtiger_CustomRecordNumberingAdvanced_View extends \App\Controller\ModalSettings
{
	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(App\Request $request)
	{
		$this->pageTitle = \App\Language::translate('LBL_ADVANCED_RECORD_NUMBERING', $request->getModule(false));
		parent::preProcessAjax($request);
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
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
		if (!empty($picklistName[1]) && $moduleModel->getFieldByName($picklistName[1])) {
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
		if (empty($picklist)) {
			$this->successBtn = '';
			$this->dangerBtn = '';
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('SUPPORTED_MODULES', $supportedModules);
		$viewer->assign('DEFAULT_MODULE_MODEL', $defaultModuleModel);
		$viewer->assign('PICKLISTS_VALUES', $picklist);
		$viewer->view('CustomRecordNumberingAdvanced.tpl', $request->getModule(false));
	}
}
