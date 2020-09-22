<?php
/**
 * MailRbl record model file.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * MailRbl record model class.
 */
class Settings_MailRbl_Record_Model extends App\Base
{
	/**
	 * Message mail mime parser instance.
	 *
	 * @var ZBateson\MailMimeParser\Message
	 */
	private $mailMimeParser;

	/**
	 * Function to get the instance of advanced permission record model.
	 *
	 * @param int $id
	 *
	 * @return \self instance, if exists
	 */
	public static function getRequestById($id)
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
					$fromIp = gethostbyname($fromDomain);
				}
				if (!($byIp = $received->getByAddress())) {
					$byIp = gethostbyname($byDomain);
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
}
