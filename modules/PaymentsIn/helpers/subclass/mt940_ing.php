<?php
/**
 * mt940 ing class
 * @package YetiForce.Helper
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
Vtiger_Loader::includeOnce('~~modules/PaymentsIn/helpers/mt940.php');

class mt940_ing extends mt940
{

	public $lastLine = '';

	public function parse()
	{
		$tab = $this->prepareFile();
		foreach ($tab as $line)
			$this->parseLine($line);
	}

	protected function parseLine($line)
	{
		$tag = substr($line, 1, strpos($line, ':', 1) - 1);
		$value = trim(substr($line, strpos($line, ':', 1) + 1));
		switch ($tag) {
			case '20':
				$this->refNumber = $value;
				break;
			case '25':
				$this->accountNumber = $value;
				break;
			case '28C':
				$this->extractNumber = $value;
				break;
			case 'NS':
				$code = substr($value, 0, 2);
				if ($code == '22')
					$this->ownerName = substr($value, 2);
				else if ($code == '23')
					$this->accountName = substr($value, 2);
				break;
			case '60F':
				$this->openBalance = $this->parseBalance($value);
				break;
			case '62F':
				$this->closeBalance = $this->parseBalance($value);
				break;
			case '64':
				$this->availableBalance = $this->parseBalance($value);
				break;
			case '61':
				self::parseOperation($value);
				break;
			case '86':
				if ($this->_lastTag == '86')
					$this->parseTransaction($value);
				else
					$this->info .= $value;
				$this->lastLine = $value;
				break;
			default:
				break;
		}
		$this->_lastTag = $tag;
	}

	protected function parseTransaction($value)
	{
		$transaction = array(
			'code' => substr($value, 0, 3),
			'typeCode' => '',
			'number' => '',
			'title' => '',
			'contName' => '',
			'contAccount' => '',
			'currancy' => ''
		);
		if ($transaction['code'] == '940')
			return;
		$delimiter = substr($value, 3, 1);
		$transaction['currancy'] = substr($this->lastLine, 9, 3);
		$transaction['currancyAmount'] = substr($this->lastLine, 12, 14);
		$tab = explode($delimiter, substr($value, 4));
		foreach ($tab as $line) {
			$subTag = substr($line, 0, 2);
			$subVal = substr($line, 2);
			switch ($subTag) {
				case '00':
					$transaction['typeCode'] = $subVal;
					break;
				case '10':
					$transaction['number'] = $subVal;
					break;
				case '20':
				case '21':
				case '22':
				case '23':
				case '24':
				case '25':
				case '26':
				case '27':
				case '28':
					$transaction['title'] .= $subVal;
					break;
				case '31':
					$transaction['contAccount'] = $subVal;
					break;
				case '32':
				case '33':
					$transaction['contName'] .= $subVal;
					break;
				case '62':
				case '63':
					$transaction['contName'] .= $subVal;
					break;
				default:
					break;
			}
		}
		$this->operations[count($this->operations) - 1]['details'] = $transaction;
	}

	protected function parseOperation($value)
	{
		if (substr($value, 15, 4) != 'S940') {
			$this->operations[] = array(
				'date' => substr($value, 0, 2) . '-' . substr($value, 2, 2) . '-' . substr($value, 4, 2),
				'accountDate' => substr($value, 6, 2) . '-' . substr($value, 8, 2),
				'indicator' => substr($value, 10, 1),
				'amount' => substr($value, 11, strpos($value, ',') - 8)
			);
		}
	}
}

?>
