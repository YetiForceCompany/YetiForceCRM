<?php

/**
 * Search view test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Gui_Search extends \Tests\GuiBase
{
    /**
     * Testing the record list.
     */
    public function testIndex()
    {
        $this->url('/index.php?module=Search&parent=Settings&view=Index');
        $this->assertEquals('Search', $this->byId('module')->value());
        $this->assertEquals('Index', $this->byId('view')->value());
        $this->assertEquals('Settings', $this->byId('parent')->value());
    }
}
