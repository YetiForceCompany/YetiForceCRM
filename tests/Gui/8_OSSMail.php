<?php

/**
 * OSSMail test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Gui_OSSMail extends \Tests\GuiBase
{
    /**
     * Testing start page.
     */
    public function testIndex()
    {
        $this->url('/index.php?module=OSSMail&parent=Settings&view=Index');
        $this->assertEquals('OSSMail', $this->byId('module')->value(), 'There is not a correct module');
        $this->assertEquals('Index', $this->byId('view')->value(), 'There is not a correct view');
        $this->assertEquals('Settings', $this->byId('parent')->value(), 'There is not a correct parent');
    }
}
