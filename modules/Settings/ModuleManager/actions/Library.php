<?php

/**
 * Library action class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_ModuleManager_Library_Action extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('download');
		$this->exposeMethod('update');
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function to update library
	 * @param Vtiger_Request $request
	 */
	public function update(Vtiger_Request $request)
	{
		Settings_ModuleManager_Library_Model::update($request->get('name'));
		header("Location: index.php?module=ModuleManager&parent=Settings&view=List");
	}

	/**
	 * Function to download library
	 * @param Vtiger_Request $request
	 */
	public function download(Vtiger_Request $request)
	{
		Settings_ModuleManager_Library_Model::download($request->get('name'));
		header("Location: index.php?module=ModuleManager&parent=Settings&view=List");
	}
}
