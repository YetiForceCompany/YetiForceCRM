<?php
/**
 * Utility for processing tags in text.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace App\Utils;

/**
 * Completions class.
 */
class Completions
{
	/**
	 * Format HTML.
	 *
	 * @var string
	 */
	const FORMAT_HTML = 'HTML';
	/**
	 * Format Text.
	 *
	 * @var string
	 */
	const FORMAT_TEXT = 'Text';
	/**
	 * Path to emoji.json.
	 *
	 * @var string
	 */
	const PATH_EMOJI_JSON = 'public_html/vendor/ckeditor/ckeditor/plugins/emoji/emoji.json';
	/**
	 * Emoji regex patter.
	 *
	 * @var string
	 */
	const EMOJI_REGEX = '/\:{2}[^\:]+\:{2}/';
	/**
	 * Record and Users regex patter.
	 *
	 * @var string
	 */
	const ROW_REGEX = '/(\@|\#){2}(\d+)(?:\@|\#){2}/';

	/**
	 * Get processed text in display mode.
	 *
	 * @param string $text
	 * @param string $format
	 *
	 * @return string
	 */
	public static function decode(string $text, string $format = self::FORMAT_HTML): string
	{
		$emojis = static::getEmojis();
		$textOut = \preg_replace_callback(
			static::EMOJI_REGEX,
			function (array $matches) use ($emojis) {
				return $emojis[$matches[0]] ?? $matches[0];
			},
			$text
		);
		return \preg_replace_callback(
			static::ROW_REGEX,
			function (array $matches) use ($format) {
				return static::decodeRow($matches[0], $matches[1], $matches[2], $format);
			},
			$textOut
		);
	}

	/**
	 * Get text to edit mode.
	 *
	 * @param string $text
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return string
	 */
	public static function encode(string $text): string
	{
		$emojis = static::getEmojis();
		$textOut = \preg_replace_callback(
			static::EMOJI_REGEX,
			function (array $matches) use ($emojis) {
				return $emojis[$matches[0]] ?? $matches[0];
			},
			$text
		);
		return \preg_replace_callback(
			static::ROW_REGEX,
			function (array $matches) {
				$type = $matches[1];
				$id = (int) $matches[2];
				if ('@' === $type) {
					$label = static::decodeOwnerText($id);
				} elseif ('#' === $type) {
					$label = static::decodeRecordText($id);
				} else {
					$label = '';
				}
				return "<a href=\"#\" data-id=\"{$type}{$id}\">{$label}</a>";
			},
			$textOut
		);
	}

	/**
	 * Process before writing to the database.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function encodeAll(string $text): string
	{
		return static::encodeRow(static::encodeEmoji($text));
	}

	/**
	 * Process before writing to the database.
	 *
	 * @example FROM: 😀 TO: :grinning_face:
	 *
	 * @param string $text
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return string
	 */
	public static function encodeEmoji(string $text): string
	{
		$emojis = array_flip(static::getEmojis());
		return str_replace(array_keys($emojis), $emojis, $text);
	}

	/**
	 * Process before writing to the database.
	 *
	 * @example FROM: <a href='#' data-id='@115'>text</a> TO: @@115@@
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function encodeRow(string $text): string
	{
		return \preg_replace_callback(
			"/<a\s+[^>]*data-id=(?:\"|')(.)(\d+)(?:\"|')[^>]*>[^<]+<\/a>/i",
			function (array $matches) {
				$type = $matches[1];
				return "{$type}{$type}{$matches[2]}{$type}{$type}";
			},
			$text
		);
	}

	/**
	 * Get array of emojis.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	private static function getEmojis(): array
	{
		if (\App\Cache::has('App\Utils\Text', 'emojis')) {
			$emojis = \App\Cache::get('App\Utils\Text', 'emojis');
		} elseif (\file_exists(static::PATH_EMOJI_JSON)) {
			$emojis = [];
			foreach (\App\Json::decode(\file_get_contents(static::PATH_EMOJI_JSON)) as $val) {
				$emojis[':' . $val['id'] . ':'] = $val['symbol'];
			}
			\App\Cache::save('App\Utils\Text', 'emojis', $emojis);
		} else {
			$emojis = [];
		}
		return $emojis;
	}

	/**
	 * Get processed text in display mode.
	 *
	 * @param string $baseText
	 * @param string $type
	 * @param string $id
	 * @param string $format
	 *
	 * @return string
	 */
	private static function decodeRow(string $baseText, string $type, string $id, string $format = self::FORMAT_HTML): string
	{
		$html = '';
		if ('#' === $type) {
			switch ($format) {
				case static::FORMAT_HTML:
					$html = static::decodeRecord($id);
					break;
				case static::FORMAT_TEXT:
					$html = static::decodeRecordText($id);
					break;
			}
		} elseif ('@' === $type) {
			switch ($format) {
				case static::FORMAT_HTML:
					$html = static::decodeOwner($id);
					break;
				case static::FORMAT_TEXT:
					$html = static::decodeOwnerText($id);
					break;
			}
		} else {
			$html = $baseText;
		}
		return $html;
	}

	/**
	 * Display record text.
	 *
	 * @param int $recordId
	 *
	 * @return string
	 */
	private static function decodeRecordText(int $recordId): string
	{
		if (\App\Record::isExists($recordId)) {
			$html = \App\Record::getLabel($recordId);
		} else {
			$html = \App\Language::translate('LBL_RECORD_NOT_FOUND');
		}
		return $html;
	}

	/**
	 * Display record.
	 *
	 * @param int $recordId
	 *
	 * @return string
	 */
	private static function decodeRecord(int $recordId): string
	{
		if (!($moduleName = \App\Record::getType($recordId))) {
			$html = \App\Language::translate('LBL_RECORD_NOT_FOUND');
		} elseif (\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId)) {
			$html = "<a href=\"index.php?module={$moduleName}&view=Detail&record={$recordId}\" class=\"js-popover-tooltip--record\" data-id=\"#{$recordId}\">" .
				\App\Record::getLabel($recordId) . '</a>&nbsp;';
		} else {
			$html = \App\Record::getLabel($recordId);
		}
		return $html;
	}

	/**
	 * Display owner.
	 *
	 * @param int $userId
	 *
	 * @return string
	 */
	private static function decodeOwner(int $userId): string
	{
		if (!\App\User::isExists($userId)) {
			$html = \App\Language::translate('LBL_RECORD_NOT_FOUND');
		} else {
			$isRecordPermitted = \App\Privilege::isPermitted('Users', 'DetailView', $userId);
			$popoverRecordClass = $isRecordPermitted ? 'js-popover-tooltip--record' : '';
			$popoverRecordHref = $isRecordPermitted ? "index.php?module=Users&view=Detail&record={$userId}" : '#';
			$html = "<a class=\"js-completions__tag $popoverRecordClass\" href=\"$popoverRecordHref\" data-id=\"@$userId\" data-js=\"click\">" .
				\App\User::getUserModel($userId)->getName() .
				'</a>';
		}
		return $html;
	}

	/**
	 * Display owner text.
	 *
	 * @param int $userId
	 *
	 * @return string
	 */
	private static function decodeOwnerText(int $userId): string
	{
		if (\App\User::isExists($userId)) {
			$html = \App\User::getUserModel($userId)->getName();
		} else {
			$html = \App\Language::translate('LBL_RECORD_NOT_FOUND');
		}
		return $html;
	}
}
