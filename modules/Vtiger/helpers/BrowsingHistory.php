<?php

/**
 * Browsing history
 * @package YetiForce.Helpers
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Michał Lorencik <m.lorencik.com>
 */
class Vtiger_BrowsingHistory_Helper
{

	/**
	 * Get browsing history
	 * @return array
	 */
	public static function getHistory()
	{
		$userId = App\User::getCurrentUserId();
		$results = (new \App\Db\Query())->from('u_#__browsinghistory')
			->where(['userid' => $userId])
			->orderBy(['view_date' => SORT_DESC])
			->limit(AppConfig::performance('BROWSING_HISTORY_VIEW_LIMIT'))
			->all();

		$today = false;
		$yesterday = false;
		$older = false;
		$dateToday = DateTimeField::convertToUserTimeZone('today')->format('U');
		$dateYesterday = DateTimeField::convertToUserTimeZone('yesterday')->format('U');
		foreach ($results as $key => $value) {
			$results[$key]['view_date'] = DateTimeField::convertToUserTimeZone($value['view_date'])->format('Y-m-d H:i:s');
			if (strtotime($results[$key]['view_date']) >= $dateToday) {
				$results[$key]['hour'] = true;
				if (!$today) {
					$results[$key]['viewToday'] = true;
					$today = true;
				}
			} elseif (strtotime($results[$key]['view_date']) >= $dateYesterday) {
				$results[$key]['hour'] = true;
				if (!$yesterday) {
					$results[$key]['viewYesterday'] = true;
					$yesterday = true;
				}
			} else {
				$results[$key]['hour'] = false;
				if (!$older) {
					$results[$key]['viewOlder'] = true;
					$older = true;
				}
			}
		}

		return $results;
	}

	/**
	 * Save step in browsing history
	 * @param string $title
	 */
	public static function saveHistory($title)
	{
		$userId = App\User::getCurrentUserId();
		$data = [
			'userid' => $userId,
			'view_date' => date('Y-m-d H:i:s'),
			'page_title' => $title,
			'url' => $_SERVER['REQUEST_URI']
		];
		\App\Db::getInstance()->createCommand()
			->insert('u_#__browsinghistory', $data)
			->execute();
	}

	/**
	 * Clear browsing history for user
	 */
	public static function deleteHistory()
	{
		$userId = App\User::getCurrentUserId();
		\App\Db::getInstance()->createCommand()
			->delete('u_#__browsinghistory', ['userid' => $userId])
			->execute();
	}
}
