<?php
/**
 * Tools for iban file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

namespace App\Fields;

/**
 * Iban class.
 */
class Iban
{
	/**
	 * Function returns IBAN value.
	 *
	 * @param array               $fieldParams
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return string
	 */
	public function getIbanValue(array $fieldParams, \Vtiger_Record_Model $recordModel): string
	{
		$this->fieldParams = $fieldParams;
		$this->recordModel = $recordModel;
		if ($this->checkIfFieldHasMandatoryParams()) {
			$iban = new \PHP_IBAN\IBAN();
			$this->countryIban = new \PHP_IBAN\IBANCountry($this->fieldParams['country']);
			$countryIbanLength = $this->countryIban->IBANLength();
			$checkSumValueForCreateIban = '00';
			$payerId = $this->getPayerId();
			$ibanNumberForCalculateCheckSum = $this->fieldParams['country'] . $checkSumValueForCreateIban . $this->fieldParams['sortCode'] . $this->fieldParams['clientId'] . $payerId;
			$checkSumValue = $iban->FindChecksum($ibanNumberForCalculateCheckSum);
			if ((int) $countryIbanLength !== \strlen($ibanNumberForCalculateCheckSum)) {
				return '';
			}
			return $this->fieldParams['country'] . $checkSumValue . $this->fieldParams['sortCode'] . $this->fieldParams['clientId'] . $payerId;
		}
	}

	/**
	 * Checks if field params has mandatory values.
	 *
	 *  @throws \App\Exceptions\AppException
	 *
	 * @return bool
	 */
	protected function checkIfFieldHasMandatoryParams(): bool
	{
		if (isset($this->fieldParams['country'], $this->fieldParams['sortCode'], $this->fieldParams['clientId'], $this->fieldParams['conditions'])) {
			return true;
		}
		throw new \App\Exceptions\AppException('ERR_NO_VALUE');
	}

	/**
	 * Create payers id value for IBAN.
	 *
	 * @return string
	 */
	protected function getPayerId(): string
	{
		$bbanLength = $this->countryIban->BBANLength();
		$conditionsForPayerId = $this->fieldParams['conditions'];
		$payerCharactersAmount = $bbanLength - \strlen($this->fieldParams['sortCode']) - \strlen($this->fieldParams['clientId']);
		if (\is_array($conditionsForPayerId)) {
			foreach ($conditionsForPayerId as $conditionValues) {
				[$fieldNameForCondition, $fieldFromGetValue, $fieldValueForCondition] = array_pad(explode(':', $conditionValues), 3, false);
				if ('defaultValue' === $fieldNameForCondition || ($fieldValueForCondition && $fieldValueForCondition === $this->recordModel->get($fieldNameForCondition))) {
					$valueForPayerId = $this->recordModel->get($fieldFromGetValue);
					if ($valueForPayerId && \strlen($valueForPayerId) !== $payerCharactersAmount) {
						return $this->addLeadingZeros($payerCharactersAmount, $valueForPayerId);
					}
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
