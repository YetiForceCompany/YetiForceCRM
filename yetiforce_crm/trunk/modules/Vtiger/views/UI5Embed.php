<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Vtiger_UI5Embed_View extends Vtiger_Index_View {
	
	protected function preProcessDisplay(Vtiger_Request $request) {}
	
	protected function getUI5EmbedURL(Vtiger_Request $request) {
		return '../index.php?action=index&module=' . $request->getModule();
	}
	
	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->assign('UI5_URL', $this->getUI5EmbedURL($request));
		$viewer->view('UI5EmbedView.tpl');
	}
	
}