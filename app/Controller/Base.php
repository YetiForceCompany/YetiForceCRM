<?php
/**
 * Abstract base controller file.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller;

/**
 * Abstract base controller class.
 */
abstract class Base
{
	/** @var \App\Headers Headers instance. */
	public $headers;
	/**
	 * CSRF is active?.
	 *
	 * @var bool
	 */
	public $csrfActive = true;

	/**
	 * Activated language locale.
	 *
	 * @var bool
	 */
	protected static $activatedLocale = false;
	/**
	 * Activated csrf.
	 *
	 * @var bool
	 */
	protected static $activatedCsrf = false;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->headers = \App\Controller\Headers::getInstance();
		if (!self::$activatedLocale && \App\Config::performance('CHANGE_LOCALE')) {
			\App\Language::initLocale();
			self::$activatedLocale = true;
		}
		if (!self::$activatedCsrf) {
			if ($this->csrfActive && \App\Config::security('csrfActive')) {
				require_once 'config/csrf_config.php';
				\CsrfMagic\Csrf::init();
				$this->csrfActive = true;
			} else {
				$this->csrfActive = false;
			}
			self::$activatedCsrf = true;
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
	 * @return void
	 */
	public function validateRequest(\App\Request $request)
	{
		$request->validateReadAccess();
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
	 * Function to check if session is extended.
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
