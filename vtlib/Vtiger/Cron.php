<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */
namespace vtlib;

/**
 * Provides API to work with Cron tasks
 * @package vtlib
 */
class Cron
{

	protected static $cronAction = false;
	protected static $baseTable = 'vtiger_cron_task';
	protected static $schemaInitialized = false;
	protected static $instanceCache = [];
	static $STATUS_DISABLED = 0;
	static $STATUS_ENABLED = 1;
	static $STATUS_RUNNING = 2;
	static $STATUS_COMPLETED = 3;
	protected $data;

	/**
	 * Constructor
	 */
	protected function __construct($values)
	{
		$this->data = $values;
		self::$instanceCache[$this->getName()] = $this;
	}

	/**
	 * set the value to the data
	 * @param type $value,$key
	 */
	public function set($key, $value)
	{
		$this->data[$key] = $value;
		return $this;
	}

	/**
	 * Get id reference of this instance.
	 */
	public function getId()
	{
		return $this->data['id'];
	}

	/**
	 * Get name of this task instance.
	 */
	public function getName()
	{
		return decode_html($this->data['name']);
	}

	/**
	 * Get the frequency set.
	 */
	public function getFrequency()
	{
		return intval($this->data['frequency']);
	}

	/**
	 * Get the status
	 */
	public function getStatus()
	{
		return intval($this->data['status']);
	}

	/**
	 * Get the timestamp lastrun started.
	 */
	public function getLastStart()
	{
		return intval($this->data['laststart']);
	}

	/**
	 * Get the timestamp lastrun ended.
	 */
	public function getLastEnd()
	{
		return intval($this->data['lastend']);
	}

	/**
	 * Get the next sequence.
	 */
	public static function nextSequence()
	{
		return \App\Db::getInstance()->getUniqueID(self::$baseTable, 'sequence', false);
	}

	/**
	 * Get the user datetimefeild
	 */
	public function getLastEndDateTime()
	{
		if ($this->data['lastend'] != NULL) {
			$lastEndDateTime = new \DateTimeField(date('Y-m-d H:i:s', $this->data['lastend']));
			return $lastEndDateTime->getDisplayDateTimeValue();
		} else {
			return '';
		}
	}

	/**
	 *
	 * get the last start datetime field
	 */
	public function getLastStartDateTime()
	{
		if ($this->data['laststart'] != NULL) {
			$lastStartDateTime = new \DateTimeField(date('Y-m-d H:i:s', $this->data['laststart']));
			return $lastStartDateTime->getDisplayDateTimeValue();
		} else {
			return '';
		}
	}

	/**
	 * Get Time taken to complete task
	 */
	public function getTimeDiff()
	{
		$lastStart = $this->getLastStart();
		$lastEnd = $this->getLastEnd();
		$timeDiff = $lastEnd - $lastStart;
		return $timeDiff;
	}

	/**
	 * Get the configured handler file.
	 */
	public function getHandlerFile()
	{
		return $this->data['handler_file'];
	}

	/**
	 * Get the Module name
	 */
	public function getModule()
	{

		return $this->data['module'];
	}

	/**
	 * get the Sequence
	 */
	public function getSequence()
	{
		return $this->data['sequence'];
	}

	/**
	 * get the description of cron
	 */
	public function getDescription()
	{
		return $this->data['description'];
	}

	public function getLockStatus()
	{
		return isset($this->data['lockStatus']) ? $this->data['lockStatus'] : false;
	}

	/**
	 * Check if task is right state for running.
	 */
	public function isRunnable()
	{
		// Take care of last time (end - on success, start - if timedout)
		// Take care to start the cron im
		$lastTime = ($this->getLastStart() > 0) ? $this->getLastStart() : $this->getLastEnd();
		$elapsedTime = time() - $lastTime;
		return ($elapsedTime >= ($this->getFrequency() - 60));
	}

	/**
	 * Helper function to check the status value.
	 */
	public function statusEqual($value)
	{
		$status = intval($this->data['status']);
		return $status == $value;
	}

	/**
	 * Is task in running status?
	 */
	public function isRunning()
	{
		return $this->statusEqual(self::$STATUS_RUNNING);
	}

	/**
	 * Is task enabled?
	 */
	public function isEnabled()
	{
		return $this->statusEqual(self::$STATUS_ENABLED);
	}

	/**
	 * Is task disabled?
	 */
	public function isDisabled()
	{
		return $this->statusEqual(self::$STATUS_DISABLED);
	}

	/**
	 * Update status
	 * @param int $status
	 * @throws \Exception
	 */
	public function updateStatus($status)
	{
		switch (intval($status)) {
			case self::$STATUS_DISABLED:
			case self::$STATUS_ENABLED:
			case self::$STATUS_RUNNING:
				break;
			default:
				throw new \Exception('Invalid status');
		}
		\App\Db::getInstance()->createCommand()->update(self::$baseTable, ['status' => $status], ['id' => $this->getId()])->execute();
	}

