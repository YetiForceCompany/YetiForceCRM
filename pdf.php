<?php
// zmiana ścieżki do katalogu głównego
//chdir(dirname(__FILE__) . '/../..');    // jeśli np. plik znajduje się w folderze modułu /modules/xxxx/xxx.php

require_once('include/main/WebUI.php');
$request = new Vtiger_Request($_REQUEST, $_REQUEST);

//*** tak i tak - sprawdza czy użytkownik jest zalogowany, jeśli tak to bierze jego, jeśli nie to automatyucznie wrzuca admina
$webUI = new Vtiger_WebUI();
$current_user = $webUI->getLogin();

if ( $current_user == false ) {
    $user = new Users();
    $current_user = $user->retrieveCurrentUserInfoFromFile( 1 );
}
//***

$pdf = new Settings_PDF_mPDF_Model();
$pdf->setTemplateId(1);
$pdf->setRecordId(109);
$pdf->setModuleName('Contacts');

$template = Settings_PDF_Record_Model::getInstanceById(1);
$html = '';

if ($template->get('margin_chkbox') == 0) {
	$pdf->setMargins(
		$template->get('margin_top'),
		$template->get('margin_right'),
		$template->get('margin_bottom'),
		$template->get('margin_left')
	);
}

$pdf->setHeader('Header', $template->get('header_content'));
$pdf->setFooter('Footer', $template->get('footer_content'));
$html = $template->get('body_content');

//$pdf->loadHTML('<bookmark content="Start of the Document" /><div>Section 2 text</div>');
$pdf->loadHTML($html);
$pdf->output();

var_dump($template);
