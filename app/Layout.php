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
	 * Truncating HTML/text  and adding a button showing all the text.
	 *
	 * @param string $text
	 * @param int    $length
	 * @param bool   $isHtml
	 *
	 * @return string
	 */
	public static function truncate(string $text, int $length, bool $isHtml = true): string
	{
		if ($isHtml) {
			$isTruncated = \mb_strlen(strip_tags($text)) > $length;
		} else {
			$isTruncated = \mb_strlen($text) > $length;
		}
		if (!$isTruncated) {
			return $text;
		}
		$teaser = $isHtml ? TextParser::htmlTruncate($text, $length, true) : TextParser::textTruncate($text, $length, true);
		$btn = \App\Language::translate('LBL_MORE_BTN');
		return "<div class=\" js-more-content\"><span class=\"teaserContent\">$teaser</span><span class=\"fullContent d-none\">$text</span><span class=\"text-right mb-1\"><button type=\"button\" class=\"btn btn-link btn-sm js-more\">{$btn}</button></span></div>";
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
	 * @param int    $length
	 *
	 * @return string
	 */
	public static function truncateHtml(string $html, int $length): string
	{
		$teaser = $css = '';
		if ($length <= 200) {
			$css = 'display: none;';
			$teaser = TextParser::textTruncate(trim(strip_tags($html)), $length);
		}
		$btn = \App\Language::translate('LBL_MORE_BTN');
		return "<div class=\"js-iframe-content\">$teaser<iframe class=\"modal-iframe\" width=\"100%\" frameborder=\"0\" style=\"border: 0;width: 100%;{$css}\" srcdoc=\"$html\"></iframe><button type=\"button\" class=\"btn btn-link btn-sm pt-0 float-right js-more\" data-iframe=\"true\">{$btn}</button></div>";
	}
}