	/**
	 * Update frequency
	 * @param int $frequency
	 */
	public function updateFrequency($frequency)
	{
		\App\Db::getInstance()->createCommand()->update(self::$baseTable, ['frequency' => $frequency], ['id' => $this->getId()])->execute();
	}

	/**
	 * Mark this instance as running.
	 */
	public function markRunning()
	{
		$time = time();
		\App\Db::getInstance()->createCommand()->update(self::$baseTable, ['status' => self::$STATUS_RUNNING, 'laststart' => $time], ['id' => $this->getId()])->execute();
		return $this->set('laststart', $time);
	}

	/**
	 * Mark this instance as finished.
	 * @return int
	 */
	public function markFinished()
	{
		$lock = $this->getLockStatus();
		$time = time();
		$contitions = ['lastend' => $time];
		if (!$lock) {
			$contitions['status'] = self::$STATUS_ENABLED;
		}
		\App\Db::getInstance()->createCommand()->update(self::$baseTable, $contitions, ['id' => $this->getId()])->execute();
		return $this->set('lastend', $time);
	}

	/**
	 * Detect if the task was started by never finished.
	 */
	public function hadTimeout()
	{
		if (!$this->isRunning()) {
			return false;
		}
		$maxExecutionTime = intval(ini_get('max_execution_time'));
		if ($maxExecutionTime == 0) {
			$maxExecutionTime = \AppConfig::main('maxExecutionCronTime');
		}
		$time = $this->getLastEnd();
		if ($time == 0) {
			$time = $this->getLastStart();
		}
		if (time() > ($time + $maxExecutionTime)) {
			return true;
		}
		return false;
	}

	/**
	 * Register cron task
	 * @param string $name
	 * @param string $handler_file
	 * @param int $frequency
	 * @param string $module
	 * @param int $status
	 * @param int $sequence
	 * @param string $description
	 */
	public static function register($name, $handler_file, $frequency, $module = 'Home', $status = 1, $sequence = 0, $description = '')
	{
		$db = \App\Db::getInstance();
		if (empty($sequence)) {
			$sequence = static::nextSequence();
		}
		$db->createCommand()->insert(self::$baseTable, [
			'name' => $name,
			'handler_file' => $handler_file,
			'frequency' => $frequency,
			'status' => $status,
			'sequence' => $sequence,
			'module' => $module,
			'description' => $description
		])->execute();
	}

	/**
	 * De-register cron task
	 * @param string $name
	 */
	public static function deregister($name)
	{
		\App\Db::getInstance()->createCommand()->delete(self::$baseTable, ['name' => $name])->execute();
		if (isset(self::$instanceCache["$name"])) {
			unset(self::$instanceCache["$name"]);
		}
	}

	/**
	 * Get instances that are active (not disabled)
	 * @return \self[]
	 */
	public static function listAllActiveInstances()
	{
		$instances = [];
		$query = (new \App\Db\Query())->from(self::$baseTable)->where(['<>', 'status', self::$STATUS_DISABLED])->orderBy(['sequence' => SORT_ASC]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$instances[] = new self($row);
		}
		return $instances;
	}

	/**
	 * Get instance of cron task.
	 * @param string $name
	 * @return \self
	 */
	public static function getInstance($name)
	{
		$instance = false;
		if (isset(self::$instanceCache["$name"])) {
			$instance = self::$instanceCache["$name"];
		}

		if ($instance === false) {
			$data = (new \App\Db\Query())->from(self::$baseTable)->where(['name' => $name])->one();
			if ($data) {
				$instance = new self($data);
			}
		}
		return $instance;
	}

	/**
	 * Get instance of cron job by id
	 * @param int $id
	 * @return \self
	 */
	public static function getInstanceById($id)
	{
		$instance = false;
		if (isset(self::$instanceCache[$id])) {
			$instance = self::$instanceCache[$id];
		}
		if ($instance === false) {
			$data = (new \App\Db\Query())->from(self::$baseTable)->where(['id' => $id])->one();
			if ($data) {
				$instance = new self($data);
			}
		}
		return $instance;
	}

	/**
	 * Get instance of cron
	 * @param string $module
	 * @return \self[]
	 */
	public static function listAllInstancesByModule($module)
	{
		$instances = [];
		$dataReader = (new \App\Db\Query())->from(self::$baseTable)->where(['module' => $moduleInstance->id])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$instances[] = new self($row);
		}
		return $instances;
	}

	public function unlockTask()
	{
		$this->updateStatus(self::$STATUS_ENABLED);
	}

	/**
	 * Delete all cron tasks associated with module
	 * @param \Module Instnace of module to use
	 */
	public static function deleteForModule($moduleInstance)
	{
		\App\Db::getInstance()->createCommand()->delete(self::$baseTable, ['module' => $moduleInstance->name])->execute();
	}

	/**
	 * Function sets cron status
	 * @param bool $status
	 */
	public static function setCronAction($status)
	{
		self::$cronAction = $status;
	}

	/**
	 * Function checks cron status
	 * @return bool
	 */
	public static function isCronAction()
	{
		return self::$cronAction;
	}
}
