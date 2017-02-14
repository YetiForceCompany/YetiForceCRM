<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

abstract class Vtiger_Header_View extends Vtiger_View_Controller
{

	public function __construct()
	{
		parent::__construct();
	}
	//Note : To get the right hook for immediate parent in PHP,
	// specially in case of deep hierarchy
	/* function preProcessParentTplName(Vtiger_Request $request) {
	  return parent::preProcessTplName($request);
	  } */

	/**
	 * Function to determine file existence in relocated module folder (under vtiger6)
	 * @param String $fileuri
	 * @return Boolean
	 *
	 * Utility function to manage the backward compatible file load
	 * which are registered for 5.x modules (and now provided for 6.x as well).
	 */
	protected function checkFileUriInRelocatedMouldesFolder($fileuri)
	{
		if (strpos($fileuri, '?') !== false) {
			list ($filename, $query) = explode('?', $fileuri);
		} else {
			$filename = $fileuri;
		}
		// prefix the base lookup folder (relocated file).
		if (strpos($filename, 'modules') === 0) {
			$filename = $filename;
		}

		return file_exists($filename);
	}

	/**
	 * Function to get the list of Header Links
	 * @return <Array> - List of Vtiger_Link_Model instances
	 */
	public function getMenuHeaderLinks(Vtiger_Request $request)
	{
		$userModel = Users_Record_Model::getCurrentUserModel();
		$headerLinks = [];
		if (Users_Module_Model::getSwitchUsers()) {
			$headerLinks[] = [
				'linktype' => 'HEADERLINK',
				'linklabel' => 'SwitchUsers',
				'linkurl' => '',
				'glyphicon' => 'glyphicon glyphicon-transfer',
				'nocaret' => true,
				'linkdata' => ['url' => $userModel->getSwitchUsersUrl()],
				'linkclass' => 'showModal',
			];
		}
		if (AppConfig::security('SHOW_MY_PREFERENCES')) {
			$headerLinks[] = [
				'linktype' => 'HEADERLINK',
				'linklabel' => 'LBL_MY_PREFERENCES',
				'linkurl' => $userModel->getPreferenceDetailViewUrl(),
				'glyphicon' => 'glyphicon glyphicon-tasks',
			];
		}
		if ($userModel->isAdminUser()) {
			if ($request->get('parent') != 'Settings') {
				$headerLinks[] = [
					'linktype' => 'HEADERLINK',
					'linklabel' => 'LBL_SYSTEM_SETTINGS',
					'linkurl' => 'index.php?module=Vtiger&parent=Settings&view=Index',
					'glyphicon' => 'glyphicon glyphicon-cog',
				];
			} else {
				$headerLinks[] = [
					'linktype' => 'HEADERLINK',
					'linklabel' => 'LBL_USER_PANEL',
					'linkurl' => 'index.php',
					'glyphicon' => 'glyphicon glyphicon-user',
				];
			}
		}
		$headerLinks[] = [
			'linktype' => 'HEADERLINK',
			'linklabel' => 'LBL_SIGN_OUT',
			'linkurl' => 'index.php?module=Users&parent=Settings&action=Logout',
			'glyphicon' => 'glyphicon glyphicon-off',
			'linkclass' => 'signOutButtonBlue'
		];
		$headerLinkInstances = [];
		foreach ($headerLinks as $headerLink) {
			$headerLinkInstance = Vtiger_Link_Model::getInstanceFromValues($headerLink);
			if (isset($headerLink['childlinks'])) {
				foreach ($headerLink['childlinks'] as $childLink) {
					$headerLinkInstance->addChildLink(Vtiger_Link_Model::getInstanceFromValues($childLink));
				}
			}
			$headerLinkInstances[] = $headerLinkInstance;
		}
		$headerLinks = Vtiger_Link_Model::getAllByType(vtlib\Link::IGNORE_MODULE, ['HEADERLINK']);
		foreach ($headerLinks as $headerType => $headerLinks) {
			foreach ($headerLinks as $headerLink) {
				$headerLinkInstances[] = Vtiger_Link_Model::getInstanceFromLinkObject($headerLink);
			}
		}
		return $headerLinkInstances;
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$headerScripts = Vtiger_Link_Model::getAllByType(vtlib\Link::IGNORE_MODULE, array('HEADERSCRIPT'));
		foreach ($headerScripts as $headerType => $headerScripts) {
			foreach ($headerScripts as $headerScript) {
				if ($this->checkFileUriInRelocatedMouldesFolder($headerScript->linkurl)) {
					$headerScriptInstances[] = Vtiger_JsScript_Model::getInstanceFromLinkObject($headerScript);
				}
			}
		}
		return $headerScriptInstances;
	}

	/**
	 * Function to get the list of Css models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_CssScript_Model instances
	 */
	public function getHeaderCss(Vtiger_Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$baseStyleCssPath = Vtiger_Theme::getBaseStylePath();
		$baseStyleCssPath = $this->checkAndConvertCssStyles(['~' . $baseStyleCssPath]);
		$headerCssInstances = array_merge($headerCssInstances, $baseStyleCssPath);

		$headerCss = Vtiger_Link_Model::getAllByType(vtlib\Link::IGNORE_MODULE, ['HEADERCSS']);
		$selectedThemeCssPath = Vtiger_Theme::getThemeStyle();
		$cssScriptModel = new Vtiger_CssScript_Model();
		$headerCssInstances[] = $cssScriptModel->set('href', $selectedThemeCssPath);

		foreach ($headerCss as $headerType => $cssLinks) {
			foreach ($cssLinks as $cssLink) {
				if ($this->checkFileUriInRelocatedMouldesFolder($cssLink->linkurl)) {
					$headerCssInstances[] = Vtiger_CssScript_Model::getInstanceFromLinkObject($cssLink);
				}
			}
		}
		return $headerCssInstances;
	}
}
