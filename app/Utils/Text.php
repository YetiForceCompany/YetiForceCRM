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
 * Text class.
 */
class Text
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
	const PATH_EMOJI_JSON = 'vendor/ckeditor/ckeditor/plugins/emoji/emoji.json';

	/**
	 * Get processed text in display mode.
	 *
	 * @param string $text
	 * @param string $format
	 *
	 * @return string
	 */
	public static function getToDisplay(string $text, string $format = self::FORMAT_HTML): string
	{
		$arrayOfEmoji = static::getArrayOfEmoji();
		$textOut = \preg_replace_callback(
			'/\:[^\:|\s]+\:/',
			function (array $matches) use ($arrayOfEmoji) {
				return $arrayOfEmoji[$matches[0]] ?? $matches[0];
			},
			$text
		);
		return \preg_replace_callback(
			"/<a\s+[^>]*data-id=(?:\"|')(.)(\d+)(?:\"|')[^>]*>.*<\/a>/i",
			function (array $matches) use ($format) {
				return static::display($matches[0], $matches[1], $matches[2], $format);
			},
			$textOut
		);
	}

	/**
	 * Get to edit.
	 *
	 * @param string $text
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return string
	 */
	public static function getToEdit(string $text): string
	{
		$arrayOfEmoji = static::getArrayOfEmoji();
		return \preg_replace_callback(
			'/\:[^\:]+\:/',
			function (array $matches) use ($arrayOfEmoji) {
				return $arrayOfEmoji[$matches[0]] ?? $matches[0];
			},
			$text
		);
	}

	/**
	 * Process before writing to the database.
	 *
	 * @param string $text
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return string
	 */
	public static function emojiSave(string $text): string
	{
		$arrayOfEmoji = array_flip(static::getArrayOfEmoji());
		return str_replace(array_keys($arrayOfEmoji), $arrayOfEmoji, $text);
	}

	/**
	 * Get array of emoji.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	private static function getArrayOfEmoji(): array
	{
		if (\App\Cache::has('App\Utils\Text', 'arrayOfEmoji')) {
			$arrayOfEmoji = \App\Cache::get('App\Utils\Text', 'arrayOfEmoji');
		} elseif (\file_exists(static::PATH_EMOJI_JSON)) {
			$arrayOfEmoji = [];
			foreach (\App\Json::decode(\file_get_contents(static::PATH_EMOJI_JSON)) as $val) {
				$arrayOfEmoji[$val['id']] = $val['symbol'];
			}
			\App\Cache::save('App\Utils\Text', 'arrayOfEmoji', $arrayOfEmoji);
		} else {
			$arrayOfEmoji = [];
		}
		return $arrayOfEmoji;
	}

	/**
	 * Get processed text in display mode.
	 *
	 * @param string $baseText
	 * @param string $mod
	 * @param string $id
	 * @param string $format
	 *
	 * @return string
	 */
	private static function display(string $baseText, string $mod, string $id, string $format = self::FORMAT_HTML): string
	{
		$html = '';
		if ('#' === $mod) {
			switch ($format) {
				case static::FORMAT_HTML:
					$html = static::displayRecord($id);
					break;
				case static::FORMAT_TEXT:
					$html = static::displayRecordText($id);
					break;
			}
		} elseif ('@' === $mod) {
			switch ($format) {
				case static::FORMAT_HTML:
					$html = static::displayOwner($id);
					break;
				case static::FORMAT_TEXT:
					$html = static::displayOwnerText($id);
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
	private static function displayRecordText(int $recordId): string
	{
		if (\App\Record::isExists($recordId)) {
			$html = \Vtiger_Record_Model::getInstanceById($recordId)->getName();
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
	private static function displayRecord(int $recordId): string
	{
		if (!\App\Record::isExists($recordId)) {
			$html = \App\Language::translate('LBL_RECORD_NOT_FOUND');
		} elseif (($recordModel = \Vtiger_Record_Model::getInstanceById($recordId))->isViewable()) {
			$html = "<a href=\"{$recordModel->getDetailViewUrl()}\" class=\"js-popover-tooltip--record\">" .
				$recordModel->getDisplayName() . '</a>&nbsp;';
		} else {
			$html = $recordModel->getDisplayName();
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
	private static function displayOwner(int $userId): string
	{
		if (!\App\User::isExists($userId)) {
			$html = \App\Language::translate('LBL_RECORD_NOT_FOUND');
		} elseif (($userModel = \Vtiger_Record_Model::getInstanceById($userId, 'Users'))->isViewable()) {
			$html = "<a href=\"{$userModel->getDetailViewUrl()}\">{$userModel->getDisplayName()}</a>";
		} else {
			$html = $userModel->getDisplayName();
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
	private static function displayOwnerText(int $userId): string
	{
		if (\App\User::isExists($userId)) {
			$html = \Vtiger_Record_Model::getInstanceById($userId, 'Users')->getDisplayName();
		} else {
			$html = \App\Language::translate('LBL_RECORD_NOT_FOUND');
		}
		return $html;
	}
}
