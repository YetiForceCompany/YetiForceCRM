<?php
/**
 * Mail test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\App;

/**
 * @internal
 * @coversNothing
 */
final class MailTest extends \Tests\Base
{
	/**
	 * Testing getAll function.
	 */
	public function testAddSmtp()
	{
		$recordModel = \Settings_MailSmtp_Record_Model::getCleanInstance();
		$recordModel->set('mailer_type', 'smtp');
		$recordModel->set('default', 1);
		$recordModel->set('name', 'Fake SMTP');
		$recordModel->set('host', 'Fake SMTP');
		$recordModel->set('port', 587);
		$recordModel->set('username', 'YetiForce@fake.smtp');
		$recordModel->set('password', 'YetiForce fake pass');
		$recordModel->set('authentication', 1);
		$recordModel->set('secure', 'tls');
		$recordModel->set('options', '');
		$recordModel->set('from_email', 'YetiForce@fake.smtp');
		$recordModel->set('from_name', 'YetiForce fake smtp');
		$recordModel->set('reply_to', '');
		$recordModel->set('individual_delivery', 0);
		$recordModel->save();

		\App\Cache::delete('DefaultSmtp', '');
		$defaultSmtp = \App\Mail::getDefaultSmtp();
		static::assertNotEmpty($defaultSmtp);
	}
}
