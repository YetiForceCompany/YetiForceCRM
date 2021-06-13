<?php
/**
 * IBAN uitype file.
 *
 * @package   UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * UIType IBAN Field Class.
 */
class Vtiger_IBAN_UIType extends Vtiger_Base_UIType
{
	public function setValueFromRequest(App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getName();
		$this->recordModel = $recordModel;
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		$value = $request->getByType($requestFieldName, 'Text');
		if (!$value) {
			$value = $this->getIBANValue();
		}
		var_dump($value);
		$this->validate($value, true);

		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/**
	 * Function returns IBAN value.
	 *
	 * @return string
	 */
	protected function getIBANValue(): string
	{
		if ($this->checkIfFieldHasMandatoryParams()) {
			$iban = new \PHP_IBAN\IBAN();
			$this->countryIBAN = new \PHP_IBAN\IBANCountry($this->fieldParams['country']);
			$countryIBANLength = $this->countryIBAN->IBANLength();
			$checkSumValueForCreateIBAN = '00';
			$payerId = $this->getPayerId();
			$ibanNumberForCalculateCheckSum = $this->fieldParams['country'] . $checkSumValueForCreateIBAN . $this->fieldParams['sortCode'] . $this->fieldParams['clientId'] . $payerId;
			$checkSumValue = $iban->FindChecksum($ibanNumberForCalculateCheckSum);

			if ((int) $countryIBANLength !== \strlen($ibanNumberForCalculateCheckSum)) {
				return '';
			}
			return $this->fieldParams['country'] . $checkSumValue . $this->fieldParams['sortCode'] . $this->fieldParams['clientId'] . $payerId;
		}
	}

	/**
	 * Checks if field params has mandatory values.
	 *
	 * @return bool
	 */
	protected function checkIfFieldHasMandatoryParams(): bool
	{
		if ($this->fieldParams = $this->getFieldModel()->getFieldParams()) {
			return isset($this->fieldParams['country']) && isset($this->fieldParams['sortCode'], $this->fieldParams['clientId'], $this->fieldParams['conditions']);
		}
		return false;
	}

	/**
	 * Create payers id value for IBAN.
	 *
	 * @return string
	 */
	protected function getPayerId(): string
	{
		$bbanLength = $this->countryIBAN->BBANLength();
		$conditionsForPayerId = $this->fieldParams['conditions'];
		$payerCharactersAmount = $bbanLength - \strlen($this->fieldParams['sortCode']) - \strlen($this->fieldParams['clientId']);

		if (\is_array($conditionsForPayerId)) {
			foreach ($conditionsForPayerId as $conditionValues) {
				[$fieldName,$fieldToGetValue, $fieldValue] = array_pad(explode(':', $conditionValues), 3, false);
				if ('defaultValue' === $fieldName || $fieldValue === $this->recordModel->get($fieldName)) {
					$valueForPayerId = $this->recordModel->get($fieldToGetValue);
					if ($valueForPayerId && \strlen($valueForPayerId) !== $payerCharactersAmount) {
						return $this->addLeadingZeros($payerCharactersAmount, $valueForPayerId);
					}
					return $valueForPayerId;
				}
			}
		}
		if ('random' === $conditionsForPayerId && isset($this->fieldParams['clientIdPattern'])) {
			return $this->createRandomPayerId();
		}
		return '';
	}

	/**
	 * Add leading zeros to payer id if necessary.
	 *
	 * @param int    $payerCharactersAmount
	 * @param string $valueForPayerId
	 *
	 * @return string
	 */
	protected function addLeadingZeros(int $payerCharactersAmount, string $valueForPayerId): string
	{
		return str_pad($valueForPayerId, $payerCharactersAmount, '0', STR_PAD_LEFT);
	}

	/**
	 * Create randomly payer id by pattern.
	 *
	 * @return string
	 */
	protected function createRandomPayerId(): string
	{
		$clientPattern = $this->fieldParams['clientIdPattern'];
		$patternLength = \strlen($clientPattern);

		$payerId = '';
		$letters = range('A', 'Z');
		$numbers = range('0', '9');
		$lettersAndNumbers = array_merge($letters, $numbers);
		for ($charPosition = 0; $charPosition < $patternLength; ++$charPosition) {
			$clientPattern[$charPosition];
			switch ($clientPattern[$charPosition]) {
				case 'N':
					$payerId .= rand(0, 9);
					break;
				case 'A':
					$randomKey = array_rand($letters, 1);
					$payerId .= $letters[$randomKey];
					break;
				case 'X':
					$randomKey = array_rand($letters, 1);
					$payerId .= $lettersAndNumbers[$randomKey];
					break;
			}
		}
		return $payerId;
	}
}
