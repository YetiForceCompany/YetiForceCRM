<?php
/**
 * Files Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Files Model Class.
 */
class Vtiger_Files_Model extends \App\Base
{
	const ATTACHMENT_ACTIVE = 1;
	const ATTACHMENT_INACTIVE = 0;

	/**
	 * Update attachments status.
	 *
	 * @param string $previousValue
	 * @param string $currentValue
	 * @param int    $id
	 * @param int    $fieldId
	 */
	public static function updateStatus($previousValue, $currentValue, $id = '', $fieldId = '')
	{
		$db = \App\Db::getInstance();
		if (!empty($currentValue)) {
			$currentValue = array_filter(explode(',', $currentValue));
			$data = ['status' => self::ATTACHMENT_ACTIVE];
			if ($id) {
				$data['crmid'] = $id;
			}
			if ($fieldId) {
				$data['fieldid'] = $fieldId;
			}
			$db->createCommand()->update('u_#__attachments', $data, ['attachmentid' => $currentValue])->execute();
		}
		if (!empty($previousValue)) {
			$previousValue = array_filter(explode(',', $previousValue));
			$diff = array_diff($previousValue, $currentValue);
			if ($diff) {
				self::getRidOfTrash(['attachmentid' => $diff]);
			}
		}
	}

	/**
	 * Deleting attachment from database and storage.
	 *
	 * @param array $condition
	 * @param int   $limit
	 */
	public static function getRidOfTrash($condition = [], $limit = false)
	{
		$db = \App\Db::getInstance();

		$query = (new \App\Db\Query())->select(['attachmentid', 'name', 'path'])->from('u_#__attachments');
		if ($condition) {
			$query->where($condition);
		} else {
			$query->where(['status' => self::ATTACHMENT_INACTIVE]);
			$query->andWhere(['<', 'createdtime', date('Y-m-d H:i:s', strtotime('-1 day'))]);
		}
		if ($limit) {
			$query->limit($limit);
		}

		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$db->createCommand()->delete('u_#__attachments', ['attachmentid' => $row['attachmentid']])->execute();
			$fileName = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $row['attachmentid'] . $row['attachmentid'] . '_' . $row['name'];
			if (file_exists($fileName)) {
				chmod($fileName, 0750);
				unlink($fileName);
			}
		}
		$dataReader->close();
	}

	/**
	 * Upload and save attachment.
	 *
	 * @param array  $fileDetails
	 * @param string $type
	 * @param string $storageName
	 *
	 * @return int|bool
	 */
	public static function uploadAndSave(array $fileDetails, $type = false, $storageName = false)
	{
		$db = \App\Db::getInstance();

		$fileInstance = \App\Fields\File::loadFromRequest($fileDetails);
		if (!$fileInstance->validate($type)) {
			return false;
		}
		$fileName = ltrim(App\Purifier::purify($fileDetails['name']));
		$filetype = $fileDetails['type'];
		$filetmp_name = $fileDetails['tmp_name'];

		$uploadFilePath = \App\Fields\File::initStorageFileDirectory($storageName);
		$db->createCommand()->insert('u_#__attachments', [
			'name' => $fileName,
			'type' => $filetype,
			'path' => $uploadFilePath,
			'createdtime' => date('Y-m-d H:i:s'),
		])->execute();
		$currentId = $db->getLastInsertID('u_#__attachments_attachmentid_seq');
		$uploadStatus = move_uploaded_file($filetmp_name, $uploadFilePath . $currentId);
		if ($uploadStatus) {
			return $currentId;
		} else {
			$db->createCommand()->delete('u_#__attachments', ['attachmentid' => $currentId])->execute();
			\App\Log::error("Moves an uploaded file to a new location failed: {$uploadFilePath}");

			return false;
		}
	}
}
