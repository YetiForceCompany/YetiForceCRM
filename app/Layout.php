<?php
/**
 * Layout class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
	 *
	 * @return string
	 */
	public static function truncateText(string $text, int $length): string
	{
		if (\mb_strlen($text) < $length) {
			return $text;
		}
		$teaser = TextParser::textTruncate($text, $length);
		$btn = \App\Language::translate('LBL_MORE_BTN');
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
	public static function truncateHtml(string $html, ?string $size = 'medium', ?int $length = 20): string
	{
		$teaser = $css = $btn = '';
		$iframeClass = 'modal-iframe';
		if ('full' === $size) {
			$iframeClass = 'js-iframe-full-height';
		} elseif ('mini' === $size) {
			$btnText = \App\Language::translate('LBL_MORE_BTN');
			$btn = "
				<a href=\"#\" class=\"js-more font-weight-lighter\" data-iframe=\"true\">
					{$btnText}
				</a>";
			$css = 'display: none;';
			$teaser = TextParser::textTruncate(trim(strip_tags($html)), $length);
		} elseif ('medium' === $size) {
			$btn = '<button type="button" class="btn btn-primary c-btn-floating-right-bottom js-more btnNoFastEdit" data-iframe="true">
			<span class="mdi mdi-fullscreen"></span>
			</button>';
		}
		return "
		<div class=\"js-iframe-content\" >
			$teaser
			<iframe class=\"w-100 {$iframeClass}\" frameborder=\"0\" style=\"{$css}\" srcdoc=\"$html\"></iframe>
			{$btn}
		</div>";
	}
}
