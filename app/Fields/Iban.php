<?php
/**
 * Tools for iban class.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	 * @param array $fieldParams
	 * @param array $recordData
	 *
	 * @return string
	 */
	public function getIBANValue(array $fieldParams, array $recordData): string
	{
		$this->fieldParams = $fieldParams;
		$this->recordData = $recordData;
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
		return isset($this->fieldParams['country']) && isset($this->fieldParams['sortCode'], $this->fieldParams['clientId'], $this->fieldParams['conditions']);
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
				[$fieldNameForCondition,$fieldToGetValue, $fieldValue] = array_pad(explode(':', $conditionValues), 3, false);
				[$fieldNameForCondition, $fieldValueForCondition, $fieldFromGetValue] = array_pad(explode(':', $conditionValues), 3, false);
				if ('defaultValue' === $fieldNameForCondition || (isset($this->recordData) && $fieldValueForCondition === $this->recordData[$fieldNameForCondition])) {
					$valueForPayerId = $this->recordData[$fieldFromGetValue] ?? '';
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
