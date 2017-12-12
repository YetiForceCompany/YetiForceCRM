<?php

/**
 * List view test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Wojciech Bruggemann <w.bruggemann@yetiforce.com>
 */
class Gui_Countries extends \Tests\GuiBase
{

	protected static $id;

	/**
	 * {@inheritDoc}
	 */
	public function setUp()
	{
		parent::setUp();

		$row = (new \App\Db\Query())->from('u_#__countries')->one();
		self::$id = $row['id'];
	}

	/**
	 * Testing view index
	 */
	public function testIndex()
	{
		$this->url('/index.php?module=Countries&parent=Settings&view=Index');
		$this->assertEquals('Countries', $this->byId('module')->value(), 'There is not a correct module');
		$this->assertEquals('Index', $this->byId('view')->value(), 'There is not a correct view');
		$this->assertEquals('Settings', $this->byId('parent')->value(), 'There is not a correct parent');
	}

	/**
	 * Testing button status
	 */
	public function testButtonStatus()
	{
		$status = $this->getValueOfField(self::$id, 'status');

		$this->url('/index.php?module=Countries&parent=Settings&view=Index');
		$this->execute([
			'script' => '$("tr[data-id=' . self::$id . '] button.status").click()',
			'args' => [],
		]);
		$this->url('/index.php?module=Countries&parent=Settings&view=Index');

		$status2 = $this->getValueOfField(self::$id, 'status');
		$this->assertNotEquals($status2, $status, 'Status was not changed in database');
	}

	/**
	 * Testing button phone
	 */
	public function testButtonPhone()
	{
		$phone = $this->getValueOfField(self::$id, 'phone');

		$this->url('/index.php?module=Countries&parent=Settings&view=Index');
		$this->execute([
			'script' => '$("tr[data-id=' . self::$id . '] button.phone").click()',
			'args' => [],
		]);
		$this->url('/index.php?module=Countries&parent=Settings&view=Index');

		$phone2 = $this->getValueOfField(self::$id, 'phone');
		$this->assertNotEquals($phone2, $phone, 'Phone was not changed in database');
	}

	/**
	 * Testing button uitype
	 */
	public function testButtonUitype()
	{
		$uitype = $this->getValueOfField(self::$id, 'uitype');

		$this->url('/index.php?module=Countries&parent=Settings&view=Index');
		$this->execute([
			'script' => '$("tr[data-id=' . self::$id . '] button.uitype").click()',
			'args' => [],
		]);
		$this->url('/index.php?module=Countries&parent=Settings&view=Index');

		$uitype2 = $this->getValueOfField(self::$id, 'uitype');
		$this->assertNotEquals($uitype2, $uitype, 'Uitype was not changed in database');
	}

	/**
	 * Testing button to top
	 */
	public function testButtonToTop()
	{
		$this->url('/index.php?module=Countries&parent=Settings&view=Index');
		$this->execute([
			'script' => '$("tr[data-id=' . self::$id . '] button.to-bottom").click()',
			'args' => [],
		]);
		$this->url('/index.php?module=Countries&parent=Settings&view=Index');

		$sortorderid = $this->getValueOfField(self::$id, 'sortorderid');

		$this->url('/index.php?module=Countries&parent=Settings&view=Index');
		$this->execute([
			'script' => '$("tr[data-id=' . self::$id . '] button.to-top").click()',
			'args' => [],
		]);
		$this->url('/index.php?module=Countries&parent=Settings&view=Index');

		$sortorderid2 = $this->getValueOfField(self::$id, 'sortorderid');

		$this->assertNotEquals($sortorderid2, $sortorderid, 'Field "sortorderid" was not changed in database');
	}

	/**
	 * Testing button to bottom
	 */
	public function testButtonToBottom()
	{
		$this->url('/index.php?module=Countries&parent=Settings&view=Index');
		$this->execute([
			'script' => '$("tr[data-id=' . self::$id . '] button.to-top").click()',
			'args' => [],
		]);
		$this->url('/index.php?module=Countries&parent=Settings&view=Index');

		$sortorderid = $this->getValueOfField(self::$id, 'sortorderid');

		$this->url('/index.php?module=Countries&parent=Settings&view=Index');
		$this->execute([
			'script' => '$("tr[data-id=' . self::$id . '] button.to-bottom").click()',
			'args' => [],
		]);
		$this->url('/index.php?module=Countries&parent=Settings&view=Index');

		$sortorderid2 = $this->getValueOfField(self::$id, 'sortorderid');

		$this->assertNotEquals($sortorderid2, $sortorderid, 'Field "sortorderid" was not changed in database');
	}

	/**
	 * Testing button all statuses
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
	 * Get value of field as scalar
	 * @param int $id
	 * @param string $fieldName
	 * @return array
	 */
	private function getValueOfField($id, $fieldName)
	{
		return (new \App\Db\Query())->from('u_#__countries')->select($fieldName)->where(['id' => $id])->scalar();
	}
}
