<?php
/**
 * Tokens utils file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Utils;

/**
 * Tokens utils class.
 */
class Tokens
{
	/** @var string Token table name. */
	const TABLE_NAME = 's_#__tokens';

	/** @var string Last generated token. */
	private static $lastToken;

	/** @var int Skip time verification when getting token data */
	public const SKIP_TIME_VERIFICATION = 1;
	/** @var int Skip count verification when getting token data */
	public const SKIP_COUNT_VERIFICATION = 2;

	/**
	 * Generate token.
	 *
	 * @param string      $method         Method name
	 * @param array       $params
	 * @param string|null $expirationDate Date and time until which the token is valid
	 * @param bool        $oneTime
	 *
	 * @return string
	 */
	public static function generate(string $method, array $params, string $expirationDate = null, bool $oneTime = true): string
	{
		if (!\is_callable($method) && !class_exists($method)) {
			throw new \App\Exceptions\AppException("The method `$method` does not exist");
		}
		$uid = self::generateUid();
		\App\Db::getInstance('admin')->createCommand()->insert(self::TABLE_NAME, [
			'uid' => $uid,
			'method' => $method,
			'params' => \App\Json::encode($params),
			'created_by_user' => \App\User::getCurrentUserRealId(),
			'created_date' => date('Y-m-d H:i:s'),
			'expiration_date' => $expirationDate,
			'one_time_use' => (int) $oneTime,
		])->execute();
		return self::$lastToken = $uid;
	}

	/**
	 * Generate uid function.
	 *
	 * @return string
	 */
	private static function generateUid(): string
	{
		$uid = \App\Encryption::generatePassword(64);
		if (null !== self::get($uid, self::SKIP_TIME_VERIFICATION | self::SKIP_COUNT_VERIFICATION)) {
			return self::generateUid();
		}
		return $uid;
	}

	/**
	 * Get token detail.
	 *
	 * @param string $uid
	 * @param int    $skip {@example self::SKIP_TIME_VERIFICATION, self::SKIP_COUNT_VERIFICATION}
	 *
	 * @return array|null
	 */
	public static function get(string $uid, int $skip = 0): ?array
	{
		$row = (new \App\Db\Query())->from(self::TABLE_NAME)
			->where(['uid' => $uid])->one(\App\Db::getInstance('admin')) ?: null;

		if (!($skip & self::SKIP_TIME_VERIFICATION) && !empty($row['expiration_date']) && strtotime($row['expiration_date']) < time()) {
			self::delete($uid);
			$row = null;
		} elseif ($row) {
			$row['params'] = \App\Json::decode($row['params']);
		}

		if (!($skip & self::SKIP_COUNT_VERIFICATION) && $row && (bool) $row['one_time_use']) {
			self::delete($uid);
		}

		return $row;
	}

	/**
	 * Delete token.
	 *
	 * @param string $uid
	 *
	 * @return void
	 */
	public static function delete(string $uid): void
	{
		\App\Db::getInstance('admin')->createCommand()->delete(self::TABLE_NAME, ['uid' => $uid])->execute();
	}

	/**
	 * Generate URL form token.
	 *
	 * @param string|null $token
	 * @param int|null    $serverId
	 *
	 * @return string
	 */
	public static function generateLink(?string $token = null, ?int $serverId = null): string
	{
		if (null === $token) {
			$token = self::$lastToken;
		}
		$url = \App\Config::main('site_URL');
		if (0 === $serverId) {
			if ($rows = \App\Integrations\Services::getByType('Token')) {
				$row = reset($rows);
				if ($row && $row['url']) {
					$url = $row['url'];
					if ('/' !== substr($url, -1)) {
						$url .= '/';
					}
				}
			}
		} elseif ($serverId && ($data = \App\Integrations\Services::getById($serverId)) && $data['url']) {
			$url = $data['url'];
			if ('/' !== substr($url, -1)) {
				$url .= '/';
			}
		}
		return $url . 'webservice/Token/' . $token;
	}

	/**
	 * Link action mechanism for TextParser.
	 *
	 * @param array             $params
	 * @param \Api\Token\Action $self
	 *
	 * @return void
	 */
	public static function runWorkflow(array $params, \Api\Token\Action $self): void
	{
		if (empty($params['recordId'])) {
			throw new \Api\Core\Exception('No record ID', 1001);
		}
		if (empty($params['workflowId'])) {
			throw new \Api\Core\Exception('No workflow ID', 1002);
		}
		if (!\App\Record::isExists($params['recordId'], '', \App\Record::STATE_ACTIVE)) {
			throw new \Api\Core\Exception("The record {$params['recordId']} does not exist", 1003);
		}
		\Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/include.php');
		$wfs = new \VTWorkflowManager();
		$workflow = $wfs->retrieve($params['workflowId']);
		if (empty($workflow)) {
			throw new \Api\Core\Exception("The workflow {$params['workflowId']} does not exist", 1004);
		}
		$recordModel = \Vtiger_Record_Model::getInstanceById($params['recordId']);
		if ($workflow->evaluate($recordModel)) {
			$workflow->performTasks($recordModel);
			if (!empty($params['messages'])) {
				echo $params['messages'];
			} elseif ($params['redirect']) {
				header('location: ' . $params['redirect']);
			}
		} else {
			throw new \Api\Core\Exception('ERR_TOKEN_NOT_EXECUTION_CONDITIONS', 1005);
		}
	}
}
