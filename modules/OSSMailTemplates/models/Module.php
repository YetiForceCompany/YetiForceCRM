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
class OSSMailTemplates_Module_Model extends Vtiger_Module_Model {
    function getListFiledOfModule($moduleName,$relID = false) {
        $db = PearDatabase::getInstance();
        $tabid = getTabid($moduleName);
        $sql = "select fieldid, fieldlabel, uitype from vtiger_field where tabid = ? AND presence <> ? AND typeofdata <> ?";
        $result = $db->pquery($sql, array($tabid,1,'P~M'), true);
        $output = array();
        for ($i = 0; $i < $db->num_rows($result); $i++) {
			if($relID){
				$id = $db->query_result($result, $i, 'fieldid').'||'.$relID;
			}else{
				$id = $db->query_result($result, $i, 'fieldid');
			}
            $output[$i]['label'] = vtranslate($db->query_result($result, $i, 'fieldlabel'), $moduleName);
            $output[$i]['id'] = $id;
            $output[$i]['uitype '] = $db->query_result($result, $i, 'uitype');
        }
        return $output;
    }
    function getListFiledOfRelatedModule($moduleName) {
        $db = PearDatabase::getInstance();
        $tabid = getTabid($moduleName);
		$sql = "select vtiger_field.fieldid, fieldlabel, uitype, vtiger_fieldmodulerel.relmodule from vtiger_field 
				left JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid where tabid = ? AND (uitype = '10' OR uitype = '59' OR uitype = '53' OR uitype = '51')";

        $resultModuleList = $db->pquery($sql, array($tabid), true);
        $moduleList = array();
        for ($i = 0; $i < $db->num_rows($resultModuleList); $i++) {
			$uitype = $db->query_result($resultModuleList, $i, 'uitype');
			$fieldid = $db->query_result($resultModuleList, $i, 'fieldid');
			$fieldlabel = $db->query_result($resultModuleList, $i, 'fieldlabel');
			if( $uitype == 10){
				$moduleList[] = array(Vtiger_Functions::getModuleId($db->query_result($resultModuleList, $i, 'relmodule')),$fieldlabel,$fieldid);
			}elseif($uitype == 51){
				$moduleList[] = array(Vtiger_Functions::getModuleId('Accounts'),$fieldlabel,$fieldid);
			}elseif($uitype == 59){
				$moduleList[] = array(Vtiger_Functions::getModuleId('Products'),$fieldlabel,$fieldid);
			}elseif($uitype == 53){
				$moduleList[] = array(Vtiger_Functions::getModuleId('Users'),$fieldlabel,$fieldid);
			}
        }
        $output = array();
        for ($i = 0; $i < count($moduleList); $i++) {
            $moduleInfoSql = "SELECT * FROM vtiger_tab WHERE tabid = ?";
            $moduleInfoResult = $db->pquery($moduleInfoSql, array($moduleList[$i][0]), true);
            $moduleName = $db->query_result($moduleInfoResult, 0, 'name');
			$moduleTrLabal = vtranslate($moduleList[$i][1], $moduleName);
			$output[$moduleTrLabal] = array();
			$output[$moduleTrLabal] = $this->getListFiledOfModule($moduleName,$moduleList[$i][2]);
        }
        return $output;
    }
    function getListSpecialFunction($path, $module) {
        $specialFunctionList = array();
        $numFile = 0;

        $dir = new DirectoryIterator($path);

        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $tmp = explode('.', $fileinfo->getFilename());
                $fullPath = $path . DIRECTORY_SEPARATOR . $tmp[0] . '.php';
                if (file_exists($fullPath)) {
                    require_once $fullPath;

                    $funObj = new $tmp[0];

                    if (in_array($module, $funObj->getListAllowedModule()) || in_array('all', $funObj->getListAllowedModule())) {
                        $specialFunctionList[$numFile]['filename'] = $tmp[0];
                        $specialFunctionList[$numFile]['label'] = vtranslate($tmp[0], $this->getName());
                        $numFile++;
                    }
                }
            }
        }
        return $specialFunctionList;
    }

    function getListTpl() {
        $db = PearDatabase::getInstance();
        $sql = "SELECT vtiger_ossmailtemplates.ossmailtemplatesid as id, "
                . "vtiger_ossmailtemplates.name as name, vtiger_ossmailtemplates.oss_module_list as module, vtiger_ossmailtemplates.`ossmailtemplates_type` AS type "
                . "FROM vtiger_ossmailtemplates "
                . "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ossmailtemplates.ossmailtemplatesid "
                . "WHERE vtiger_crmentity.deleted = 0";
        $result = $db->query($sql, true);
        $output = array();
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $moduleName = $db->query_result($result, $i, 'module');
            $id = $db->query_result($result, $i, 'id');
            $name = $db->query_result($result, $i, 'name');
            $type = $db->query_result($result, $i, 'type');
            $output[$i]['id'] = $id;
            $output[$i]['name'] =  $name;
            $output[$i]['module'] =  $moduleName;
            $output[$i]['type'] =  $type;
        }
        return $output;
    }

    function getModuleList() {
        $db = PearDatabase::getInstance();
        $sql = "SELECT * FROM vtiger_ossmailtemplates AS mail INNER JOIN vtiger_crmentity AS crm ON crm.crmid = mail.ossmailtemplatesid WHERE `deleted` = 0";
        $result = $db->query($sql);
        $output = [];
        $modules = [];
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $moduleName = $db->query_result($result, $i, 'oss_module_list');
            $type = $db->query_result($result, $i, 'ossmailtemplates_type');
            if(!$type){
                continue;
            }elseif(!$modules[$type]){
                $modules[$type] = [];
            }
            if(!in_array($moduleName, $modules[$type])){
                $modules[$type][] = $moduleName;
                $output[$type][$i]['name'] = $moduleName;
                $output[$type][$i]['type'] = $type;
                $output[$type][$i]['tr_name'] = vtranslate($output[$type][$i]['name'], $output[$type][$i]['name']);
            }
        }
        return $output;
    }
}