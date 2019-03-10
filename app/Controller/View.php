<?php
/**
 * Abstract view controller class.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller;

/**
 * Class View.
 */
abstract class View extends Base
{
	/**
	 * Viewer instance.
	 *
	 * @var \Vtiger_Viewer
	 */
	protected $viewer;

	/**
	 * Page title.
	 *
	 * @var string
	 */
	protected $pageTitle;

	/**
	 * Breadcrumb title.
	 *
	 * @var string
	 */
	protected $breadcrumbTitle;

	/**
	 * Show body header.
	 *
	 * @return bool
	 */
	protected function showBodyHeader()
	{
		return true;
	}

	/**
	 * Show footer.
	 *
	 * @return bool
	 */
	protected function showFooter()
	{
		return true;
	}

	/**
	 * Show bread crumbs.
	 *
	 * @return bool
	 */
	protected function showBreadCrumbLine()
	{
		return true;
	}

	/**
	 * Static function to get the Instance of the Vtiger_Viewer.
	 *
	 * @param \App\Request $request
	 *
	 * @return \Vtiger_Viewer
	 */
	public function getViewer(\App\Request $request)
	{
		if (!isset($this->viewer)) {
			$this->viewer = \Vtiger_Viewer::getInstance();
			$this->viewer->assign('APPTITLE', \App\Language::translate('APPTITLE'));
			$this->viewer->assign('YETIFORCE_VERSION', \App\Version::get());
			$this->viewer->assign('MODULE_NAME', $request->getModule());
			if ($request->isAjax()) {
				$this->viewer->assign('USER_MODEL', \Users_Record_Model::getCurrentUserModel());
				if (!$request->isEmpty('parent', true) && $request->getByType('parent', 2) === 'Settings') {
					$this->viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
				}
			}
		}
		return $this->viewer;
	}

