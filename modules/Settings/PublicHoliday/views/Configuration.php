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
class Settings_PublicHoliday_Configuration_View extends Settings_Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
		global $log;
		$log->debug("Entering Settings_PublicHoliday_Configuration_View::process() method ...");
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$sourceModule = $request->get('sourceModule');

		if(empty($sourceModule))
			$sourceModule = 'Home';

		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		$yearFrom = $request->get('yearFrom');
		$yearTo = $request->get('yearTo');

		if ( empty($yearTo) ) {
			$currentYear = $dateTo = date('Y');
		}
		else {
			$dateTo = $currentYear = $yearTo;
		}

		if ( empty($yearFrom) ) {
			$minus3Years = $dateFrom = date('Y', strtotime('-3 years'));
		}
		else {
			$minus3Years = $dateFrom = $yearFrom;
		}

		$holidays = Settings_PublicHoliday_Module_Model::getHolidays( $dateFrom, $dateTo );

		$viewer->assign('CURRENT_YEAR', $currentYear);
		$viewer->assign('THREE_YEARS_BACK', $minus3Years);
		$viewer->assign('HOLIDAYS', $holidays);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));

		echo $viewer->view('Configuration.tpl', $request->getModule(false), true);
		$log->debug("Exiting Settings_PublicHoliday_Configuration_View::process() method ...");
	}
}
