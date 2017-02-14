<?php
namespace App;

/**
 * Purifier basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Purifier
{

	private static $purifyInstanceCache = false;
	private static $htmlEventAttributes = 'onerror|onblur|onchange|oncontextmenu|onfocus|oninput|oninvalid|onreset|onsearch|onselect|onsubmit|onkeydown|onkeypress|onkeyup|' .
		'onclick|ondblclick|ondrag|ondragend|ondragenter|ondragleave|ondragover|ondragstart|ondrop|onmousedown|onmousemove|onmouseout|onmouseover|' .
		'onmouseup|onmousewheel|onscroll|onwheel|oncopy|oncut|onpaste|onload|onselectionchange|onabort|onselectstart';

	/**
	 * Purify (Cleanup) malicious snippets of code from the input
	 * @param String $value
	 * @param Boolean $ignore Skip cleaning of the input
	 * @return String
	 */
	public static function purify($input, $ignore = false)
	{
		if (empty($input)) {
			return $input;
		}
		$value = $input;
		if (!is_array($input)) {
			$md5OfInput = md5($input) . '0';
			if (Cache::has('purify', $md5OfInput)) {
				$value = Cache::get('purify', $md5OfInput);
				$ignore = true; //to escape cleaning up again
			}
		}
		if ($ignore === false) {
			// Initialize the instance if it has not yet done
			if (static::$purifyInstanceCache === false) {
				$useCharset = \AppConfig::main('default_charset');
				if (empty($useCharset)) {
					$useCharset = 'UTF-8';
				}
				$config = \HTMLPurifier_Config::createDefault();
				$config->set('Core.Encoding', $useCharset);
				$config->set('Cache.SerializerPath', ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'vtlib');
				static::$purifyInstanceCache = new \HTMLPurifier($config);
			}
			if (static::$purifyInstanceCache) {
				// Composite type
				if (is_array($input)) {
					$value = [];
					foreach ($input as $k => &$v) {
						$value[$k] = static::purify($v, $ignore);
					}
				} else { // Simple type
					$value = static::$purifyInstanceCache->purify($input);
					$value = static::purifyHtmlEventAttributes($value);
					$value = str_replace('&amp;', '&', $value);
					Cache::save('purify', $md5OfInput, $value, Cache::SHORT);
				}
			}
		}
		return $value;
	}

	/**
	 * To purify malicious html event attributes
	 * @param String $value
	 * @return String
	 */
	public static function purifyHtmlEventAttributes($value)
	{
		if (preg_match("/\s(" . static::$htmlEventAttributes . ")\s*=/i", $value)) {
			$value = str_replace('=', '&equals;', $value);
		}
		return $value;
	}

	private static $purifyHtmlInstanceCache = false;

	/**
	 * Purify HTML (Cleanup) malicious snippets of code from the input
	 *
	 * @param String $value
	 * @param Boolean $ignore Skip cleaning of the input
	 * @return String
	 */
	public static function purifyHtml($input, $ignore = false)
	{
		if (empty($input)) {
			return $input;
		}
		$value = $input;
		if (!is_array($input)) {
			$md5OfInput = md5($input) . '1';
			if (Cache::has('purifyHtml', $md5OfInput)) {
				$value = Cache::get('purifyHtml', $md5OfInput);
				$ignore = true; //to escape cleaning up again
			}
		}
		if ($ignore === false) {
			// Initialize the instance if it has not yet done
			if (static::$purifyHtmlInstanceCache === false) {
				$useCharset = \AppConfig::main('default_charset');
				if (empty($useCharset)) {
					$useCharset = 'UTF-8';
				}
				$allowed = array(
					'img[src|alt|title|width|height|style|data-mce-src|data-mce-json|class]',
					'figure', 'figcaption',
					'video[src|type|width|height|poster|preload|controls|style|class]', 'source[src|type]',
					'audio[src|type|preload|controls|class]',
					'a[href|target|class]',
					'iframe[width|height|src|frameborder|allowfullscreen|class]',
					'strong', 'b', 'i', 'u', 'em', 'br', 'font',
					'h1[style|class]', 'h2[style|class]', 'h3[style|class]', 'h4[style|class]', 'h5[style|class]', 'h6[style|class]',
					'p[style|class]', 'div[style|class]', 'center', 'address[style]',
					'span[style|class]', 'pre[style]',
					'ul', 'ol', 'li',
					'table[width|height|border|style|class]', 'th[width|height|border|style|class]',
					'tr[width|height|border|style|class]', 'td[width|height|border|style|class]',
					'hr',
				);
				$config = \HTMLPurifier_Config::createDefault();
				$config->set('Core.Encoding', $useCharset);
				$config->set('Cache.SerializerPath', ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'vtlib');
				$config->set('HTML.Doctype', 'HTML 4.01 Transitional');
				$config->set('CSS.AllowTricky', true);
				$config->set('CSS.Proprietary', true);
				$config->set('HTML.SafeIframe', true);
				$config->set('HTML.SafeEmbed', true);
				$config->set('URI.SafeIframeRegexp', '%^(http:|https:)?//(www.youtube(?:-nocookie)?.com/embed/|player.vimeo.com/video/)%');
				$config->set('HTML.Allowed', implode(',', $allowed));
				$config->set('HTML.DefinitionRev', 1);
				if ($def = $config->getHTMLDefinition(true)) {
					// http://developers.whatwg.org/sections.html
					$def->addElement('section', 'Block', 'Flow', 'Common');
					$def->addElement('nav', 'Block', 'Flow', 'Common');
					$def->addElement('article', 'Block', 'Flow', 'Common');
					$def->addElement('aside', 'Block', 'Flow', 'Common');
					$def->addElement('header', 'Block', 'Flow', 'Common');
					$def->addElement('footer', 'Block', 'Flow', 'Common');
					// Content model actually excludes several tags, not modelled here
					$def->addElement('address', 'Block', 'Flow', 'Common');
					$def->addElement('hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common');
					// http://developers.whatwg.org/grouping-content.html
					$def->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
					$def->addElement('figcaption', 'Inline', 'Flow', 'Common');
					// http://developers.whatwg.org/the-video-element.html#the-video-element
					$def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
						'src' => 'URI',
						'type' => 'Text',
						'width' => 'Length',
						'height' => 'Length',
						'poster' => 'URI',
						'preload' => 'Enum#auto,metadata,none',
						'controls' => 'Bool',
					));
					$def->addElement('audio', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
						'src' => 'URI',
						'type' => 'Text',
						'preload' => 'Enum#auto,metadata,none',
						'controls' => 'Bool',
					));
					$def->addElement('source', 'Block', 'Flow', 'Common', array(
						'src' => 'URI',
						'type' => 'Text',
					));
					// http://developers.whatwg.org/text-level-semantics.html
					$def->addElement('s', 'Inline', 'Inline', 'Common');
					$def->addElement('var', 'Inline', 'Inline', 'Common');
					$def->addElement('sub', 'Inline', 'Inline', 'Common');
					$def->addElement('sup', 'Inline', 'Inline', 'Common');
					$def->addElement('mark', 'Inline', 'Inline', 'Common');
					$def->addElement('wbr', 'Inline', 'Empty', 'Core');
					// http://developers.whatwg.org/edits.html
					$def->addElement('ins', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));
					$def->addElement('del', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));
					// TinyMCE
					$def->addAttribute('img', 'data-mce-src', 'Text');
					$def->addAttribute('img', 'data-mce-json', 'Text');
					// Others
					$def->addAttribute('iframe', 'allowfullscreen', 'Bool');
					$def->addAttribute('table', 'height', 'Text');
					$def->addAttribute('td', 'border', 'Text');
					$def->addAttribute('th', 'border', 'Text');
					$def->addAttribute('tr', 'width', 'Text');
					$def->addAttribute('tr', 'height', 'Text');
					$def->addAttribute('tr', 'border', 'Text');
				}
				static::$purifyHtmlInstanceCache = new \HTMLPurifier($config);
			}
			if (static::$purifyHtmlInstanceCache) {
				// Composite type
				if (is_array($input)) {
					$value = [];
					foreach ($input as $k => &$v) {
						$value[$k] = static::purify($v, $ignore);
					}
				} else { // Simple type
					$value = static::$purifyHtmlInstanceCache->purify($input);
					$value = static::purifyHtmlEventAttributes($value);
					$value = str_replace('&amp;', '&', $value);
					Cache::save('purifyHtml', $md5OfInput, $value, Cache::SHORT);
				}
			}
		}
		return $value;
	}

	/**
	 * Function to return the valid SQl input.
	 * @param String $string
	 * @param Boolean $skipEmpty Skip the check if string is empty.
	 * @return String $string
	 */
	public static function purifySql($string, $skipEmpty = true)
	{
		if ((empty($string) && $skipEmpty) || preg_match("/^[_a-zA-Z0-9.,]+$/", $string)) {
			return $string;
		}
		return false;
	}

	private static $toHtmlInUTF8;

	/**
	 * Function to convert the given string to html
	 * @param $string -- string:: Type string
	 * @param $encode -- boolean:: Type boolean
	 * @returns $string -- string:: Type string
	 *
	 */
	public static function toHtml($string, $encode = true)
	{
		$oginalString = $string;
		if (Cache::has('toHtml', $oginalString)) {
			return Cache::get('toHtml', $oginalString);
		}
		$default_charset = vglobal('default_charset');

		$action = \AppRequest::has('action') ? \AppRequest::get('action') : false;
		$search = \AppRequest::has('search') ? \AppRequest::get('search') : false;
		$ajaxAction = false;
		$doconvert = false;

		// For optimization - default_charset can be either upper / lower case.
		if (!isset(static::$toHtmlInUTF8)) {
			static::$toHtmlInUTF8 = (strtoupper($default_charset) == 'UTF-8');
		}

		if (\AppRequest::has('module') && \AppRequest::has('file') && \AppRequest::get('module') !== 'Settings' && \AppRequest::get('file') !== 'ListView' && \AppRequest::get('module') !== 'Portal' && \AppRequest::get('module') !== 'Reports')
			$ajaxAction = \AppRequest::get('module') . 'Ajax';

		if (is_string($string) && !empty($string)) {
			if ($action !== 'CustomView' && $action !== 'Export' && $action !== $ajaxAction && $action !== 'LeadConvertToEntities' && \AppRequest::get('module') !== 'Dashboard' && (!\AppRequest::has('submode'))) {
				$doconvert = true;
			} else if ($search === true) {
				// Fix for tickets #4647, #4648. Conversion required in case of search results also.
				$doconvert = true;
			}

			// In vtiger5 ajax request are treated specially and the data is encoded
			if ($doconvert === true) {
				if (static::$toHtmlInUTF8)
					$string = htmlentities($string, ENT_QUOTES, $default_charset);
				else
					$string = preg_replace(['/</', '/>/', '/"/'], ['&lt;', '&gt;', '&quot;'], $string);
			}
		}
		Cache::save('toHtml', $oginalString, $string, Cache::LONG);
		return $string;
	}
}
