<?php

/**
 * Browsing history
 * @package YetiForce.BrowsingHistory
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Michał Lorencik <m.lorencik.com>
 */
class Vtiger_BrowsingHistory_Model extends Vtiger_Widget_Model
{

	/**
	 * History engine
	 * @param \App\Request $request
	 * @param string $title
	 * @return array
	 */
	public static function historyEngine(\App\Request $request, $title = '')
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();

		if ($request->has('clearBrowsingHistory')) {
			self::deleteHistory($userId);
		}

		$history = self::getHistory($userId);

		if (!$request->has('clearBrowsingHistory')) {
			self::saveHistory($userId, $title);
		}

		return $history;
	}

	/**
	 * Get browsing history
	 * @param int $userId
	 * @return array
	 */
	public static function getHistory($userId)
	{
		$results = (new \App\Db\Query())->from('u_yf_browsinghistory')
			->where(['id_user' => $userId])
			->orderBy('view_date desc')
			->limit(AppConfig::performance('BROWSING_HISTORY_VIEW_LIMIT'))
			->all();

		$today = false;
		$yesterday = false;
		$older = false;
		foreach ($results as $key => $value) {
			$results[$key]['view_date'] = DateTimeField::convertToUserTimeZone($value['view_date'])->format("Y-m-d H:i:s");
			if (strtotime($results[$key]['view_date']) >= DateTimeField::convertToUserTimeZone('today')->format("U")) {
				$results[$key]['hour'] = true;
				if (!$today) {
					$results[$key]['viewToday'] = true;
					$today = true;
				}
			} elseif (strtotime($results[$key]['view_date']) >= DateTimeField::convertToUserTimeZone('yesterday')->format("U")) {
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
	 * @param int $userId
	 * @param string $title
	 */
	public static function saveHistory($userId, $title)
	{
		$data = [
			'id_user' => $userId,
			'view_date' => date('Y-m-d H:i:s'),
			'page_title' => $title,
			'url' => $_SERVER['REQUEST_URI']
		];
		\App\Db::getInstance()->createCommand()
			->insert('u_yf_browsinghistory', $data)
			->execute();
	}

	/**
	 * Clear browsing history for user
	 * @param int $userId
	 */
	public static function deleteHistory($userId)
	{
		\App\Db::getInstance()->createCommand()
			->delete('u_yf_browsinghistory', ['id_user' => $userId])
			->execute();

		header("Location: " . $_SERVER['HTTP_REFERER']);
	}
}
