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
		$message = \ZBateson\MailMimeParser\Message::from($this->get('header'));
		foreach ($message->getAllHeadersByName('Received') as $received) {
			$row = [];
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
				$row['extra']['Comments'] = implode(' | ', $received->getComments());
			}
			$rows[] = $row;
		}
		return array_reverse($rows);
	}
}
