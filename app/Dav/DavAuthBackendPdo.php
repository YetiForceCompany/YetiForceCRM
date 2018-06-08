<?php

namespace App\Dav;

use Sabre\DAV;

/**
 * This is an authentication backend that uses a database to manage passwords.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class DavAuthBackendPdo extends DAV\Auth\Backend\PDO
{
	/**
	 * PDO table name we'll be using.
	 *
	 * @var string
	 */
	public $tableName = 'dav_users';

	/**
	 * Authentication Realm.
	 *
	 * The realm is often displayed by browser clients when showing the
	 * authentication dialog.
	 *
	 * @var string
	 */
	protected $realm = 'YetiDAV';
}
