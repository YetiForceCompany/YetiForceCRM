<?php
/**
 * Layout/Icon test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

class Layout_Icon extends \Tests\Base
{
	/**
	 * Testing getIconByFileType function.
	 */
	public function testGetIconByFileType()
	{
		$this->assertSame('far fa-file-video', \App\Layout\Icon::getIconByFileType('video'), 'Expected icon class name(video)');
		$this->assertSame('fas fa-calendar-alt', \App\Layout\Icon::getIconByFileType('text/vcard'), 'Expected icon class name(text/vcard)');
		$this->assertSame('yfm-Documents', \App\Layout\Icon::getIconByFileType('NotExists'), 'Expected icon class name(text/vcard)');
	}
}
