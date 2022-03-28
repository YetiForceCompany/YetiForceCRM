<?php
/**
 * Profiling file records log messages in a profile table.
 *
 * @package Log
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Log;

use App\Db;
use yii\helpers\VarDumper;
use yii\log\Logger;
use yii\log\Target;

/**
 * Profiling class records log messages in a profile table.
 */
class Profiling extends Target
{
	public $db;

	/**
	 * @var string name of the DB table to store cache content. Defaults to "log"
	 */
	public $logTable = 'l_#__profile';

	/**
	 * Initializes the DbTarget component.
	 * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
	 *
	 * @throws InvalidConfigException if [[db]] is invalid
	 */
	public function init()
	{
		parent::init();
		$this->db = DB::getInstance('log');
	}

	/**
	 * Stores log messages to DB.
	 */
	public function export()
	{
		$timings = [];
		$stack = [];
		foreach ($this->messages as $i => $log) {
			[$token, $level, , $timestamp] = $log;
			$log[5] = $i;
			if (Logger::LEVEL_PROFILE_BEGIN == $level) {
				$stack[] = $log;
			} elseif (Logger::LEVEL_PROFILE_END == $level) {
				if (null !== ($last = array_pop($stack)) && $last[0] === $token) {
					$timings[$last[5]] = [
						'info' => $last[0],
						'category' => $last[2],
						'timestamp' => $last[3],
						'trace' => $last[4],
						'level' => \count($stack),
						'duration' => $timestamp - $last[3],
					];
				}
			}
		}
		$logID = (new \App\Db\Query())->from($this->db->quoteSql($this->logTable))->max('id', $this->db);
		++$logID;
		foreach ($timings as &$message) {
			$text = $message['info'];
			if (!\is_string($text)) {
				// exceptions may not be serializable if in the call stack somewhere is a Closure
				if ($text instanceof \Throwable || $text instanceof \Exception) {
					$text = (string) $text;
				} else {
					$text = VarDumper::export($text);
				}
			}
			$this->db->createCommand()->insert($this->db->quoteSql($this->logTable), [
				'id' => $logID,
				'info' => $text,
				'category' => $message['category'],
				'log_time' => $message['timestamp'],
				'trace' => $message['trace'],
				'level' => $message['level'],
				'duration' => $message['duration'],
			])->execute();
		}
	}
}
