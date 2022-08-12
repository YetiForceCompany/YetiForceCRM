<?php

namespace App;

/**
 * Utils class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Utils
{
	/**
	 * Function to capture the initial letters of words.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public static function getInitials(string $name): string
	{
		preg_match_all('#(?<=\s|\b)\pL|[()]#u', $name, $initial);
		return isset($initial[0]) ? implode('', $initial[0]) : '';
	}

	/**
	 * Outputs or returns a parsable string representation of a variable.
	 *
	 * @see https://php.net/manual/en/function.var-export.php
	 *
	 * @param mixed $variable
	 *
	 * @return mixed the variable representation when the <i>return</i>
	 */
	public static function varExport($variable)
	{
		if (\is_array($variable)) {
			$toImplode = [];
			if (static::isAssoc($variable)) {
				foreach ($variable as $key => $value) {
					$toImplode[] = var_export($key, true) . '=>' . static::varExport($value);
				}
			} else {
				foreach ($variable as $value) {
					$toImplode[] = static::varExport($value);
				}
			}
			return '[' . implode(',', $toImplode) . ']';
		}
		return var_export($variable, true);
	}

	/**
	 * Check if array is associative.
	 *
	 * @param array $arr
	 *
	 * @return bool
	 */
	public static function isAssoc(array $arr)
	{
		if (empty($arr)) {
			return false;
		}
		return array_keys($arr) !== range(0, \count($arr) - 1);
	}

	/**
	 * Flatten a multi-dimensional array into a single level.
	 *
	 * @param array $array
	 * @param float $depth
	 *
	 * @return array
	 */
	public static function flatten(array $array, float $depth = INF): array
	{
		$result = [];
		foreach ($array as $item) {
			if (\is_array($item)) {
				$values = 1 === $depth ? array_values($item) : static::flatten($item, $depth - 1);
				foreach ($values as $value) {
					$result[] = $value;
				}
			} else {
				$result[] = $item;
			}
		}
		return $result;
	}

	/**
	 * Flatten the multidimensional array on one level, keeping the key names unique.
	 *
	 * @param array  $array
	 * @param string $type
	 * @param float  $depth
	 *
	 * @return array
	 */
	public static function flattenKeys(array $array, string $type = '_', float $depth = INF): array
	{
		$result = [];
		foreach ($array as $key => $item) {
			if (\is_array($item)) {
				if (1 === $depth) {
					$values = array_values($item);
				} else {
					$values = static::flattenKeys($item, $type, $depth - 1);
				}
				foreach ($values as $keySec => $value) {
					switch ($type) {
						case 'ucfirst':
							$keySec = \ucfirst($keySec);
							$newKey = "{$key}{$keySec}";
							break;
						default:
						$newKey = "{$key}{$type}{$keySec}";
							break;
					}
					$result[$newKey] = $value;
				}
			} else {
				$result[$key] = $item;
			}
		}
		return $result;
	}

	/**
	 * Merge two arrays.
	 *
	 * @param array $array1
	 * @param array $array2
	 *
	 * @return array
	 */
	public static function merge(array $array1, array $array2): array
	{
		foreach ($array2 as $key => $value) {
			if (isset($array1[$key])) {
				if (\is_array($array1[$key]) && \is_array($value)) {
					$array1[$key] = self::merge($array1[$key], $value);
				} else {
					$array1[$key] = $value;
				}
			} else {
				$array1[$key] = $value;
			}
		}
		return $array1;
	}

	/**
	 * Convert string from encoding to encoding.
	 *
	 * @param string $value
	 * @param string $fromCharset
	 * @param string $toCharset
	 *
	 * @return string
	 */
	public static function convertCharacterEncoding($value, $fromCharset, $toCharset)
	{
		if (\function_exists('mb_convert_encoding') && \function_exists('mb_list_encodings') && \in_array($fromCharset, mb_list_encodings()) && \in_array($toCharset, mb_list_encodings())) {
			$value = mb_convert_encoding($value, $toCharset, $fromCharset);
		} else {
			$value = iconv($fromCharset, $toCharset, $value);
		}
		return $value;
	}

	/**
	 * Function to check is a html message.
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	public static function isHtml(string $content): bool
	{
		$content = trim($content);
		if ('<' === substr($content, 0, 1) && '>' === substr($content, -1)) {
			return true;
		}
		return $content != strip_tags($content);
	}

	/**
	 * Strip tags content.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public static function htmlToText(string $content): string
	{
		return trim(preg_replace('/[ \t\n]+/', ' ', strip_tags($content)));
	}

	/**
	 * Function to save php file with cleaning file cache.
	 *
	 * @param string       $pathDirectory
	 * @param array|string $content
	 * @param string       $comment
	 * @param int          $flag
	 * @param bool         $return
	 *
	 * @return bool $value
	 */
	public static function saveToFile(string $pathDirectory, $content, string $comment = '', int $flag = LOCK_EX, bool $return = false): bool
	{
		if (\is_array($content)) {
			$content = self::varExport($content);
		}
		if ($return) {
			$content = "return $content;";
		}
		if ($comment) {
			$content = "<?php \n/**  {$comment}  */\n{$content}\n";
		} else {
			$content = "<?php $content" . PHP_EOL;
		}
		if (false !== $value = file_put_contents($pathDirectory, $content, $flag)) {
			Cache::resetFileCache($pathDirectory);
		}
		return (bool) $value;
	}

	/**
	 * Replacement for the ucfirst function for proper Multibyte String operation.
	 * Delete function will exist as mb_ucfirst.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function mbUcfirst($string)
	{
		return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
	}

	/**
	 * Sanitize special chars from given string.
	 *
	 * @param string $string
	 * @param string $delimiter
	 *
	 * @return string
	 */
	public static function sanitizeSpecialChars(string $string, string $delimiter = '_'): string
	{
		$string = mb_convert_encoding((string) $string, 'UTF-8', mb_list_encodings());
		$replace = [
			'ъ' => '-', 'Ь' => '-', 'Ъ' => '-', 'ь' => '-',
			'Ă' => 'A', 'Ą' => 'A', 'À' => 'A', 'Ã' => 'A', 'Á' => 'A', 'Æ' => 'A', 'Â' => 'A', 'Å' => 'A', 'Ä' => 'Ae',
			'Þ' => 'B', 'Ć' => 'C', 'ץ' => 'C', 'Ç' => 'C', 'È' => 'E', 'Ę' => 'E', 'É' => 'E', 'Ë' => 'E', 'Ê' => 'E',
			'Ğ' => 'G', 'İ' => 'I', 'Ï' => 'I', 'Î' => 'I', 'Í' => 'I', 'Ì' => 'I', 'Ł' => 'L', 'Ñ' => 'N', 'Ń' => 'N',
			'Ø' => 'O', 'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'Oe', 'Ş' => 'S', 'Ś' => 'S', 'Ș' => 'S',
			'Š' => 'S', 'Ț' => 'T', 'Ù' => 'U', 'Û' => 'U', 'Ú' => 'U', 'Ü' => 'Ue', 'Ý' => 'Y', 'Ź' => 'Z', 'Ž' => 'Z',
			'Ż' => 'Z', 'â' => 'a', 'ǎ' => 'a', 'ą' => 'a', 'á' => 'a', 'ă' => 'a', 'ã' => 'a', 'Ǎ' => 'a', 'а' => 'a',
			'А' => 'a', 'å' => 'a', 'à' => 'a', 'א' => 'a', 'Ǻ' => 'a', 'Ā' => 'a', 'ǻ' => 'a', 'ā' => 'a', 'ä' => 'ae',
			'æ' => 'ae', 'Ǽ' => 'ae', 'ǽ' => 'ae',	'б' => 'b', 'ב' => 'b', 'Б' => 'b', 'þ' => 'b',	'ĉ' => 'c', 'Ĉ' => 'c',
			'Ċ' => 'c', 'ć' => 'c', 'ç' => 'c', 'ц' => 'c', 'צ' => 'c', 'ċ' => 'c', 'Ц' => 'c', 'Č' => 'c', 'č' => 'c',
			'Ч' => 'ch', 'ч' => 'ch', 'ד' => 'd', 'ď' => 'd', 'Đ' => 'd', 'Ď' => 'd', 'đ' => 'd', 'д' => 'd', 'Д' => 'D',
			'ð' => 'd', 'є' => 'e', 'ע' => 'e', 'е' => 'e', 'Е' => 'e', 'Ə' => 'e', 'ę' => 'e', 'ĕ' => 'e', 'ē' => 'e',
			'Ē' => 'e', 'Ė' => 'e', 'ė' => 'e', 'ě' => 'e', 'Ě' => 'e', 'Є' => 'e', 'Ĕ' => 'e', 'ê' => 'e', 'ə' => 'e',
			'è' => 'e', 'ë' => 'e', 'é' => 'e', 'ф' => 'f', 'ƒ' => 'f', 'Ф' => 'f', 'ġ' => 'g', 'Ģ' => 'g', 'Ġ' => 'g',
			'Ĝ' => 'g', 'Г' => 'g', 'г' => 'g', 'ĝ' => 'g', 'ğ' => 'g', 'ג' => 'g', 'Ґ' => 'g', 'ґ' => 'g', 'ģ' => 'g',
			'ח' => 'h', 'ħ' => 'h', 'Х' => 'h', 'Ħ' => 'h', 'Ĥ' => 'h', 'ĥ' => 'h', 'х' => 'h', 'ה' => 'h', 'î' => 'i',
			'ï' => 'i', 'í' => 'i', 'ì' => 'i', 'į' => 'i', 'ĭ' => 'i', 'ı' => 'i', 'Ĭ' => 'i', 'И' => 'i', 'ĩ' => 'i',
			'ǐ' => 'i', 'Ĩ' => 'i', 'Ǐ' => 'i', 'и' => 'i', 'Į' => 'i', 'י' => 'i', 'Ї' => 'i', 'Ī' => 'i', 'І' => 'i',
			'ї' => 'i', 'і' => 'i', 'ī' => 'i', 'ĳ' => 'ij', 'Ĳ' => 'ij', 'й' => 'j', 'Й' => 'j', 'Ĵ' => 'j', 'ĵ' => 'j',
			'я' => 'ja', 'Я' => 'ja', 'Э' => 'je', 'э' => 'je', 'ё' => 'jo', 'Ё' => 'jo', 'ю' => 'ju', 'Ю' => 'ju',
			'ĸ' => 'k', 'כ' => 'k', 'Ķ' => 'k', 'К' => 'k', 'к' => 'k', 'ķ' => 'k', 'ך' => 'k', 'Ŀ' => 'l', 'ŀ' => 'l',
			'Л' => 'l', 'ł' => 'l', 'ļ' => 'l', 'ĺ' => 'l', 'Ĺ' => 'l', 'Ļ' => 'l', 'л' => 'l', 'Ľ' => 'l', 'ľ' => 'l',
			'ל' => 'l', 'מ' => 'm', 'М' => 'm', 'ם' => 'm', 'м' => 'm', 'ñ' => 'n', 'н' => 'n', 'Ņ' => 'n', 'ן' => 'n',
			'ŋ' => 'n', 'נ' => 'n', 'Н' => 'n', 'ń' => 'n', 'Ŋ' => 'n', 'ņ' => 'n', 'ŉ' => 'n', 'Ň' => 'n', 'ň' => 'n',
			'о' => 'o', 'О' => 'o', 'ő' => 'o', 'õ' => 'o', 'ô' => 'o', 'Ő' => 'o', 'ŏ' => 'o', 'Ŏ' => 'o', 'Ō' => 'o',
			'ō' => 'o', 'ø' => 'o', 'ǿ' => 'o', 'ǒ' => 'o', 'ò' => 'o', 'Ǿ' => 'o', 'Ǒ' => 'o', 'ơ' => 'o', 'ó' => 'o',
			'Ơ' => 'o', 'œ' => 'oe', 'Œ' => 'oe', 'ö' => 'oe', 'פ' => 'p', 'ף' => 'p', 'п' => 'p', 'П' => 'p', 'ק' => 'q',
			'ŕ' => 'r', 'ř' => 'r', 'Ř' => 'r', 'ŗ' => 'r', 'Ŗ' => 'r', 'ר' => 'r', 'Ŕ' => 'r', 'Р' => 'r', 'р' => 'r',
			'ș' => 's', 'с' => 's', 'Ŝ' => 's', 'š' => 's', 'ś' => 's', 'ס' => 's', 'ş' => 's', 'С' => 's', 'ŝ' => 's',
			'Щ' => 'sch', 'щ' => 'sch', 'ш' => 'sh', 'Ш' => 'sh', 'ß' => 'ss', 'т' => 't', 'ט' => 't', 'ŧ' => 't',
			'ת' => 't', 'ť' => 't', 'ţ' => 't', 'Ţ' => 't', 'Т' => 't', 'ț' => 't', 'Ŧ' => 't', 'Ť' => 't', '™' => 'tm',
			'ū' => 'u', 'у' => 'u', 'Ũ' => 'u', 'ũ' => 'u', 'Ư' => 'u', 'ư' => 'u', 'Ū' => 'u', 'Ǔ' => 'u', 'ų' => 'u',
			'Ų' => 'u', 'ŭ' => 'u', 'Ŭ' => 'u', 'Ů' => 'u', 'ů' => 'u', 'ű' => 'u', 'Ű' => 'u', 'Ǖ' => 'u', 'ǔ' => 'u',
			'Ǜ' => 'u', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'У' => 'u', 'ǚ' => 'u', 'ǜ' => 'u', 'Ǚ' => 'u', 'Ǘ' => 'u',
			'ǖ' => 'u', 'ǘ' => 'u', 'ü' => 'ue', 'в' => 'v', 'ו' => 'v', 'В' => 'v', 'ש' => 'w', 'ŵ' => 'w', 'Ŵ' => 'w',
			'ы' => 'y', 'ŷ' => 'y', 'ý' => 'y', 'ÿ' => 'y', 'Ÿ' => 'y', 'Ŷ' => 'y', 'Ы' => 'y', 'ž' => 'z', 'З' => 'z',
			'з' => 'z', 'ź' => 'z', 'ז' => 'z', 'ż' => 'z', 'ſ' => 'z', 'Ж' => 'zh', 'ж' => 'zh', 'Ð' => 'D', 'Θ' => '8',
			'©' => '(c)', 'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Ι' => 'I',
			'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',	'Ρ' => 'R', 'Σ' => 'S',
			'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W', 'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I',
			'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I', 'Ϋ' => 'Y', 'α' => 'a', 'β' => 'b', 'γ' => 'g',
			'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8', 'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm',
			'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p', 'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f',
			'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w', 'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h',
			'ώ' => 'w', 'ς' => 's', 'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
		];
		$string = strtr($string, $replace);
		$string = preg_replace('/[^\p{L}\p{Nd}\.]+/u', $delimiter, $string);
		return trim($string, $delimiter);
	}

	/**
	 * Change the order of associative array.
	 *
	 * @param array $array
	 * @param array $order
	 *
	 * @return array
	 */
	public static function changeSequence(array $array, array $order): array
	{
		if (!$order) {
			return $array;
		}
		$returnLinks = [];
		foreach ($order as $value) {
			if ($array[$value]) {
				$returnLinks[$value] = $array[$value];
			}
			unset($array[$value]);
		}
		return array_merge($returnLinks, $array);
	}

	/**
	 * Get locks content by events.
	 *
	 * @param array $locks
	 *
	 * @return string
	 */
	public static function getLocksContent(array $locks): string
	{
		$return = '';
		foreach ($locks as $lock) {
			switch ($lock) {
				case 'copy':
					$return .= ' oncopy = "return false"';
					break;
				case 'cut':
					$return .= ' oncut = "return false"';
					break;
				case 'paste':
					$return .= ' onpaste = "return false"';
					break;
				case 'contextmenu':
					$return .= ' oncontextmenu = "return false"';
					break;
				case 'selectstart':
					$return .= ' onselectstart = "return false" onselect = "return false"';
					break;
				case 'drag':
					$return .= ' ondragstart = "return false" ondrag = "return false"';
					break;
			}
		}
		return $return;
	}
}
