<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_TagCloudSearchAjax_View extends Vtiger_IndexAjax_View {

	function process(Vtiger_Request $request) {
		
		$tagId = $request->get('tag_id');
		$taggedRecords = Vtiger_Tag_Model::getTaggedRecords($tagId);
		
		$viewer = $this->getViewer($request);
		
		$viewer->assign('TAGGED_RECORDS',$taggedRecords);
		$viewer->assign('TAG_NAME',$request->get('tag_name'));
		
		echo $viewer->view('TagCloudResults.tpl', $module, true);
	}
	
	
	
}