<?php

/**
 * Special function to display the last fails login.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Users_Bruteforce_Textparser extends \App\TextParser\Base
{
	/** @var string Class name */
	public $name = 'LBL_BRUTAL_FORCE';

	/** @var mixed Parser type */
	public $type = 'mail';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$html = \App\Language::translate('LBL_BLOCKED_IP', 'Settings::BruteForce') . ': ' . $this->displayIpAddress($this->textParser->getParam('ip'));
		$html .= '<hr><table border="1" cellspacing="0" style="width:100%"><tr style="font-weight: bold;"><td>' . \App\Language::translate('SINGLE_Users', 'Users') . '</td><td>' . \App\Language::translate('LBL_DATE') . '</td><td>' . \App\Language::translate('LBL_STATUS', 'Settings:BruteForce') . '</td><td>' . \App\Language::translate('LBL_BROWSERS', 'Settings:BruteForce') . '</td><td>' . \App\Language::translate('LBL_USER_AGENT', 'Settings:BruteForce') . '</td></tr>';
		$attemptsLogin = (new \App\Db\Query())->from('vtiger_loginhistory')
			->where([
				'and',
				['user_ip' => $this->textParser->getParam('ip')],
				['>=', 'login_time', $this->textParser->getParam('time')],
			])->orderBy(['login_time' => SORT_DESC])->all();
		foreach ($attemptsLogin as $data) {
			$time = \App\Fields\DateTime::formatToDisplay($data['login_time']);
			$status = \App\Language::translate($data['status'], 'Users');
			$html .= "<tr><td>{$data['user_name']}</td><td>{$time}</td><td>{$status}</td><td>{$data['browser']}</td><td>{$data['agent']}</td></tr>";
		}
		return $html . '</table>';
	}

	/**
	 * Function to display ip address.
	 *
	 * @param string $ip
	 */
	private function displayIpAddress($ip)
	{
		$ipAddress = explode('.', $ip);
		unset($ipAddress[\count($ipAddress) - 1]);
		return implode('.', $ipAddress) . '.xxx';
	}
}
