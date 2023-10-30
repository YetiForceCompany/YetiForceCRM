<?php
/**
 * YetiForce shop AbstractBaseProduct file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\YetiForce;

/**
 * YetiForce shop AbstractBaseProduct class.
 */
abstract class AbstractBase
{
	/** @var string Last error. */
	protected ?string $error = null;
	/** @var bool Response result */
	protected bool $success = false;

	/**
	 * Get last error.
	 *
	 * @return string
	 */
	public function getError(): string
	{
		return $this->error ?? '';
	}
}
