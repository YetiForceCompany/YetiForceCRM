<?php

/**
 * Settings menu SaveAjax action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Menu_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
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

    public function createMenu(\App\Request $request)
    {
        $data = $request->get('mdata');
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

    public function updateMenu(\App\Request $request)
    {
        $data = $request->get('mdata');
        $recordModel = Settings_Menu_Record_Model::getInstanceById($data['id']);
        $recordModel->initialize($data);
        $recordModel->set('edit', true);
        $recordModel->save($data);
        $response = new Vtiger_Response();
        $response->setResult([
            'success' => true,
            'message' => \App\Language::translate('LBL_SAVED_MENU', $request->getModule(false)),
        ]);
        $response->emit();
    }

    public function removeMenu(\App\Request $request)
    {
        $data = $request->get('mdata');
        $settingsModel = Settings_Menu_Record_Model::getCleanInstance();
        $settingsModel->removeMenu($data);
        $response = new Vtiger_Response();
        $response->setResult([
            'success' => true,
            'message' => \App\Language::translate('LBL_REMOVED_MENU_ITEM', $request->getModule(false)),
        ]);
        $response->emit();
    }

    public function updateSequence(\App\Request $request)
    {
        $data = $request->get('mdata');
        $recordModel = Settings_Menu_Record_Model::getCleanInstance();
        $recordModel->saveSequence($data, true);
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
    public function copyMenu(\App\Request $request)
    {
        $fromRole = filter_var($request->get('fromRole'), FILTER_SANITIZE_NUMBER_INT);
        $toRole = filter_var($request->get('toRole'), FILTER_SANITIZE_NUMBER_INT);
        $recordModel = Settings_Menu_Record_Model::getCleanInstance();
        $recordModel->copyMenu($fromRole, $toRole);
        $response = new Vtiger_Response();
        $response->setResult([
            'success' => true,
            'message' => \App\Language::translate('LBL_SAVED_MAP_MENU', $request->getModule(false)),
        ]);

        $response->emit();
    }
}
