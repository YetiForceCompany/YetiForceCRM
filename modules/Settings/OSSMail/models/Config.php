<?php
/**
 * OSSMail Config Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_OSSMail_Config_Model extends App\Base
{
	/**
	 * Path to file.
	 */
	const FILENAME = 'config/modules/OSSMail.php';

	/**
	 * function to get clean instance.
	 *
	 * @return \static
	 */
	public static function getCleanIntance()
	{
		return new static();
	}

	/**
	 * Function to get instance with configuration.
	 *
	 * @return \static
	 */
	public static function getInstance()
	{
		$instance = static::getCleanIntance();
		include 'config/modules/OSSMail.php';
		foreach ($config as $key => $value) {
			if ($key === 'skin_logo') {
				$instance->set($key, $value['*']);
			} else {
				$instance->set($key, $value);
			}
		}

		return $instance;
	}

	/**
	 * Return information about fields in form.
	 *
	 * @return array
	 */
	public function getForm()
	{
		return [
			'product_name' => ['label' => 'LBL_RC_product_name', 'fieldType' => 'text', 'required' => 1],
			'validate_cert' => ['label' => 'LBL_RC_validate_cert', 'fieldType' => 'checkbox', 'required' => 0],
			'imap_open_add_connection_type' => ['label' => 'LBL_RC_imap_open_add_connection_type', 'fieldType' => 'checkbox', 'required' => 0],
			'default_host' => ['label' => 'LBL_RC_default_host', 'fieldType' => 'multipicklist', 'required' => 1],
			'default_port' => ['label' => 'LBL_RC_default_port', 'fieldType' => 'int', 'required' => 1],
			'smtp_server' => ['label' => 'LBL_RC_smtp_server', 'fieldType' => 'text', 'required' => 1],
			'smtp_user' => ['label' => 'LBL_RC_smtp_user', 'fieldType' => 'text', 'required' => 1],
			'smtp_pass' => ['label' => 'LBL_RC_smtp_pass', 'fieldType' => 'text', 'required' => 1],
			'smtp_port' => ['label' => 'LBL_RC_smtp_port', 'fieldType' => 'int', 'required' => 1],
			'language' => ['label' => 'LBL_RC_language', 'fieldType' => 'picklist', 'required' => 1, 'value' => ['ar_SA', 'az_AZ', 'be_BE', 'bg_BG', 'bn_BD', 'bs_BA', 'ca_ES', 'cs_CZ', 'cy_GB', 'da_DK', 'de_CH', 'de_DE', 'el_GR', 'en_CA', 'en_GB', 'en_US', 'es_419', 'es_AR', 'es_ES', 'et_EE', 'eu_ES', 'fa_AF', 'fa_IR', 'fi_FI', 'fr_FR', 'fy_NL', 'ga_IE', 'gl_ES', 'he_IL', 'hi_IN', 'hr_HR', 'hu_HU', 'hy_AM', 'id_ID', 'is_IS', 'it_IT', 'ja_JP', 'ka_GE', 'km_KH', 'ko_KR', 'lb_LU', 'lt_LT', 'lv_LV', 'mk_MK', 'ml_IN', 'mr_IN', 'ms_MY', 'nb_NO', 'ne_NP', 'nl_BE', 'nl_NL', 'nn_NO', 'pl_PL', 'pt_BR', 'pt_PT', 'ro_RO', 'ru_RU', 'si_LK', 'sk_SK', 'sl_SI', 'sq_AL', 'sr_CS', 'sv_SE', 'ta_IN', 'th_TH', 'tr_TR', 'uk_UA', 'ur_PK', 'vi_VN', 'zh_CN', 'zh_TW']],
			'username_domain' => ['label' => 'LBL_RC_username_domain', 'fieldType' => 'text', 'required' => 0],
			'skin_logo' => ['label' => 'LBL_RC_skin_logo', 'fieldType' => 'text', 'required' => 1],
			'ip_check' => ['label' => 'LBL_RC_ip_check', 'fieldType' => 'checkbox', 'required' => 0],
			'enable_spellcheck' => ['label' => 'LBL_RC_enable_spellcheck', 'fieldType' => 'checkbox', 'required' => 0],
			'identities_level' => ['label' => 'LBL_RC_identities_level', 'fieldType' => 'picklist', 'required' => 1, 'value' => [0, 1, 2, 3, 4]],
			'session_lifetime' => ['label' => 'LBL_RC_session_lifetime', 'fieldType' => 'int', 'required' => 1],
		];
	}

	/**
	 * Set config params.
	 */
	public function save()
	{
		$fileContent = file_get_contents(static::FILENAME);
		$fields = static::getForm();
		$param = $this->getData();
		foreach ($param as $fieldName => $fieldValue) {
			if (!isset($fields[$fieldName])) {
				continue;
			}
			$type = $fields[$fieldName]['fieldType'];
			if ($type == 'multipicklist') {
				if (!is_array($fieldValue)) {
					$fieldValue = [$fieldValue];
				}
				$saveValue = [];
				foreach ($fieldValue as $value) {
					$saveValue[$value] = $value;
				}
				$fieldValue = $saveValue;
			} elseif ($fieldName === 'skin_logo') {
				$fieldValue = ['*' => $fieldValue];
			}
			$replacement = sprintf("\$config['%s'] = %s;", $fieldName, App\Utils::varExport($fieldValue));
			$fileContent = preg_replace('/(\$config\[\'' . $fieldName . '\'\])[\s]+=([^\n]+);/', $replacement, $fileContent);
		}
		file_put_contents(static::FILENAME, $fileContent);
		\App\Db::getInstance()->createCommand()->update('roundcube_users', ['language' => $param['language']])->execute();
	}
}
