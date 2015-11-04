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

	function __construct()
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
		list ($filename, $query) = explode('?', $fileuri);

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
	public function getHeaderLinks(Vtiger_Request $request)
	{
		$userModel = Users_Record_Model::getCurrentUserModel();
		$headerLinks = [];
		if ($userModel->isAdminUser()) {
			if($request->get('parent') != 'Settings') {
				$headerLinks[] = [
					'linktype' => 'HEADERLINK',
					'linklabel' => 'LBL_SYSTEM_SETTINGS',
					'linkurl' => 'index.php?module=Vtiger&parent=Settings&view=Index',
					'glyphicon' => 'glyphicon glyphicon-cog',
				];
			}else{
				$headerLinks[] = [
					'linktype' => 'HEADERLINK',
					'linklabel' => 'LBL_USER_PANEL',
					'linkurl' => 'index.php',
					'glyphicon' => 'glyphicon glyphicon-user',
				];
			}
		}
		//TODO To remove in the future
		if (SysSecurity::getBoolean('SHOW_MY_PREFERENCES')) {
			$headerLinks[] = [
				'linktype' => 'HEADERLINK',
				'linklabel' => 'LBL_MY_PREFERENCES',
				'linkurl' => $userModel->getPreferenceDetailViewUrl(),
				'glyphicon' => 'glyphicon glyphicon-tasks',
			];
		}
		
		$headerLinks[] = [
			'linktype' => 'HEADERLINK',
			'linklabel' => 'LBL_SIGN_OUT',
			'linkurl' => 'index.php?module=Users&parent=Settings&action=Logout',
			'glyphicon' => 'glyphicon glyphicon-off',
		];

		require('user_privileges/switchUsers.php');
		$baseUserId = $userModel->getId();
		if (Vtiger_Session::has('baseUserId') && Vtiger_Session::get('baseUserId') != '') {
			$baseUserId = Vtiger_Session::get('baseUserId');
		}

		if (key_exists($baseUserId, $switchUsers)) {
			$childlinks = [];
			if (Vtiger_Session::has('baseUserId') && Vtiger_Session::get('baseUserId') != '') {
				$entityData = Vtiger_Functions::getEntityModuleInfo('Users');
				$user = new Users();
				$currentUser = $user->retrieveCurrentUserInfoFromFile($baseUserId);
				$colums = [];
				foreach (explode(',', $entityData['fieldname']) as &$fieldname) {
					$colums[] = $currentUser->column_fields[$fieldname];
				}
				$userName = implode(' ', $colums);
				$childlinks[] = [
					'linktype' => 'HEADERLINK',
					'linklabel' => $userName,
					'linkurl' => '?module=Users&action=SwitchUsers&id=' . $baseUserId,
					'linkicon' => '',
				];
				$childlinks[] = [
					'linktype' => 'HEADERLINK',
					'linklabel' => NULL,
				];
			}
			foreach ($switchUsers[$baseUserId] as $userid => $userName) {
				if ($userid != $baseUserId) {
					$childlinks[] = [
						'linktype' => 'HEADERLINK',
						'linklabel' => $userName,
						'linkurl' => '?module=Users&action=SwitchUsers&id=' . $userid,
						'linkicon' => '',
					];
				}
			}
			$customHeaderLinks = [
				'linktype' => 'HEADERLINK',
				'linklabel' => 'SwitchUsers',
				'linkurl' => '',
				'linkicon' => 'glyphicon glyphicon-transfer',
				'nocaret' => true,
				'childlinks' => $childlinks
			];
			if(count($childlinks)){
				array_push($headerLinks, $customHeaderLinks);
			}
		}
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
		$headerLinks = Vtiger_Link_Model::getAllByType(Vtiger_Link::IGNORE_MODULE, ['HEADERLINK']);
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
	function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$headerScripts = Vtiger_Link_Model::getAllByType(Vtiger_Link::IGNORE_MODULE, array('HEADERSCRIPT'));
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
	function getHeaderCss(Vtiger_Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$headerCss = Vtiger_Link_Model::getAllByType(Vtiger_Link::IGNORE_MODULE, array('HEADERCSS'));
		$selectedThemeCssPath = Vtiger_Theme::getStylePath();
		//TODO : check the filename whether it is less or css and add relative less
		$isLessType = (strpos($selectedThemeCssPath, ".less") !== false) ? true : false;
		$cssScriptModel = new Vtiger_CssScript_Model();
		$headerCssInstances[] = $cssScriptModel->set('href', $selectedThemeCssPath)
			->set('rel', $isLessType ?
				Vtiger_CssScript_Model::LESS_REL :
				Vtiger_CssScript_Model::DEFAULT_REL);

		foreach ($headerCss as $headerType => $cssLinks) {
			foreach ($cssLinks as $cssLink) {
				if ($this->checkFileUriInRelocatedMouldesFolder($cssLink->linkurl)) {
					$headerCssInstances[] = Vtiger_CssScript_Model::getInstanceFromLinkObject($cssLink);
				}
			}
		}
		return $headerCssInstances;
	}

	/**
	 * Function to get the Announcement
	 * @return Vtiger_Base_Model - Announcement
	 */
	function getAnnouncement()
	{
		//$announcement = Vtiger_Cache::get('announcement', 'value');
		$model = new Vtiger_Base_Model();
		//if(!$announcement) {
		$announcement = get_announcements();
		//Vtiger_Cache::set('announcement', 'value', $announcement);
		//}
		return $model->set('announcement', $announcement);
	}
}
