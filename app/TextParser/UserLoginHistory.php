<?php

/**
 * User login history.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
			$html = '<table><tr><td>' . \App\Language::translate('LBL_IP_ADDRESS') . '</td><td>' . \App\Language::translate('LBL_DATE') . '</td><td>' . \App\Language::translate('LBL_BROWSER') . '</td></tr>';
			$dataReader = (new \App\Db\Query())->select(['user_ip', 'login_time', 'browser'])->from('vtiger_loginhistory')
				->where([
					'and',
					['userid' => $userId],
				]);
			if (isset(\App\Condition::DATE_OPERATORS[$this->params[0]])) {
				$dateRange = \DateTimeRange::getDateRangeByType($this->params[0]);
				$dataReader->andWhere(['between', 'login_time', $dateRange[0] . ' 00:00:00', $dateRange[1] . ' 23:59:59']);
			}
			$dataReader = $dataReader->createCommand()->query();
			while ($row = $dataReader->read()) {
				$existLoginHistory = true;
				$html .= "<tr><td>{$row['user_ip']}</td><td>{$row['login_time']}</td><td>{$row['browser']}</td></tr>";
			}
			$html .= '</table>';
		}
		return $existLoginHistory ? $html : \App\Language::translate('LBL_NO_RECORDS');
	}
}