	/**
	 * Get page title.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	public function getPageTitle(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleNameArray = explode(':', $qualifiedModuleName);
		$moduleName = end($moduleNameArray);
		$prefix = '';
		if ($moduleName !== 'Vtiger') {
			$prefix = \App\Language::translate($moduleName, $qualifiedModuleName) . ' ';
		}
		if (isset($this->pageTitle)) {
			$title = \App\Language::translate($this->pageTitle, $qualifiedModuleName);
		} else {
			$title = $this->getBreadcrumbTitle($request);
		}
		return $prefix . $title;
	}

	/**
	 * Get breadcrumb title.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	public function getBreadcrumbTitle(\App\Request $request)
	{
		if (isset($this->breadcrumbTitle)) {
			return $this->breadcrumbTitle;
		}
		if (isset($this->pageTitle)) {
			return \App\Language::translate($this->pageTitle, $request->getModule(false));
		}
		return '';
	}

	/**
	 * Pre process function.
	 *
	 * @param \App\Request $request
	 * @param bool         $display
	 */
	public function preProcess(\App\Request $request, $display = true)
	{
		$moduleName = $request->getModule();
		$view = $this->getViewer($request);
		$title = $this->getPageTitle($request);
		$this->loadJsConfig($request);
		if (\AppConfig::performance('BROWSING_HISTORY_WORKING')) {
			\Vtiger_BrowsingHistory_Helper::saveHistory($title);
		}
		$view->assign('PAGETITLE', $title);
		$view->assign('BREADCRUMB_TITLE', $this->getBreadcrumbTitle($request));
		$view->assign('HEADER_SCRIPTS', $this->getHeaderScripts($request));
		$view->assign('STYLES', $this->getHeaderCss($request));
		$view->assign('SKIN_PATH', \Vtiger_Theme::getCurrentUserThemePath());
		$view->assign('LAYOUT_PATH', \App\Layout::getPublicUrl('layouts/' . \App\Layout::getActiveLayout()));
		$view->assign('LANGUAGE_STRINGS', $this->getJSLanguageStrings($request));
		$view->assign('LANGUAGE', \App\Language::getLanguage());
		$view->assign('HTMLLANG', \App\Language::getShortLanguageName());
		$view->assign('SHOW_BODY_HEADER', $this->showBodyHeader());
		$view->assign('SHOW_BREAD_CRUMBS', $this->showBreadCrumbLine());
		$view->assign('USER_MODEL', \Users_Record_Model::getCurrentUserModel());
		$view->assign('CURRENT_USER', \App\User::getCurrentUserModel());
		$view->assign('MODULE', $moduleName);
		$view->assign('VIEW', $request->getByType('view', 1));
		$view->assign('MODULE_NAME', $moduleName);
		$view->assign('PARENT_MODULE', $request->getByType('parent', 2));
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	/**
	 * Pre process display function.
	 *
	 * @param \App\Request $request
	 */
	protected function preProcessDisplay(\App\Request $request)
	{
		$this->getViewer($request)->view($this->preProcessTplName($request), $request->getModule());
	}

	/**
	 * Pre process template name.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	protected function preProcessTplName(\App\Request $request)
	{
		return 'Header.tpl';
	}

	/**
	 * Post process function.
	 *
	 * @param \App\Request $request
	 */
	public function postProcess(\App\Request $request, $display = true)
	{
		$view = $this->getViewer($request);
		$currentUser = \Users_Record_Model::getCurrentUserModel();
		$view->assign('ACTIVITY_REMINDER', $currentUser->getCurrentUserActivityReminderInSeconds());
		$view->assign('FOOTER_SCRIPTS', $this->getFooterScripts($request));
		$view->assign('SHOW_FOOTER', $this->showFooter() && \App\YetiForce\Register::getStatus() !== 8);
		$view->view('Footer.tpl');
	}

	/**
	 * Get header css files that need to loaded in the page.
	 *
	 * @param \App\Request $request Request instance
	 *
	 * @return Vtiger_CssScript_Model[]
	 */
	public function getHeaderCss(\App\Request $request)
	{
		return $this->checkAndConvertCssStyles([
			'~layouts/resources/icons/userIcons.css',
			'~layouts/resources/icons/adminIcons.css',
			'~layouts/resources/icons/additionalIcons.css',
			'~libraries/@fortawesome/fontawesome-free/css/all.css',
			'~libraries/jquery-ui-dist/jquery-ui.css',
			'~libraries/select2/dist/css/select2.css',
			'~libraries/simplebar/dist/simplebar.css',
			'~libraries/perfect-scrollbar/css/perfect-scrollbar.css',
			'~libraries/jQuery-Validation-Engine/css/validationEngine.jquery.css',
			'~libraries/bootstrap-tabdrop/css/tabdrop.css',
			'~libraries/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css',
			'~libraries/bootstrap-daterangepicker/daterangepicker.css',
			'~libraries/footable/css/footable.core.css',
			'~libraries/clockpicker/dist/bootstrap4-clockpicker.css',
			'~libraries/animate.css/animate.css',
			'~libraries/tributejs/dist/tribute.css',
			'~libraries/emojipanel/dist/emojipanel.css',
			'~layouts/resources/colors/calendar.css',
			'~layouts/resources/colors/owners.css',
			'~layouts/resources/colors/modules.css',
			'~layouts/resources/colors/picklists.css',
			'~layouts/resources/styleTemplate.css',
			'~' . \Vtiger_Theme::getBaseStylePath(),
		]);
	}

	/**
	 * Get header scripts files that need to loaded in the page.
	 *
	 * @param \App\Request $request Request instance
	 *
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getHeaderScripts(\App\Request $request)
	{
		return $this->checkAndConvertJsScripts([
			'libraries.jquery.dist.jquery',
		]);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request Request instance
	 *
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$jsFileNames = [
			'~libraries/block-ui/jquery.blockUI.js',
			'~libraries/select2/dist/js/select2.full.js',
			'~libraries/jquery-ui-dist/jquery-ui.js',
			'~libraries/jquery.class.js/jquery.class.js',
			'~libraries/perfect-scrollbar/dist/perfect-scrollbar.js',
			'~libraries/jquery-slimscroll/jquery.slimscroll.js',
			'~libraries/pnotify/dist/iife/PNotify.js',
			'~libraries/pnotify/dist/iife/PNotifyButtons.js',
			'~libraries/pnotify/dist/iife/PNotifyAnimate.js',
			'~libraries/pnotify/dist/iife/PNotifyMobile.js',
			'~libraries/pnotify/dist/iife/PNotifyConfirm.js',
			'~libraries/pnotify/dist/iife/PNotifyDesktop.js',
			'~libraries/jquery-hoverintent/jquery.hoverIntent.js',
			'~libraries/popper.js/dist/umd/popper.js',
			'~libraries/bootstrap/dist/js/bootstrap.js',
			'~libraries/bootstrap-tabdrop/js/bootstrap-tabdrop.js',
			'~libraries/bootbox/dist/bootbox.min.js',
			'~libraries/microplugin/src/microplugin.js',
			'~libraries/sifter/sifter.js',
			'~libraries/jQuery-Validation-Engine/js/jquery.validationEngine.js',
			'~libraries/moment/min/moment.min.js',
			'~libraries/bootstrap-datepicker/dist/js/bootstrap-datepicker.js',
			'~libraries/bootstrap-datepicker/dist/locales/' . \App\Language::getLanguage() . '.min.js',
			'~libraries/bootstrap-daterangepicker/daterangepicker.js',
			'~libraries/jquery-outside-events/jquery.ba-outside-events.js',
			'~libraries/dompurify/dist/purify.js',
			'~libraries/footable/dist/footable.js',
			'~vendor/ckeditor/ckeditor/ckeditor.js',
			'~vendor/ckeditor/ckeditor/adapters/jquery.js',
			'~libraries/tributejs/dist/tribute.js',
			'~libraries/emojipanel/dist/emojipanel.js',
			'~layouts/resources/app.js',
			'~libraries/blueimp-file-upload/js/jquery.fileupload.js',
			'~libraries/floatthead/dist/jquery.floatThead.js',
			'~libraries/store/dist/store.legacy.min.js',
			'~layouts/resources/fields/MultiImage.js',
			'~layouts/resources/Fields.js',
			'~layouts/resources/Tools.js',
			'~layouts/resources/helper.js',
			'~layouts/resources/Connector.js',
			'~layouts/resources/ProgressIndicator.js',
		];
		if (\App\Privilege::isPermitted('OSSMail')) {
			$jsFileNames[] = '~layouts/basic/modules/OSSMail/resources/checkmails.js';
		}
		if (\App\Privilege::isPermitted('Chat')) {
			$jsFileNames[] = '~layouts/basic/modules/Chat/resources/Chat.js';
		}
		$languageHandlerShortName = \App\Language::getShortLanguageName();
		$fileName = "~libraries/jQuery-Validation-Engine/js/languages/jquery.validationEngine-$languageHandlerShortName.js";
		if (!file_exists(\Vtiger_Loader::resolveNameToPath($fileName, 'js'))) {
			$fileName = '~libraries/jQuery-Validation-Engine/js/languages/jquery.validationEngine-en.js';
		}
		$jsFileNames[] = $fileName;
		return $this->checkAndConvertJsScripts($jsFileNames);
	}

	/**
	 * Check and convert js scripts.
	 *
	 * @param string[] $jsFileNames
	 *
	 * @return Vtiger_JsScript_Model[]
	 */
	public function checkAndConvertJsScripts($jsFileNames)
	{
		$fileExtension = 'js';
		$jsScriptInstances = [];
		$prefix = '';
		if (!IS_PUBLIC_DIR && $fileExtension !== 'php') {
			$prefix = 'public_html/';
		}
		foreach ($jsFileNames as $jsFileName) {
			$jsScript = new \Vtiger_JsScript_Model();
			if (\App\Cache::has('ConvertJsScripts', $jsFileName)) {
				$jsScriptInstances[$jsFileName] = $jsScript->set('src', \App\Cache::get('ConvertJsScripts', $jsFileName));
				continue;
			}
			// external javascript source file handling
			if (strpos($jsFileName, 'http://') === 0 || strpos($jsFileName, 'https://') === 0) {
				$jsScriptInstances[$jsFileName] = $jsScript->set('src', $jsFileName);
				continue;
			}
			$completeFilePath = \Vtiger_Loader::resolveNameToPath($jsFileName, $fileExtension);
			if (is_file($completeFilePath)) {
				$jsScript->set('base', $completeFilePath);
				if (strpos($jsFileName, '~') === 0) {
					$filePath = ltrim(ltrim($jsFileName, '~'), '/');
				} else {
					$filePath = str_replace('.', '/', $jsFileName) . '.' . $fileExtension;
				}
				$minFilePath = str_replace('.js', '.min.js', $filePath);
				if (\vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(\Vtiger_Loader::resolveNameToPath('~' . $minFilePath, $fileExtension))) {
					$filePath = $minFilePath;
				}
				\App\Cache::save('ConvertJsScripts', $jsFileName, $prefix . $filePath, \App\Cache::LONG);
				$jsScriptInstances[$jsFileName] = $jsScript->set('src', $prefix . $filePath);
			} else {
				$preLayoutPath = '';
				if (strpos($jsFileName, '~') === 0) {
					$jsFile = ltrim(ltrim($jsFileName, '~'), '/');
					$preLayoutPath = '~';
				} else {
					$jsFile = $jsFileName;
				}
				// Checking if file exists in selected layout
				$isFileExists = false;
				$layoutPath = 'custom/layouts/' . \App\Layout::getActiveLayout();
				$fallBackFilePath = \Vtiger_Loader::resolveNameToPath($preLayoutPath . $layoutPath . '/' . $jsFile, $fileExtension);
				if (!($isFileExists = is_file($fallBackFilePath))) {
					$layoutPath = 'layouts/' . \App\Layout::getActiveLayout();
					$fallBackFilePath = \Vtiger_Loader::resolveNameToPath($preLayoutPath . $layoutPath . '/' . $jsFile, $fileExtension);
					$isFileExists = is_file($fallBackFilePath);
				}
				if ($isFileExists) {
					$jsScript->set('base', $fallBackFilePath);
					$filePath = $jsFile;
					if (empty($preLayoutPath)) {
						$filePath = str_replace('.', '/', $filePath) . '.js';
					}
					$minFilePath = str_replace('.js', '.min.js', $filePath);
					if (\vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(\Vtiger_Loader::resolveNameToPath('~' . $layoutPath . '/' . $minFilePath, $fileExtension))) {
						$filePath = $minFilePath;
					}
					$filePath = "{$prefix}{$layoutPath}/{$filePath}";
					\App\Cache::save('ConvertJsScripts', $jsFileName, $filePath, \App\Cache::LONG);
					$jsScriptInstances[$jsFileName] = $jsScript->set('src', $filePath);
					continue;
				}
				// Checking if file exists in default layout
				$isFileExists = false;
				$layoutPath = 'custom/layouts/' . \Vtiger_Viewer::getDefaultLayoutName();
				$fallBackFilePath = \Vtiger_Loader::resolveNameToPath($preLayoutPath . $layoutPath . '/' . $jsFile, $fileExtension);
				if (!($isFileExists = is_file($fallBackFilePath))) {
					$layoutPath = 'layouts/' . \Vtiger_Viewer::getDefaultLayoutName();
					$fallBackFilePath = \Vtiger_Loader::resolveNameToPath($preLayoutPath . $layoutPath . '/' . $jsFile, $fileExtension);
					$isFileExists = is_file($fallBackFilePath);
				}
				if ($isFileExists) {
					$jsScript->set('base', $fallBackFilePath);
					$filePath = $jsFile;
					if (empty($preLayoutPath)) {
						$filePath = str_replace('.', '/', $jsFile) . '.js';
					}
					$minFilePath = str_replace('.js', '.min.js', $filePath);
					if (\vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(\Vtiger_Loader::resolveNameToPath('~' . $layoutPath . '/' . $minFilePath, $fileExtension))) {
						$filePath = $minFilePath;
					}
					$filePath = "{$prefix}{$layoutPath}/{$filePath}";
					\App\Cache::save('ConvertJsScripts', $jsFileName, $filePath, \App\Cache::LONG);
					$jsScriptInstances[$jsFileName] = $jsScript->set('src', $filePath);
				}
			}
		}
		return $jsScriptInstances;
	}

	/**
	 * Check and convert css files.
	 *
	 * @param string[] $cssFileNames
	 * @param string   $fileExtension
	 *
	 * @return Vtiger_CssScript_Model[]
	 */
	public function checkAndConvertCssStyles($cssFileNames, $fileExtension = 'css')
	{
		$prefix = '';
		if (!IS_PUBLIC_DIR && $fileExtension !== 'php') {
			$prefix = 'public_html/';
		}
		$cssStyleInstances = [];
		foreach ($cssFileNames as $cssFileName) {
			$cssScriptModel = new \Vtiger_CssScript_Model();
			if (\App\Cache::has('ConvertCssStyles', $cssFileName)) {
				$cssStyleInstances[$cssFileName] = $cssScriptModel->set('href', \App\Cache::get('ConvertCssStyles', $cssFileName));
				continue;
			}
			if (strpos($cssFileName, 'http://') === 0 || strpos($cssFileName, 'https://') === 0) {
				$cssStyleInstances[] = $cssScriptModel->set('href', $cssFileName);
				continue;
			}
			$completeFilePath = \Vtiger_Loader::resolveNameToPath($cssFileName, $fileExtension);
			if (file_exists($completeFilePath)) {
				$cssScriptModel->set('base', $completeFilePath);
				if (strpos($cssFileName, '~') === 0) {
					$filePath = $prefix . ltrim(ltrim($cssFileName, '~'), '/');
				} else {
					$filePath = $prefix . str_replace('.', '/', $cssFileName) . '.' . $fileExtension;
				}
				$minFilePath = str_replace('.css', '.min.css', $filePath);
				if (\vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(\Vtiger_Loader::resolveNameToPath('~' . $minFilePath, $fileExtension))) {
					$filePath = $minFilePath;
				}
				\App\Cache::save('ConvertCssStyles', $cssFileName, $filePath, \App\Cache::LONG);
				$cssStyleInstances[$cssFileName] = $cssScriptModel->set('href', $filePath);
			} else {
				$preLayoutPath = '';
				if (strpos($cssFileName, '~') === 0) {
					$cssFile = ltrim(ltrim($cssFileName, '~'), '/');
					$preLayoutPath = '~';
				} else {
					$cssFile = $cssFileName;
				}
				// Checking if file exists in selected layout
				$isFileExists = false;
				$layoutPath = 'custom/layouts/' . \App\Layout::getActiveLayout();
				$fallBackFilePath = \Vtiger_Loader::resolveNameToPath($preLayoutPath . $layoutPath . '/' . $cssFile, $fileExtension);
				if (!($isFileExists = is_file($fallBackFilePath))) {
					$layoutPath = 'layouts/' . \App\Layout::getActiveLayout();
					$fallBackFilePath = \Vtiger_Loader::resolveNameToPath($preLayoutPath . $layoutPath . '/' . $cssFile, $fileExtension);
					$isFileExists = is_file($fallBackFilePath);
				}
				if ($isFileExists) {
					$cssScriptModel->set('base', $fallBackFilePath);
					if (empty($preLayoutPath)) {
						$filePath = str_replace('.', '/', $cssFile) . '.css';
					}
					$minFilePath = str_replace('.css', '.min.css', $filePath);
					if (\vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(\Vtiger_Loader::resolveNameToPath('~' . $layoutPath . '/' . $minFilePath, $fileExtension))) {
						$filePath = $minFilePath;
					}
					$filePath = "{$prefix}{$layoutPath}/{$filePath}";
					\App\Cache::save('ConvertCssStyles', $cssFileName, $filePath, \App\Cache::LONG);
					$cssStyleInstances[$cssFileName] = $cssScriptModel->set('href', $filePath);
					continue;
				}
				// Checking if file exists in default layout
				$isFileExists = false;
				$layoutPath = 'custom/layouts/' . \Vtiger_Viewer::getDefaultLayoutName();
				$fallBackFilePath = \Vtiger_Loader::resolveNameToPath($preLayoutPath . $layoutPath . '/' . $cssFile, $fileExtension);
				if (!($isFileExists = is_file($fallBackFilePath))) {
					$layoutPath = 'layouts/' . \Vtiger_Viewer::getDefaultLayoutName();
					$fallBackFilePath = \Vtiger_Loader::resolveNameToPath($preLayoutPath . $layoutPath . '/' . $cssFile, $fileExtension);
					$isFileExists = is_file($fallBackFilePath);
				}
				if ($isFileExists) {
					$cssScriptModel->set('base', $fallBackFilePath);
					if (empty($preLayoutPath)) {
						$filePath = str_replace('.', '/', $cssFile) . '.css';
					}
					$minFilePath = str_replace('.css', '.min.css', $filePath);
					if (\vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(\Vtiger_Loader::resolveNameToPath('~' . $layoutPath . '/' . $minFilePath, $fileExtension))) {
						$filePath = $minFilePath;
					}
					$filePath = "{$prefix}{$layoutPath}/{$filePath}";
					\App\Cache::save('ConvertCssStyles', $cssFileName, $filePath, \App\Cache::LONG);
					$cssStyleInstances[$cssFileName] = $cssScriptModel->set('href', $filePath);
				}
			}
		}
		return $cssStyleInstances;
	}

	/**
	 * Function returns the Client side language string.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	public function getJSLanguageStrings(\App\Request $request)
	{
		$moduleName = $request->getModule(false);
		if ($moduleName === 'Settings:Users') {
			$moduleName = 'Users';
		}
		return \App\Language::getJsStrings($moduleName);
	}

	/**
	 * Load js config.
	 *
	 * @param \App\Request $request
	 */
	public function loadJsConfig(\App\Request $request)
	{
		$userModel = \App\User::getCurrentUserModel();
		foreach ([
					 'skinPath' => \Vtiger_Theme::getCurrentUserThemePath(),
					 'siteUrl' => \App\Layout::getPublicUrl('', true),
					 'layoutPath' => \App\Layout::getPublicUrl('layouts/' . \App\Layout::getActiveLayout()),
					 'langPrefix' => \App\Language::getLanguage(),
					 'langKey' => \App\Language::getShortLanguageName(),
					 'parentModule' => $request->getByType('parent', 2),
					 'dateFormat' => $userModel->getDetail('date_format'),
					 'dateFormatJs' => \App\Fields\Date::currentUserJSDateFormat($userModel->getDetail('date_format')),
					 'hourFormat' => $userModel->getDetail('hour_format'),
					 'startHour' => $userModel->getDetail('start_hour'),
					 'endHour' => $userModel->getDetail('end_hour'),
					 'firstDayOfWeek' => $userModel->getDetail('dayoftheweek'),
					 'firstDayOfWeekNo' => \App\Fields\Date::$dayOfWeek[$userModel->getDetail('dayoftheweek')] ?? false,
					 'eventLimit' => \AppConfig::module('Calendar', 'EVENT_LIMIT'),
					 'timeZone' => $userModel->getDetail('time_zone'),
					 'currencyId' => $userModel->getDetail('currency_id'),
					 'currencyName' => $userModel->getDetail('currency_name'),
					 'currencyCode' => $userModel->getDetail('currency_code'),
					 'currencySymbol' => $userModel->getDetail('currency_symbol'),
					 'currencyGroupingPattern' => $userModel->getDetail('currency_grouping_pattern'),
					 'currencyDecimalSeparator' => $userModel->getDetail('currency_decimal_separator'),
					 'currencyGroupingSeparator' => $userModel->getDetail('currency_grouping_separator'),
					 'currencySymbolPlacement' => $userModel->getDetail('currency_symbol_placement'),
					 'noOfCurrencyDecimals' => (int) $userModel->getDetail('no_of_currency_decimals'),
					 'truncateTrailingZeros' => $userModel->getDetail('truncate_trailing_zeros'),
					 'rowHeight' => $userModel->getDetail('rowheight'),
					 'userId' => $userModel->getId(),
					 'backgroundClosingModal' => \AppConfig::main('backgroundClosingModal'),
					 'globalSearchAutocompleteActive' => \AppConfig::search('GLOBAL_SEARCH_AUTOCOMPLETE'),
					 'globalSearchAutocompleteMinLength' => \AppConfig::search('GLOBAL_SEARCH_AUTOCOMPLETE_MIN_LENGTH'),
					 'globalSearchAutocompleteAmountResponse' => \AppConfig::search('GLOBAL_SEARCH_AUTOCOMPLETE_LIMIT'),
					 'globalSearchDefaultOperator' => \AppConfig::search('GLOBAL_SEARCH_DEFAULT_OPERATOR'),
					 'sounds' => \AppConfig::sounds(),
					 'intervalForNotificationNumberCheck' => \AppConfig::performance('INTERVAL_FOR_NOTIFICATION_NUMBER_CHECK'),
					 'recordPopoverDelay' => \AppConfig::performance('RECORD_POPOVER_DELAY'),
					 'searchShowOwnerOnlyInList' => \AppConfig::performance('SEARCH_SHOW_OWNER_ONLY_IN_LIST'),
					 'fieldsReferencesDependent' => \AppConfig::security('FIELDS_REFERENCES_DEPENDENT'),
					 'soundFilesPath' => \App\Layout::getPublicUrl('layouts/resources/sounds/'),
					 'debug' => (bool) \AppConfig::debug('JS_DEBUG'),
				 ] as $key => $value) {
			\App\Config::setJsEnv($key, $value);
		}
		if (\App\Session::has('ShowAuthy2faModal')) {
			\App\Config::setJsEnv('ShowAuthy2faModal', \App\Session::get('ShowAuthy2faModal'));
			if (\AppConfig::security('USER_AUTHY_MODE') === 'TOTP_OPTIONAL') {
				\App\Session::delete('ShowAuthy2faModal');
			}
		}
		if (\App\Session::has('ShowUserPasswordChange')) {
			\App\Config::setJsEnv('ShowUserPasswordChange', \App\Session::get('ShowUserPasswordChange'));
			if ((int) \App\Session::get('ShowUserPasswordChange') === 1) {
				\App\Session::delete('ShowUserPasswordChange');
			}
		}
	}
}
