<?php

/**
 * List view test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Gui_Roles extends \Tests\GuiBase
{
    /**
     * Testing the record list.
     */
    public function testIndex()
    {
        $this->url('/index.php?module=Roles&parent=Settings&view=Index');
        $this->assertEquals('Roles', $this->byId('module')->value());
        $this->assertEquals('Index', $this->byId('view')->value());
        $this->assertEquals('Settings', $this->byId('parent')->value());
    }

    /**
     * Testing the add group.
     *
     * Test unfinished
     */
    public function testAdd()
    {
        $this->url('/index.php?module=Roles&parent=Settings&view=Index');
        $this->byCssSelector('.toolbar-handle .toolbar a')->click();
        $this->assertEquals('Roles', $this->byId('module')->value());
    }
}
