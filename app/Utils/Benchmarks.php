<?php

/**
 * Benchmarks file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Utils;

/**
 * Benchmarks class.
 */
class Benchmarks
{
	/** @var string[] Math functions list */
	public static $mathFunctions = ['abs', 'acos', 'asin', 'atan', 'floor', 'exp', 'sin', 'tan', 'is_finite', 'is_nan', 'sqrt'];
	/** @var string[] Hash functions list */
	public static $hashAlgo = ['md4', 'md5', 'crc32', 'sha1', 'adler32', 'ripemd256', 'sha256', 'sha384', 'sha512'];
	/** @var string[] String functions list */
	public static $stringFunctions = ['addslashes', 'chunk_split', 'metaphone', 'strip_tags', 'strtoupper', 'strtolower', 'strrev', 'strlen', 'soundex', 'ord', 'ucfirst', 'ucwords', 'rtrim', 'trim', 'ltrim'];

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
			'math' => [
				'operations' => $mathOperations,
				'time' => (int) ($mathOperations / $mathTime),
			],
			'hash' => [
				'operations' => $hashOperations,
				'time' => (int) ($hashOperations / $hashTime),
			],
			'string' => [
				'operations' => $stringOperations,
				'time' => (int) ($stringOperations / $stringTime),
			],
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
			'read' => [
				'operations' => $readOperations,
				'time' => (int) ($readOperations / $readTime),
			],
			'write' => [
				'operations' => $writeOperations,
				'time' => (int) ($writeOperations / $writeTime),
			],
		];
	}

	/**
	 * Hard drive benchmark.
	 *
	 * @return array
	 */
	private static function hardDrive(): array
	{
		$countGroup = 50;
		$maxTime = 0.2;
		$read = $write = [];
		$dir = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'cache' . \DIRECTORY_SEPARATOR . 'speed' . \DIRECTORY_SEPARATOR;
		foreach ([1 => 207, 10 => 2050, 100 => 20500] as $key => $value) {
			$writeOperations = 0;
			$fileContent = str_repeat('12345', $value);
			$timeStart = microtime(true);
			$t = 0;
			while ((microtime(true) - $timeStart) < $maxTime) {
				$dirGroup = $dir . $key . \DIRECTORY_SEPARATOR . $t . \DIRECTORY_SEPARATOR;
				if (!is_dir($dirGroup)) {
					mkdir($dirGroup, 0755, true);
				}
				for ($i = 0; $i < $countGroup; ++$i) {
					file_put_contents("{$dirGroup}{$i}.txt", $fileContent);
					++$writeOperations;
				}
				$t += $countGroup;
			}
			$write[$key] = [
				'operations' => $writeOperations,
				'time' => (int) ($writeOperations / (microtime(true) - $timeStart)),
			];
		}
		foreach ([1, 10, 100] as $value) {
			$readOperations = 0;
			$timeStart = microtime(true);
			$dirGroup = $dir . $value . \DIRECTORY_SEPARATOR;
			for ($i = 0; $i < 4; ++$i) {
				foreach (new \DirectoryIterator($dirGroup) as $itemGroup) {
					if (!$itemGroup->isDot() && $itemGroup->isDir()) {
						foreach (new \DirectoryIterator($itemGroup->getPathname()) as $item) {
							if ($item->isFile()) {
								$fileContent = file_get_contents($item->getPathname());
								++$readOperations;
							}
						}
					}
				}
			}
			$read[$value] = [
				'operations' => $readOperations,
				'time' => (int) ($readOperations / (microtime(true) - $timeStart)),
			];
		}
		register_shutdown_function(function () {
			try {
				\vtlib\Functions::recurseDelete('cache/speed');
			} catch (\Throwable $e) {
				\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString());
				throw $e;
			}
		});
		return [
			'read' => $read,
			'write' => $write,
		];
	}

	/**
	 * RAM benchmark.
	 *
	 * @return array
	 */
	private static function db(): array
	{
		$benchmarkCountGroup = 20;
		$insertCountGroup = 5;
		$updateCountGroup = 5;
		$selectCountGroup = 10;
		$deleteCountGroup = 5;
		$maxTime = 0.2;
		$deleteOperations = $selectOperations = $updateOperations = $insertOperations = $benchmarkOperations = 0;
		$string = str_repeat('zxcvb', 20);
		$return = [];
		$db = \App\Db::getInstance();
		$schema = $db->getSchema();
		$dbCommand = $db->createCommand();
		$db->createCommand('set profiling=1;')->execute();
		if (!$db->getTableSchema('benchmark_temp_table')) {
			$db->createTable('benchmark_temp_table', [
				'id' => \yii\db\Schema::TYPE_UPK,
				'date' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_TIMESTAMP)->null(),
				'string' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_STRING, 200),
				'crmid' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_INTEGER, 10),
			]);
			$return['createTable'] = [
				'profile' => $db->createCommand('SHOW PROFILE')->queryAll(),
			];
		}
		$timeStart = microtime(true);
		while ((microtime(true) - $timeStart) < $maxTime) {
			for ($i = 0; $i < $benchmarkCountGroup; ++$i) {
				$db->createCommand('SELECT BENCHMARK(1000,1+1);')->execute();
				$benchmarkOperations += 1000;
			}
		}
		$return['benchmark'] = [
			'operations' => $benchmarkOperations,
			'time' => (int) ($benchmarkOperations / (microtime(true) - $timeStart)),
			'query' => "SELECT BENCHMARK({$benchmarkOperations},1+1);",
			'queryTime' => microtime(true) - $timeStart,
			'profile' => $db->createCommand('SHOW PROFILE')->queryAll(),
		];
		$timeStart = microtime(true);
		$j = 1;
		while ((microtime(true) - $timeStart) < $maxTime) {
			for ($i = 0; $i < $insertCountGroup; ++$i) {
				$dbCommand->insert('benchmark_temp_table', [
					'date' => date('Y-m-d H:i:s'),
					'string' => $string,
					'crmid' => $j,
				])->execute();
				++$insertOperations;
				++$j;
			}
		}
		$return['insert'] = [
			'operations' => $insertOperations,
			'time' => (int) ($insertOperations / (microtime(true) - $timeStart)),
			'profile' => $db->createCommand('SHOW PROFILE')->queryAll(),
		];
		$timeStart = microtime(true);
		$lastId = $insertOperations;
		while ((microtime(true) - $timeStart) < $maxTime) {
			for ($i = 0; $i < $selectCountGroup; ++$i) {
				(new \App\Db\Query())->select(new \yii\db\Expression('sql_no_cache *'))->from('benchmark_temp_table')->where(['id' => $lastId])->all();
				++$selectOperations;
				--$lastId;
				if (0 === $lastId) {
					$lastId = $insertOperations;
				}
			}
		}
		$return['select'] = [
			'operations' => $selectOperations,
			'time' => (int) ($selectOperations / (microtime(true) - $timeStart)),
			'profile' => $db->createCommand('SHOW PROFILE')->queryAll(),
		];
		$timeStart = microtime(true);
		$string = str_repeat('123', 20);
		$lastId = $insertOperations;
		while ((microtime(true) - $timeStart) < $maxTime) {
			for ($i = 0; $i < $updateCountGroup; ++$i) {
				$dbCommand->update('benchmark_temp_table', [
					'date' => date('Y-m-d H:i:s'),
					'string' => $string,
				], ['id' => $lastId])->execute();
				++$updateOperations;
				--$lastId;
				if (0 === $lastId) {
					$lastId = $insertOperations;
				}
			}
		}
		$return['update'] = [
			'operations' => $updateOperations,
			'time' => (int) ($updateOperations / (microtime(true) - $timeStart)),
			'profile' => $db->createCommand('SHOW PROFILE')->queryAll(),
		];
		$timeStart = microtime(true);
		$lastId = $insertOperations;
		while ((microtime(true) - $timeStart) < $maxTime) {
			for ($i = 0; $i < $deleteCountGroup; ++$i) {
				$dbCommand->delete('benchmark_temp_table', ['crmid' => $lastId])->execute();
				++$deleteOperations;
				--$lastId;
				if (0 === $lastId) {
					$lastId = $insertOperations;
				}
			}
		}
		$return['delete'] = [
			'operations' => $deleteOperations,
			'time' => (int) ($deleteOperations / (microtime(true) - $timeStart)),
			'profile' => $db->createCommand('SHOW PROFILE')->queryAll(),
		];
		$dbCommand->dropTable('benchmark_temp_table')->execute();
		$return['dropTable'] = [
			'profile' => $db->createCommand('SHOW PROFILE')->queryAll(),
		];
		$db->createCommand('set profiling=0;')->execute();
		return $return;
	}

	public static function all()
	{
		return [
			'cpu' => self::cpu(),
			'ram' => self::ram(),
			'hardDrive' => self::hardDrive(),
			'db' => self::db(),
		];
	}
}
