<?php
// PHP Upload Script for CKEditor:  http://coursesweb.net/

include_once 'conf/config.php';

$iConf = &$CONFIG['img'];
$aConf = &$CONFIG['audio'];
$vConf = &$CONFIG['video'];
define('RENAME', $CONFIG['rename']);

$response = false;


if (isset($_FILES['upload']) && strlen($_FILES['upload']['name']) > 1) {
	// Gets filename without extension
	$fileName = preg_replace('/\.(.+?)$/i', '', basename($_FILES['upload']['name']));
	// Gets protocol and host name to send the absolute image path to CKEditor
	$protocol = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
	$site = $protocol . $_SERVER['SERVER_NAME'] . '/';
	$sepext = explode('.', strtolower($_FILES['upload']['name']));
	// Gets extension
	$type = end($sepext);
	$fileType = false;
	
	foreach ($CONFIG as $cType => $conf) {
		if (in_array($cType, $CONFIG['ftypes']))
		{
			if (in_array($type, $conf['type'])) {
				$fileType = $cType;
				$uploadDir = trim($conf['dir'], '/') . '/';
				break;
			}
		}
	}
	
	switch ($fileType) {
		default:
			$response = 'The file: ' . $_FILES['upload']['name'] . ' has not the allowed extension type.';
			break;
		case 'img':
			// Image width and height
			list($width, $height) = getimagesize($_FILES['upload']['tmp_name']);
			
			if (isset($width) && isset($height)) {
				if ($width > $iConf['maxwidth'] || $height > $iConf['maxheight']) {
					$response = '\\n Width x Height = ' . $width . ' x ' . $height . ' \\n '
					. 'The maximum Width x Height must be: ' . $iConf['maxwidth'] . ' x ' . $iConf['maxheight'];
				}
				if ($width < $iConf['minwidth'] || $height < $iConf['minheight']) {
					$response = '\\n Width x Height = ' . $width . ' x ' . $height . '\\n '
					. 'The minimum Width x Height must be: ' . $iConf['minwidth'] . ' x ' . $iConf['minheight'];
				}
				if ($_FILES['upload']['size'] > $iConf['maxsize'] * 1000) {
					$response = '\\n Maximum file size must be: ' . $iConf['maxsize'] . ' KB.';
				}
			}
			break;
		case 'audio':
			if ($_FILES['upload']['size'] > $aConf['maxsize'] * 1000) {
				$response = '\\n Maximum file size must be: ' . $aConf['maxsize'] . ' KB.';
			}
			break;
		case 'video':
			if ($_FILES['upload']['size'] > $vConf['maxsize'] * 1000) {
				$response = '\\n Maximum file size must be: ' . $vConf['maxsize'] . ' KB.';
			}
			break;
	}
	
	$fullUploadDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $uploadDir;
	$fName = setFName($fullUploadDir, $fileName, ".$type", 0);
	// Full file path
	$uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $uploadDir . $fName;
	// If there is no errors no errors, upload the image, else, output the errors
	if (! $response) {
		if (move_uploaded_file($_FILES['upload']['tmp_name'], $uploadPath)) {
			$CKEditorFuncNum = $_GET['CKEditorFuncNum'];
			$url = $site . $uploadDir . $fName;
			$msg = $fileName . '.' . $type . ' successfully uploaded: \\n-';
			$msg.= 'Size: ' . number_format($_FILES['upload']['size'] / 1024, 2, '.', '') . ' KB';
			
			switch ($fileType) {
				case 'img':
					$response = "window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')";
					break;
				case 'audio':
					$response = 'var cke_ob = window.parent.CKEDITOR;'
					. 'for(var ckid in cke_ob.instances)'
					. '{if(cke_ob.instances[ckid].focusManager.hasFocus) break;}'
					. 'cke_ob.instances[ckid].insertHtml'
					. '(\'<div><audio src="' . $url . '" controls></audio></div><p></p>\', \'unfiltered_html\');'
					. 'alert("' . $msg . '");'
					. 'var dialog = cke_ob.dialog.getCurrent();'
					. 'dialog.hide()';
					break;
				case 'video':
					$response = 'var cke_ob = window.parent.CKEDITOR;'
					. 'for(var ckid in cke_ob.instances)'
					. '{if(cke_ob.instances[ckid].focusManager.hasFocus) break;}'
					. 'cke_ob.instances[ckid].insertHtml'
					. '(\'<div><video src="' . $url . '" class="' . $vConf['tagclass'] . '" controls></video></div><p></p>\', \'unfiltered_html\');'
					. 'alert("' . $msg . '");'
					. 'var dialog = cke_ob.dialog.getCurrent();'
					. 'dialog.hide()';
					break;
			} 
		} else {
			$response = 'alert("Unable to upload the file")';
		}
	} else {
		$response = 'alert("' . $response . '")';
	}
}

/**
 * Sets filename; if file exists, and RENAME_F is 1, set "img_name_I"
 * @param type $p directory path
 * @param type $fn filename to check
 * @param type $ex extension
 * @param type $i index to rename
 * @param type $fileName filename
 * @return string filename with extension
 */
function setFName($p, $fn, $ex, $i)
{
	if (RENAME == 1 && file_exists($p . $fn . $ex)) {
		return setFName($p, $fn . '_' . ($i + 1), $ex, ($i + 1));
	} else {
		return $fn . $ex;
	}
}

@header('Content-type: text/html; charset=utf-8');
echo '<script type="text/javascript">' . $response . ';</script>';
