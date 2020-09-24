<?php
/**
 * Mail RBL file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail;

/**
 * Mail RBL class.
 */
class Rbl extends \App\Base
{
	/**
	 * Request statuses.
	 */
	public const REQUEST_STATUS = [
		0 => ['label' => 'LBL_FOR_VERIFICATION', 'icon' => 'fas fa-question'],
		1 => ['label' => 'LBL_ACCEPTED', 'icon' => 'fas fa-check text-success '],
		2 => ['label' => 'LBL_REJECTED', 'icon' => 'fas fa-times text-danger'],
		3 => ['label' => 'PLL_CANCELLED', 'icon' => 'fas fa-minus'],
	];
	/**
	 * List statuses.
	 */
	public const LIST_STATUS = [
		0 => ['label' => 'LBL_ACTIVE', 'icon' => 'fas fa-check text-success '],
		1 => ['label' => 'LBL_CANCELED', 'icon' => 'fas fa-times text-danger'],
	];
	/**
	 * List statuses.
	 */
	public const LIST_TYPES = [
		0 => ['label' => 'LBL_BLACK_LIST', 'icon' => 'fas fa-ban text-danger', 'color' => '#eaeaea'],
		1 => ['label' => 'LBL_WHITE_LIST', 'icon' => 'far fa-check-circle text-success', 'color' => '#E1FFE3'],
		2 => ['label' => 'LBL_PUBLIC_BLACK_LIST', 'icon' => 'fas fa-ban text-danger', 'color' => '#eaeaea`'],
		3 => ['label' => 'LBL_PUBLIC_WHITE_LIST', 'icon' => 'far fa-check-circle text-success', 'color' => '#E1FFE3'],
	];
	/**
	 * RLB black list type.
	 */
	public const LIST_TYPE_BLACK_LIST = 0;
	/**
	 * RLB white list type.
	 */
	public const LIST_TYPE_WHITE_LIST = 1;
	/**
	 * RLB public black list type.
	 */
	public const LIST_TYPE_PUBLIC_BLACK_LIST = 2;
	/**
	 * RLB public white list type.
	 */
	public const LIST_TYPE_PUBLIC_WHITE_LIST = 3;

	/**
	 * List statuses.
	 */
	public const SPF = [
		1 => ['label' => 'LBL_SPF_NONE', 'class' => 'badge-secondary', 'icon' => 'fas fa-question'],
		2 => ['label' => 'LBL_SPF_PASS', 'class' => 'badge-success', 'icon' => 'fas fa-check'],
		3 => ['label' => 'LBL_SPF_FAIL', 'class' => 'badge-danger', 'icon' => 'fas fa-times'],
	];
	/**
	 * Check result: None, Neutral, TempError, PermError.
	 *
	 * @var string
	 */
	public const SPF_NONE = 1;
	/**
	 * Check result: Pass (the SPF record stated that the IP address is authorized).
	 *
	 * @var string
	 */
	public const SPF_PASS = 2;
	/**
	 * Check result: Fail, SoftFail.
	 *
	 * @var string
	 */
	public const SPF_FAIL = 3;
	/**
	 * Message mail mime parser instance.
	 *
	 * @var \ZBateson\MailMimeParser\Message
	 */
	public $mailMimeParser;

	/**
	 * Function to get the instance of advanced permission record model.
	 *
	 * @param int $id
	 *
	 * @return \self instance, if exists
	 */
	public static function getRequestById(int $id)
	{
		$query = (new \App\Db\Query())->from('s_#__mail_rbl_request')->where(['id' => $id]);
		$row = $query->createCommand(\App\Db::getInstance('admin'))->queryOne();
		$instance = false;
		if (false !== $row) {
			$instance = new self();
			$instance->setData($row);
		}
		return $instance;
	}

	/**
	 * Create an empty object and initialize it with data.
	 *
	 * @param array $data
	 *
	 * @return self
	 */
	public static function getInstance(array $data): self
	{
		if (isset($data['header'])) {
			if (\is_array($data['header'])) {
				$data['header'] = implode(PHP_EOL . 'Received: ', $data['header']);
			}
			$data['header'] = 'Received: ' . $data['header'];
		}
		$instance = new self();
		$instance->setData($data);
		return $instance;
	}

