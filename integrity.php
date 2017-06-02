<?php

if('cli' !== php_sapi_name()) {
    echo 'This should only be called from console';
    die;
}

error_reporting(E_ERROR | E_PARSE);
require_once 'include/ConfigUtils.php';
require_once 'include/utils/utils.php';
require_once 'include/utils/CommonUtils.php';

require 'config/config.inc.php';

//simple cli-router

if(!isset($argv[1])) {
    echo "\nFollowing commands are available:\n";
    echo "check \t Checks if all tables, rows and columns are present in the database\n";
    echo "rebuild \t Rebuilds the cache\n";
    die;
}

switch($argv[1]) {

    case 'check':
        IntegrityCheck::getInstance()->check();
        break;
    case 'rebuild':
        IntegrityCheck::toggleBuildState();
        IntegrityCheck::getInstance()->setup();
        break;
    default:
        echo "No valid command has been given! \n";
        break;
}

class IntegrityCheck {

    /**
     * @var null | IntegrityCheck
     */
    protected static $instance = null;

    /**
     * Path to the cachefile
     *
     * @var string
     */
    protected $cachePath = 'cache/integrity/cache.php';

    /**
     * @var string
     */
    protected $logFile = 'cache/logs/integrity.log';

    /**
     * Holds the extracted data
     *
     * @var bool|array
     */
    protected $data = false;

    /**
     * Determines if the cache is loaded or created first
     *
     * @var bool
     */
    protected static $rebuild = false;

    /**
     * IntegrityCheck constructor.
     */
    protected function __construct() {
        if(!is_readable($this->cachePath) || static::$rebuild == true) {
            echo "Building the cache first, please be patient \n";
            $this->setup();
        } else {
            $this->data = unserialize(file_get_contents($this->cachePath));
            $this->tables = array_keys($this->data);
        }
    }

    public static function toggleBuildState() {
        static::$rebuild = (static::$rebuild === false) ? true : false;
    }

    /**
     * Creates the needed folder for the cache (might get deprecated soon)
     */
    public function setup() {
        if(!is_file($this->cachePath)) {
            @mkdir('cache/integrity');
        }
        $tables = $this->loadSchema();
        $this->cache($tables);
    }

    /**
     * Caches the schema
     *
     * @param array $tables
     */
    protected function cache($tables) {
        file_put_contents($this->cachePath, serialize($tables));
    }

    /**
     * Maps the scheme.sql file into an php array
     *
     * @return array
     */
    protected function loadSchema() {
        $sql = file_get_contents('install/install_schema/scheme.sql');
        $pattern = '/CREATE +.+?ENGINE/is'; //super greedy

        preg_match_all($pattern, $sql, $matches);

        $matches = $matches[0];

        $tables = [];
        foreach($matches as $match) {

            $lines = preg_split('/\R/', $match);
            $name = false;
            foreach($lines as $line) {
                $line = trim($line);
                if(!$name) {
                    $name = trim(str_replace(['CREATE TABLE', '`', '('], '',$line));
                    continue;
                }
                if(substr($line,0,1) != '`') {
                    continue;
                }
                $tables[$name][] = trim(str_replace('`', '', strstr($line, ' ', true)));
            }

        }
        $sql = null; //go easy on memory
        return $tables;
    }

    /**
     * Singleton implementation
     *
     * @return IntegrityCheck
     */
    public static function getInstance() {
        if(static::$instance === null) {
            static::$instance = new IntegrityCheck();
        }

        return static::$instance;
    }

    /**
     * Checks the database for a clean install
     *
     * @return bool
     */
    public function check() {

        if(!$this->data) {
            $this->setup();
        }

        $link = PearDatabase::getInstance();
        foreach($this->tables as $table) {

            echo "Checking table '$table' \n";

            $query = 'SHOW FIELDS FROM '.$table.';';
            $result = $link->pquery($query);
            if(!$result) {
                echo "Table $table is missing entirely.\n";
                file_put_contents($this->logFile, "Table $table is missing entirely.\n", FILE_APPEND);
                continue;
            }
            if($link->getRowCount($result) < count($this->data[$table])) { //if the count doesnt match look for the missing column
                $foundFields = [];
                while($fieldMeta = $link->fetch_array($result)) {
                    foreach($this->data[$table] as $field) {
                        if($field == $fieldMeta['Field']) {
                            $foundFields[] = $field;
                        }
                    }
                }
                foreach(array_diff($this->data[$table],$foundFields) as $lostColumn) {
                    echo "Column '$lostColumn' is missing from table $table.\n";
                    file_put_contents($this->logFile, "Column '$lostColumn' is missing from table $table.\n", FILE_APPEND);
                }
            }
        }

    }

}