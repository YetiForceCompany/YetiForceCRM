<?php
/**
 * Record Class for PDF Settings
 * @package YetiForce.Helpers
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
chdir(dirname(__FILE__) . '/../../../..');

require_once('include/main/WebUI.php');
$request = new Vtiger_Request($_REQUEST);

$webUI = new Vtiger_WebUI();
$current_user = $webUI->getLogin();

$templateId = $request->get('template_id');
$newName = basename($_FILES['watermark']['name'][0]);
$newName = explode('.', $newName);
$newName = $templateId . '.' . end($newName);
$targetDir = 'layouts/vlayout/modules/Settings/PDF/resources/watermark_images/';
$targetFile = $targetDir . $newName;
$uploadOk = 1;
$imageFileType = pathinfo($targetFile, PATHINFO_EXTENSION);

// Check if image file is a actual image or fake image
$check = getimagesize($_FILES['watermark']['tmp_name'][0]);
if ($check !== false) {
	echo 'File is an image - ' . $check['mime'] . '.';
	$uploadOk = 1;
} else {
	echo 'File is not an image.';
	$uploadOk = 0;
}

// Check if file already exists
if (file_exists($targetFile) && $uploadOk) {
	echo 'Sorry, file already exists.';
	$uploadOk = 0;
}
// Check file size
if ($_FILES['watermark']['size'][0] > vglobal('upload_maxsize') && $uploadOk) {
	//echo 'Sorry, your file is too large.';
	$uploadOk = 0;
}
// Allow certain file formats
$allowedFormats = ['jpg', 'png', 'jpeg', 'gif'];
if ($uploadOk && !in_array($imageFileType, $allowedFormats)) {
	//echo 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.';
	$uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
	//echo 'Sorry, your file was not uploaded.';
	// if everything is ok, try to upload file
} else {
	$db = PearDatabase::getInstance();
	$query = 'SELECT `watermark_image` FROM `a_yf_pdf` WHERE `pdfid` = ? LIMIT 1;';
	$result = $db->pquery($query, [$templateId]);
	$watermarkImage = $db->getSingleValue($result);

	if (file_exists($watermarkImage)) {
		unlink($watermarkImage);
	}
	if (move_uploaded_file($_FILES['watermark']['tmp_name'][0], $targetFile)) {
		//echo 'The file '. basename( $_FILES['watermark']['name'][0]). ' has been uploaded.';
		$query = 'UPDATE `a_yf_pdf` SET `watermark_image` = ? WHERE `pdfid` = ? LIMIT 1;';
		$db = $db->pquery($query, [$targetFile, $templateId]);
	} else {
		//echo 'Sorry, there was an error uploading your file.';
	}
}
