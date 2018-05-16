<?php

namespace App\Controller;

/**
 * Abstract view controller class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
			$pageTitle = \App\Language::translate($this->pageTitle, $qualifiedModuleName);
		} else {
			$pageTitle = $this->getBreadcrumbTitle($request);
		}
		return $prefix . $pageTitle;
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
		$viewer = $this->getViewer($request);
		$pageTitle = $this->getPageTitle($request);
		$this->loadJsConfig($request);
		if (\AppConfig::performance('BROWSING_HISTORY_WORKING')) {
			\Vtiger_BrowsingHistory_Helper::saveHistory($pageTitle);
		}
		$viewer->assign('PAGETITLE', $pageTitle);
		$viewer->assign('BREADCRUMB_TITLE', $this->getBreadcrumbTitle($request));
		$viewer->assign('HEADER_SCRIPTS', $this->getHeaderScripts($request));
		$viewer->assign('STYLES', $this->getHeaderCss($request));
		$viewer->assign('SKIN_PATH', \Vtiger_Theme::getCurrentUserThemePath());
		$viewer->assign('LAYOUT_PATH', \App\Layout::getPublicUrl('layouts/' . \App\Layout::getActiveLayout()));
		$viewer->assign('LANGUAGE_STRINGS', $this->getJSLanguageStrings($request));
		$viewer->assign('LANGUAGE', \App\Language::getLanguage());
		$viewer->assign('HTMLLANG', \App\Language::getShortLanguageName());
		$viewer->assign('SHOW_BODY_HEADER', $this->showBodyHeader());
		$viewer->assign('SHOW_BREAD_CRUMBS', $this->showBreadCrumbLine());
		$viewer->assign('USER_MODEL', \Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('VIEW', $request->getByType('view', 1));
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PARENT_MODULE', $request->getByType('parent', 2));
		$companyDetails = \App\Company::getInstanceById();
		$viewer->assign('COMPANY_DETAILS', $companyDetails);
		$viewer->assign('COMPANY_LOGO', $companyDetails->getLogo(false, false));
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
		$viewer = $this->getViewer($request);
		$currentUser = \Users_Record_Model::getCurrentUserModel();
		$viewer->assign('ACTIVITY_REMINDER', $currentUser->getCurrentUserActivityReminderInSeconds());
		$viewer->assign('FOOTER_SCRIPTS', $this->getFooterScripts($request));
		$viewer->assign('SHOW_FOOTER', $this->showFooter());
		$viewer->view('Footer.tpl');
	}

	/**
	 * Retrieves css styles that need to loaded in the page.
	 *
	 * @param \App\Request $request - request model
	 *
	 * @return <array> - array of Vtiger_CssScript_Model
	 */

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
				'~libraries/chosen-js/chosen.css',
				'~libraries/bootstrap-chosen/bootstrap-chosen.css',
				'~libraries/jquery-ui-dist/jquery-ui.css',
				'~libraries/selectize/dist/css/selectize.bootstrap3.css',
				'~libraries/select2/dist/css/select2.css',
				'~libraries/simplebar/dist/simplebar.css',
				'~libraries/perfect-scrollbar/css/perfect-scrollbar.css',
				'~libraries/jQuery-Validation-Engine/css/validationEngine.jquery.css',
				'~libraries/bootstrap-tabdrop/css/tabdrop.css',
				'~libraries/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css',
				'~libraries/bootstrap-daterangepicker/daterangepicker.css',
				'~libraries/footable/css/footable.core.css',
				'~libraries/clockpicker/dist/bootstrap-clockpicker.css',
				'~libraries/animate.css/animate.css',
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
				'~libraries/@fortawesome/fontawesome/index.js',
				'~libraries/@fortawesome/fontawesome-free-regular/index.js',
				'~libraries/@fortawesome/fontawesome-free-solid/index.js',
				'~libraries/@fortawesome/fontawesome-free-brands/index.js',
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
			'~libraries/chosen-js/chosen.jquery.js',
			'~libraries/select2/dist/js/select2.full.js',
			'~libraries/jquery-ui-dist/jquery-ui.js',
			'~libraries/jquery.class.js/jquery.class.js',
			'~libraries/jstorage/jstorage.js',
			'~libraries/perfect-scrollbar/dist/perfect-scrollbar.js',
			'~libraries/jquery-slimscroll/jquery.slimscroll.js',
			'~libraries/pnotify/dist/iife/PNotify.js',
			'~libraries/pnotify/dist/iife/PNotifyButtons.js',
			'~libraries/pnotify/dist/iife/PNotifyAnimate.js',
			'~libraries/pnotify/dist/iife/PNotifyMobile.js',
			'~libraries/pnotify/dist/iife/PNotifyConfirm.js',
			'~libraries/jquery-hoverintent/jquery.hoverIntent.js',
			'~libraries/popper.js/dist/umd/popper.js',
			'~libraries/bootstrap/dist/js/bootstrap.js',
			'~libraries/bootstrap-tabdrop/js/bootstrap-tabdrop.js',
			'~libraries/bootbox/bootbox.js',
			'~libraries/microplugin/src/microplugin.js',
			'~libraries/sifter/sifter.js',
			'~libraries/selectize/dist/js/selectize.js',
			'~libraries/jQuery-Validation-Engine/js/jquery.validationEngine.js',
			'~libraries/moment/min/moment.min.js',
			'~libraries/bootstrap-datepicker/dist/js/bootstrap-datepicker.js',
			'~libraries/bootstrap-datepicker/dist/locales/' . \App\Language::getLanguage() . '.min.js',
			'~libraries/bootstrap-daterangepicker/daterangepicker.js',
			'~libraries/jquery-outside-events/jquery.ba-outside-events.js',
			'~libraries/dompurify/dist/purify.js',
			'~libraries/footable/dist/footable.js',
			'~layouts/resources/app.js',
			'~libraries/blueimp-file-upload/js/jquery.fileupload.js',
			'~layouts/resources/fields/MultiImage.js',
			'~layouts/resources/Fields.js',
			'~layouts/resources/helper.js',
			'~layouts/resources/Connector.js',
			'~layouts/resources/ProgressIndicator.js',
		];
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
					$filePath = $prefix . ltrim(ltrim($jsFileName, '~'), '/');
				} else {
					$filePath = $prefix . str_replace('.', '/', $jsFileName) . '.' . $fileExtension;
				}
				$minFilePath = str_replace('.js', '.min.js', $filePath);
				if (\vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(\Vtiger_Loader::resolveNameToPath('~' . $minFilePath, $fileExtension))) {
					$filePath = $minFilePath;
				}
				\App\Cache::save('ConvertJsScripts', $jsFileName, $filePath, \App\Cache::LONG);
				$jsScriptInstances[$jsFileName] = $jsScript->set('src', $filePath);
				continue;
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
					continue;
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
				continue;
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
					continue;
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
		'firstDayOfWeekNo' => \App\Fields\Date::$dayOfWeek[$userModel->getDetail('dayoftheweek')],
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
		'sounds' => \AppConfig::sounds(),
		'intervalForNotificationNumberCheck' => \AppConfig::performance('INTERVAL_FOR_NOTIFICATION_NUMBER_CHECK'),
		'fieldsReferencesDependent' => \AppConfig::security('FIELDS_REFERENCES_DEPENDENT'),
		'soundFilesPath' => \App\Layout::getPublicUrl('layouts/resources/sounds/'),
		] as $key => $value) {
			\App\Config::setJsEnv($key, $value);
		}
	}
}
