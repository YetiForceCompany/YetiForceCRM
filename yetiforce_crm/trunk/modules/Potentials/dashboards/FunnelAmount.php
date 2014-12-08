<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/

class Potentials_FunnelAmount_Dashboard extends Vtiger_IndexAjax_View {
	
	/**
	 * Retrieves css styles that need to loaded in the page
	 * @param Vtiger_Request $request - request model
	 * @return <array> - array of Vtiger_CssScript_Model
	 */
	function getHeaderCss(Vtiger_Request $request){
		$cssFileNames = array(
			//Place your widget specific css files here
		);
		$headerCssScriptInstances = $this->checkAndConvertCssStyles($cssFileNames);
		return $headerCssScriptInstances;
	}
    
    function getSearchParams($stage) {
        $listSearchParams = array();
        $conditions = array(array("sales_stage","e",$stage));
        $listSearchParams[] = $conditions;
        return '&search_params='. json_encode($listSearchParams);
    }

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$linkId = $request->get('linkid');
		
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$data = $moduleModel->getPotentialTotalAmountBySalesStage();
        $listViewUrl = $moduleModel->getListViewUrl();
        for($i = 0;$i<count($data);$i++){
            $data[$i]["links"] = $listViewUrl.$this->getSearchParams($data[$i][1]);
			$data[$i][1] = vtranslate($data[$i][1], $moduleName);
        }
        
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);

		$viewer->assign('STYLES',$this->getHeaderCss($request));
		$viewer->assign('CURRENTUSER', $currentUser);

		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/FunnelAmount.tpl', $moduleName);
		}
	}
}
