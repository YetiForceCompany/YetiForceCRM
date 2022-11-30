<?php
/**
 * Tool file for the field type `MapCoordinates`.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Fields;

/**
 * Tool class for the field type `MapCoordinates`.
 */
class MapCoordinates
{
	const DECIMAL = 'decimal';
	const DEGREES = 'degrees';
	const CODE_PLUS = 'codeplus';

	/** @var string[] Coordinate formats */
	const COORDINATE_FORMATS = [
		self::DECIMAL => 'LBL_DECIMAL',
		self::DEGREES => 'LBL_DEGREES',
		self::CODE_PLUS => 'LBL_CODE_PLUS'
	];
	/** @var array Coordinate format validators */
	const VALIDATORS = [
		self::DECIMAL => ['lat' => 'Double', 'lon' => 'Double'],
		self::DEGREES => ['lat' => 'Text', 'lon' => 'Text'],
		self::CODE_PLUS => 'Text',
		'type' => 'Standard',
	];

	/**
	 * Converting coordinates from formats: {@see self::COORDINATE_FORMATS}.
	 *
	 * @param string $from
	 * @param string $to
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public static function convert(string $from, string $to, $value)
	{
		if ($from === $to) {
			return $value;
		}
		switch ($from) {
			case self::DECIMAL:
				['lat' => $lat, 'lon' => $lon] = $value;
				break;
			case self::DEGREES:
				$lat = self::degreesToDecimal($value['lat']);
				$lon = self::degreesToDecimal($value['lon']);
				break;
			case self::CODE_PLUS:
				['lat' => $lat, 'lon' => $lon] = self::codePlusToDecimal($value);
				break;
			default:
				throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE||' . $from);
		}
		switch ($to) {
			case self::DECIMAL:
				$return = ['lat' => $lat, 'lon' => $lon];
				break;
			case self::DEGREES:
				$return = ['lat' => self::decimalToDegrees($lat, 'lat'), 'lon' => self::decimalToDegrees($lon, 'lon')];
				break;
			case self::CODE_PLUS:
				$return = self::decimalToCodePlus($lat, $lon);
				break;
			default:
				throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE||' . $to);
		}
		return $return;
	}

	/**
	 * Convert coordinates from decimal to degrees.
	 *
	 * @param string $coord     Coordinates in decimal, e.g. 52.23155431436567
	 * @param string $type      Type: `lat` or `lon`
	 * @param int    $precision Precision, default `4`
	 *
	 * @return string Coordinates in degrees, e.g. `52°13'53.5955"N`
	 */
	public static function decimalToDegrees(string $coord, string $type, int $precision = 4): string
	{
		if ('lat' === $type) {
			$dir = $coord < 0 ? 'S' : 'N';
		} else {
			$dir = $coord < 0 ? 'W' : 'E';
		}
		$vars = explode('.', $coord, 2);
		if (isset($vars[1])) {
			$val = (float) ('0.' . ($vars[1] ?? 0)) * 3600;
			$min = floor($val / 60);
			$sec = round($val - ($min * 60), $precision);
			if (0 == $sec) {
				return sprintf("%s°%02d'%s", $vars[0], $min, $dir);
			}
			return sprintf("%s°%02d'%s\"%s", $vars[0], $min, $sec, $dir);
		}
		return sprintf('%s°%s', $vars[0], $dir);
	}

