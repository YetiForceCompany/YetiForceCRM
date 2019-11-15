<?php

namespace App\Controller;

/**
 * Abstract base controller class.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
abstract class Base
{
	/**
	 * Headers instance.
	 *
	 * @var \App\Headers
	 */
	public $headers;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->headers = \App\Headers::getInstance();
		if (\App\Config::performance('CHANGE_LOCALE')) {
			\App\Language::initLocale();
		}
	}

	/**
	 * Function to check login required permission.
	 *
	 * @return bool
	 */
	public function loginRequired()
	{
		return true;
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	abstract public function checkPermission(\App\Request $request);

	/**
	 * Process function.
	 *
	 * @param \App\Request $request
	 */
	abstract public function process(\App\Request $request);

	/**
	 * Function to validate request method.
	 *
	 * @param \App\Request $request
	 *
	 * @return bool
	 */
	public function validateRequest(\App\Request $request)
	{
		return $request->validateReadAccess();
	}

	/**
	 * Pre process ajax function.
	 *
	 * @param \App\Request $request
	 */
	public function preProcessAjax(\App\Request $request)
	{
	}

	/**
	 * Pre process function.
	 *
	 * @param \App\Request $request
	 * @param bool         $display
	 */
	public function preProcess(\App\Request $request, $display = true)
	{
	}

	/**
	 * Post process function.
	 *
	 * @param \App\Request $request
	 * @param bool         $display
	 */
	public function postProcess(\App\Request $request, $display = true)
	{
	}

	/**
	 * Post process ajax function.
	 *
	 * @param \App\Request $request
	 */
	public function postProcessAjax(\App\Request $request)
	{
	}

	/**
	 * Send headers.
	 */
	public function sendHeaders()
	{
		$this->headers->send();
	}

	/**
	 * Function to check if session is extend.
	 *
	 * @param \App\Request $request
	 *
	 * @return bool
	 */
	public function isSessionExtend(\App\Request $request)
	{
		return true;
	}
}
