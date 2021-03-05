<?php
/**
 * Layout class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	 * @return array
	 */
	public static function getTemplatePath($templateName, $moduleName = '')
	{
		return \Vtiger_Viewer::getInstance()->getTemplatePath($templateName, $moduleName);
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
	 *
	 * @return string
	 */
	public static function truncateText(string $text, int $length, bool $showIcon = false): string
	{
		if (\mb_strlen($text) < $length) {
			return $text;
		}
		$teaser = TextParser::textTruncate($text, $length);
		$text = nl2br($text);
		if ($showIcon) {
			$btn = '<span class="mdi mdi-overscan"></span>';
		} else {
			$btn = \App\Language::translate('LBL_MORE_BTN');
		}
		return "<div class=\"js-more-content\"><span class=\"teaserContent\">$teaser</span><span class=\"fullContent d-none\">$text</span><span class=\"text-right mb-1\"><button type=\"button\" class=\"btn btn-link btn-sm pt-0 js-more\">{$btn}</button></span></div>";
	}

	/**
	 * Truncating HTML and adding a button showing all the text.
	 *
	 * @param string $html
	 * @param string $size
	 * @param int    $length
	 *
	 * @return string
	 */
	public static function truncateHtml(?string $html, ?string $size = 'medium', ?int $length = 200): string
	{
		if (empty($html)) {
			return '';
		}
		$teaser = $css = $btn = '';
		$btnTemplate = function (string $popoverText = '', ?string $btnClass = ''): string {
			$popoverText = \App\Language::translate($popoverText);
			return "<a href=\"#\" class=\"js-more noLinkBtn font-weight-lighter js-popover-tooltip {$btnClass}\" data-iframe=\"true\" data-content=\"{$popoverText}\"><span class=\"mdi mdi-overscan\"></span></a>";
		};
		$iframeClass = 'modal-iframe js-modal-iframe';
		if ('full' === $size) {
			$iframeClass = 'js-iframe-full-height';
		} elseif ('mini' === $size) {
			$btn = $btnTemplate('LBL_SHOW_ORIGINAL_CONTENT');
			$css = 'display: none;';
			$teaser = TextParser::textTruncate(trim(strip_tags($html)), $length);
		} elseif ('medium' === $size) {
			$btn = $btnTemplate('LBL_FULLSCREEN', 'c-btn-floating-right-bottom btn btn-primary');
		}
		$html = Purifier::encodeHtml($html);
		return "<div class=\"js-iframe-content\" >$teaser <iframe sandbox=\"allow-same-origin allow-popups allow-popups-to-escape-sandbox\" class=\"w-100 {$iframeClass}\" frameborder=\"0\" style=\"{$css}\" srcdoc=\"{$html}\"></iframe>{$btn}</div>";
	}

	/**
	 * Get record label or href.
	 *
	 * @param int         $record
	 * @param string|null $moduleName
	 *
	 * @return string
	 */
	public static function getRecordLabel(int $record, ?string $moduleName = null): string
	{
		if (!$record) {
			return '-';
		}
		if (null === $moduleName) {
			$moduleName = Record::getType($record);
		}
		$label = TextParser::textTruncate(Record::getLabel($record) ?? '-', \App\Config::main('href_max_length'));
		if (!$moduleName || !Privilege::isPermitted($moduleName, 'DetailView', $record)) {
			return $label;
		}
		if ('Active' !== \App\Record::getState($record)) {
			$label = "<s>$label</s>";
		}
		return "<a class=\"modCT_{$moduleName} showReferenceTooltip js-popover-tooltip--record\" href=\"index.php?module={$moduleName}&view=Detail&record={$record}\">{$label}</a>";
	}
}
