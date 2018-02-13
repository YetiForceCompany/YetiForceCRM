<?php
/**
 * A file archive, compressed with Zip.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Zip class.
 */
class Zip extends \ZipArchive
{
    /**
     * Files extension for extract.
     *
     * @var array
     */
    protected $onlyExtensions;

    /**
     * Illegal extensions for extract.
     *
     * @var array
     */
    protected $illegalExtensions;

    /**
     * Check files before unpacking.
     *
     * @var bool
     */
    protected $checkFiles = true;

    /**
     * Construct.
     */
    public function __construct($fileName = false, $options = [])
    {
        if ($fileName) {
            if (!file_exists($fileName) || !$this->open($fileName)) {
                throw new \App\Exceptions\AppException('Unable to open the zip file');
            }
            if (!$this->checkFreeSpace()) {
                throw new \App\Exceptions\AppException('The content of the zip file is too large');
            }
            foreach ($options as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Function to extract files.
     *
     * @param string $toDir Target directory
     *
     * @return string[] Unpacked files
     *
     * @throws \App\Exceptions\AppException
     */
    public function unzip($toDir)
    {
        $fileList = [];
        if (is_array($toDir)) {
            foreach ($toDir as $dirname => $target) {
                for ($i = 0; $i < $this->numFiles; ++$i) {
                    $path = $this->getNameIndex($i);
                    if (strpos($path, "{$dirname}/") !== 0 || ($this->checkFiles && $this->checkFile($path))) {
                        continue;
                    }
                    // Determine output filename (removing the $source prefix)
                    $file = $target.'/'.substr($path, strlen($dirname) + 1);
                    // Create the directories if necessary
                    $dir = dirname($file);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    $fileList[] = $path;
                    if (!$this->isDir($path)) {
                        // Read from Zip and write to disk
                        $fpr = $this->getStream($path);
                        $fpw = fopen($file, 'w');
                        while ($data = fread($fpr, 1024)) {
                            fwrite($fpw, $data);
                        }
                        fclose($fpr);
                        fclose($fpw);
                    }
                }
            }
        } else {
            if (!is_dir($toDir)) {
                throw new \App\Exceptions\AppException('Directory not found, and unable to create it');
            }
            if (!is_writable($toDir)) {
                throw new \App\Exceptions\AppException('No permissions to create files');
            }
            for ($i = 0; $i < $this->numFiles; ++$i) {
                $path = $this->getNameIndex($i);
                if ($this->checkFiles && $this->checkFile($path)) {
                    continue;
                }
                $fileList[] = $path;
            }
            $this->extractTo($toDir, $fileList);
        }
        $this->close();

        return $fileList;
    }

    /**
     * Check illegal characters.
     *
     * @param string $path
     *
     * @return bool
     */
    public function checkFile($path)
    {
        preg_match("[^\w\s\d\.\-_~,;:\[\]\(\]]", $path, $matches);
        if ($matches) {
            return true;
        }
        if (stripos($path, '../') === 0 || stripos($path, '..\\') === 0) {
            return true;
        }
        if (!$this->isDir($path)) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if (isset($this->onlyExtensions) && !in_array($extension, $this->onlyExtensions)) {
                return true;
            }
            if (isset($this->illegalExtensions) && in_array($extension, $this->illegalExtensions)) {
                return true;
            }
            $stat = $this->statName($path);
            $fileInstance = \App\Fields\File::loadFromInfo([
                    'content' => $this->getFromName($path),
                    'path' => $this->getLocalPath($path),
                    'name' => basename($path),
                    'size' => $stat['size'],
                    'validateAllCodeInjection' => true,
            ]);
            if (!$fileInstance->validate()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the file path is directory.
     *
     * @param string $filePath
     *
     * @return bool
     */
    public function isDir($filePath)
    {
        if (substr($filePath, -1, 1) === '/') {
            return true;
        }

        return false;
    }

    /**
     * Function to extract single file.
     *
     * @param string $compressedFileName
     * @param string $targetFileName
     *
     * @return bool
     */
    public function unzipFile($compressedFileName, $targetFileName)
    {
        return copy($this->getLocalPath($compressedFileName), $targetFileName);
    }

    /**
     * Get compressed file path.
     *
     * @param string $compressedFileName
     *
     * @return string
     */
    public function getLocalPath($compressedFileName)
    {
        return "zip://{$this->filename}#{$compressedFileName}";
    }

    /**
     * Check free disk space.
     *
     * @return bool
     */
    public function checkFreeSpace()
    {
        $df = disk_free_space(ROOT_DIRECTORY.DIRECTORY_SEPARATOR);
        $size = 0;
        for ($i = 0; $i < $this->numFiles; ++$i) {
            $stat = $this->statIndex($i);
            $size += $stat['size'];
        }

        return $df > $size;
    }
}
