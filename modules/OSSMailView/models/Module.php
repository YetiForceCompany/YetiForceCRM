<?php

/**
 * OSSMailView ListView model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailView_Module_Model extends Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public function getSettingLinks(): array
	{
		$settingsLinks = parent::getSettingLinks();
		$layoutEditorImagePath = Vtiger_Theme::getImagePath('LayoutEditor.gif');
		if ($menu = Settings_Vtiger_MenuItem_Model::getInstance('Mail View')) {
			$settingsLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_MODULE_CONFIGURATION',
				'linkurl' => 'index.php?module=OSSMailView&parent=Settings&view=index&block=' . $menu->get('blockid') . '&fieldid=' . $menu->get('fieldid'),
				'linkicon' => $layoutEditorImagePath,
			];
		}
		return $settingsLinks;
	}

	public function isPermitted($actionName)
	{
		if ('EditView' === $actionName || 'CreateView' === $actionName) {
			return false;
		}
		return $this->isActive() && \App\Privilege::isPermitted($this->getName(), $actionName);
	}

	public function getPreviewViewUrl($id)
	{
		return 'index.php?module=' . $this->get('name') . '&view=Preview&record=' . $id;
	}

	public function isQuickCreateSupported()
	{
		return false;
	}
}
