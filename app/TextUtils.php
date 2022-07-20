<?php
/**
 * Text utils file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Text utils class.
 */
class TextUtils
{
	/**
	 * Get text length.
	 *
	 * @param string $text
	 *
	 * @return int
	 */
	public static function getTextLength($text)
	{
		return null !== $text ? mb_strlen($text) : 0;
	}

	/**
	 * Truncating text.
	 *
	 * @param string   $text
	 * @param bool|int $length
	 * @param bool     $addDots
	 *
	 * @return string
	 */
	public static function textTruncate($text, $length = false, $addDots = true)
	{
		if (!$length) {
			$length = Config::main('listview_max_textlength');
		}
		$textLength = 0;
		if (null !== $text) {
			$textLength = mb_strlen($text);
		}
		if ((!$addDots && $textLength > $length) || ($addDots && $textLength > $length + 2)) {
			$text = mb_substr($text, 0, $length, Config::main('default_charset'));
			if ($addDots) {
				$text .= '...';
			}
		}
		return $text;
	}

	/**
	 * Truncating HTML by words.
	 *
	 * @param string $html
	 * @param int    $length
	 * @param string $ending
	 *
	 * @return string
	 */
	public static function htmlTruncateByWords(string $html, int $length = 0, string $ending = '...'): string
	{
		if (!$length) {
			$length = Config::main('listview_max_textlength');
		}
		if (\strlen(strip_tags($html)) <= $length) {
			return $html;
		}
		$totalLength = \mb_strlen($ending);
		$openTagsLength = 0;
		$openTags = [];
		preg_match_all('/(<.+?>)?([^<>]*)/s', $html, $tags, PREG_SET_ORDER);
		$html = '';
		foreach ($tags as $tag) {
			$tagLength = \mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $tag[2]));
			if (($totalLength + $tagLength + $openTagsLength) >= $length) {
				if (empty($html)) {
					preg_match('/^<\s*([^\s>!]+).*?>$/s', $tag[1], $tagName);
					$openTags[] = $tagName[1];
					$html = $tag[1] . self::textTruncate($tag[2], $length - 3, false);
				}
				break;
			}
			if (!empty($tag[1])) {
				if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $tag[1])) {
					// if tag is a closing tag
				} elseif (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $tag[1], $tagName)) {
					$pos = array_search(strtolower($tagName[1]), $openTags);
					if (false !== $pos) {
						unset($openTags[$pos]);
						$openTagsLength -= \mb_strlen("</{$tagName[1]}>");
					}
				} elseif (preg_match('/^<\s*([^\s>!]+).*?>$/s', $tag[1], $tagName)) {
					array_unshift($openTags, strtolower($tagName[1]));
					$openTagsLength += \mb_strlen("</{$tagName[1]}>");
				}
			}
			$html .= $tag[0];
			$totalLength += $tagLength;
		}
		$html .= $ending;
		if ($openTags) {
			$html .= '</' . implode('></', $openTags) . '>';
		}
		return $html;
	}

	/**
	 * Truncating HTML.
	 *
	 * @param string $html
	 * @param int    $length
	 * @param string $ending
	 *
	 * @return string
	 */
	public static function htmlTruncate(string $html, int $length = 255, string $ending = '...'): string
	{
		if (\strlen($html) <= $length) {
			return $html;
		}
		$totalLength = \mb_strlen($ending);
		$openTagsLength = 0;
		$openTags = [];
		preg_match_all('/(<.+?>)?([^<>]*)/s', $html, $tags, PREG_SET_ORDER);
		$html = '';
		foreach ($tags as $tag) {
			$tagLength = \mb_strlen($tag[0]);
			if (($totalLength + $tagLength + $openTagsLength) >= $length) {
				if (empty($html)) {
					preg_match('/^<\s*([^\s>!]+).*?>$/s', $tag[1], $tagName);
					$openTags[] = $tagName[1];
					$html = $tag[1] . self::textTruncate($tag[2], $length - 3, false);
				}
				break;
			}
			if (!empty($tag[1])) {
				if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $tag[1])) {
					// if tag is a closing tag
				} elseif (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $tag[1], $tagName)) {
					$pos = array_search(strtolower($tagName[1]), $openTags);
					if (false !== $pos) {
						unset($openTags[$pos]);
						$openTagsLength -= \mb_strlen("</{$tagName[1]}>");
					}
				} elseif (preg_match('/^<\s*([^\s>!]+).*?>$/s', $tag[1], $tagName)) {
					array_unshift($openTags, strtolower($tagName[1]));
					$openTagsLength += \mb_strlen("</{$tagName[1]}>");
				}
			}
			$html .= $tag[0];
			$totalLength += $tagLength;
		}
		$html .= $ending;
		if ($openTags) {
			$html .= '</' . implode('></', $openTags) . '>';
		}
		return $html;
	}

	/**
	 * Get all attributes of a tag.
	 *
	 * @param string $tag
	 *
	 * @return string[]
	 */
	public static function getTagAttributes(string $tag): array
	{
		$dom = new \DOMDocument('1.0', 'UTF-8');
		$previousValue = libxml_use_internal_errors(true);
		$dom->loadHTML('<?xml encoding="utf-8"?>' . $tag);
		libxml_clear_errors();
		libxml_use_internal_errors($previousValue);
		$tag = $dom->getElementsByTagName('*')->item(2);
		$attributes = [];
		if ($tag->hasAttributes()) {
			foreach ($tag->attributes as $attr) {
				$attributes[$attr->name] = $attr->value;
			}
		}
		return $attributes;
	}
}
