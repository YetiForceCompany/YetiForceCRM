<?php

/**
 * Issue Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Github_Issues_Model
{
	private $valueMap;
	public static $totalCount;

	public function get($key)
	{
		return $this->valueMap->$key;
	}

	public static function getInstanceFromArray($issueArray)
	{
		$issueModel = new self();
		$issueModel->valueMap = $issueArray;

		return $issueModel;
	}

	/**
	 * Return issue reporting rules url.
	 *
	 * @return string
	 */
	public static function getIssueReportRulesUrl()
	{
		if (\App\Language::getShortLanguageName() === 'pl') {
			$url = 'https://yetiforce.com/pl/baza-wiedzy/dokumentacja/dokumentacja-wdrozeniowa/item/jak-zglaszac-bledy';
		} else {
			$url = 'https://yetiforce.com/en/knowledge-base/documentation/implementer-documentation/item/how-to-report-bugs';
		}
		return "<a href='{$url}' target='_blank' rel='noreferrer noopener'>link</a>";
	}

	/**
	 * Format system errors for GitHub issue creation.
	 *
	 * @param array $errors
	 * @param bool  $nameOnly
	 *
	 * @return string
	 */
	public static function formatErrorsForIssue($errors, $nameOnly = false): string
	{
		$return = '';
		foreach ($errors as $name => $config) {
			$return .= '<br />';
			if ($nameOnly) {
				$return .= $name;
			} else {
				$return .= "{$name}: " . \App\Language::translate($config['www'] ?? '-') . ' | ' . \App\Language::translate($config['cron'] ?? '-');
			}
		}
		return $return;
	}
}
