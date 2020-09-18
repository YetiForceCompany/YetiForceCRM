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
			if ($received->getFromName() || $received->getFromHostname()) {
				$rows[] = [
					'From name' => $received->getFromName(),
					'From hostname' => $received->getFromHostname(),
					'By name' => $received->getByName(),
					'By hostname' => $received->getByHostname(),
					'With' => $received->getValueFor('with'),
				];
			}
		}
		return array_reverse($rows);
	}
}
