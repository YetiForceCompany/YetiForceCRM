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

class Potentials_Widget_View extends Vtiger_Index_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('showPotentialsList');
	}
	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
	
	function showPotentialsList(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$fromModule = $request->get('fromModule');
		
		$viewer = $this->getViewer($request);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$data = $moduleModel->getPotentialsList($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('SOURCE_MODULE', $fromModule);
		$viewer->assign('RELATED_MODULE', 'Potentials');
		$viewer->view('widgets/PotentialsList.tpl', $moduleName);
	}
}
