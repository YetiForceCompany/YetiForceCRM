<?php

/**
 * List view test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Gui_ListView extends \Tests\GuiBase
{
    /**
     * Testing the record list.
     */
    public function testList()
    {
        foreach (vtlib\Functions::getAllModules() as $module) {
            if ($module['name'] === 'Events') {
                continue;
            }
            $this->url("/index.php?module={$module['name']}&view=List");
            $this->logs = $module['name'];
            $this->assertEquals($module['name'], $this->byId('module')->value());
            $this->assertEquals('List', $this->byId('view')->value());
            $this->url("/index.php?module={$module['name']}&view=Edit");
        }
    }

    public function testView()
    {
        $this->url('/index.php?module=Accounts&view=List');

        $this->execute([
            'script' => '$(".Accounts_listViewBasic_action_LBL_SHOW_QUICK_DETAILS").first().click();',
            'args' => [],
        ]);

        $this->execute([
            'script' => '$(".listViewEntries")[0].click();',
            'args' => [],
        ]);

        $this->assertEquals('Accounts', $this->byId('module')->value());
        $this->assertEquals('Detail', $this->byId('view')->value());

        $this->execute([
            'script' => '$(".Accounts_detailViewBasic_action_BTN_RECORD_EDIT").click();',
            'args' => [],
        ]);

        $this->assertEquals('Accounts', $this->byId('module')->value());
        $this->assertEquals('Edit', $this->byId('view')->value());

//		$this->byCssSelector('.formActionsPanel button.btn-success')->click();
//		$this->assertEquals('Accounts', $this->byId('module')->value());
//		$this->assertEquals('Detail', $this->byId('view')->value());

//		$this->execute([
//			'script' => '$(".Accounts_detailViewExtended_action_LBL_ARCHIVE_RECORD").click();',
//			'args' => [],
//		]);
//		$this->execute([
//			'script' => '$(".bootbox-confirm  button.btn-primary").click();',
//			'args' => [],
//		]);

//		$this->execute([
//			'script' => '$(".Accounts_detailViewBasic_action_LBL_DUPLICATE").click();',
//			'args' => [],
//		]);
//		$this->assertEquals('Accounts', $this->byId('module')->value());
//		$this->assertEquals('Edit', $this->byId('view')->value());
    }

    public function testListActions()
    {
        $this->url('/index.php?module=Accounts&view=List');
        $this->execute([
            'script' => '$(".Accounts_listViewHeader_action_BTN_WATCHING_MODULE").click();',
            'args' => [],
        ]);
        $this->execute([
            'script' => '$(".Accounts_listViewHeader_action_LBL_SEND_NOTIFICATION").click();',
            'args' => [],
        ]);

        $this->execute([
            'script' => '$(".Accounts_listViewHeader_action_LBL_SHOW_MAP").click();',
            'args' => [],
        ]);
        $this->assertEquals('Accounts', $this->byId('module')->value());
    }
}
