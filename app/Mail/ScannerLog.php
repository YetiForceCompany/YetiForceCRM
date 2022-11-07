<?php
/**
 * Mail scanner file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Mail;

/**
 * Mail scanner class.
 */
class ScannerLog extends \App\Base
{
	/** @var string Logs table name */
	public const LOG_TABLE = 'vtiger_ossmails_logs';
	/** @var int Status complete */
	public const STATUS_COMPLETE = 0;
	/** @var int Status running */
	public const STATUS_RUNNING = 1;
	/** @var int Status manually stopped */
	public const STATUS_STOPPED = 2;
	/** @var int Status error */
	public const STATUS_ERROR = 3;

	/** @var int Scann log ID */
	private $scanId;

	public static function isScannRunning(): bool
	{
		return (new \App\Db\Query())->from(self::LOG_TABLE)->where(['status' => self::STATUS_RUNNING])->exists();
	}

	public function start()
	{
		if (!$this->scanId) {
			$db = \App\Db::getInstance();
			$userName = \App\User::getCurrentUserModel()->getDetail('user_name');

			$db->createCommand()->insert(self::LOG_TABLE, [
				'status' => self::STATUS_RUNNING,
				'start_time' => date('Y-m-d H:i:s'),
				'count' => 0,
				'user' => \PHP_SAPI . ($userName ? " - {$userName}" : '')
			])->execute();
			$this->scanId = $db->getLastInsertID('vtiger_ossmails_logs_id_seq');
		}

		return $this;
	}

	public function isRunning()
	{
		return (new \App\Db\Query())->from(self::LOG_TABLE)->where(['id' => $this->scanId, 'status' => self::STATUS_RUNNING])->exists();
	}

	public function updateCount(int $count)
	{
		\App\Db::getInstance()->createCommand()->update(self::LOG_TABLE, ['count' => $count], ['id' => $this->scanId])->execute();

		return $this;
	}

	public function close(int $status = self::STATUS_COMPLETE, string $message = '', string $action = '')
	{
		$data = [
			'end_time' => date('Y-m-d H:i:s'),
			'status' => $status,
			'action' => $action,
			'info' => \App\TextUtils::textTruncate($message, 100, true, true)
		];
		if (self::STATUS_COMPLETE !== $status) {
			$data['stop_user'] = \App\User::getCurrentUserModel()->getDetail('user_name');
		}

		\App\Db::getInstance()->createCommand()->update(self::LOG_TABLE, $data, ['id' => $this->scanId])->execute();
		$this->scanId = null;

		return $this;
	}
}
