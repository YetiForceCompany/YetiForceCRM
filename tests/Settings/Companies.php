<?php

/**
 * Companies test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class Companies extends \Tests\Base
{
	/**
	 * Companies id.
	 */
	private static $id;

	/**
	 * Testing add companies creation.
	 */
	public function testAddCompanies()
	{
		$recordModel = new Settings_Companies_Record_Model();
		$recordModel->set('name', 'Name');
		$recordModel->set('short_name', 'Short name');
		$recordModel->set('default', 1);
		$recordModel->set('industry', 'Industry');
		$recordModel->set('street', 'Street');
		$recordModel->set('city', 'City');
		$recordModel->set('code', '00-000');
		$recordModel->set('state', 'State');
		$recordModel->set('country', 'Country');
		$recordModel->set('phone', '+48 00 000 00 00');
		$recordModel->set('fax', '+48 00 000 00 00');
		$recordModel->set('website', 'www.website.com');
		$recordModel->set('vatid', '000-000-00-00');
		$recordModel->set('id1', '001111112');
		$recordModel->set('id2', '0000111113');
		$recordModel->set('email', 'email@gmail.com');
		$recordModel->set('logo_login', 'logo_one.png');
		$recordModel->set('logo_login_height', '200');
		$recordModel->set('logo_main', 'logo_two.png');
		$recordModel->set('logo_main_height', '38');
		$recordModel->set('logo_mail', 'logo_three.png');
		$recordModel->set('logo_mail_height', '50');
		$recordModel->save();
		static::$id = $recordModel->getId();
		$this->assertNotNull(static::$id, 'Id is null');

		$row = (new \App\Db\Query())->from('s_#__companies')->where(['id' => static::$id])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertEquals($row['name'], 'Name');
		$this->assertEquals($row['short_name'], 'Short name');
		$this->assertEquals($row['default'], 1);
		$this->assertEquals($row['industry'], 'Industry');
		$this->assertEquals($row['street'], 'Street');
		$this->assertEquals($row['city'], 'City');
		$this->assertEquals($row['code'], '00-000');
		$this->assertEquals($row['state'], 'State');
		$this->assertEquals($row['country'], 'Country');
		$this->assertEquals($row['phone'], '+48 00 000 00 00');
		$this->assertEquals($row['fax'], '+48 00 000 00 00');
		$this->assertEquals($row['website'], 'www.website.com');
		$this->assertEquals($row['vatid'], '000-000-00-00');
		$this->assertEquals($row['id1'], '001111112');
		$this->assertEquals($row['id2'], '0000111113');
		$this->assertEquals($row['email'], 'email@gmail.com');
		$this->assertEquals($row['logo_login'], 'logo_one.png');
		$this->assertEquals($row['logo_login_height'], '200');
		$this->assertEquals($row['logo_main'], 'logo_two.png');
		$this->assertEquals($row['logo_main_height'], '38');
		$this->assertEquals($row['logo_mail'], 'logo_three.png');
		$this->assertEquals($row['logo_mail_height'], '50');
	}

	/**
	 * Testing edit companies creation.
	 */
	public function testEditCompanies()
	{
		$recordModel = Settings_Companies_Record_Model::getInstance(static::$id);
		$recordModel->set('name', 'Company');
		$recordModel->set('short_name', 'Short company');
		$recordModel->set('default', 0);
		$recordModel->set('industry', 'Ingenuity');
		$recordModel->set('street', 'Avenue');
		$recordModel->set('city', 'Town');
		$recordModel->set('code', '00-100');
		$recordModel->set('state', 'Condition');
		$recordModel->set('country', 'Land');
		$recordModel->set('phone', '+48 11 111 11 11');
		$recordModel->set('fax', '+48 11 111 11 11');
		$recordModel->set('website', 'www.website-site.com');
		$recordModel->set('vatid', '111-111-11-11');
		$recordModel->set('id1', '000000001');
		$recordModel->set('id2', '000000003');
		$recordModel->set('email', 'emailtwo@gmail.com');
		$recordModel->set('logo_login', 'logo_login.png');
		$recordModel->set('logo_login_height', '150');
		$recordModel->set('logo_main', 'logo_main.png');
		$recordModel->set('logo_main_height', '35');
		$recordModel->set('logo_mail', 'logo_mail.png');
		$recordModel->set('logo_mail_height', '48');
		$recordModel->save();
		static::$id = $recordModel->getId();

		$row = (new \App\Db\Query())->from('s_#__companies')->where(['id' => static::$id])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertEquals($row['name'], 'Company');
		$this->assertEquals($row['short_name'], 'Short company');
		$this->assertEquals($row['default'], 0);
		$this->assertEquals($row['industry'], 'Ingenuity');
		$this->assertEquals($row['street'], 'Avenue');
		$this->assertEquals($row['city'], 'Town');
		$this->assertEquals($row['code'], '00-100');
		$this->assertEquals($row['state'], 'Condition');
		$this->assertEquals($row['country'], 'Land');
		$this->assertEquals($row['phone'], '+48 11 111 11 11');
		$this->assertEquals($row['fax'], '+48 11 111 11 11');
		$this->assertEquals($row['website'], 'www.website-site.com');
		$this->assertEquals($row['vatid'], '111-111-11-11');
		$this->assertEquals($row['id1'], '000000001');
		$this->assertEquals($row['id2'], '000000003');
		$this->assertEquals($row['email'], 'emailtwo@gmail.com');
		$this->assertEquals($row['logo_login'], 'logo_login.png');
		$this->assertEquals($row['logo_login_height'], '150');
		$this->assertEquals($row['logo_main'], 'logo_main.png');
		$this->assertEquals($row['logo_main_height'], '35');
		$this->assertEquals($row['logo_mail'], 'logo_mail.png');
		$this->assertEquals($row['logo_mail_height'], '48');
	}

	/**
	 * Testing delete companies creation.
	 */
	public function testDeleteCompanies()
	{
		$recordModel = Settings_Companies_Record_Model::getInstance(static::$id);
		$recordModel->delete();
		$this->assertFalse((new \App\Db\Query())->from('s_#__companies')->where(['id' => static::$id])->exists());
	}
}
