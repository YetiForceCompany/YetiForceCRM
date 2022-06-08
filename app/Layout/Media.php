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
				$path = $row['path'];
				if (IS_PUBLIC_DIR && 0 === strpos($path, 'public_html/')) {
					$path = $path = substr($path, 12, \strlen($path));
				}
				$row['src'] = "{$path}{$row['key']}.{$row['ext']}";
				$row['relativePath'] = "{$row['path']}{$row['key']}.{$row['ext']}";
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
		return ($path = self::getImages()[$key]['relativePath'] ?? '') && file_exists($path) && $dbCommand->delete(self::TABLE_NAME_MEDIA, ['key' => $key])->execute() && unlink($path);
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
		$path = self::getImages()[$key]['src'] ?? '';
		if ($path && !file_exists(self::getImages()[$key]['relativePath'])) {
			$path = '';
		}

		return $path;
	}
}
