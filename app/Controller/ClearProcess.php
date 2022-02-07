<?php
/**
 * Trait clear process controller file.
 *
 * @package   Controller
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller;

/**
 * Trait clear process controller class.
 */
trait ClearProcess
{
	/**
	 * Empty pre process.
	 *
	 * @param \App\Request $request
	 * @param bool         $display
	 */
	public function preProcess(\App\Request $request, $display = true)
	{
	}

	/**
	 * Empty pos process.
	 *
	 * @param \App\Request $request
	 * @param bool         $display
	 */
	public function postProcess(\App\Request $request, $display = true)
	{
	}
}
