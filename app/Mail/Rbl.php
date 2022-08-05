<?php
/**
 * Mail RBL file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		1 => ['label' => 'LBL_ACCEPTED', 'icon' => 'fas fa-check text-success'],
		2 => ['label' => 'LBL_REJECTED', 'icon' => 'fas fa-times text-danger'],
		3 => ['label' => 'PLL_CANCELLED', 'icon' => 'fas fa-minus'],
		4 => ['label' => 'LBL_REPORTED', 'icon' => 'fas fa-paper-plane text-primary'],
	];
	/**
	 * List statuses.
	 *
	 * @var array
	 */
	public const LIST_STATUS = [
		0 => ['label' => 'LBL_ACTIVE', 'icon' => 'fas fa-check text-success'],
		1 => ['label' => 'LBL_CANCELED', 'icon' => 'fas fa-times text-danger'],
	];
	/**
	 * List statuses.
	 *
	 * @var array
	 */
	public const LIST_TYPES = [
		0 => ['label' => 'LBL_BLACK_LIST', 'icon' => 'fas fa-ban text-danger', 'alertColor' => '#ff555233', 'listColor' => '#ff555233'],
		1 => ['label' => 'LBL_WHITE_LIST', 'icon' => 'far fa-check-circle text-success', 'alertColor' => '#E1FFE3', 'listColor' => '#fff'],
		2 => ['label' => 'LBL_PUBLIC_BLACK_LIST', 'icon' => 'fas fa-ban text-danger', 'alertColor' => '#eaeaea', 'listColor' => '#ff555233'],
		3 => ['label' => 'LBL_PUBLIC_WHITE_LIST', 'icon' => 'far fa-check-circle text-success', 'alertColor' => '#E1FFE3', 'listColor' => '#fff'],
	];
	/**
	 * List categories.
	 *
	 * @var array
	 */
	public const LIST_CATEGORIES = [
		'Black' => [
			'[SPAM] Single unwanted message' => 'LBL_SPAM_SINGLE_UNWANTED_MESSAGE',
			'[SPAM] Mass unwanted message' => 'LBL_SPAM_MASS_UNWANTED_MESSAGE',
			'[SPAM] Sending an unsolicited message repeatedly' => 'LBL_SPAM_SENDING_UNSOLICITED_MESSAGE_REPEATEDLY',
			'[Fraud] Money scam' => 'LBL_FRAUD_MONEY_SCAM',
			'[Fraud] Phishing' => 'LBL_FRAUD_PHISHING',
			'[Fraud] An attempt to persuade people to buy a product or service' => 'LBL_FRAUD_ATTEMPT_TO_PERSUADE_PEOPLE_TO_BUY',
			'[Security] An attempt to impersonate another person' => 'LBL_SECURITY_ATTEMPT_TO_IMPERSONATE_ANOTHER_PERSON',
			'[Security] An attempt to persuade the recipient to open a resource from outside the organization' => 'LBL_SECURITY_ATTEMPT_TO_PERSUADE_FROM_ORGANIZATION',
			'[Security] An attempt to persuade the recipient to open a resource inside the organization' => 'LBL_SECURITY_ATTEMPT_TO_PERSUADE_INSIDE_ORGANIZATION',
			'[Security] Infrastructure and application scanning' => 'LBL_SECURITY_INFRASTRUCTURE_AND_APPLICATION_SCANNING',
			'[Security] Attack on infrastructure or application' => 'LBL_SECURITY_ATTACK_INFRASTRUCTURE_OR_APPLICATION',
			'[Security] Overloading infrastructure or application' => 'LBL_SECURITY_OVERLOADING_INFRASTRUCTURE_OR_APPLICATION',
			'[Other] The message contains inappropriate words' => 'LBL_OTHER_MESSAGE_CONTAINS_INAPPROPRIATE_WORDS',
			'[Other] The message contains inappropriate materials' => 'LBL_OTHER_MESSAGE_CONTAINS_INAPPROPRIATE_MATERIALS',
			'[Other] Malicious message' => 'LBL_OTHER_MALICIOUS_MESSAGE',
		],
		'White' => [
			'[Whitelist] Trusted sender' => 'LBL_TRUSTED_SENDER',
		],
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
	 * SPF cache.
	 *
	 * @var array
	 */
	private $spfCache;
	/**
	 * DKIM cache.
	 *
	 * @var array
	 */
	private $dkimCache;

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
		$instance->mailMimeParser = \ZBateson\MailMimeParser\Message::from($instance->get('header'), false);
		return $instance;
	}

	/**
	 * Parsing the email body or headers.
	 *
	 * @return void
	 */
	public function parse(): void
	{
		$this->mailMimeParser = \ZBateson\MailMimeParser\Message::from($this->has('rawBody') ? $this->get('rawBody') : $this->get('header') . "\r\n\r\n", false);
	}

	/**
	 * Get email from header by name.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	protected function getHeaderEmail(string $name): string
	{
		if ($header = $this->mailMimeParser->getHeader($name)) {
			return $header->getEmail() ?: '';
		}
		return '';
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
			$row = ['key' => $key, 'fromIP' => $received->getFromAddress() ?? ''];
			if ($received->getFromName()) {
				$row['fromName'] = $received->getFromName();
			}
			if ($received->getFromHostname()) {
				$row['fromName'] .= PHP_EOL . '(' . $received->getFromHostname() . ')';
			}
			if ($received->getByName()) {
				$row['byName'] = $received->getByName();
			}
			if ($received->getByHostname()) {
				$row['byName'] .= PHP_EOL . '(' . $received->getByHostname() . ')';
			}
			if ($received->getByAddress()) {
				$row['byIP'] = $received->getByAddress();
			}
			if ($received->getValueFor('with')) {
				$row['extraWith'] = $received->getValueFor('with');
			}
			if ($received->getComments()) {
				$row['extraComments'] = preg_replace('/\s+/', ' ', trim(implode(' | ', $received->getComments())));
			}
			if ($received->getDateTime()) {
				$row['dateTime'] = $received->getDateTime()->format('Y-m-d H:i:s');
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
					if (!($fromIp = $this->findIpByName($received, 'from'))) {
						$fromIp = $this->getIpByName($received->getFromName(), $received->getFromHostname());
					}
				}
				if (!($byIp = $received->getByAddress())) {
					if (!($byIp = $this->findIpByName($received, 'by'))) {
						$byIp = $this->getIpByName($received->getByName(), $received->getByHostname());
					}
				}
				if ($fromIp && $byIp && $fromIp !== $byIp && ((!$fromDomain && !$byDomain) || $fromDomain !== $byDomain)) {
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
	 * Find mail ip address.
	 *
	 * @param \ZBateson\MailMimeParser\Header\ReceivedHeader $received
	 * @param string                                         $type
	 *
	 * @return string
	 */
	public function findIpByName(\ZBateson\MailMimeParser\Header\ReceivedHeader $received, string $type): string
	{
		$value = $received->getValueFor($type);
		$pattern = '~\[(IPv[64])?([a-f\d\.\:]+)\]~i';
		if (preg_match($pattern, $value, $matches)) {
			if (!empty($matches[2])) {
				return $matches[2];
			}
		}
		$lastReceivedPart = null;
		foreach ($received->getParts() as $part) {
			if ($part instanceof \ZBateson\MailMimeParser\Header\Part\ReceivedPart) {
				$lastReceivedPart = $part->getName();
			} elseif ($part instanceof \ZBateson\MailMimeParser\Header\Part\CommentPart) {
				if ($lastReceivedPart === $type) {
					if (preg_match($pattern, $part->getComment(), $matches)) {
						if (!empty($matches[2])) {
							return $matches[2];
						}
					}
				}
			}
		}
		return '';
	}

	/**
	 * Get mail ip address by hostname or ehloName.
	 *
	 * @param string  $fromName
	 * @param ?string $hostName
	 *
	 * @return string
	 */
	public function getIpByName(string $fromName, ?string $hostName = null): string
	{
		if (']' === substr($fromName, -1) || '[' === substr($fromName, 0, 1)) {
			$fromName = rtrim(ltrim($fromName, '['), ']');
		}
		if (filter_var($fromName, FILTER_VALIDATE_IP)) {
			return $fromName;
		}
		if (0 === stripos($hostName, 'helo=')) {
			$hostName = substr($hostName, 5);
			if ($ip = \App\RequestUtil::getIpByName($hostName)) {
				return $ip;
			}
		}
		if ($ip = \App\RequestUtil::getIpByName($fromName)) {
			return $ip;
		}
		return '';
	}

	/**
	 * Get senders.
	 *
	 * @return string
	 */
	public function getSenders(): array
	{
		$senders = [
			'From' => $this->mailMimeParser->getHeaderValue('From'),
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
		$from = $this->getHeaderEmail('from');
		if (!$from) {
			return ['status' => true, 'info' => ''];
		}
		$status = true;
		$info = '';
		if ($returnPath = $this->getHeaderEmail('Return-Path')) {
			if (0 === stripos($returnPath, 'SRS') && ($separator = substr($returnPath, 4, 1)) !== '') {
				$returnPathSrs = '';
				$parts = explode($separator, $returnPath);
				$mail = explode('@', array_pop($parts));
				if (isset($mail[1])) {
					$last = array_pop($parts);
					$returnPathSrs = "{$mail[0]}@{$last}";
				}
				$status = $from === $returnPathSrs;
			} else {
				$status = $from === $returnPath;
			}
			if (!$status) {
				$info .= "From: {$from} <> Return-Path: {$returnPath}" . PHP_EOL;
			}
		}
		if ($status && ($sender = $this->getHeaderEmail('Sender'))) {
			$status = $from === $sender;
			if (!$status) {
				$info .= "From: {$from} <> Sender: {$sender}" . PHP_EOL;
			}
		}
		return ['status' => $status, 'info' => $info];
	}

	/**
	 * Verify SPF (Sender Policy Framework) for Authorizing Use of Domains in Email.
	 *
	 * @see https://tools.ietf.org/html/rfc7208
	 *
	 * @return array
	 */
	public function verifySpf(): array
	{
		if (isset($this->spfCache)) {
			return $this->spfCache;
		}
		$returnPath = $this->getHeaderEmail('Return-Path');
		$sender = $this->getSender();
		$return = ['status' => self::SPF_NONE];
		if ($email = $this->getHeaderEmail('from')) {
			$return['domain'] = explode('@', $email)[1];
		}
		if (isset($sender['ip'])) {
			$cacheKey = "{$sender['ip']}-{$returnPath}";
			if (\App\Cache::has('RBL:verifySpf', $cacheKey)) {
				$status = \App\Cache::get('RBL:verifySpf', $cacheKey);
			} else {
				$status = null;
				try {
					$environment = new \SPFLib\Check\Environment($sender['ip'], '', $returnPath);
					switch ((new \SPFLib\Checker())->check($environment, \SPFLib\Checker::FLAG_CHECK_MAILFROADDRESS)->getCode()) {
						case \SPFLib\Check\Result::CODE_PASS:
							$status = self::SPF_PASS;
							break;
						case \SPFLib\Check\Result::CODE_FAIL:
						case \SPFLib\Check\Result::CODE_SOFTFAIL:
							$status = self::SPF_FAIL;
							break;
					}
				} catch (\Throwable $e) {
					\App\Log::warning($e->getMessage(), __NAMESPACE__);
				}
				\App\Cache::save('RBL:verifySpf', $cacheKey, $status, \App\Cache::LONG);
			}
			if (isset($status)) {
				$return['status'] = $status;
			}
		}
		return $this->spfCache = $return + self::SPF[$return['status']];
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
		if (isset($this->dkimCache)) {
			return $this->dkimCache;
		}
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
		return $this->dkimCache = \App\Utils::merge(['status' => $status, 'logs' => trim($logs)], self::DKIM[$status]);
	}

	/**
	 * Verify DMARC (Domain-based Message Authentication, Reporting and Conformance).
	 *
	 * @return array
	 */
	public function verifyDmarc(): array
	{
		$fromDomain = explode('@', $this->getHeaderEmail('from'))[1] ?? '';
		$status = self::DMARC_NONE;
		if (empty($fromDomain) || !($dmarcRecord = $this->getDmarcRecord($fromDomain))) {
			return ['status' => $status, 'logs' => \App\Language::translateArgs('LBL_NO_DMARC_DNS', 'Settings:MailRbl', $fromDomain)] + self::DMARC[$status];
		}
		$logs = '';
		if ($this->mailMimeParser->getHeader('DKIM-Signature')) {
			$verifyDmarcDkim = $this->verifyDmarcDkim($fromDomain, $dmarcRecord['adkim']);
			$status = $verifyDmarcDkim['status'] && self::DKIM_PASS === $this->verifyDkim()['status'] ? self::DMARC_PASS : self::DMARC_FAIL;
			if (!$status) {
				$logs = $verifyDmarcDkim['log'];
			}
		}
		if (self::DMARC_FAIL !== $status) {
			$verifyDmarcSpf = $this->verifyDmarcSpf($fromDomain, $dmarcRecord['aspf']);
			if (null === $verifyDmarcSpf['status']) {
				$logs = \App\Language::translate('LBL_NO_DMARC_FROM', 'Settings:MailRbl');
			} else {
				$status = $verifyDmarcSpf['status'] && self::SPF_PASS === $this->verifySpf()['status'] ? self::DMARC_PASS : self::DMARC_FAIL;
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
		if (\App\Cache::has('RBL:getDmarcRecord', $domain)) {
			return \App\Cache::get('RBL:getDmarcRecord', $domain);
		}
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
		\App\Cache::save('RBL:getDmarcRecord', $domain, $dkimParams, \App\Cache::LONG);
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
			return ['status' => $status, 'log' => ($status ? '' : "From: $fromDomain | DKIM domain: $dkimDomain")];
		}
		$status = (mb_strlen($fromDomain) - mb_strlen('.' . $dkimDomain)) === strpos($fromDomain, '.' . $dkimDomain) || (mb_strlen($dkimDomain) - mb_strlen('.' . $fromDomain)) === strpos($dkimDomain, '.' . $fromDomain);
		return ['status' => $status, 'log' => ($status ? '' : "From: $fromDomain | DKIM domain: $dkimDomain")];
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
		if ($returnPath = $this->getHeaderEmail('Return-Path')) {
			$mailFrom = explode('@', $returnPath)[1];
		}
		if (!$mailFrom && !($mailFrom = $this->getSender()['from'] ?? '')) {
			return ['status' => null];
		}
		$status = $fromDomain === $mailFrom;
		if ($status || 's' === $aspf) {
			return ['status' => $status, 'log' => ($status ? '' : "RFC5321.MailFrom domain: $mailFrom | RFC5322.From domain: $fromDomain")];
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
		return ['status' => $status, 'log' => trim($logs)];
	}

	/**
	 * Update list by request id.
	 *
	 * @param int $record
	 *
	 * @return int
	 */
	public function updateList(int $record): int
	{
		$sender = $this->getSender();
		$return = 0;
		if (!empty($sender['ip'])) {
			$dbCommand = \App\Db::getInstance('admin')->createCommand();
			$id = false;
			if ($rows = self::findIp($sender['ip'])) {
				foreach ($rows as $row) {
					if ((self::LIST_TYPE_BLACK_LIST !== (int) $row['type']) && (self::LIST_TYPE_PUBLIC_BLACK_LIST !== (int) $row['type'])) {
						$id = $row['id'];
						break;
					}
				}
			}
			if ($id) {
				$dbCommand->update('s_#__mail_rbl_list', [
					'status' => 0,
					'type' => $this->get('type'),
					'request' => $record,
				], ['id' => $id])->execute();
			} else {
				$dbCommand->insert('s_#__mail_rbl_list', [
					'ip' => $sender['ip'],
					'status' => 0,
					'type' => $this->get('type'),
					'request' => $record,
					'source' => '',
				])->execute();
			}
			\App\Cache::delete('MailRblIpColor', $sender['ip']);
			\App\Cache::delete('MailRblList', $sender['ip']);
			$return = 2;
			if (\Config\Components\Mail::$rcListSendReportAutomatically ?? false) {
				self::sendReport(['id' => $record]);
				$return = 3;
			}
		}
		return $return;
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
	 * @param bool   $onlyActive
	 *
	 * @return array
	 */
	public static function findIp(string $ip, $onlyActive = false): array
	{
		$cacheName = "$ip|$onlyActive";
		if (\App\Cache::has('MailRblList', $cacheName)) {
			return \App\Cache::get('MailRblList', $cacheName);
		}
		$query = (new \App\Db\Query())->from('s_#__mail_rbl_list')->where(['ip' => $ip])->orderBy(['type' => SORT_ASC]);
		if ($onlyActive) {
			$query->andWhere(['status' => 0]);
		}
		$rows = $query->all(\App\Db::getInstance('admin'));
		\App\Cache::save('MailRblList', $cacheName, $rows, \App\Cache::LONG);
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
				$color = self::LIST_TYPES[$row['type']]['listColor'];
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

	/**
	 * Add report.
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	public static function addReport(array $data): string
	{
		$status = 0;
		if (\Config\Components\Mail::$rcListAcceptAutomatically ?? false) {
			$status = 1;
		}
		$db = \App\Db::getInstance('admin');
		$dbCommand = $db->createCommand();
		$dbCommand->insert('s_#__mail_rbl_request', [
			'status' => $status,
			'datetime' => date('Y-m-d H:i:s'),
			'user' => \App\User::getCurrentUserId(),
			'type' => $data['type'],
			'header' => $data['header'],
			'body' => $data['body'] ?? null,
		])->execute();
		$record = $db->getLastInsertID();
		$return = 'LBL_RC_ERROR_RBL_REPORT';
		if ($record) {
			$return = 'LBL_RC_ADDED_RBL_REPORT_LOCAL';
			if ($status) {
				$rblRecord = self::getRequestById($record);
				$rblRecord->parse();
				$sender = $rblRecord->getSender();
				if (empty($sender['ip'])) {
					$dbCommand->update('s_#__mail_rbl_request', ['status' => 0], ['id' => $record])->execute();
					$return = 'LBL_NO_IP_ADDRESS';
				} else {
					$blacklist = 0 == $rblRecord->get('type');
					$skipUpdate = false;
					if ($rows = self::findIp($sender['ip'])) {
						foreach ($rows as $row) {
							if (0 == $row['status']) {
								if ($blacklist && (self::LIST_TYPE_WHITE_LIST == $row['type'] || self::LIST_TYPE_PUBLIC_WHITE_LIST == $row['type'])) {
									$return = 'LBL_RC_ADDED_RBL_REPORT_IP_WHITE';
									$skipUpdate = true;
									break;
								}
								if (!$blacklist && (self::LIST_TYPE_WHITE_LIST == $row['type'] || self::LIST_TYPE_PUBLIC_WHITE_LIST == $row['type'])) {
									$return = 'LBL_RC_ADDED_RBL_REPORT_IP_BLACK';
									$skipUpdate = true;
									break;
								}
							}
						}
					}
					if ($skipUpdate) {
						$dbCommand->update('s_#__mail_rbl_request', ['status' => 0], ['id' => $record])->execute();
					} elseif (3 === $rblRecord->updateList($record)) {
						$return = 'LBL_RC_ADDED_RBL_REPORT_PUBLIC';
					}
				}
			}
		}
		return $return;
	}

	/**
	 * Send report.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function sendReport(array $data): array
	{
		if (!\App\RequestUtil::isNetConnection()) {
			return ['status' => false, 'message' => \App\Language::translate('ERR_NO_INTERNET_CONNECTION', 'Other:Exceptions')];
		}
		$id = $data['id'];
		unset($data['id']);
		$recordModel = self::getRequestById($id);
		$recordModel->parse();
		$data['type'] = $recordModel->get('type') ? 'White' : 'Black';
		if (empty($data['category'])) {
			$data['category'] = '[SPAM] Single unwanted message';
		}
		$url = 'https://soc.yetiforce.com/api/Application';
		\App\Log::beginProfile("POST|Rbl::sendReport|{$url}", __NAMESPACE__);
		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->post($url, [
			'http_errors' => false,
			'headers' => [
				'crm-ik' => \App\YetiForce\Register::getInstanceKey(),
			],
			'json' => array_merge($data, [
				'ik' => \App\YetiForce\Register::getInstanceKey(),
				'ip' => $recordModel->getSender()['ip'] ?? '-',
				'header' => $recordModel->get('header'),
				'body' => $recordModel->get('body'),
			]),
		]);
		\App\Log::endProfile("POST|Rbl::sendReport|{$url}", __NAMESPACE__);
		$body = \App\Json::decode($response->getBody()->getContents());
		if (200 == $response->getStatusCode() && 'ok' === $body['result']) {
			\App\Db::getInstance('admin')->createCommand()->update('s_#__mail_rbl_request', [
				'status' => 4,
			], ['id' => $id])->execute();
			return ['status' => true];
		}
		\App\Log::warning($response->getReasonPhrase() . ' | ' . ($body['error']['message'] ?? $body['result']), __METHOD__);
		return ['status' => false, 'message' => ($body['error']['message'] ?? $body['result'])];
	}

	/**
	 * Get IP list from public RBL.
	 *
	 * @param int $type
	 *
	 * @return array
	 */
	public static function getPublicList(int $type): array
	{
		if (!\App\RequestUtil::isNetConnection()) {
			\App\Log::warning('ERR_NO_INTERNET_CONNECTION', __METHOD__);
			return [];
		}
		$url = 'https://soc.yetiforce.com/list/' . (self::LIST_TYPE_PUBLIC_BLACK_LIST === $type ? 'black' : 'white');
		\App\Log::beginProfile("POST|Rbl::sendReport|{$url}", __NAMESPACE__);
		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->get($url, [
			'http_errors' => false,
			'headers' => [
				'crm-ik' => \App\YetiForce\Register::getInstanceKey(),
			],
		]);
		\App\Log::endProfile("POST|Rbl::sendReport|{$url}", __NAMESPACE__);
		$list = [];
		if (200 === $response->getStatusCode()) {
			$list = \App\Json::decode($response->getBody()->getContents()) ?? [];
		} else {
			$body = \App\Json::decode($response->getBody()->getContents());
			\App\Log::warning($response->getReasonPhrase() . ' | ' . $body['error']['message'], __METHOD__);
		}
		return $list;
	}

	/**
	 * Public list synchronization.
	 *
	 * @param int $type
	 *
	 * @return void
	 */
	public static function sync(int $type): void
	{
		if (!\App\RequestUtil::isNetConnection()) {
			\App\Log::warning('ERR_NO_INTERNET_CONNECTION', __METHOD__);
			return;
		}
		$public = self::getPublicList($type);
		$publicKeys = array_keys($public);
		$db = \App\Db::getInstance('admin');
		$dbCommand = $db->createCommand();
		$query = (new \App\Db\Query())->select(['ip', 'source', 'id'])->from('s_#__mail_rbl_list')->where(['type' => $type]);
		$rows = $query->createCommand($db)->queryAllByGroup(1);
		$keys = array_keys($rows);
		foreach (array_chunk(array_diff($publicKeys, $keys), 50, true) as $chunk) {
			$insertData = [];
			foreach ($chunk as $ip) {
				$insertData[] = [$ip, 0, $type, $public[$ip]['source'], $public[$ip]['comment']];
			}
			$dbCommand->batchInsert('s_#__mail_rbl_list', ['ip', 'status', 'type', 'source', 'comment'], $insertData)->execute();
		}
		foreach (array_diff($keys, $publicKeys) as $ip) {
			$dbCommand->delete('s_#__mail_rbl_list', ['id' => $rows[$ip]['id']])->execute();
		}
	}
}
