<?php
/**
 * YetiForce register order file.
 * Modifying this file or functions that affect the footer appearance will violate the license terms!!!
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\YetiForce;

/**
 * YetiForce register class.
 */
final class Order
{
	/** @var string URL */
	public const URL = 'https://api.yetiforce.eu/registrations/orders';

	/**
	 * Array of company form fields.
	 */
	private const COMPANY_FORM_FIELDS = [
		'name',
		'vat_id',
		'country',
		'post_code',
		'city',
		'address',
	];

	/** @var string Last error. */
	public ?string $error = null;
	/** @var bool Response result */
	private bool $success;
	/** @var string Order ID */
	private ?string $id;

	/** @var array Raw data */
	private array $data = [];
	/** @var Product package ID */
	private string $packageId;

	/**
	 * Function determines fields available in payment view.
	 *
	 * @return \Settings_Vtiger_Field_Model[]
	 */
	public function getFieldInstances(): array
	{
		$company = \App\Company::getCompany();
		$fields = [];
		foreach (self::COMPANY_FORM_FIELDS as $fieldName) {
			$params = [
				'uitype' => 1,
				'fieldvalue' => $company[$fieldName] ?? null,
				'displaytype' => 1,
				'typeofdata' => 'V~M',
				'presence' => '',
				'isEditableReadOnly' => false,
				'maximumlength' => '255',
				'column' => $fieldName,
				'name' => $fieldName,
				'label' => 'LBL_' . strtoupper($fieldName),
				'purifyType' => \App\Purifier::TEXT
			];

			switch ($fieldName) {
				case 'city':
					$params['maximumlength'] = '100';
					break;
				case 'country':
					$params['uitype'] = 16;
					$params['maximumlength'] = '100';
					$params['picklistValues'] = [];
					foreach (\App\Fields\Country::getAll() as $country) {
						$params['picklistValues'][$country['name']] = \App\Language::translateSingleMod(
							$country['name'],
							'Other.Country'
						);
					}
					break;
				default:
					break;
			}

			$fields[$fieldName] = \Settings_Vtiger_Field_Model::init('Vtiger', $params);
		}

		return $fields;
	}

	/**
	 * Set value.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function set(string $key, $value)
	{
		$this->data[$key] = $value;
		return $this;
	}

	/**
	 * Send order data.
	 *
	 * @return bool
	 */
	public function send(): bool
	{
		$this->success = false;
		$client = new ApiClient();
		$client->send(self::URL, 'POST', ['form_params' => $this->getData()]);
		$this->error = $client->getError();
		if (!$this->error && ($code = $client->getStatusCode())) {
			$content = $client->getResponseBody();
			$this->success = \in_array($code, [200, 201]) && $content;
			if ($this->success) {
				$this->setId(\App\Json::decode($content)['id']);
			}
		}

		return $this->success;
	}

	/**
	 * Get order ID.
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->id ?? '';
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
	 * Set product package ID.
	 *
	 * @param string $packageId
	 *
	 * @return self
	 */
	public function setPackageId(string $packageId): self
	{
		$this->packageId = $packageId;

		return $this;
	}

	/**
	 * set order ID.
	 *
	 * @param string $id
	 *
	 * @return self
	 */
	private function setId(string $id): self
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * Get registration data.
	 *
	 * @return string[]
	 */
	private function getData(): array
	{
		return [
			'packageId' => $this->packageId,
			'company' => $this->data['name'],
			'city' => $this->data['city'],
			'vatId' => $this->data['vat_id'],
			'country' => $this->data['country'],
			'postCode' => $this->data['post_code'],
			'address' => $this->data['address'],
		];
	}
}
