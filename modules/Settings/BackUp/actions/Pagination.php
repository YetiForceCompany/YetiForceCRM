<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

class Settings_BackUp_Pagination_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $limit = 10;

        $backups = Settings_BackUp_Module_Model::getBackUps();

        $allBackups = count($backups);

        if ($request->get('page') != '') {
            $page = $request->get('page');
            $offset = ($page - 1 ) * $limit;
            if ($request->get('page') == 1) {
                $prevPage = 0;
            } else {
                $prevPage = 1;
            }
        } else {
            $page = 1;
            $offset = 0;
            $prevPage = 0;
        }

        $nextPage = 1;
        $allPages = ceil($allBackups / $limit);
        if (($allPages == $page) || ($allBackups <= $limit))
            $nextPage = 0;
      

        $backups = Settings_BackUp_Module_Model::getBackUps($offset, $limit);
        $result = array(
            'prevPage' => $prevPage,
            'nextPage' => $nextPage,
            'offset' => $offset,
            'allPages' => $allPages,
            'page' => $page,
            'backups' => $backups
        );
        if ($request->get('ajaxCall') === '') {
            $json = json_encode($result);
            return $json;
        }
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

}
