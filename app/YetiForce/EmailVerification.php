<?php
/**
 * YetiForce admin email verification file.
 * Modifying this file or functions will violate the license terms!!!
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Klaudia Łozowska <k.lozowska@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\YetiForce;

/**
 * YetiForce admin email verification class.
 */
class EmailVerification
{
	/** @var string URL */
	public const URL = 'https://api.yetiforce.eu/emails';
	/** Process identificator - init */
	public const PROCESS_INIT = 0;
	/** Process identificator - register */
	public const PROCESS_REGISTER = 1;
	/** @var string Endpoints */
	private const TYPES = [
		self::PROCESS_INIT => 'POST',
		self::PROCESS_REGISTER => 'PUT'
	];

	/** @var string|null Last eroor */
	protected ?string $error;
	/** @var int Type */
	protected int $type;
	/** @var bool Response result */
	protected bool $success;
	/** @var string E-mail address */
	private string $email;
	/** @var bool Newsletter agreement */
	private bool $newsletter;
	/** @var string Code */
	private string $token;

	/**
	 * Set type request.
	 *
	 * @param int $type Types {@see self::TYPES}
	 *
	 * @return $this
	 */
	public function setType(int $type): self
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * Set e-mail address.
	 *
	 * @param string $email
	 *
	 * @return self
	 */
	public function setEmail(string $email): self
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * Set newsletter agreement.
	 *
	 * @param bool $newsletter
	 *
	 * @return self
	 */
	public function setNewsletter(bool $newsletter): self
	{
		$this->newsletter = $newsletter;

		return $this;
	}

	/**
	 * Set code.
	 *
	 * @param string $code
	 *
	 * @return self
	 */
	public function setCode(string $code): self
	{
		$this->token = $code;

		return $this;
	}

	/**
	 * Request sending an email with a verification token.
	 *
	 * @return bool
	 */
	public function send(): bool
	{
		$this->success = false;
		$type = self::TYPES[$this->type];
		$client = new ApiClient();
		$client->send(self::URL, $type, ['form_params' => $this->getData()]);
		$this->error = $client->getError();

		if (409 === $client->getStatusCode() && false !== stripos($this->error, 'app')) {
			(new \App\YetiForce\Register())->recreate();
			throw new \App\Exceptions\AppException('ERR_RECREATE_APP_ACCESS');
		}

		return $this->success = 204 === $client->getStatusCode();
	}

	/**
	 * Get last error.
	 *
	 * @return string
	 */
	public function getError(): string
	{
		return $this->error ?? '';
	}

	/**
	 * Post processes.
	 *
	 * @return void
	 */
	public function postProcess(): void
	{
		if ($this->success && self::PROCESS_REGISTER === $this->type) {
			$fieldName = 'email';
			$recordModel = \Settings_Companies_Record_Model::getInstance();
			$fieldModel = $recordModel->getFieldInstanceByName($fieldName);
			$fieldModel->getUITypeModel()->validate($this->email, true);
			$recordModel->set($fieldName, $fieldModel->getDBValue($this->email));
			$recordModel->save();
		}
	}

	/**
	 * Get data for request.
	 *
	 * @return array
	 */
	private function getData(): array
	{
		$data = [
			'appId' => Register::getInstanceKey()
		];

		$reflect = new \ReflectionClass($this);
		foreach ($reflect->getProperties(\ReflectionProperty::IS_PRIVATE) as $property) {
			$property->setAccessible(true);
			if ($property->isInitialized($this) && null !== ($value = $property->getValue($this))) {
				$data[$property->getName()] = $value;
			}
		}

		return $data;
	}
}
