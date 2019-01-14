<?php

/**
 * Configuration file.
 * This file is auto-generated.
 *
 * @package Config
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

namespace Config;

/**
 * Configuration Class.
 */
class Api
{
	/** List of active services. Available: dav, webservices, webservice */
	public static $enabledServices = ['dav'];

	/** Dav configuration. Available: false, true */
	public static $enableBrowser = false;

	/** Dav configuration. Available: false, true */
	public static $enableCardDAV = false;

	/** Dav configuration. Available: false, true */
	public static $enableCalDAV = false;

	/** Dav configuration. Available: false, true */
	public static $enableWebDAV = false;

	/** Webservice config. Available: false, true */
	public static $ENCRYPT_DATA_TRANSFER = false;

	/** Webservice config. */
	public static $AUTH_METHOD = 'Basic';

	/** Webservice config. */
	public static $PRIVATE_KEY = 'config/private.key';

	/** Webservice config. */
	public static $PUBLIC_KEY = 'config/public.key';
}