	/**
	 * Get received header.
	 *
	 * @return array
	 */
	public function getReceived(): array
	{
		$rows = [];
		$this->mailMimeParser = $this->mailMimeParser ?? \ZBateson\MailMimeParser\Message::from($this->get('header'));
		foreach ($this->mailMimeParser->getAllHeadersByName('Received') as $key => $received) {
			$row = ['key' => $key];
			if ($received->getFromName()) {
				$row['from']['Name'] = $received->getFromName();
			}
			if ($received->getFromHostname()) {
				$row['from']['Hostname'] = $received->getFromHostname();
			}
			if ($received->getFromAddress()) {
				$row['from']['IP'] = $received->getFromAddress();
			}
			if ($received->getByName()) {
				$row['by']['Name'] = $received->getByName();
			}
			if ($received->getByHostname()) {
				$row['by']['Hostname'] = $received->getByHostname();
			}
			if ($received->getByAddress()) {
				$row['by']['IP'] = $received->getByAddress();
			}
			if ($received->getValueFor('with')) {
				$row['extra']['With'] = $received->getValueFor('with');
			}
			if ($received->getComments()) {
				$row['extra']['Comments'] = preg_replace('/\s+/', ' ', trim(implode(' | ', $received->getComments())));
			}
			$rows[] = $row;
		}
		return array_reverse($rows);
	}

	/**
	 * Get sender details.
	 *
	 * @return array
	 */
	public function getSender(): array
	{
		$first = $row = [];
		$this->mailMimeParser = $this->mailMimeParser ?? \ZBateson\MailMimeParser\Message::from($this->get('header'));
		foreach ($this->mailMimeParser->getAllHeadersByName('Received') as $key => $received) {
			if ($received->getFromName() && $received->getByName() && $received->getFromName() !== $received->getByName()) {
				$fromDomain = $this->getDomain($received->getFromName());
				$byDomain = $this->getDomain($received->getByName());
				if (!($fromIp = $received->getFromAddress())) {
					$fromIp = $this->getIp($received->getFromName());
				}
				if (!($byIp = $received->getByAddress())) {
					$byIp = $this->getIp($received->getByName());
				}
				if ($fromIp !== $byIp && ((!$fromDomain && !$byDomain) || $fromDomain !== $byDomain)) {
					$row['ip'] = $fromIp;
					$row['key'] = $key;
					$row['from'] = $received->getFromName();
					$row['by'] = $received->getByName();
					break;
				}
				if (empty($first)) {
					$first = [
						'ip' => $fromIp,
						'key' => $key,
						'from' => $received->getFromName(),
						'by' => $received->getByName(),
					];
				}
			}
			if (!empty($byIp)) {
				$row['ip'] = $byIp;
			} elseif ($received->getByAddress()) {
				$row['ip'] = $received->getByAddress();
			}
		}
		if (!isset($row['key'])) {
			if ($first) {
				$row = $first;
			} else {
				$row['key'] = false;
			}
		}
		return $row;
	}

	/**
	 * Get domain from URL.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function getDomain(string $url): string
	{
		if (']' === substr($url, -1) || '[' === substr($url, 0, 1)) {
			$url = rtrim(ltrim($url, '['), ']');
		}
		if (filter_var($url, FILTER_VALIDATE_IP)) {
			return $url;
		}
		$domains = explode('.', $url, substr_count($url, '.'));
		return end($domains);
	}

	/**
	 * Get mail ip address.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function getIp(string $url): string
	{
		if (']' === substr($url, -1) || '[' === substr($url, 0, 1)) {
			$url = rtrim(ltrim($url, '['), ']');
		}
		if (filter_var($url, FILTER_VALIDATE_IP)) {
			return $url;
		}
		return gethostbyname($url);
	}

	/**
	 * Get from.
	 *
	 * @return string
	 */
	public function getFrom(): string
	{
		$this->mailMimeParser = $this->mailMimeParser ?? \ZBateson\MailMimeParser\Message::from($this->get('header'));
		$from = $this->mailMimeParser->getHeader('from')->getEmail();
		if ($name = $this->mailMimeParser->getHeader('from')->getPersonName()) {
			$from = "$name <$from>";
		}
		return $from;
	}

