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
	 *
	 * @var array
	 */
	public const REQUEST_STATUS = [
		0 => ['label' => 'LBL_FOR_VERIFICATION', 'icon' => 'fas fa-question'],
		1 => ['label' => 'LBL_ACCEPTED', 'icon' => 'fas fa-check text-success '],
		2 => ['label' => 'LBL_REJECTED', 'icon' => 'fas fa-times text-danger'],
		3 => ['label' => 'PLL_CANCELLED', 'icon' => 'fas fa-minus'],
	];
	/**
	 * List statuses.
	 *
	 * @var array
	 */
	public const LIST_STATUS = [
		0 => ['label' => 'LBL_ACTIVE', 'icon' => 'fas fa-check text-success '],
		1 => ['label' => 'LBL_CANCELED', 'icon' => 'fas fa-times text-danger'],
	];
	/**
	 * List statuses.
	 *
	 * @var array
	 */
	public const LIST_TYPES = [
		0 => ['label' => 'LBL_BLACK_LIST', 'icon' => 'fas fa-ban text-danger', 'color' => '#eaeaea'],
		1 => ['label' => 'LBL_WHITE_LIST', 'icon' => 'far fa-check-circle text-success', 'color' => '#E1FFE3'],
		2 => ['label' => 'LBL_PUBLIC_BLACK_LIST', 'icon' => 'fas fa-ban text-danger', 'color' => '#eaeaea'],
		3 => ['label' => 'LBL_PUBLIC_WHITE_LIST', 'icon' => 'far fa-check-circle text-success', 'color' => '#E1FFE3'],
	];
	/**
	 * RLB black list type.
	 *
	 * @var int
	 */
	public const LIST_TYPE_BLACK_LIST = 0;
	/**
	 * RLB white list type.
	 *
	 * @var int
	 */
	public const LIST_TYPE_WHITE_LIST = 1;
	/**
	 * RLB public black list type.
	 *
	 * @var int
	 */
	public const LIST_TYPE_PUBLIC_BLACK_LIST = 2;
	/**
	 * RLB public white list type.
	 *
	 * @var int
	 */
	public const LIST_TYPE_PUBLIC_WHITE_LIST = 3;

	/**
	 * SPF statuses.
	 *
	 * @var array
	 */
	public const SPF = [
		1 => ['label' => 'LBL_NONE', 'desc' => 'LBL_SPF_NONE_DESC', 'class' => 'badge-secondary', 'icon' => 'fas fa-question'],
		2 => ['label' => 'LBL_CORRECT', 'desc' => 'LBL_SPF_PASS_DESC', 'class' => 'badge-success', 'icon' => 'fas fa-check'],
		3 => ['label' => 'LBL_INCORRECT', 'desc' => 'LBL_SPF_FAIL_DESC', 'class' => 'badge-danger', 'icon' => 'fas fa-times'],
	];
	/**
	 * Check result: None, Neutral, TempError, PermError.
	 *
	 * @var int
	 */
	public const SPF_NONE = 1;
	/**
	 * Check result: Pass (the SPF record stated that the IP address is authorized).
	 *
	 * @var int
	 */
	public const SPF_PASS = 2;
	/**
	 * Check result: Fail, SoftFail.
	 *
	 * @var int
	 */
	public const SPF_FAIL = 3;
	/**
	 * DKIM statuses.
	 *
	 * @var array
	 */
	public const DKIM = [
		0 => ['label' => 'LBL_NONE', 'desc' => 'LBL_DKIM_NONE_DESC', 'class' => 'badge-secondary', 'icon' => 'fas fa-question'],
		1 => ['label' => 'LBL_CORRECT', 'desc' => 'LBL_DKIM_PASS_DESC', 'class' => 'badge-success', 'icon' => 'fas fa-check'],
		2 => ['label' => 'LBL_INCORRECT', 'desc' => 'LBL_DKIM_FAIL_DESC', 'class' => 'badge-danger', 'icon' => 'fas fa-times'],
	];
	/**
	 * DKIM header not found.
	 *
	 * @var int
	 */
	public const DKIM_NONE = 0;
	/**
	 * DKIM header verified correctly.
	 *
	 * @var int
	 */
	public const DKIM_PASS = 1;
	/**
	 * DKIM header verified incorrectly.
	 *
	 * @var int
	 */
	public const DKIM_FAIL = 2;
	/**
	 * DMARC statuses.
	 *
	 * @var array
	 */
	public const DMARC = [
		0 => ['label' => 'LBL_NONE', 'desc' => 'LBL_DMARC_NONE_DESC', 'class' => 'badge-secondary', 'icon' => 'fas fa-question'],
		1 => ['label' => 'LBL_CORRECT', 'desc' => 'LBL_DMARC_PASS_DESC', 'class' => 'badge-success', 'icon' => 'fas fa-check'],
		2 => ['label' => 'LBL_INCORRECT', 'desc' => 'LBL_DMARC_FAIL_DESC', 'class' => 'badge-danger', 'icon' => 'fas fa-times'],
	];
	/**
	 * DMARC header not found.
	 *
	 * @var int
	 */
	public const DMARC_NONE = 0;
	/**
	 * DMARC header verified correctly.
	 *
	 * @var int
	 */
	public const DMARC_PASS = 1;
	/**
	 * DMARC header verified incorrectly.
	 *
	 * @var int
	 */
	public const DMARC_FAIL = 2;
	/**
	 * Message mail mime parser instance.
	 *
	 * @var \ZBateson\MailMimeParser\Message
	 */
	public $mailMimeParser;
	/**
	 * Sender cache.
	 *
	 * @var array
	 */
	private $senderCache;

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
		$instance = new self();
		$instance->setData($data);
		$instance->mailMimeParser = \ZBateson\MailMimeParser\Message::from($instance->get('header'));
		return $instance;
	}

	/**
	 * Parsing the email body or headers.
	 *
	 * @return void
	 */
	public function parse(): void
	{
		$this->mailMimeParser = \ZBateson\MailMimeParser\Message::from($this->has('rawBody') ? $this->get('rawBody') : $this->get('header') . "\r\n\r\n");
	}

	/**
	 * Get received header.
	 *
	 * @return array
	 */
	public function getReceived(): array
	{
		$rows = [];
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
		if (isset($this->senderCache)) {
			return $this->senderCache;
		}
		$first = $row = [];
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
		return $this->senderCache = $row;
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
		return filter_var(gethostbyname($url), FILTER_VALIDATE_IP);
	}

	/**
	 * Get senders.
	 *
	 * @return string
	 */
	public function getSenders(): array
	{
		$senders = [
			'From' => $this->mailMimeParser->getHeaderValue('From')
		];
		if ($returnPath = $this->mailMimeParser->getHeaderValue('Return-Path')) {
			$senders['Return-Path'] = $returnPath;
		}
		if ($sender = $this->mailMimeParser->getHeaderValue('Sender')) {
			$senders['Sender'] = $sender;
		}
		if ($sender = $this->mailMimeParser->getHeaderValue('X-Sender')) {
			$senders['X-Sender'] = $sender;
		}
		return $senders;
	}

	/**
	 * Verify sender email address.
	 *
	 * @return array
	 */
	public function verifySender(): array
	{
		$from = $this->mailMimeParser->getHeader('from')->getEmail();
		$status = true;
		$info = '';
		if (($returnPathHeader = $this->mailMimeParser->getHeader('Return-Path')) && ($returnPath = $returnPathHeader->getEmail())) {
			$status = $from === $returnPath;
			if (!$status) {
				$info .= "From: $from <> Return-Path: $returnPath" . PHP_EOL;
			}
		}
		if ($status && ($senderHeader = $this->mailMimeParser->getHeader('Sender')) && ($sender = $senderHeader->getEmail())) {
			$status = $from === $sender;
			if (!$status) {
				$info .= "From: $from <> Sender: $sender" . PHP_EOL;
			}
		}
		return ['status' => $status, 'info' => $info];
	}

	/**
	 * Verify SPF (Sender Policy Framework) for Authorizing Use of Domains in Email.
	 *
	 * @see @see https://tools.ietf.org/html/rfc7208
	 *
	 * @return array
	 */
	public function verifySpf(): array
	{
		$sender = $this->getSender();
		$return = ['status' => self::SPF_NONE];
		if (isset($sender['ip'])) {
			try {
				$environment = new \SPFLib\Check\Environment($sender['ip'], $sender['from'] ?? '', $this->mailMimeParser->getHeader('Return-Path')->getEmail());
				foreach ([\SPFLib\Checker::FLAG_CHECK_MAILFROADDRESS, \SPFLib\Checker::FLAG_CHECK_HELODOMAIN] as $flag) {
					if (self::SPF_FAIL !== $return['status']) {
						switch ((new \SPFLib\Checker())->check($environment, $flag)->getCode()) {
							case \SPFLib\Check\Result::CODE_PASS:
								$return['status'] = self::SPF_PASS;
								break;
							case \SPFLib\Check\Result::CODE_FAIL:
							case \SPFLib\Check\Result::CODE_SOFTFAIL:
								$return['status'] = self::SPF_FAIL;
								break;
						}
						break;
					}
				}
				if ($email = $this->mailMimeParser->getHeader('from')->getEmail()) {
					$return['domain'] = explode('@', $email)[1];
				} else {
					$return['domain'] = $sender['from'] ?? '';
				}
			} catch (\Throwable $e) {
				\App\Log::warning($e->getMessage(), __NAMESPACE__);
			}
		}
		$this->verifySpf = $return['status'];
		return $return + self::SPF[$return['status']];
	}

	/**
	 * Verify DKIM (DomainKeys Identified Mail).
	 *
	 * @see https://tools.ietf.org/html/rfc4871
	 *
	 * @return array
	 */
	public function verifyDkim(): array
	{
		$content = $this->has('rawBody') ? $this->get('rawBody') : $this->get('header') . "\r\n\r\n";
		$status = self::DKIM_NONE;
		$logs = '';
		if ($this->mailMimeParser->getHeader('DKIM-Signature')) {
			try {
				$validate = (new \PHPMailer\DKIMValidator\Validator($content))->validate();
				$status = ((1 === \count($validate)) && (1 === \count($validate[0])) && ('SUCCESS' === $validate[0][0]['status'])) ? self::DKIM_PASS : self::DKIM_FAIL;
				foreach ($validate as $rows) {
					foreach ($rows as $row) {
						$logs .= "[{$row['status']}] {$row['reason']}\n";
					}
				}
			} catch (\Throwable $e) {
				\App\Log::warning($e->getMessage(), __NAMESPACE__);
			}
		}
		$this->verifyDkim = $status;
		return ['status' => $status, 'logs' => trim($logs)] + self::DKIM[$status];
	}

	/**
	 * Verify DMARC (Domain-based Message Authentication, Reporting and Conformance).
	 *
	 * @return array
	 */
	public function verifyDmarc(): array
	{
		$fromDomain = explode('@', $this->mailMimeParser->getHeader('from')->getEmail())[1];
		$status = self::DMARC_NONE;
		$dmarcRecord = $this->getDmarcRecord($fromDomain);
		if (!$dmarcRecord) {
			return ['status' => $status, 'logs' => \App\Language::translateArgs('LBL_NO_DMARC_DNS', 'Settings:MailRbl', $fromDomain)] + self::DMARC[$status];
		}
		$logs = '';
		if ($this->mailMimeParser->getHeader('DKIM-Signature')) {
			$verifyDmarcDkim = $this->verifyDmarcDkim($fromDomain, $dmarcRecord['adkim']);
			$status = $verifyDmarcDkim['status'] ? self::DMARC_PASS : self::DMARC_FAIL;
			if (!$status) {
				$logs = $verifyDmarcDkim['log'];
			}
		}
		if (self::DMARC_FAIL !== $status) {
			$verifyDmarcSpf = $this->verifyDmarcSpf($fromDomain, $dmarcRecord['aspf']);
			if (null === $verifyDmarcSpf['status']) {
				$logs = \App\Language::translate('LBL_NO_DMARC_FROM', 'Settings:MailRbl');
			} else {
				$status = $verifyDmarcSpf['status'] ? self::DMARC_PASS : self::DMARC_FAIL;
				if (!$verifyDmarcSpf['status']) {
					$logs = $verifyDmarcSpf['log'];
				}
			}
		}
		return ['status' => $status, 'logs' => trim($logs)] + self::DMARC[$status];
	}

	/**
	 * Get DMARC TXT record.
	 *
	 * @param string $domain
	 *
	 * @return array
	 */
	public function getDmarcRecord(string $domain): array
	{
		$dns = dns_get_record('_dmarc.' . $domain, DNS_TXT);
		if (!$dns) {
			return [];
		}
		$dkimParams = [];
		foreach ($dns as $key) {
			if (preg_match('/^v=dmarc(.*)/i', $key['txt'])) {
				$dkimParams = self::parseHeaderParams($key['txt']);
				break;
			}
		}
		if (empty($dkimParams['adkim'])) {
			$dkimParams['adkim'] = 'r';
		}
		if (empty($dkimParams['aspf'])) {
			$dkimParams['aspf'] = 'r';
		}
		return $dkimParams;
	}

	/**
	 * Verify DMARC ADKIM Tag.
	 *
	 * @param string $fromDomain
	 * @param string $adkim
	 *
	 * @return array
	 */
	private function verifyDmarcDkim(string $fromDomain, string $adkim): array
	{
		$dkimDomain = self::parseHeaderParams($this->mailMimeParser->getHeaderValue('DKIM-Signature'))['d'];
		$status = $fromDomain === $dkimDomain;
		if ($status || 's' === $adkim) {
			return  ['status' => $status, 'log' => ($status ? '' : "From: $fromDomain | DKIM domain: $dkimDomain")];
		}
		$status = (mb_strlen($fromDomain) - mb_strlen('.' . $dkimDomain)) === strpos($fromDomain, '.' . $dkimDomain) || (mb_strlen($dkimDomain) - mb_strlen('.' . $fromDomain)) === strpos($dkimDomain, '.' . $fromDomain);
		return  ['status' => $status, 'log' => ($status ? '' : "From: $fromDomain | DKIM domain: $dkimDomain")];
	}

	/**
	 * Verify DMARC ASPF Tag.
	 *
	 * @param string $fromDomain
	 * @param string $aspf
	 *
	 * @return array
	 */
	private function verifyDmarcSpf(string $fromDomain, string $aspf): array
	{
		$mailFrom = '';
		if ($returnPathHeader = $this->mailMimeParser->getHeader('Return-Path')) {
			$mailFrom = explode('@', $returnPathHeader->getEmail())[1];
		}
		if (!$mailFrom && !($mailFrom = $this->getSender()['from'] ?? '')) {
			return ['status' => null];
		}
		$status = $fromDomain === $mailFrom;
		if ($status || 's' === $aspf) {
			return  ['status' => $status, 'log' => ($status ? '' : "RFC5321.MailFrom domain: $mailFrom | RFC5322.From domain: $fromDomain")];
		}
		$logs = '';
		$status = (mb_strlen($mailFrom) - mb_strlen('.' . $fromDomain)) === strpos($mailFrom, '.' . $fromDomain);
		if (!$status) {
			$logs = "RFC5321.MailFrom domain: $mailFrom | RFC5322.From domain: $fromDomain \n";
			if ($this->mailMimeParser->getHeader('DKIM-Signature')) {
				$dkimDomain = self::parseHeaderParams($this->mailMimeParser->getHeaderValue('DKIM-Signature'))['d'];
				$status = (mb_strlen($dkimDomain) - mb_strlen('.' . $fromDomain)) === strpos($dkimDomain, '.' . $fromDomain);
				if (!$status) {
					$logs .= "DKIM domain: $dkimDomain | RFC5322.From domain: $fromDomain";
				}
			}
		}
		return  ['status' => $status, 'log' => trim($logs)];
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

	/**
	 * Parse header params.
	 *
	 * @param string $string
	 *
	 * @return array
	 */
	public static function parseHeaderParams(string $string): array
	{
		$params = [];
		foreach (explode(';', rtrim(preg_replace('/\s+/', '', $string), ';')) as $param) {
			[$tagName, $tagValue] = explode('=', trim($param), 2);
			if ('' !== $tagName) {
				$params[$tagName] = $tagValue;
			}
		}
		return $params;
	}
}
