<?php
/**
 * Batch method.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * BatchMethod class.
 */
class BatchMethod extends Base
{
	/** Enebled */
	const STATUS_ENABLED = 1;

	/** Running     */
	const STATUS_RUNNING = 2;

	/** Halted */
	const STATUS_HALTED = 3;

	/** Completed */
	const STATUS_COMPLETED = 4;

	/** String constant 'userid' @var string */
	private const STR_USER_ID = 'userid';
	/** String constant 'status' @var string */
	private const STR_STATUS = 'status';
	/** String constant 'params' @var string */
	private const STR_PARAMS = 'params';
	/** String constant 'method' @var string */
	private const STR_METHOD = 'method';
	/** String constant 's_#__batchmethod' @var string */
	private const STR_TABLE_BATCHMETHOD = 's_#__batchmethod';
	/** @var array [name => type, ...] */
	protected $allowedFields = [
		self::STR_USER_ID => 'integer',
		self::STR_STATUS => 'integer',
		self::STR_PARAMS => 'string',
		self::STR_METHOD => 'string'
	];

	/** Previous status */
	private $previousStatus;

	/**
	 * Constructor.
	 *
	 * @param array $values
	 */
	public function __construct($values = [])
	{
		$values[static::STR_STATUS] = $values[static::STR_STATUS] ?? static::STATUS_ENABLED;
		$values[static::STR_USER_ID] = $values[static::STR_USER_ID] ?? \App\User::getCurrentUserId();
		parent::__construct($values);
		$this->previousStatus = $values[static::STR_STATUS];
	}

	/**
	 * Save.
	 *
	 * @return bool
	 */
	public function save()
	{
		$db = Db::getInstance();
		if ($this->get('id')) {
			$result = $db->createCommand()->update(static::STR_TABLE_BATCHMETHOD, $this->getData(), ['id' => $this->get('id')])->execute();
		} else {
			$exists = (new Db\Query())->from(static::STR_TABLE_BATCHMETHOD)->where([self::STR_METHOD => $this->get(self::STR_METHOD), self::STR_PARAMS => $this->get(self::STR_PARAMS)])->exists();
			if ($exists) {
				return false;
			}
			$this->value['created_time'] = date('Y-m-d H:i:s');
			$result = $db->createCommand()->insert(static::STR_TABLE_BATCHMETHOD, $this->getData())->execute();
			$this->value['id'] = $db->getLastInsertID('s_#__batchmethod_id_seq');
		}
		$this->previousStatus = $this->get(static::STR_STATUS);
		return (bool) $result;
	}

	/**
	 * Execute.
	 */
	public function execute()
	{
		try {
			$this->setStatus(static::STATUS_RUNNING);
			if (\is_callable($this->get(self::STR_METHOD))) {
				\call_user_func_array($this->get(self::STR_METHOD), Json::decode($this->get(self::STR_PARAMS)));
			} else {
				throw new Exceptions\AppException("ERR_CONTENTS_VARIABLE_CANT_CALLED_FUNCTION||{$this->get(self::STR_METHOD)}", 406);
			}
			$this->setStatus(static::STATUS_COMPLETED);
		} catch (\Throwable $ex) {
			Log::error($ex->getMessage());
			if ($this->previousStatus === static::STATUS_HALTED) {
				$this->log($ex->getMessage() . $ex->getTraceAsString());
				$this->delete();
			} else {
				$this->setStatus(static::STATUS_HALTED);
			}
		}
	}

	/**
	 * Set status.
	 *
	 * @param int $status
	 */
	public function setStatus(int $status)
	{
		$result = Db::getInstance()->createCommand()->update(static::STR_TABLE_BATCHMETHOD, [static::STR_STATUS => $status], ['id' => $this->get('id')])->execute();
		if ($result) {
			$this->set(static::STR_STATUS, $status);
		}
	}

	/**
	 * Function check is status completed.
	 *
	 * @return bool
	 */
	public function isCompleted()
	{
		return $this->get(static::STR_STATUS) === static::STATUS_COMPLETED;
	}

	/**
	 * Delete.
	 */
	public function delete()
	{
		Db::getInstance()->createCommand()->delete(static::STR_TABLE_BATCHMETHOD, ['id' => $this->get('id')])->execute();
	}

	/**
	 * Log.
	 *
	 * @param string $message
	 */
	private function log($message)
	{
		Db::getInstance('log')->createCommand()->insert('l_#__batchmethod', [
			static::STR_STATUS => $this->get(static::STR_STATUS),
			'date' => date('Y-m-d H:i:s'),
			self::STR_METHOD => $this->get(self::STR_METHOD),
			self::STR_PARAMS => $this->get(self::STR_PARAMS),
			static::STR_USER_ID => $this->get(static::STR_USER_ID),
			'message' => $message
		])->execute();
	}
}
