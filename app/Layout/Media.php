<?php
/**
 * Media file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Layout;

/**
 * Media class.
 */
class Media
{
	/** @var string Media table name. */
	public const TABLE_NAME_MEDIA = 'u_#__file_upload';

	/**
	 * Images.
	 *
	 * @var array
	 */
	protected static $images;

	public static function getImages(): array
	{
		if (null === self::$images) {
			self::$images = [];
			$dataReader = (new \App\Db\Query())->from(static::TABLE_NAME_MEDIA)->where(['status' => 1, 'fieldname' => 'image'])->createCommand()->query();
			while ($row = $dataReader->read()) {
				$row['src'] = "{$row['path']}{$row['key']}.{$row['ext']}";
				self::$images[$row['key']] = $row;
			}
			$dataReader->close();
		}

		return self::$images;
	}

	/**
	 * Get image data.
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	public static function getImage(string $key): array
	{
		return self::getImages()[$key] ?? [];
	}

	/**
	 * Delete image file.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public static function removeImage(string $key): bool
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		return ($src = self::getImages()[$key]['src'] ?? '') && file_exists($src) && $dbCommand->delete(self::TABLE_NAME_MEDIA, ['key' => $key])->execute() && unlink($src);
	}

	/**
	 * Get image URL.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public static function getImageUrl(string $key): string
	{
		return self::getImages()[$key]['src'] ?? '';
	}
}