	/**
	 * Check SPF (Sender Policy Framework) for Authorizing Use of Domains in Email.
	 *
	 * @return array
	 */
	public function checkSpf(): array
	{
		$this->mailMimeParser = $this->mailMimeParser ?? \ZBateson\MailMimeParser\Message::from($this->get('header'));
		$sender = $this->getSender();
		$status = self::SPF_NONE;
		if (isset($sender['ip'])) {
			$environment = new \SPFLib\Check\Environment($sender['ip'], $sender['from'] ?? '', $this->mailMimeParser->getHeader('from')->getEmail());
			foreach ([\SPFLib\Checker::FLAG_CHECK_MAILFROADDRESS, \SPFLib\Checker::FLAG_CHECK_HELODOMAIN] as $flag) {
				if (self::SPF_FAIL !== $status) {
					switch ((new \SPFLib\Checker())->check($environment, $flag)->getCode()) {
						case \SPFLib\Check\Result::CODE_PASS:
							$status = self::SPF_PASS;
							break;
						case \SPFLib\Check\Result::CODE_FAIL:
						case \SPFLib\Check\Result::CODE_SOFTFAIL:
							$status = self::SPF_FAIL;
							break;
					}
				}
			}
		}
		return ['status' => $status] + self::SPF[$status];
	}

	/**
	 * Get color by ips.
	 *
	 * @param array $ips
	 *
	 * @return array
	 */
	public static function getColorByIps(array $ips): array
	{
		$return = $find = [];
		foreach ($ips as $uid => $ip) {
			if (\App\Cache::has('MailRblIpColor', $ip)) {
				$return[$uid] = \App\Cache::get('MailRblIpColor', $ip);
			} else {
				$find[$ip][] = $uid;
			}
		}
		if ($find) {
			$list = [];
			$dataReader = (new \App\Db\Query())->select(['id', 'ip', 'status', 'source', 'type'])->from('s_#__mail_rbl_list')->where(['ip' => array_keys($find)])
				->createCommand(\App\Db::getInstance('admin'))->query();
			while ($row = $dataReader->read()) {
				$list[$row['ip']][] = $row;
			}
			foreach ($list as $ip => $rows) {
				$color = self::getColorByList($ip, $rows);
				foreach ($find[$ip] as $uid) {
					$return[$uid] = $color;
				}
			}
		}
		return $return;
	}

	/**
	 * Find ip in list.
	 *
	 * @param string $ip
	 *
	 * @return array
	 */
	public static function findIp(string $ip): array
	{
		if (\App\Cache::has('MailRblList', $ip)) {
			return \App\Cache::get('MailRblList', $ip);
		}
		$rows = (new \App\Db\Query())->from('s_#__mail_rbl_list')->where(['ip' => $ip])->orderBy(['type' => SORT_ASC])
			->all(\App\Db::getInstance('admin'));
		\App\Cache::save('MailRblList', $ip, $rows, \App\Cache::LONG);
		return $rows;
	}

	/**
	 * Get color by list.
	 *
	 * @param string $ip
	 * @param array  $rows
	 *
	 * @return string
	 */
	public static function getColorByList(string $ip, array $rows): string
	{
		if (\App\Cache::has('MailRblIpColor', $ip)) {
			return \App\Cache::get('MailRblIpColor', $ip);
		}
		$color = '';
		foreach ($rows as $row) {
			if (1 !== (int) $row['status']) {
				$color = self::LIST_TYPES[$row['type']]['color'];
				break;
			}
		}
		\App\Cache::save('MailRblIpColor', $ip, $color, \App\Cache::LONG);
		return $color;
	}
}
