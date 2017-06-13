<?php
/**
 * Image class to handle files
 * @package YetiForce.Files
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
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
			$path = $data['path'] . $data['attachmentid'];
			$path = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $path;
			$file = App\Fields\File::loadFromPath($path);
			header('Content-Type: ' . $file->getMimeType());
			header("Content-Transfer-Encoding: binary");
			header('Pragma: public');
			header('Cache-Control: max-age=86400');
			header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
			readfile($path);
		}
		return false;
	}
}
