<?php
define('IS_PUBLIC_DIR', true);
$path_rel = 'storage/';
if (!isset($_GET['path'])) {
	http_response_code(404);
	echo 'Path not found';
	exit();
}
$path_real = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR);
$file_rel = $path_rel . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $_GET['path']);
$file_real = realpath($path_real . DIRECTORY_SEPARATOR . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $_GET['path']));
if (strpos($file_real, $path_real) !== 0) {
	http_response_code(404);
	echo 'File not found';
	exit();
}
if (file_exists($file_real)) {
	$auth = true;
	// Place for access checks
	if (!$auth) {
		http_response_code(403);
		echo 'Unauthorized';
		exit();
	}
	header('Content-Description: File Transfer');
	header('Content-Type: ' . mime_content_type($file_real));
	header('Content-Disposition: filename="' . basename($file_real) . '"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($file_real));
	readfile($file_real);
	exit;
} else {
	http_response_code(404);
	echo 'File not found';
	exit();
}