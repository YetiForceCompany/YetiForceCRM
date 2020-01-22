<?php

/**
 * Benchmarks file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Utils;

/**
 * Benchmarks class.
 */
class Benchmarks
{
	public static $mathFunctions = ['abs', 'acos', 'asin', 'atan', 'bindec', 'floor', 'exp', 'sin', 'tan', 'pi', 'is_finite', 'is_nan', 'sqrt'];
	public static $hashAlgo = ['md4', 'md5', 'crc32', 'sha1', 'adler32', 'ripemd256', 'sha256', 'sha384', 'sha512'];
	public static $stringFunctions = ['addslashes', 'chunk_split', 'metaphone', 'strip_tags', 'strtoupper', 'strtolower', 'strrev', 'strlen', 'soundex', 'ord', 'ucfirst', 'ucwords', 'rtrim', 'trim', 'ltrim'];

	private static $methods = [
		'cpu', 'ram', 'hdd', 'db'
	];

	/**
	 * CPU benchmark.
	 *
	 * @return array
	 */
	private static function cpu(): array
	{
		$mathFunctions = self::$mathFunctions;
		$mathCountGroup = 5000;
		$mathMaxTime = 0.3;

		$hashAlgo = self::$hashAlgo;
		$hashCountGroup = 1000;
		$hashMaxTime = 0.3;

		$stringFunctions = self::$stringFunctions;
		$stringCountGroup = 1000;
		$stringMaxTime = 0.3;

		$stringOperations = $hashOperations = $mathOperations = 0;
		$timeStart = microtime(true);
		while ((microtime(true) - $timeStart) < $mathMaxTime) {
			$t = 0;
			for ($i = $t; $i < $mathCountGroup; ++$i) {
				foreach ($mathFunctions as $function) {
					$function($i);
					++$mathOperations;
				}
			}
			$t += $mathCountGroup;
		}
		$mathTime = microtime(true) - $timeStart;
		$timeStart = microtime(true);
		while ((microtime(true) - $timeStart) < $hashMaxTime) {
			$t = 0;
			for ($i = $t; $i < $hashCountGroup; ++$i) {
				foreach ($hashAlgo as $algo) {
					hash($algo, "$i - $i | $i");
					++$hashOperations;
				}
			}
			$t += $mathCountGroup;
		}
		$hashTime = microtime(true) - $timeStart;
		$timeStart = microtime(true);
		while ((microtime(true) - $timeStart) < $stringMaxTime) {
			$t = 0;
			for ($i = $t; $i < $stringCountGroup; ++$i) {
				foreach ($stringFunctions as $function) {
					$function("$i - $i | $i");
					++$stringOperations;
				}
			}
			$t += $mathCountGroup;
		}
		$stringTime = microtime(true) - $timeStart;
		return [
			'math' => (int) ($mathOperations / $mathTime),
			'hash' => (int) ($hashOperations / $hashTime),
			'string' => (int) ($stringOperations / $stringTime),
		];
	}

	/**
	 * RAM benchmark.
	 *
	 * @return array
	 */
	private static function ram(): array
	{
		$mathCountGroup = 1000;
		$writeTime = $readTime = $readOperations = $writeOperations = 0;
		for ($i = 0; $i < 5; ++$i) {
			$test = [];
			$timeStart = microtime(true);
			while ((microtime(true) - $timeStart) < 0.05) {
				for ($j = 0; $j < $mathCountGroup; ++$j) {
					$test[] = [[[$j]]];
					++$writeOperations;
				}
			}
			$writeTime += (microtime(true) - $timeStart);

			$timeStart = microtime(true);
			while ((microtime(true) - $timeStart) < 0.05) {
				$t = 0;
				for ($j = $t; $j < $mathCountGroup; ++$j) {
					$test[$j];
					++$readOperations;
				}
				$t += $mathCountGroup;
			}
			$readTime += (microtime(true) - $timeStart);
		}

		return [
			'read' => (int) ($readOperations / $readTime),
			'write' => (int) ($writeOperations / $writeTime),
		];
	}

	public static function all()
	{
		return [
			'cpu' => self::cpu(),
			'ram' => self::ram(),
		];
	}

	/**
	 * Test server speed.
	 *
	 * @return array
	 */
	public static function test()
	{
		$dir = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'cache' . \DIRECTORY_SEPARATOR . 'speed' . \DIRECTORY_SEPARATOR;
		if (!is_dir($dir)) {
			mkdir($dir, 0755);
		}
		$testStartTime = microtime(true);
		$filesWrite = 0;
		while ((microtime(true) - $testStartTime) < 1) {
			file_put_contents("{$dir}{$testStartTime}{$filesWrite}.txt", $testStartTime);
			++$filesWrite;
		}
		$iterator = new \DirectoryIterator($dir);
		$readS = microtime(true);
		foreach ($iterator as $item) {
			if ($item->isFile()) {
				file_get_contents($item->getPathname());
			}
		}
		$filesRead = $filesWrite / (microtime(true) - $readS);
		$testStartTime = microtime(true);
		\vtlib\Functions::recurseDelete('cache/speed');
		$dbs = microtime(true);
		\App\Db::getInstance()->createCommand('SELECT BENCHMARK(1000000,1+1);')->execute();
		$dbe = microtime(true);
		return [
			'FilesRead' => (int) $filesRead,
			'FilesWrite' => $filesWrite,
			'DB' => (int) (1000000 / ($dbe - $dbs))
		];
	}
}
