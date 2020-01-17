<?php

/**
 * User login history.
 *
 * @package 	App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\TextParser;

/**
 * User login history class.
 */
class UserLoginHistory extends \App\TextParser\Base
{
	/** @var string Class name */
	public $name = 'LBL_REPORT_LOGIN_HISTORY';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$existLoginHistory = false;
		if (!empty($textParserParams = $this->textParser->getParam('textParserParams')) && isset($textParserParams['userId'])) {
			$userId = $textParserParams['userId'];
			$html = '<table><tr><td>' . \App\Language::translate('LBL_IP', 'Other:Reports') . '</td><td>' . \App\Language::translate('LBL_DATE') . '</td><td>' . \App\Language::translate('LBL_AGENT', 'Other:Reports') . '</td></tr>';
			$historyLogin = (new \App\Db\Query())->select(['user_ip', 'login_time', 'agent'])->from('vtiger_loginhistory')
				->where([
					'and',
					['userid' => $userId],
				]);
			if (isset(\App\Condition::DATE_OPERATORS[$this->params[0]])) {
				$dateRange = \DateTimeRange::getDateRangeByType($this->params[0]);
				$historyLogin->andWhere(['between', 'login_time', $dateRange[0].' 00:00:00', $dateRange[1].' 23:59:59']);
			}
			$historyLogin = $historyLogin->all();
			foreach ($historyLogin as $data) {
				$existLoginHistory = true;
				$html .= "<tr><td>{$data['user_ip']}</td><td>{$data['login_time']}</td><td>{$data['agent']}</td></tr>";
			}
			$html .= '</table>';
		}
		return $existLoginHistory ? $html : \App\Language::translate('LBL_NO_RECORDS', 'Other.Reports');
	}
}
