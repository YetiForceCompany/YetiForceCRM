<?php
/**
 * Base Connector to find routing between two points.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Map\Routing;

/**
 * Base Connector to get routing.
 */
abstract class Base
{
	/** @var string API server URL. */
	protected $url;

	/** @var array Custom routing parameters. */
	protected $params;

	/** @var float[] */
	protected $start;

	/** @var float[] */
	protected $end;

	/** @var array */
	protected $indirectPoints;

	/** @var string Rout description */
	protected $description = '';

	/** @var float Travel time */
	protected $travelTime = 0;

	/** @var float */
	protected $distance = 0;

	/** @var array GeoJSON, route geometry format */
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
	public function setStart(float $lat, float $lon): void
	{
		$this->start = ['lat' => $lat, 'lon' => $lon];
	}

	/**
	 * Set end of routing.
	 *
	 * @param float $lat .
	 * @param float $lon
	 */
	public function setEnd(float $lat, float $lon): void
	{
		$this->end = ['lat' => $lat, 'lon' => $lon];
	}

	/**
	 * Add indirect point.
	 *
	 * @param float $lat
	 * @param float $lon
	 */
	public function addIndirectPoint(float $lat, float $lon): void
	{
		$this->indirectPoints[] = ['lat' => $lat, 'lon' => $lon];
	}

	/**
	 * Get geojson - RFC 7946.
	 *
	 * @return array
	 */
	public function getGeoJson(): array
	{
		return $this->geoJson;
	}

	/**
	 * Get description.
	 *
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * Get time of travel.
	 *
	 * @return float
	 */
	public function getTravelTime(): float
	{
		return $this->travelTime;
	}

	/**
	 * Get distance of routing.
	 *
	 * @return float
	 */
	public function getDistance(): float
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
