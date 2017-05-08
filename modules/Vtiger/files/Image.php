<?php
/**
 * Image class to handle files
 * @package YetiForce.Files
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Image class to handle files
 */
class Vtiger_Image_File extends Vtiger_Basic_File
{

	/**
	 * Storage name
	 * @var string 
	 */
	public $storageName = 'MultiImage';

	/**
	 * File type
	 * @var string
	 */
	public $fileType = 'image';

	/**
	 * View image
	 * @param \App\Request $request
	 * @return string|boolean
	 * @throws \Exception\NoPermitted
	 */
	public function get(\App\Request $request)
	{
		$record = $request->get('attachment');
		if (empty($record)) {
			throw new \Exception\NoPermitted('Not Acceptable', 406);
		}
		$data = (new App\Db\Query())->from('u_#__attachments')->where(['attachmentid' => $record])->one();
		if ($data) {
			$path = $data['path'] . $data['attachmentid'] . '_' . $data['name'];
			$path = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $path;
			$file = App\Fields\File::loadFromPath($path);
			header('Content-Type: ' . $file->getMimeType());
			header("Content-Transfer-Encoding: binary");
			readfile($path);
		}
		return false;
	}
}
