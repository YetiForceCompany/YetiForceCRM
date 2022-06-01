<?php

/**
 * OSSMailView preview view class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailView_Preview_View extends Vtiger_Index_View
{
	const TYPE_COLORS = [
		0 => 'bgGreen',
		1 => 'bgDanger',
		2 => 'bgBlue',
	];

	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');

		$recordPermission = \App\Privilege::isPermitted($moduleName, 'DetailView', $recordId);
		if (!$recordPermission) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		return true;
	}

	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
	}

	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		$load = $request->get('noloadlibs');
		$recordModel = OSSMailView_Record_Model::getInstanceById($record, $moduleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('SHOW_FOOTER', false);
		$viewer->assign('FOOTER_SCRIPTS', $this->getFooterScripts($request));
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('NOLOADLIBS', $load);
		$viewer->assign('TO', explode(',', $recordModel->getDisplayValue('to_email')));
		$viewer->assign('CC', $recordModel->getDisplayValue('cc_email'));
		$viewer->assign('BCC', $recordModel->getDisplayValue('bcc_email'));
		if (\App\Utils::isHtml($recordModel->get('content'))) {
			$content = $recordModel->getDisplayValue('content', false, false, 'full');
		} else {
			$content = nl2br(\App\Layout::truncateHtml(\App\Purifier::purify($recordModel->get('content')), 'full'));
		}
		$firstLetterBg = self::TYPE_COLORS[$recordModel->get('type')];
		$firstLetter = strtoupper(App\TextUtils::textTruncate(trim(strip_tags($recordModel->getDisplayValue('from_email'))), 1, false));
		if ($recordModel->get('orginal_mail')) {
			$rblInstance = \App\Mail\Rbl::getInstance([]);
			$rblInstance->set('rawBody', $recordModel->get('orginal_mail'));
			$rblInstance->parse();
			if (($verifySender = $rblInstance->verifySender()) && !$verifySender['status']) {
				$firstLetter = '<span class="fas fa-exclamation-triangle text-danger" title="' . \App\Purifier::encodeHtml($verifySender['info']) . '"></span>';
				$firstLetterBg = 'bg-warning';
			}
		}
		$viewer->assign('CONTENT', $content);
		$viewer->assign('FIRSTLETTER', $firstLetter);
		$viewer->assign('FIRSTLETTERBG', $firstLetterBg);
		$viewer->assign('OWNER', $recordModel->getDisplayValue('assigned_user_id'));
		$viewer->assign('SENT', $recordModel->getDisplayValue('createdtime'));
		$viewer->assign('ATTACHMENTS', $recordModel->getAttachments());
		$viewer->assign('RECORD', $record);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('ISMODAL', $request->isAjax());
		$viewer->assign('SCRIPTS', $this->getModalScripts($request));
		$viewer->assign('SMODULENAME', $request->getByType('smodule'));
		$viewer->assign('SRECORD', $request->getInteger('srecord'));
		$viewer->view('preview.tpl', 'OSSMailView');
	}

	public function getModalScripts(App\Request $request)
	{
		return $this->checkAndConvertJsScripts([
			'~layouts/basic/modules/OSSMailView/resources/preview.js',
		]);
	}
}
