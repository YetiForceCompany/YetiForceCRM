<?php

class Settings_Vtiger_Policy_View extends Settings_Vtiger_Index_View
{
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->view('Policy.tpl', $qualifiedModuleName);
	}
}
