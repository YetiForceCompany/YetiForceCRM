<?php
/**
 * Base mail scanner action file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail\ScannerAction;

/**
 * Base mail scanner action class.
 */
class OpenHelpDesk extends Base
{
	/** {@inheritdoc} */
	public static $priority = 4;

	/** {@inheritdoc} */
	public function process(): void
	{
		$scanner = $this->scannerEngine;
		if ($this->checkExceptions('CreatedHelpDesk') || false === $scanner->getMailCrmId() || 1 !== $scanner->getMailType()) {
			$scanner->findRelatedRecordsBySubject();
		}
	}
}
