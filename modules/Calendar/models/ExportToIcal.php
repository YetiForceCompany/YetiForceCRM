<?php

/**
 * Calendar export model class.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Calendar_ExportToIcal_Model extends Vtiger_Export_Model
{
	/**
	 * {@inheritdoc}
	 */
	protected $fileExtension = 'ics';

	/**
	 * {@inheritdoc}
	 */
	public function getExportContentType(): string
	{
		return 'text/calendar';
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitizeValues($arr)
	{
		return $arr;
	}

	/**
	 * {@inheritdoc}
	 */
	public function output($headers, $entries)
	{
		$calendar = \App\Integrations\Dav\Calendar::createEmptyInstance();
		foreach ($entries as $row) {
			$calendar->loadFromArray($row);
			$calendar->createComponent();
		}
		echo $calendar->getVCalendar()->serialize();
	}
}
