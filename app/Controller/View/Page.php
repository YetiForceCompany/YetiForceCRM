<?php
/**
 * Abstract page view controller file.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller\View;

/**
 * Abstract page view controller class.
 */
abstract class Page extends Base
{
	/**
	 * {@inheritdoc}
	 */
	protected function showBodyHeader()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function showFooter()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$view = $this->getViewer($request);
		if (\App\Config::performance('BROWSING_HISTORY_WORKING')) {
			\Vtiger_BrowsingHistory_Helper::saveHistory((string) $view->getVariable('PAGETITLE'));
		}
		$view->assign('BREADCRUMB_TITLE', $this->getBreadcrumbTitle($request));
		$view->assign('SHOW_BREAD_CRUMBS', $this->showBreadCrumbLine());
		if ($activeReminder = \App\Module::isModuleActive('Calendar')) {
			$userPrivilegesModel = \Users_Privileges_Model::getCurrentUserPrivilegesModel();
			$activeReminder = $userPrivilegesModel->hasModulePermission('Calendar');
		}
		$view->assign('REMINDER_ACTIVE', $activeReminder);
		$view->assign('QUALIFIED_MODULE', $request->getModule(false));
		$view->assign('MENUS', $this->getMenu());
		$view->assign('BROWSING_HISTORY', \Vtiger_BrowsingHistory_Helper::getHistory());
		$view->assign('HOME_MODULE_MODEL', \Vtiger_Module_Model::getInstance('Home'));
		$view->assign('MENU_HEADER_LINKS', $this->getMenuHeaderLinks($request));
		if (\App\Config::performance('GLOBAL_SEARCH')) {
			$view->assign('SEARCHABLE_MODULES', \Vtiger_Module_Model::getSearchableModules());
		}
		if (\App\Config::search('GLOBAL_SEARCH_SELECT_MODULE')) {
			$view->assign('SEARCHED_MODULE', $request->getModule());
		}
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcess(\App\Request $request, $display = true)
	{
		parent::postProcess($request, false);
		$view = $this->getViewer($request);
		$view->assign('ACTIVITY_REMINDER', \Users_Record_Model::getCurrentUserModel()->getCurrentUserActivityReminderInSeconds());
		$view->assign('SHOW_FOOTER_BAR', $this->showFooter() && 8 !== \App\YetiForce\Register::getStatus());
		$view->assign('SHOW_FOOTER', true);
		if ($display) {
			$view->view('PageFooter.tpl');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$jsFileNames = [
			'modules.Vtiger.resources.Menu',
			'modules.Vtiger.resources.Header',
			'modules.Vtiger.resources.Edit',
			"modules.$moduleName.resources.Edit",
			'~layouts/resources/Field.js',
			'~layouts/resources/validator/BaseValidator.js',
			'~layouts/resources/validator/FieldValidator.js',
			'modules.Vtiger.resources.BasicSearch',
			'modules.Vtiger.resources.ConditionBuilder',
			'modules.Vtiger.resources.AdvanceFilter',
			"modules.$moduleName.resources.AdvanceFilter",
			'modules.Vtiger.resources.AdvanceSearch',
		];
		if (\App\Privilege::isPermitted('OSSMail')) {
			$jsFileNames[] = '~layouts/basic/modules/OSSMail/resources/checkmails.js';
		}
		if (\App\Privilege::isPermitted('Chat')) {
			$jsFileNames[] = '~layouts/basic/modules/Chat/Chat.vue.js';
		}
		if (\App\Privilege::isPermitted('KnowledgeBase')) {
			$jsFileNames[] = '~layouts/resources/views/KnowledgeBase/KnowledgeBase.vue.js';
		}
		foreach (\Vtiger_Link_Model::getAllByType(\vtlib\Link::IGNORE_MODULE, ['HEADERSCRIPT']) as $headerScripts) {
			foreach ($headerScripts as $headerScript) {
				if ($this->checkFileUriInRelocatedModulesFolder($headerScript->linkurl)) {
					if (!IS_PUBLIC_DIR) {
						$headerScript->linkurl = 'public_html/' . $headerScript->linkurl;
					}
					$jsFileNames[] = \Vtiger_JsScript_Model::getInstanceFromLinkObject($headerScript);
				}
			}
		}
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(\App\Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$cssScriptModel = new \Vtiger_CssScript_Model();
		$headerCssInstances[] = $cssScriptModel->set('href', \Vtiger_Theme::getThemeStyle());
		foreach (\Vtiger_Link_Model::getAllByType(\vtlib\Link::IGNORE_MODULE, ['HEADERCSS']) as $cssLinks) {
			foreach ($cssLinks as $cssLink) {
				if ($this->checkFileUriInRelocatedModulesFolder($cssLink->linkurl)) {
					if (!IS_PUBLIC_DIR) {
						$cssLink->linkurl = 'public_html/' . $cssLink->linkurl;
					}
					$headerCssInstances[] = \Vtiger_CssScript_Model::getInstanceFromLinkObject($cssLink);
				}
			}
		}
		return $headerCssInstances;
	}

	/**
	 * {@inheritdoc}
	 */
	public function loadJsConfig(\App\Request $request)
	{
		parent::loadJsConfig($request);
		if (\App\Session::has('ShowAuthy2faModal')) {
			\App\Config::setJsEnv('ShowAuthy2faModal', \App\Session::get('ShowAuthy2faModal'));
			if ('TOTP_OPTIONAL' === \App\Config::security('USER_AUTHY_MODE')) {
				\App\Session::delete('ShowAuthy2faModal');
			}
		}
		if (\App\Session::has('ShowUserPasswordChange')) {
			\App\Config::setJsEnv('ShowUserPasswordChange', \App\Session::get('ShowUserPasswordChange'));
			if (1 === (int) \App\Session::get('ShowUserPasswordChange')) {
				\App\Session::delete('ShowUserPasswordChange');
			}
		}
	}

	/**
	 * Function to determine file existence in relocated module folder (under vtiger6).
	 *
	 * @param string $fileUri
	 *
	 * @return bool
	 *
	 * Utility function to manage the backward compatible file load
	 * which are registered for 5.x modules (and now provided for 6.x as well)
	 */
	protected function checkFileUriInRelocatedModulesFolder(string $fileUri)
	{
		if (false !== strpos($fileUri, '?')) {
			[$filename] = explode('?', $fileUri);
		} else {
			$filename = $fileUri;
		}
		return file_exists(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'public_html' . \DIRECTORY_SEPARATOR . $filename);
	}

	/**
	 * Function to get the list of Header Links.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_Link_Model[] - List of Vtiger_Link_Model instances
	 */
	protected function getMenuHeaderLinks(\App\Request $request)
	{
		$userModel = \Users_Record_Model::getCurrentUserModel();
		$headerLinks = [];
		if (\Users_Module_Model::getSwitchUsers()) {
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
		if (\App\Config::security('SHOW_MY_PREFERENCES')) {
			$headerLinks[] = [
				'linktype' => 'HEADERLINK',
				'linklabel' => 'LBL_MY_PREFERENCES',
				'linkurl' => $userModel->getPreferenceDetailViewUrl(),
				'icon' => 'fas fa-user-cog fa-fw',
			];
		}
		if ($userModel->isAdminUser()) {
			if ('Settings' !== $request->getByType('parent', 2)) {
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
			$headerLinkInstance = \Vtiger_Link_Model::getInstanceFromValues($headerLink);
			if (isset($headerLink['childlinks'])) {
				foreach ($headerLink['childlinks'] as $childLink) {
					$headerLinkInstance->addChildLink(\Vtiger_Link_Model::getInstanceFromValues($childLink));
				}
			}
			$headerLinkInstances[] = $headerLinkInstance;
		}
		$headerLinks = \Vtiger_Link_Model::getAllByType(\vtlib\Link::IGNORE_MODULE, ['HEADERLINK']);
		foreach ($headerLinks as $headerLinks) {
			foreach ($headerLinks as $headerLink) {
				$headerLinkInstances[] = \Vtiger_Link_Model::getInstanceFromLinkObject($headerLink);
			}
		}
		return $headerLinkInstances;
	}

	/**
	 * Function to get the list of menu.
	 *
	 * @return array
	 */
	protected function getMenu()
	{
		return \Vtiger_Menu_Model::getAll(true);
	}
}
