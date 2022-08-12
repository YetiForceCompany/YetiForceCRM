<?php
/**
 * Layout class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Layout class.
 */
class Layout
{
	/**
	 * Get active layout name.
	 *
	 * @return string
	 */
	public static function getActiveLayout()
	{
		if (Session::has('layout')) {
			return Session::get('layout');
		}
		return \App\Config::main('defaultLayout');
	}

	/**
	 * Get file from layout.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public static function getLayoutFile($name)
	{
		$basePath = 'layouts' . '/' . \App\Config::main('defaultLayout') . '/';
		$filePath = \Vtiger_Loader::resolveNameToPath('~' . $basePath . $name);
		if (is_file($filePath)) {
			if (!IS_PUBLIC_DIR) {
				$basePath = 'public_html/' . $basePath;
			}
			return $basePath . $name;
		}
		$basePath = 'layouts' . '/' . \Vtiger_Viewer::getDefaultLayoutName() . '/';
		if (!IS_PUBLIC_DIR) {
			$basePath = 'public_html/' . $basePath;
		}
		return $basePath . $name;
	}

	/**
	 * Gets layout paths.
	 *
	 * @return array
	 */
	public static function getLayoutPaths(): array
	{
		$basePrefix = 'layouts/' . self::getActiveLayout() . \DIRECTORY_SEPARATOR;
		$defaultPrefix = 'layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . \DIRECTORY_SEPARATOR;
		if (\App\Config::performance('LOAD_CUSTOM_FILES')) {
			$layoutsPath['custom/'] = 'custom/';
			$layoutsPath["custom/{$basePrefix}"] = "custom/{$basePrefix}";
			$layoutsPath["custom/{$defaultPrefix}"] = "custom/{$defaultPrefix}";
		}
		$layoutsPath[''] = '';
		$layoutsPath[$basePrefix] = $basePrefix;
		$layoutsPath[$defaultPrefix] = $defaultPrefix;
		return $layoutsPath;
	}

	/**
	 * Get all layouts list.
	 *
	 * @return string[]
	 */
	public static function getAllLayouts()
	{
		$all = (new \App\Db\Query())->select(['name', 'label'])->from('vtiger_layout')->all();
		$folders = [
			'basic' => Language::translate('LBL_DEFAULT'),
		];
		foreach ($all as $row) {
			$folders[$row['name']] = Language::translate($row['label']);
		}
		return $folders;
	}

	/**
	 * Get public url from file.
	 *
	 * @param string $name
	 * @param bool   $full
	 *
	 * @return string
	 */
	public static function getPublicUrl($name, $full = false)
	{
		$basePath = '';
		if ($full) {
			$basePath .= \App\Config::main('site_URL');
		}
		if (!IS_PUBLIC_DIR) {
			$basePath .= 'public_html/';
		}
		return $basePath . $name;
	}

	/**
	 * The function get path  to the image.
	 *
	 * @param string $imageName
	 *
	 * @return array
	 */
	public static function getImagePath($imageName)
	{
		return \Vtiger_Theme::getImagePath($imageName);
	}

	/**
	 * Function takes a template path.
	 *
	 * @param string $templateName
	 * @param string $moduleName
	 *
	 * @return string
	 */
	public static function getTemplatePath(string $templateName, string $moduleName = ''): string
	{
		return \Vtiger_Viewer::getInstance()->getTemplatePath($templateName, $moduleName);
	}

	/**
	 * Check if template exists.
	 *
	 * @param string $templateName
	 * @param string $moduleName
	 *
	 * @return bool
	 */
	public static function checkTemplatePath(string $templateName, string $moduleName = ''): bool
	{
		self::getTemplatePath($templateName, $moduleName);
		return file_exists(\Vtiger_Viewer::$completeTemplatePath);
	}

	/**
	 * Get unique id for HTML ids.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public static function getUniqueId($name = '')
	{
		return str_replace([' ', '"', "'"], '', $name) . random_int(100, 99999);
	}

	/**
	 * Truncating plain text and adding a button showing all the text.
	 *
	 * @param string $text
	 * @param int    $length
	 * @param bool   $showIcon
	 * @param bool   $nl2br
	 *
	 * @return string
	 */
	public static function truncateText(string $text, int $length, bool $showIcon = false, bool $nl2br = false): string
	{
		if (\mb_strlen($text) < $length) {
			return $nl2br ? nl2br($text) : $text;
		}
		$teaser = TextUtils::textTruncate($text, $length);
		if ($showIcon) {
			$btn = '<span class="mdi mdi-overscan"></span>';
		} else {
			$btn = \App\Language::translate('LBL_MORE_BTN');
		}
		return "<div class=\"js-more-content c-text-divider\"><pre class=\"teaserContent u-pre\">$teaser</pre><span class=\"fullContent d-none\"><pre class=\"u-pre\">$text</pre></span><span class=\"text-right\"><button type=\"button\" class=\"btn btn-link btn-sm p-0 js-more\">{$btn}</button></span></div>";
	}

	/**
	 * Truncating HTML and adding a button showing all the text.
	 *
	 * @param string $html
	 * @param string $size
	 * @param int    $length
	 * @param mixed  $showBtn
	 *
	 * @return string
	 */
	public static function truncateHtml(?string $html, ?string $size = 'medium', ?int $length = 200, $showBtn = false): string
	{
		if (empty($html)) {
			return '';
		}
		$teaser = $css = $btn = '';
		$loadData = $iframe = true;
		$btnTemplate = function (string $popoverText = '', ?string $btnClass = '', string $data = 'data-iframe="true"'): string {
			$popoverText = \App\Language::translate($popoverText);
			return "<a href=\"#\" class=\"js-more noLinkBtn font-weight-lighter js-popover-tooltip ml-2 {$btnClass}\" {$data} data-content=\"{$popoverText}\"><span class=\"mdi mdi-overscan\"></span></a>";
		};
		$iframeClass = 'modal-iframe js-modal-iframe';
		switch ($size) {
			case 'full':
				$iframeClass = 'js-iframe-full-height';
				break;
			case 'medium':
				$btn = $btnTemplate('LBL_FULLSCREEN', 'c-btn-floating-right-bottom btn btn-primary');
				break;
			case 'mini':
				$btn = $btnTemplate('LBL_SHOW_ORIGINAL_CONTENT');
				$css = 'display: none;';
				$teaser = TextUtils::textTruncate(trim(strip_tags($html)), $length);
				$loadData = false;
				break;
			case 'miniHtml':
				$btn = $btnTemplate('LBL_SHOW_ORIGINAL_CONTENT', '', 'data-modal-size="modal-md"');
				$css = 'display: none;';
				$teaserBefore = str_replace('<br>', '', $html);
				$teaser = TextUtils::htmlTruncateByWords(str_replace('<br>', '', $teaserBefore), $length);
				if (false === $showBtn && $teaserBefore == $teaser) {
					$html = $btn = '';
				}
				$iframe = false;
				break;
			default:
				break;
		}
		if ($iframe) {
			$html = Purifier::encodeHtml($html);
			$content = "<div class=\"js-iframe-content\">{$teaser}<iframe sandbox=\"allow-same-origin allow-popups allow-popups-to-escape-sandbox\" class=\"w-100 {$iframeClass}\" frameborder=\"0\" style=\"{$css}\" " . ($loadData ? 'srcdoc' : 'srcdoctemp') . "=\"{$html}\"></iframe>";
		} else {
			$content = "<div class=\"js-more-content\">{$teaser}<div class=\"w-100 {$iframeClass} fullContent\" style=\"{$css}\">{$html}</div>";
		}
		return $content . $btn . '</div>';
	}
}
