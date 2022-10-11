<?php
/**
 * OAuth mail authorization api file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\OAuth\BaseAction;

class MailAccount extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** @var string Module name */
	private $moduleName = 'MailAccount';

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
		\App\Session::init();
		$state = $this->controller->request->get('state');
		$hash = $state ? sha1($state) : '';
		if (!$state || !\App\Session::has("OAuth.State.{$hash}") || $state !== \App\Session::get("OAuth.State.{$hash}")['state']) {
			throw new \Api\Core\Exception('No permission or wrong data' . print_r([$_SESSION, $this->controller->request->getAllRaw()], true), 401);
		}
	}

	/** {@inheritdoc} */
	public function updateSession(array $data = []): void
	{
	}

	/** {@inheritdoc}  */
	protected function checkPermissionToModule(): void
	{
	}

	public function get()
	{
		$code = $this->controller->request->getRaw('code');
		$state = $this->controller->request->get('state');
		$hash = $state ? sha1($state) : '';
		$key = "OAuth.State.{$hash}";

		$data = \App\Session::get($key);
		\App\Session::delete($key);
		$recordId = $data['recordId'];

		try {
			if ($this->controller->request->get('error') && !$code) {
				$message = $this->controller->request->get('error_description') ?: 'Authentication error';
				$this->setLogs($recordId, $message);
			} else {
				$mailAccount = \App\Mail\Account::getInstanceById($recordId);
				$mailAccount->getAccessToken(['code' => $code]);
				$provider = $mailAccount->getOAuthProvider();
				$resourceOwner = $provider->getResourceOwner();
				if (($aud = $resourceOwner->toArray()['aud'] ?? '') && $aud !== $mailAccount->getServer()->get('client_id')) {
					$this->setLogs($recordId, 'Attempted to authenticate the wrong data aud: ' . $aud);
				} else {
					$mailAccount->update();
				}
			}

			$redirectUri = $data['redirectUri'];
			header('location: ' . $redirectUri);
			exit;
		} catch (\Throwable $th) {
			$message = $th->getMessage();
			if ($th instanceof \App\Exceptions\AppException) {
				$message = $th->getDisplayMessage();
			}
			$url = $this->setLogs($recordId, $message);
			header('location: ' . \App\Config::main('site_URL') . $url);
			exit;
		}
	}

	private function setLogs(int $recordId, string $message): string
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $this->moduleName);
		$fieldModel = $recordModel->getField('logs');
		$fieldModelStatus = $recordModel->getField('mailaccount_status');
		$status = 'PLL_LOCKED';
		if (mb_strlen($message) > $fieldModel->getMaxValue()) {
			$message = substr($message, 0, $fieldModel->getMaxValue());
		}
		$recordModel->set($fieldModel->getName(), $message)->setDataForSave([$fieldModel->getTableName() => [$fieldModel->getColumnName() => $message]]);
		$recordModel->set($fieldModelStatus->getName(), $status)->setDataForSave([$fieldModelStatus->getTableName() => [$fieldModelStatus->getColumnName() => $status]]);
		$recordModel->save();

		return $recordModel->getDetailViewUrl();
	}
}
