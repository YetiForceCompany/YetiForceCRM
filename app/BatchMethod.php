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

	/** @var array [name => type, ...] */
	protected $allowedFields = [
		'userid' => 'integer',
		'status' => 'integer',
		'params' => 'string',
		'method' => 'string'
	];

	/** Previous status */
	private $previousStatus;

	/**
	 * BatchMethod constructor.
	 *
	 * @param array $values
	 * @param bool  $encode
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function __construct($values = [], $encode = true)
	{
		$values['status'] = $values['status'] ?? static::STATUS_ENABLED;
		$values['userid'] = $values['userid'] ?? User::getCurrentUserId();
		if ($encode) {
			$values['params'] = Json::encode($values['params']);
		}
		parent::__construct($values);
		$this->previousStatus = $values['status'];
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
			$result = $db->createCommand()->update('s_#__batchmethod', $this->getData(), ['id' => $this->get('id')])->execute();
		} else {
			$exists = (new Db\Query())->from('s_#__batchmethod')->where(['method' => $this->get('method'), 'params' => $this->get('params')])->exists();
			if ($exists) {
				return false;
			}
			$this->value['created_time'] = date('Y-m-d H:i:s');
			$result = $db->createCommand()->insert('s_#__batchmethod', $this->getData())->execute();
			$this->value['id'] = $db->getLastInsertID('s_#__batchmethod_id_seq');
		}
		$this->previousStatus = $this->get('status');
		return (bool) $result;
	}

	/**
	 * Execute.
	 */
	public function execute()
	{
		try {
			$this->setStatus(static::STATUS_RUNNING);
			if (\is_callable($this->get('method'))) {
				\call_user_func_array($this->get('method'), Json::decode($this->get('params')));
			} else {
				throw new Exceptions\AppException("ERR_CONTENTS_VARIABLE_CANT_CALLED_FUNCTION||{$this->get('method')}", 406);
			}
			$this->setStatus(static::STATUS_COMPLETED);
		} catch (\Throwable $ex) {
			Log::error($ex->getMessage());
			if ($this->previousStatus === static::STATUS_HALTED) {
				$this->log($ex->__toString());
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
		$result = Db::getInstance()->createCommand()->update('s_#__batchmethod', ['status' => $status], ['id' => $this->get('id')])->execute();
		if ($result) {
			$this->set('status', $status);
		}
	}

	/**
	 * Function check is status completed.
	 *
	 * @return bool
	 */
	public function isCompleted()
	{
		return $this->get('status') === static::STATUS_COMPLETED;
	}

	/**
	 * Delete.
	 */
	public function delete()
	{
		Db::getInstance()->createCommand()->delete('s_#__batchmethod', ['id' => $this->get('id')])->execute();
	}

	/**
	 * Log.
	 *
	 * @param string $message
	 */
	private function log($message)
	{
		Db::getInstance('log')->createCommand()->insert('l_#__batchmethod', [
			'status' => $this->get('status'),
			'date' => date('Y-m-d H:i:s'),
			'method' => $this->get('method'),
			'params' => $this->get('params'),
			'userid' => $this->get('userid'),
			'message' => $message
		])->execute();
	}
}
