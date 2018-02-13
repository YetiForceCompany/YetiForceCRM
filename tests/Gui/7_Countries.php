<?php

/**
 * List view test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Wojciech Bruggemann <w.bruggemann@yetiforce.com>
 */
class Gui_Countries extends \Tests\GuiBase
{
    /**
     * Record ID which exists in database and will be changed.
     *
     * @var int
     */
    protected static $id;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        static::$id = (new \App\Db\Query())->from('u_#__countries')->select('id')->scalar();
    }

    /**
     * Testing view index.
     */
    public function testIndex()
    {
        $this->url('/index.php?module=Countries&parent=Settings&view=Index');
        $this->assertEquals('Countries', $this->byId('module')->value(), 'There is not a correct module');
        $this->assertEquals('Index', $this->byId('view')->value(), 'There is not a correct view');
        $this->assertEquals('Settings', $this->byId('parent')->value(), 'There is not a correct parent');
    }

    /**
     * Testing button status.
     */
    public function testButtonStatus()
    {
        $status = $this->getValueOfField('status');
        $this->url('/index.php?module=Countries&parent=Settings&view=Index');
        $this->execute([
            'script' => '$("tr[data-id='.static::$id.'] button.status").click()',
            'args' => [],
        ]);
        $this->url('/index.php?module=Countries&parent=Settings&view=Index');
        $status2 = $this->getValueOfField('status');
        $this->assertNotEquals($status2, $status, 'Status was not changed in database');
    }

    /**
     * Testing button phone.
     */
    public function testButtonPhone()
    {
        $phone = $this->getValueOfField('phone');
        $this->url('/index.php?module=Countries&parent=Settings&view=Index');
        $this->execute([
            'script' => '$("tr[data-id='.static::$id.'] button.phone").click()',
            'args' => [],
        ]);
        $this->url('/index.php?module=Countries&parent=Settings&view=Index');
        $phone2 = $this->getValueOfField('phone');
        $this->assertNotEquals($phone2, $phone, 'Phone was not changed in database');
    }

    /**
     * Testing button uitype.
     */
    public function testButtonUitype()
    {
        $uitype = $this->getValueOfField('uitype');
        $this->url('/index.php?module=Countries&parent=Settings&view=Index');
        $this->execute([
            'script' => '$("tr[data-id='.static::$id.'] button.uitype").click()',
            'args' => [],
        ]);
        $this->url('/index.php?module=Countries&parent=Settings&view=Index');
        $uitype2 = $this->getValueOfField('uitype');
        $this->assertNotEquals($uitype2, $uitype, 'Uitype was not changed in database');
    }

    /**
     * Testing button to top.
     */
    public function testButtonToTop()
    {
        $this->url('/index.php?module=Countries&parent=Settings&view=Index');
        $this->execute([
            'script' => '$("tr[data-id='.static::$id.'] button.to-bottom").click()',
            'args' => [],
        ]);

        $this->url('/index.php?module=Countries&parent=Settings&view=Index');
        $sortorderid = $this->getValueOfField('sortorderid');

        $this->url('/index.php?module=Countries&parent=Settings&view=Index');
        $this->execute([
            'script' => '$("tr[data-id='.static::$id.'] button.to-top").click()',
            'args' => [],
        ]);
        $this->url('/index.php?module=Countries&parent=Settings&view=Index');
        $sortorderid2 = $this->getValueOfField('sortorderid');
        $this->assertNotEquals($sortorderid2, $sortorderid, 'Field "sortorderid" was not changed in database');
    }

    /**
     * Testing button to bottom.
     */
    public function testButtonToBottom()
    {
        $this->url('/index.php?module=Countries&parent=Settings&view=Index');
        $this->execute([
            'script' => '$("tr[data-id='.static::$id.'] button.to-top").click()',
            'args' => [],
        ]);
        $this->url('/index.php?module=Countries&parent=Settings&view=Index');
        $sortorderid = $this->getValueOfField('sortorderid');

        $this->url('/index.php?module=Countries&parent=Settings&view=Index');
        $this->execute([
            'script' => '$("tr[data-id='.static::$id.'] button.to-bottom").click()',
            'args' => [],
        ]);

        $this->url('/index.php?module=Countries&parent=Settings&view=Index');
        $sortorderid2 = $this->getValueOfField('sortorderid');
        $this->assertNotEquals($sortorderid2, $sortorderid, 'Field "sortorderid" was not changed in database');
    }

    /**
     * Testing button all statuses.
     */
    public function testButtonAllStatuses()
    {
        $this->url('/index.php?module=Countries&parent=Settings&view=Index');
        $this->execute([
            'script' => '$("button.all-statuses").click()',
            'args' => [],
        ]);
        $this->url('/index.php?module=Countries&parent=Settings&view=Index');
        $exists0 = (new \App\Db\Query())->from('u_#__countries')->where(['status' => 0])->exists();
        $exists1 = (new \App\Db\Query())->from('u_#__countries')->where(['status' => 1])->exists();
        $this->assertNotEquals($exists0, $exists1, 'There are records with status equal 0 and equal 1');
    }

    /**
     * Get value of field as scalar.
     *
     * @param string $fieldName
     *
     * @return string
     */
    private function getValueOfField($fieldName)
    {
        return (new \App\Db\Query())->from('u_#__countries')->select($fieldName)->where(['id' => static::$id])->scalar();
    }
}
