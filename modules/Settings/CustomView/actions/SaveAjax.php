<?php

/**
 * CustomView save class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_CustomView_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('delete');
        $this->exposeMethod('updateField');
        $this->exposeMethod('upadteSequences');
        $this->exposeMethod('setFilterPermissions');
    }

    public function delete(\App\Request $request)
    {
        $params = $request->get('param');
        Settings_CustomView_Module_Model::delete($params);
        $response = new Vtiger_Response();
        $response->setResult([
            'success' => $saveResp['success'],
            'message' => \App\Language::translate('Delete CustomView', $request->getModule(false)),
        ]);
        $response->emit();
    }

    public function updateField(\App\Request $request)
    {
        $params = $request->get('param');
        Settings_CustomView_Module_Model::updateField($params);
        Settings_CustomView_Module_Model::updateOrderAndSort($params);
        $response = new Vtiger_Response();
        $response->setResult([
            'message' => \App\Language::translate('Saving CustomView', $request->getModule(false)),
        ]);
        $response->emit();
    }

    public function upadteSequences(\App\Request $request)
    {
        $params = $request->get('param');
        Settings_CustomView_Module_Model::upadteSequences($params);
        $response = new Vtiger_Response();
        $response->setResult([
            'message' => \App\Language::translate('LBL_SAVE_SEQUENCES', $request->getModule(false)),
        ]);
        $response->emit();
    }

    public function setFilterPermissions(\App\Request $request)
    {
        $params = $request->get('param');
        $type = $request->get('type');
        if ($type == 'default') {
            $result = Settings_CustomView_Module_Model::setDefaultUsersFilterView($params['tabid'], $params['cvid'], $params['user'], $params['action']);
        } elseif ($type == 'featured') {
            $result = Settings_CustomView_Module_Model::setFeaturedFilterView($params['cvid'], $params['user'], $params['action']);
        }

        if (!empty($result)) {
            $data = [
                'message' => \App\Language::translate('LBL_EXISTS_PERMISSION_IN_CONFIG', $request->getModule(false), \App\Language::translate($result, $params['tabid'])),
                'success' => false,
            ];
        } else {
            $data = [
                'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false)),
                'success' => true,
            ];
        }
        $response = new Vtiger_Response();
        $response->setResult($data);
        $response->emit();
    }
}
