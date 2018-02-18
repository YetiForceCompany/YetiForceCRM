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

    /**
     * Testing save configuration.
     */
    public function testSave()
    {
        $this->url('/index.php?module=OSSMail&parent=Settings&view=Index');
        $this->execute([
            'script' => '$(\'[name="product_name"]\').val(\'YetiForce_GUI_TEST\');',
            'args' => [],
        ]);
        $this->execute([
            'script' => '$(\'[name="session_lifetime"]\').val(\'35\');',
            'args' => [],
        ]);
        $this->byClassName('saveButton')->click();
        $this->url('/index.php?module=OSSMail&parent=Settings&view=Index');
        $this->assertEquals('YetiForce_GUI_TEST', $this->byName('product_name')->attribute('value'), 'Incorrect value');
        $this->assertEquals('35', $this->byName('session_lifetime')->attribute('value'), 'Incorrect value');
    }
}
