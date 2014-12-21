<?php
/* * *******************************************************************************
 * The content of this file is subject to the MYC Vtiger Customer Portal license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is Proseguo s.l. - MakeYourCloud
 * Portions created by Proseguo s.l. - MakeYourCloud are Copyright(C) Proseguo s.l. - MakeYourCloud
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */

class Home extends BaseModule{
	
	public function get_list(){
		$params = Array('id'=>$_SESSION["loggeduser"]['id'], 'type'=> $GLOBALS["summary_widgets"]);
		$result = $GLOBALS["sclient"]->call('get_summary_widgets', $params);
		$data['widgets'] = ($result!=false && $result[0]!='')?$result[0]:array();
		Template::display($this->module,$data,'index');
	}
}
