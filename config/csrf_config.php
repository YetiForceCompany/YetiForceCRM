<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ****************************************************************************** */

class CSRFConfig
{
	/**
	 * Specific custom config startup for CSRF.
	 */
	public static function startup()
	{
		//Override the default expire time of token
		\CsrfMagic\Csrf::$expires = \App\Config::security('csrfLifetimeToken', 7200);
		\CsrfMagic\Csrf::$callback = function ($tokens) {
			throw new \App\Exceptions\Csrf('Invalid request - Response For Illegal Access', 403);
		};
		$js = 'vendor/yetiforce/csrf-magic/src/Csrf.min.js';
		if (!IS_PUBLIC_DIR) {
			$js = 'public_html/' . $js;
		}
		\CsrfMagic\Csrf::$defer = true;
		\CsrfMagic\Csrf::$dirSecret = __DIR__;
		\CsrfMagic\Csrf::$rewriteJs = $js;
		\CsrfMagic\Csrf::$cspToken = \App\Session::get('CSP_TOKEN');
		\CsrfMagic\Csrf::$frameBreaker = \Config\Security::$csrfFrameBreaker;
		\CsrfMagic\Csrf::$windowVerification = \Config\Security::$csrfFrameBreakerWindow;

		/*
		 * if an ajax request initiated, then if php serves content with <html> tags
		 * as a response, then unnecessarily we are injecting csrf magic javascipt
		 * in the response html at <head> and <body> using csrf_ob_handler().
		 * So, to overwride above rewriting we need following config.
		 */
		if (static::isAjax()) {
			\CsrfMagic\Csrf::$frameBreaker = false;
			\CsrfMagic\Csrf::$rewriteJs = null;
		}
	}

	public static function isAjax()
	{
		if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
			return true;
		}
		return false;
	}
}
