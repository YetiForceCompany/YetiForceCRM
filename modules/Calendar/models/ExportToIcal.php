<?php

/**
 * Calendar export model class.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Calendar_ExportToIcal_Model extends \App\Export\Records
{
	/** {@inheritdoc} */
	protected $fileExtension = 'ics';

	/** {@inheritdoc} */
	public function getExportContentType(): string
	{
		return 'text/calendar';
	}

	/** {@inheritdoc} */
	public function exportData()
	{
		$this->output([], $this->getExportQuery()->all());
	}

	/** {@inheritdoc} */
	public function sanitizeValues($arr): array
	{
		return $arr;
	}

	/** {@inheritdoc} */
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
