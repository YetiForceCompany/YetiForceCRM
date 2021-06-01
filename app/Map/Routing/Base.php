<?php
/**
 * Base Connector to find routing between two points.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Map\Routing;

/**
 * Base Connector to get routing.
 */
abstract class Base
{
	/**
	 * API url.
	 *
	 * @var string
	 */
	protected $url;
	/**
	 * Custom routing parameters.
	 *
	 * @var array
	 */
	protected $params;
	/**
	 * @var float[]
	 */
	protected $start;
	/**
	 * @var float[]
	 */
	protected $end;
	/**
	 * @var array
	 */
	protected $indirectPoints;
	/**
	 * @var string
	 */
	protected $description;
	/**
	 * @var float
	 */
	protected $travelTime;
	/**
	 * @var float
	 */
	protected $distance;
	/** @var array geo json */
	protected $geoJson;

	/**
	 * Construct.
	 *
	 * @param array $provider
	 */
	public function __construct(array $provider)
	{
		$this->url = $provider['apiUrl'];
		$this->params = $provider['params'] ?? [];
	}

	/**
	 * Set start of routing.
	 *
	 * @param float $lat
	 * @param float $lon
	 */
	public function setStart(float $lat, float $lon)
	{
		$this->start = ['lat' => $lat, 'lon' => $lon];
	}

	/**
	 * Set end of routing.
	 *
	 * @param float $lat .
	 * @param float $lon
	 */
	public function setEnd(float $lat, float $lon)
	{
		$this->end = ['lat' => $lat, 'lon' => $lon];
	}

	/**
	 * Add indirect point.
	 *
	 * @param float $lat
	 * @param float $lon
	 */
	public function addIndirectPoint(float $lat, float $lon)
	{
		$this->indirectPoints[] = ['lat' => $lat, 'lon' => $lon];
	}

	/**
	 * Get geojson - RFC 7946.
	 *
	 * @return array
	 */
	public function getGeoJson()
	{
		return $this->geoJson;
	}

	/**
	 * Get description.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Get time of travel.
	 *
	 * @return float
	 */
	public function getTravelTime()
	{
		return $this->travelTime;
	}

	/**
	 * Get distance of routing.
	 *
	 * @return float
	 */
	public function getDistance()
	{
		return $this->distance;
	}

	/**
	 * Function to calculate routing.
	 */
	abstract public function calculate();

	/**
	 * Function to parse points.
	 *
	 * @return array
	 */
	abstract public function parsePoints(): array;
}
