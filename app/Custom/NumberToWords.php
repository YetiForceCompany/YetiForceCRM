<?php

namespace App\Custom;

/**
 * Numbers to words converter class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class NumberToWords
{
	/**
	 * Contains an array of translations.
	 *
	 * @var array
	 */
	protected static $words = [];

	/**
	 * Initializes the translation.
	 */
	public static function initialize()
	{
		$minus = \App\Language::translate('LBL_MINUS');
		$zero = \App\Language::translate('LBL_ZERO');
		$one = \App\Language::translate('LBL_ONE');
		$two = \App\Language::translate('LBL_TWO');
		$three = \App\Language::translate('LBL_THREE');
		$four = \App\Language::translate('LBL_FOUR');
		$five = \App\Language::translate('LBL_FIVE');
		$six = \App\Language::translate('LBL_SIX');
		$seven = \App\Language::translate('LBL_SEVEN');
		$eight = \App\Language::translate('LBL_EIGHT');
		$nine = \App\Language::translate('LBL_NINE');
		$ten = \App\Language::translate('LBL_TEN');
		$eleven = \App\Language::translate('LBL_ELEVEN');
		$twelve = \App\Language::translate('LBL_TWELVE');
		$thirteen = \App\Language::translate('LBL_THIRTEEN');
		$fourteen = \App\Language::translate('LBL_FOURTEEN');
		$fifteen = \App\Language::translate('LBL_FIFTEEN');
		$sixteen = \App\Language::translate('LBL_SIXTEEN');
		$seventeen = \App\Language::translate('LBL_SEVENTEEN');
		$eighteen = \App\Language::translate('LBL_EIGHTEEN');
		$nineteen = \App\Language::translate('LBL_NINETEEN');
		$twenty = \App\Language::translate('LBL_TWENTY');
		$thirty = \App\Language::translate('LBL_THIRTY');
		$forty = \App\Language::translate('LBL_FORTY');
		$fifty = \App\Language::translate('LBL_FIFTY');
		$sixty = \App\Language::translate('LBL_SIXTY');
		$seventy = \App\Language::translate('LBL_SEVENTY');
		$eighty = \App\Language::translate('LBL_EIGHTY');
		$ninety = \App\Language::translate('LBL_NINETY');
		$hundred = \App\Language::translate('LBL_HUNDRED');
		$twoHundred = \App\Language::translate('LBL_TWO_HUNDRED');
		$threeHundred = \App\Language::translate('LBL_THREE_HUNDRED');
		$fourHundred = \App\Language::translate('LBL_FOUR_HUNDRED');
		$fiveHundred = \App\Language::translate('LBL_FIVE_HUNDRED');
		$sixHundred = \App\Language::translate('LBL_SIX_HUNDRED');
		$sevenHundred = \App\Language::translate('LBL_SEVEN_HUNDRED');
		$eightHundred = \App\Language::translate('LBL_EIGHT_HUNDRED');
		$nineHundred = \App\Language::translate('LBL_NINE_HUNDRED');
		$thousand = \App\Language::translate('LBL_THOUSAND');
		$thousands = \App\Language::translate('LBL_THOUSANDS');
		$thousandss = \App\Language::translate('LBL_THOUSANDSS');
		$million = \App\Language::translate('LBL_MILLION');
		$millions = \App\Language::translate('LBL_MILLIONS');
		$millionss = \App\Language::translate('LBL_MILLIONSS');
		$billion = \App\Language::translate('LBL_BILLION');
		$billions = \App\Language::translate('LBL_BILLIONS');
		$billionss = \App\Language::translate('LBL_BILLIONSS');
		$trillion = \App\Language::translate('LBL_TRILLION');
		$trillions = \App\Language::translate('LBL_TRILLIONS');
		$trillionss = \App\Language::translate('LBL_TRILLIONSS');
		$quadrillion = \App\Language::translate('LBL_QUADRILLION');
		$quadrillions = \App\Language::translate('LBL_QUADRILLIONS');
		$quadrillionss = \App\Language::translate('LBL_QUADRILLIONSS');
		$quinrillion = \App\Language::translate('LBL_QUINTILLION');
		$quinrillions = \App\Language::translate('LBL_QUINTILLIONS');
		$quinrillionss = \App\Language::translate('LBL_QUINTILLIONSS');
		$sextillion = \App\Language::translate('LBL_SEXTILLION');
		$sextillions = \App\Language::translate('LBL_SEXTILLIONS');
		$sextillionss = \App\Language::translate('LBL_SEXTILLIONSS');
		$septillion = \App\Language::translate('LBL_SEPTILLION');
		$septillions = \App\Language::translate('LBL_SEPTILLIONS');
		$septillionss = \App\Language::translate('LBL_SEPTILLIONSS');
		$nonillion = \App\Language::translate('LBL_NONILLION');
		$nonillions = \App\Language::translate('LBL_NONILLIONS');
		$nonillionss = \App\Language::translate('LBL_NONILLIONSS');
		$undecillion = \App\Language::translate('LBL_UNDECILLION');
		$undecillions = \App\Language::translate('LBL_UNDECILLIONS');
		$undecillionss = \App\Language::translate('LBL_UNDECILLIONSS');
		$tredecillion = \App\Language::translate('LBL_TREDECILLION');
		$tredecillions = \App\Language::translate('LBL_TREDECILLIONS');
		$tredecillionss = \App\Language::translate('LBL_TREDECILLIONSS');
		$quindecillion = \App\Language::translate('LBL_QUINDECILLION');
		$quindecillions = \App\Language::translate('LBL_QUINDECILLIONS');
		$quindecillionss = \App\Language::translate('LBL_QUINDECILLIONSS');
		$septendecillion = \App\Language::translate('LBL_SEPTENDECILLION');
		$septendecillions = \App\Language::translate('LBL_SEPTENDECILLIONS');
		$septendecillionss = \App\Language::translate('LBL_SEPTENDECILLIONSS');
		$novemdecillion = \App\Language::translate('LBL_NOVEMDECILLION');
		$novemdecillions = \App\Language::translate('LBL_NOVEMDECILLIONS');
		$novemdecillionss = \App\Language::translate('LBL_NOVEMDECILLIONSS');

		$words = [
			$minus,
			[$zero, $one, $two, $three, $four, $five, $six, $seven, $eight, $nine],
			[$ten, $eleven, $twelve, $thirteen, $fourteen, $fifteen, $sixteen, $seventeen, $eighteen, $nineteen],
			[$ten, $twenty, $thirty, $forty, $fifty, $sixty, $seventy, $eighty, $ninety],
			[$hundred, $twoHundred, $threeHundred, $fourHundred, $fiveHundred, $sixHundred, $sevenHundred, $eightHundred, $nineHundred],
			[$thousand, $thousands, $thousandss],
			[$million, $millions, $millionss],
			[$billion, $billions, $billionss],
			[$trillion, $trillions, $trillionss],
			[$quadrillion, $quadrillions, $quadrillionss],
			[$quinrillion, $quinrillions, $quinrillionss],
			[$sextillion, $sextillions, $sextillionss],
			[$septillion, $septillions, $septillionss],
			[$nonillion, $nonillions, $nonillionss],
			[$undecillion, $undecillions, $undecillionss],
			[$tredecillion, $tredecillions, $tredecillionss],
			[$quindecillion, $quindecillions, $quindecillionss],
			[$septendecillion, $septendecillions, $septendecillionss],
			[$novemdecillion, $novemdecillions, $novemdecillionss],
		];
		self::$words = $words;
	}

	/**
	 * Provides a verbal value of integer (also provided as string).
	 *
	 * @param int $int
	 *
	 * @return string
	 */
	public static function integerNumberToWords($int)
	{
		static::initialize();
		$int = (string) $int;
		$in = preg_replace('/[^-\d]+/', '', $int);

		$return = '';

		if ($in[0] == '-') {
			$in = substr($in, 1);
			$return = self::$words[0] . ' ';
		}

		$txt = str_split(strrev($in), 3);

		if ($in == 0) {
			$return = self::$words[1][0] . ' ';
		}

		for ($i = count($txt) - 1; $i >= 0; --$i) {
			$number = (int) strrev($txt[$i]);

			if ($number > 0) {
				if ($i == 0) {
					$return .= self::number($number) . ' ';
				} else {
					$return .= ($number > 1 ? self::number($number) . ' ' : '')
						. self::inflection(self::$words[4 + $i], $number) . ' ';
				}
			}
		}
		return self::clear($return);
	}

	/**
	 * Provides a verbal value of total amount with numbers after a comma.
	 * Does not accept values separated with a comma (as non-numerical values).
	 *
	 * @param int|string $amount
	 * @param string     $currencyName
	 * @param string     $centName
	 *
	 * @throws \Exception
	 *
	 * @return string
	 */
	public static function process($amount, $currencyName = 'zł', $centName = 'gr')
	{
		self::initialize();
		if (!is_numeric($amount)) {
			throw new \App\Exceptions\AppException('ERR_ILLEGAL_VALUE');
		}
		$amountString = number_format($amount, 2, '.', '');
		list($bigAmount, $smallAmount) = explode('.', $amountString);

		$bigAmount = static::integerNumberToWords($bigAmount) . ' ' . $currencyName . ' ';
		$smallAmount = static::integerNumberToWords($smallAmount) . ' ' . $centName;
		return self::clear($bigAmount . $smallAmount);
	}

	/**
	 * Cleans double spaces and trimms.
	 *
	 * @param $string
	 *
	 * @return mixed
	 */
	protected static function clear($string)
	{
		return preg_replace('!\s+!', ' ', trim($string));
	}

	/**
	 * Array inflection.
	 *
	 * @param array $inflections
	 * @param int   $int
	 *
	 * @return array
	 */
	protected static function inflection(array $inflections, $int)
	{
		$txt = $inflections[2];

		if ($int == 1) {
			$txt = $inflections[0];
		}

		$units = (int) (substr($int, -1));

		$rest = $int % 100;

		if (($units > 1 && $units < 5) & !($rest > 10 && $rest < 20)) {
			$txt = $inflections[1];
		}
		return $txt;
	}

	/**
	 * Variety for numbers < 1000.
	 *
	 * @param int $int
	 *
	 * @return string
	 */
	protected static function number($int)
	{
		$return = '';

		$j = abs((int) $int);

		if ($j == 0) {
			return self::$words[1][0];
		}

		$units = $j % 10;
		$dozens = (int) (($j % 100 - $units) / 10);
		$hundreds = (int) (($j - $dozens * 10 - $units) / 100);

		if ($hundreds > 0) {
			$return .= self::$words[4][$hundreds - 1] . ' ';
		}

		if ($dozens > 0) {
			if ($dozens == 1) {
				$return .= self::$words[2][$units] . ' ';
			} else {
				$return .= self::$words[3][$dozens - 1] . ' ';
			}
		}

		if ($units > 0 && $dozens != 1) {
			$return .= self::$words[1][$units] . ' ';
		}
		return $return;
	}
}
