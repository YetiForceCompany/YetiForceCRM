<?php
namespace App\Custom;

/**
 * Numbers to words converter class
 * @package YetiForce.Custom
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class NumberToWords
{

	/**
	 * @var array
	 */
	protected static $words = [];

	public static function initialize()
	{
		$minus = vtranslate('LBL_MINUS');
		$zero = vtranslate('LBL_ZERO');
		$one = vtranslate('LBL_ONE');
		$two = vtranslate('LBL_TWO');
		$three = vtranslate('LBL_THREE');
		$four = vtranslate('LBL_FOUR');
		$five = vtranslate('LBL_FIVE');
		$six = vtranslate('LBL_SIX');
		$seven = vtranslate('LBL_SEVEN');
		$eight = vtranslate('LBL_EIGHT');
		$nine = vtranslate('LBL_NINE');
		$ten = vtranslate('LBL_TEN');
		$eleven = vtranslate('LBL_ELEVEN');
		$twelve = vtranslate('LBL_TWELVE');
		$thirteen = vtranslate('LBL_THIRTEEN');
		$fourteen = vtranslate('LBL_FOURTEEN');
		$fifteen = vtranslate('LBL_FIFTEEN');
		$sixteen = vtranslate('LBL_SIXTEEN');
		$seventeen = vtranslate('LBL_SEVENTEEN');
		$eighteen = vtranslate('LBL_EIGHTEEN');
		$nineteen = vtranslate('LBL_NINETEEN');
		$twenty = vtranslate('LBL_TWENTY');
		$thirty = vtranslate('LBL_THIRTY');
		$forty = vtranslate('LBL_FORTY');
		$fifty = vtranslate('LBL_FIFTY');
		$sixty = vtranslate('LBL_SIXTY');
		$seventy = vtranslate('LBL_SEVENTY');
		$eighty = vtranslate('LBL_EIGHTY');
		$ninety = vtranslate('LBL_NINETY');
		$hundred = vtranslate('LBL_HUNDRED');
		$twoHundred = vtranslate('LBL_TWO_HUNDRED');
		$threeHundred = vtranslate('LBL_THREE_HUNDRED');
		$fourHundred = vtranslate('LBL_FOUR_HUNDRED');
		$fiveHundred = vtranslate('LBL_FIVE_HUNDRED');
		$sixHundred = vtranslate('LBL_SIX_HUNDRED');
		$sevenHundred = vtranslate('LBL_SEVEN_HUNDRED');
		$eightHundred = vtranslate('LBL_EIGHT_HUNDRED');
		$nineHundred = vtranslate('LBL_NINE_HUNDRED');
		$thousand = vtranslate('LBL_THOUSAND');
		$thousands = vtranslate('LBL_THOUSANDS');
		$thousandss = vtranslate('LBL_THOUSANDSS');
		$million = vtranslate('LBL_MILLION');
		$millions = vtranslate('LBL_MILLIONS');
		$millionss = vtranslate('LBL_MILLIONSS');
		$billion = vtranslate('LBL_BILLION');
		$billions = vtranslate('LBL_BILLIONS');
		$billionss = vtranslate('LBL_BILLIONSS');
		$trillion = vtranslate('LBL_TRILLION');
		$trillions = vtranslate('LBL_TRILLIONS');
		$trillionss = vtranslate('LBL_TRILLIONSS');
		$quadrillion = vtranslate('LBL_QUADRILLION');
		$quadrillions = vtranslate('LBL_QUADRILLIONS');
		$quadrillionss = vtranslate('LBL_QUADRILLIONSS');
		$quinrillion = vtranslate('LBL_QUINTILLION');
		$quinrillions = vtranslate('LBL_QUINTILLIONS');
		$quinrillionss = vtranslate('LBL_QUINTILLIONSS');
		$sextillion = vtranslate('LBL_SEXTILLION');
		$sextillions = vtranslate('LBL_SEXTILLIONS');
		$sextillionss = vtranslate('LBL_SEXTILLIONSS');
		$septillion = vtranslate('LBL_SEPTILLION');
		$septillions = vtranslate('LBL_SEPTILLIONS');
		$septillionss = vtranslate('LBL_SEPTILLIONSS');
		$nonillion = vtranslate('LBL_NONILLION');
		$nonillions = vtranslate('LBL_NONILLIONS');
		$nonillionss = vtranslate('LBL_NONILLIONSS');
		$undecillion = vtranslate('LBL_UNDECILLION');
		$undecillions = vtranslate('LBL_UNDECILLIONS');
		$undecillionss = vtranslate('LBL_UNDECILLIONSS');
		$tredecillion = vtranslate('LBL_TREDECILLION');
		$tredecillions = vtranslate('LBL_TREDECILLIONS');
		$tredecillionss = vtranslate('LBL_TREDECILLIONSS');
		$quindecillion = vtranslate('LBL_QUINDECILLION');
		$quindecillions = vtranslate('LBL_QUINDECILLIONS');
		$quindecillionss = vtranslate('LBL_QUINDECILLIONSS');
		$septendecillion = vtranslate('LBL_SEPTENDECILLION');
		$septendecillions = vtranslate('LBL_SEPTENDECILLIONS');
		$septendecillionss = vtranslate('LBL_SEPTENDECILLIONSS');
		$novemdecillion = vtranslate('LBL_NOVEMDECILLION');
		$novemdecillions = vtranslate('LBL_NOVEMDECILLIONS');
		$novemdecillionss = vtranslate('LBL_NOVEMDECILLIONSS');

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
			[$novemdecillion, $novemdecillions, $novemdecillionss]
		];

		self::$words = $words;
	}

	/**
	 * Podaje słowną wartość liczby całkowitej (równierz podaną w postaci stringa)
	 *
	 * @param integer $int
	 * @return string
	 */
	public static function integerNumberToWords($int)
	{
		static::initialize();
		$int = strval($int);
		$in = preg_replace('/[^-\d]+/', '', $int);

		$return = '';

		if ($in{0} == '-') {
			$in = substr($in, 1);
			$return = self::$words[0] . ' ';
		}

		$txt = str_split(strrev($in), 3);

		if ($in == 0) {
			$return = self::$words[1][0] . ' ';
		}

		for ($i = count($txt) - 1; $i >= 0; $i--) {
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
	 * Podaje słowną wartość kwoty wraz z wartościami po kropce.
	 * Nie przyjmuje wartości przedzielonych przecinkami (jako wartości nie numerycznych).
	 *
	 * @param integer|string $amount
	 * @param string $currencyName
	 * @param string $centName
	 * @return string
	 * @throws \Exception
	 */
	public static function process($amount, $currencyName = 'zł', $centName = 'gr')
	{
		self::initialize();

		if (!is_numeric($amount)) {
			throw new \Exception('Nieprawidłowa kwota');
		}

		$amountString = number_format($amount, 2, '.', '');
		list($bigAmount, $smallAmount) = explode('.', $amountString);

		$bigAmount = static::integerNumberToWords($bigAmount) . ' ' . $currencyName . ' ';
		$smallAmount = static::integerNumberToWords($smallAmount) . ' ' . $centName;

		return self::clear($bigAmount . $smallAmount);
	}

	/**
	 * Czyści podwójne spacje i trimuje
	 *
	 * @param $string
	 * @return mixed
	 */
	protected static function clear($string)
	{
		return preg_replace('!\s+!', ' ', trim($string));
	}

	/**
	 * $inflections = Array('jeden','dwa','pięć')
	 *
	 * @param string[] $inflections
	 * @param $int
	 * @return mixed
	 */
	protected static function inflection(array $inflections, $int)
	{
		$txt = $inflections[2];

		if ($int == 1) {
			$txt = $inflections[0];
		}

		$units = intval(substr($int, -1));

		$rest = $int % 100;

		if (($units > 1 && $units < 5) & !($rest > 10 && $rest < 20)) {
			$txt = $inflections[1];
		}

		return $txt;
	}

	/**
	 * Odmiana dla liczb < 1000
	 *
	 * @param integer $int
	 * @return string
	 */
	protected static function number($int)
	{
		$return = '';

		$j = abs(intval($int));

		if ($j == 0) {
			return self::$words[1][0];
		}

		$units = $j % 10;
		$dozens = intval(($j % 100 - $units) / 10);
		$hundreds = intval(($j - $dozens * 10 - $units) / 100);

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
