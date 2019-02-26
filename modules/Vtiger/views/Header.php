<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

abstract class Vtiger_Header_View extends \App\Controller\View
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Function to determine file existence in relocated module folder (under vtiger6).
	 *
	 * @param string $fileuri
	 *
	 * @return bool
	 *
	 * Utility function to manage the backward compatible file load
	 * which are registered for 5.x modules (and now provided for 6.x as well)
	 */
	protected function checkFileUriInRelocatedMouldesFolder($fileuri)
	{
		if (strpos($fileuri, '?') !== false) {
			list($filename) = explode('?', $fileuri);
		} else {
			$filename = $fileuri;
		}
		return file_exists(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . $filename);
	}

	/**
	 * Function to get the list of Header Links.
	 *
	 * @return <Array> - List of Vtiger_Link_Model instances
	 */
	public function getMenuHeaderLinks(\App\Request $request)
	{
		$userModel = Users_Record_Model::getCurrentUserModel();
		$headerLinks = [];
		if (Users_Module_Model::getSwitchUsers()) {
			$headerLinks[] = [
				'linktype' => 'HEADERLINK',
				'linklabel' => 'SwitchUsers',
				'linkurl' => '',
				'icon' => 'fas fa-exchange-alt fa-fw',
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
				'icon' => 'fas fa-user-cog fa-fw',
			];
		}
		if ($userModel->isAdminUser()) {
			if ($request->getByType('parent', 2) !== 'Settings') {
				$headerLinks[] = [
					'linktype' => 'HEADERLINK',
					'linklabel' => 'LBL_SYSTEM_SETTINGS',
					'linkurl' => 'index.php?module=Vtiger&parent=Settings&view=Index',
					'icon' => 'fas fa-cog fa-fw',
				];
			} else {
				$headerLinks[] = [
					'linktype' => 'HEADERLINK',
					'linklabel' => 'LBL_USER_PANEL',
					'linkurl' => 'index.php',
					'icon' => 'fas fa-user fa-fw',
				];
			}
		}
		$headerLinks[] = [
			'linktype' => 'HEADERLINK',
			'linklabel' => 'LBL_SIGN_OUT',
			'linkurl' => 'index.php?module=Users&parent=Settings&action=Logout',
			'icon' => 'fas fa-power-off fa-fw',
			'linkclass' => 'btn-danger',
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
		foreach ($headerLinks as $headerLinks) {
			foreach ($headerLinks as $headerLink) {
				$headerLinkInstances[] = Vtiger_Link_Model::getInstanceFromLinkObject($headerLink);
			}
		}
		return $headerLinkInstances;
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$headerScripts = Vtiger_Link_Model::getAllByType(vtlib\Link::IGNORE_MODULE, ['HEADERSCRIPT']);
		foreach ($headerScripts as $headerScripts) {
			foreach ($headerScripts as $headerScript) {
				if ($this->checkFileUriInRelocatedMouldesFolder($headerScript->linkurl)) {
					if (!IS_PUBLIC_DIR) {
						$headerScript->linkurl = 'public_html/' . $headerScript->linkurl;
					}
					$headerScriptInstances[] = Vtiger_JsScript_Model::getInstanceFromLinkObject($headerScript);
				}
			}
		}
		return $headerScriptInstances;
	}

	/**
	 * Function to get the list of Css models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_CssScript_Model[]
	 */
	public function getHeaderCss(\App\Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$headerCss = Vtiger_Link_Model::getAllByType(vtlib\Link::IGNORE_MODULE, ['HEADERCSS']);
		$selectedThemeCssPath = Vtiger_Theme::getThemeStyle();
		$cssScriptModel = new Vtiger_CssScript_Model();
		$headerCssInstances[] = $cssScriptModel->set('href', $selectedThemeCssPath);

		foreach ($headerCss as $cssLinks) {
			foreach ($cssLinks as $cssLink) {
				if ($this->checkFileUriInRelocatedMouldesFolder($cssLink->linkurl)) {
					if (!IS_PUBLIC_DIR) {
						$cssLink->linkurl = 'public_html/' . $cssLink->linkurl;
					}
					$headerCssInstances[] = Vtiger_CssScript_Model::getInstanceFromLinkObject($cssLink);
				}
			}
		}
		return $headerCssInstances;
	}
}
