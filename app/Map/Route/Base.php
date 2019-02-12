<?php
/**
 * Base Connector to find route between two points.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App\Map\Route;

/**
 * Base Connector to get route.
 */
abstract class Base
{
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
	/**
	 * @var array
	 */
	protected $geoJson;

	/**
	 * Set start of route.
	 *
	 * @param float $lat
	 * @param float $lon
	 */
	public function setStart(float $lat, float $lon)
	{
		$this->start = ['lat' => $lat, 'lon' => $lon];
	}

	/**
	 * Set end of route.
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
		$this->indirectPoints[]= ['lat' => $lat, 'lon' => $lon];
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
	 * Get distance of route.
	 *
	 * @return float
	 */
	public function getDistance()
	{
		return $this->distance;
	}

	/**
	 * Function to calculate route.
	 */
	abstract public function calculate();
}
