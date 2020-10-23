<?php

use App\Controller\Action;
use App\Db\Query;
use App\Exceptions\NoPermitted;
use App\Request;

class Vtiger_Autocomplete_Action extends Action
{
    public function checkPermission(Request $request): void
    {
        $module = $request->getModule();
        $privileges = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        if ($privileges !== null && !$privileges->hasModulePermission($module)) {
            throw new NoPermitted('LBL_PERMISSION_DENIED', 406);
        }
    }

    public function process(Request $request): void
    {
        $module = $request->getModule();
        $field = $request->get('field');
        $term = $request->get('term');

        $moduleInstance = Vtiger_Module_Model::getInstance($module);
        $table = $moduleInstance->basetable;

        $query = new Query();

        $result = $query->select($field)
            ->distinct()
            ->from($table)
            ->where(['like', $field, $term . '%', false])
            ->orderBy($field)
            ->limit(20)
            ->all();

        $result = array_map('reset', $result);

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}