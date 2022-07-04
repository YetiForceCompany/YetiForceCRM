<?php

/**
 * Settings menu SaveAjax action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Menu_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('createMenu');
		$this->exposeMethod('updateMenu');
		$this->exposeMethod('removeMenu');
		$this->exposeMethod('updateSequence');
		$this->exposeMethod('copyMenu');
	}

	public function createMenu(App\Request $request)
	{
		$data = $request->getMultiDimensionArray('mdata', [
			'type' => 'Alnum',
			'module' => 'Alnum',
			'label' => 'Text',
			'newwindow' => 'Integer',
			'hotkey' => 'Text',
			'filters' => ['Integer'],
			'icon' => 'Text',
			'role' => 'Alnum',
			'dataurl' => 'Url',
			'filterId' => 'Integer',
			'countentries' => 'Integer',
		]
		);
		if ('CustomFilter' === $data['type'] && \in_array($data['type'], \App\Menu::TYPES)) {
			$data['dataurl'] = $data['filterId'];
			unset($data['filterId']);
		}
		$data['source'] = $request->getInteger('source');
		$recordModel = Settings_Menu_Record_Model::getCleanInstance();
		$recordModel->initialize($data);
		$recordModel->save();
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_ITEM_ADDED_TO_MENU', $request->getModule(false)),
		]);
		$response->emit();
	}

	public function updateMenu(App\Request $request)
	{
		$data = $request->getMultiDimensionArray('mdata', [
			'id' => 'Integer',
			'type' => 'Alnum',
			'module' => 'Alnum',
			'label' => 'Text',
			'newwindow' => 'Integer',
			'hotkey' => 'Text',
			'filters' => ['Integer'],
			'icon' => 'Text',
			'role' => 'Alnum',
			'dataurl' => 'Url',
			'filterId' => 'Integer',
			'countentries' => 'Integer',
		]
		);
		if (!empty($data['filterId']) && empty($data['dataurl'])) {
			$data['dataurl'] = $data['filterId'];
			unset($data['filterId']);
		}
		$data['source'] = $request->getInteger('source');
		$recordModel = Settings_Menu_Record_Model::getInstanceById($data['id']);
		$recordModel->initialize($data);
		$recordModel->set('edit', true);
		$recordModel->save();
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVED_MENU', $request->getModule(false)),
		]);
		$response->emit();
	}

	public function removeMenu(App\Request $request)
	{
		$settingsModel = Settings_Menu_Record_Model::getCleanInstance();
		$settingsModel->removeMenu($request->getArray('mdata', 'Integer'));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_REMOVED_MENU_ITEM', $request->getModule(false)),
		]);
		$response->emit();
	}

	public function updateSequence(App\Request $request)
	{
		$recordModel = Settings_Menu_Record_Model::getCleanInstance();
		$recordModel->saveSequence($request->getArray('mdata', 'Text'), Settings_Menu_Record_Model::SRC_ROLE === $request->getInteger('source'));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVED_MAP_MENU', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Function to trigger copying menu.
	 *
	 * @param \App\Request $request
	 */
	public function copyMenu(App\Request $request)
	{
		$roleTo = $request->getByType('toRole', 'Alnum');
		$fromRole = filter_var($request->getByType('fromRole', 'Alnum'), FILTER_SANITIZE_NUMBER_INT);
		$toRole = filter_var($roleTo, FILTER_SANITIZE_NUMBER_INT);
		$recordModel = Settings_Menu_Record_Model::getCleanInstance();
		$recordModel->copyMenu($fromRole, $toRole, $roleTo);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVED_MAP_MENU', $request->getModule(false)),
		]);

		$response->emit();
	}
}