	/**
	 * Convert coordinates from degrees to decimal.
	 *
	 * @param string $coord Coordinates in degrees, e.g. `21°0'17.983"E`
	 *
	 * @return string|null Coordinates in decimal, e.g. `21.004995277778`
	 */
	public static function degreesToDecimal(string $coord): ?string
	{
		if (($dots = substr_count($coord, '.')) > 1) {
			if (2 < \count(explode(' ', trim(preg_replace('/[a-zA-Z]/', '', preg_replace('/\./', ' ', $coord, $dots - 1)))))) {
				$coord = preg_replace('/\./', ' ', $coord, $dots - 1);
			} else {
				$coord = str_replace('.', ' ', $coord);
			}
		}
		$coord = trim(str_replace(['º', '°', "'", '"', '  '], ' ', trim($coord)));
		$coord = substr($coord, 0, 1) . str_replace('-', ' ', substr($coord, 1));
		if ($coord) {
			$direction = 1;
			if (preg_match('/^(-?\\d{1,3})\\s+(\\d{1,3})\\s*(\\d*(?:\\.\\d*)?)\\s*([nsewoNSEWO]?)$/', $coord, $matches)) {
				// `50°12'13.1188" N` , direction at the end of the string
				$deg = (int) ($matches[1]);
				$min = (int) ($matches[2]);
				$sec = (float) ($matches[3]);
				$dir = strtoupper($matches[4]);
				if ('S' === $dir || 'W' === $dir || $deg < 0) {
					$direction = -1;
					$deg = abs($deg);
				}
				$decimal = ($deg + ($min / 60) + ($sec / 3600)) * $direction;
			} elseif (preg_match('/^([nsewoNSEWO]?)\\s*(\\d{1,3})\\s+(\\d{1,3})\\s*(\\d*\\.?\\d*)$/', $coord, $matches)) {
				// `N 50°12'13.1188"` , direction at the start of the string
				$dir = strtoupper($matches[1]);
				$deg = (int) ($matches[2]);
				$min = (int) ($matches[3]);
				$sec = (float) ($matches[4]);
				if ('S' === $dir || 'W' === $dir) {
					$direction = -1;
				}
				$decimal = ($deg + ($min / 60) + ($sec / 3600)) * $direction;
			} elseif (preg_match('/^(-?\\d+(?:\\.\\d+)?)\\s*([nsewNSEW]?)$/', $coord, $matches)) {
				$dir = strtoupper($matches[2]);
				if ('S' === $dir || 'W' === $dir) {
					$direction = -1;
				}
				$decimal = $matches[1] * $direction;
			} elseif (preg_match('/^([nsewNSEW]?)\\s*(\\d+(?:\\.\\d+)?)$/', $coord, $matches)) {
				$dir = strtoupper($matches[1]);
				if ('S' === $dir || 'W' === $dir) {
					$direction = -1;
				}
				$decimal = $matches[2] * $direction;
			}
		}
		return isset($decimal) ? preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $decimal) : null;
	}

	/**
	 * Convert coordinates from decimal to full Open Location Code.
	 *
	 * @see https://plus.codes/
	 *
	 * @param float $lat A latitude in signed decimal degrees.
	 *                   Will be clipped to the range -90 to 90, e.g. `52.231313`
	 * @param float $lon A longitude in signed decimal degrees.
	 *                   Will be normalized to the range -180 to 180, e.g. `21.004562`
	 *
	 * @return string Full Open Location Code., e.g. `9G4362J3+GR`
	 */
	public static function decimalToCodePlus(float $lat, float $lon): string
	{
		return \OpenLocationCode\OpenLocationCode::encode($lat, $lon, 12);
	}

	/**
	 * Undocumented function.
	 *
	 * @see https://plus.codes/
	 *
	 * @param string $coord Full Open Location Code., e.g. `9G4362J3+GR`
	 *
	 * @return float[] Coordinates in decimal, e.g. `[lat=>52.2313125,lon=>21.0045625]`
	 */
	public static function codePlusToDecimal(string $coord): array
	{
		$return = \OpenLocationCode\OpenLocationCode::decode($coord);
		return ['lat' => $return['latitudeCenter'], 'lon' => $return['longitudeCenter']];
	}

	/**
	 * Update of coordinates on the map.
	 *
	 * @param int    $recordId
	 * @param string $fieldName
	 * @param array  $coordinateData
	 * @param string $action
	 */
	public static function updateMapCoordinates(int $recordId, string $fieldName, array $coordinateData, string $action): void
	{
		$db = \App\Db::getInstance();
		switch ($action) {
			case 'insert':
				$db->createCommand()->insert(\OpenStreetMap_Module_Model::COORDINATES_TABLE_NAME, [
					'crmid' => $recordId,
					'type' => $fieldName,
					'lat' => round($coordinateData['lat'], 7),
					'lon' => round($coordinateData['lon'], 7),
				])->execute();
				break;
			case 'update':
				$db->createCommand()->update(\OpenStreetMap_Module_Model::COORDINATES_TABLE_NAME, ['lat' => round($coordinateData['lat'], 7), 'lon' => round($coordinateData['lon'], 7)], ['crmid' => $recordId, 'type' => $fieldName])->execute();
				break;
			case 'delete':
				$db->createCommand()->delete(\OpenStreetMap_Module_Model::COORDINATES_TABLE_NAME, ['crmid' => $recordId, 'type' => $fieldName])->execute();
				break;
			}
	}
}
