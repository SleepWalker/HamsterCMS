<?php
/**
 * Module Migration manager
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    ext.yii-module-migration
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

// namespace ymm;
// use \Yii;

class MigrationManager extends CComponent 
{
    /**
     * @var string $migrationAlias the alias to directory with migrations (relative to package alias)
     */
    public $migrationAlias = 'migrations';

    public static $tableName = '{{module_migration}}';

    protected $_packages = array();

    public function __construct() 
    {
        $this->ensureTable();
    }

    /**
     * Migrates all the registered packages
     * @return boolean whether it was successfull
     */
    public function migrate()
    {
        foreach ($this->packages as $package) {
            $this->migratePackage($package);
        }
    }

    /**
     * Runs all migrations within package migration path
     * @param  string $package package alias
     * @return boolean          whether migration was successfull
     */
    public function migratePackage($package) {
        $path = Yii::getPathOfAlias($package);
        $files = glob($path.'/*.php');

        foreach ($files as $file) {
            $class = basename(str_replace('.php', '', $file));

            $migration = $this->instantinateMigration($class, $package);

            if(!$this->runMigration($migration, $package)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Runs migration
     * @param  string $class class name of migration
     * @param  string $package package name (yii alias of migrations directory)
     * @return boolean true if migrated successfully
     */
    public function runMigration($migration, $package)
    {
        if(!$migration instanceof CDbMigration) {
            throw new MDMException(get_class($migration).' class should extend CDbMigration');
        }
        
        $class = get_class($migration);

        $info = $this->getInfo($class);

        if(!$info) {
            throw new MDMException("Bad Class Name. Class name should have the following format mX_X_X_comment");
        }

        $canApply = $this->db->createCommand()
            ->select('count(*)')
            ->from(self::$tableName)
            ->where('package=:package AND (version=:v OR major > :mj OR minor > :m OR patch > :p)', array(
                ':package' => $package,
                ':v' => $info->version,
                ':mj' => $info->major,
                ':m' => $info->minor,
                ':p' => $info->patch,
                ))
            ->queryScalar() == '0';
        if(!$canApply) {
            return false; // this migration is already applied or it version < that current 
        }

        if(($success = $migration->up())) {
            $this->db->createCommand()->insert(self::$tableName, array(
                'package' => $package,
                'version' => $info->version,
                'major' => $info->major,
                'minor' => $info->minor,
                'patch' => $info->patch,
                'comment' => $info->comment,
                ));

            return true;
        }

        return false;
    }

    /**
     * Registers package to check for migrations
     * @param  string $package  yii alias of directory containing migration files
     */
    public function registerPackage($package)
    {
        if(!$this->isPackageRegistered($package)) {
            array_push($this->_packages, $package);
        }

        return $this;
    }

    /**
     * Registers packages array
     * @param  array $packages  array of yii aliases of directoryies containing migration files
     * @see  MigrationManager::registerPackage()
     */
    public function registerPackages($packages = array())
    {
        foreach ($packages as $package) {
            $this->registerPackage($package);
        }

        return $this;
    }

    public function getPackages()
    {
        return $this->_packages;
    }

    /**
     * @param  string $package  yii alias of directory containing migration files
     * @return boolean wheter the package is registered
     */
    public function isPackageRegistered($package)
    {
        return in_array($package, $this->_packages);
    }

    /**
     * Returns an object with information about migration by its $className
     * @param  string $className the name of the migration class
     * @return StdObject            object with fields version, major, minor, patch, comment
     */
    public function getInfo($className)
    {
        preg_match('/^.(\d+)_(\d+)_(\d+)_(.+)/', $className, $matches);
        if(empty($matches)) {
            return null;
        }

        $version = implode('.', array_slice($matches, 1, 3));

        return (object)array(
            'version' => $version,
            'major' => $matches[1],
            'minor' => $matches[2],
            'patch' => $matches[3],
            'comment' => $matches[4],
            );
    }

    /**
     * Parses the version from class name
     * @param  string $className the name of the migration class
     * @return string            version string
     */
    public function getVersion($className)
    {
        return $this->getInfo($className)->version;
    }

    /**
     * Parses the migration comment from the class name
     * @param  string $className the name of the migration class
     * @return string            comment string
     */
    public function getComment($className)
    {
        preg_match('/^.(\d+)_(\d+)_(\d+)_(.+)/', $className, $matches);
        return end($matches);
    }

    public function getQueue()
    {
        return array(
            'applied' => array(),
            'pending' => array(),
            'skipped' => array(),
        );
    }

    /**
     * Instantiates migration class and returns migration object
     * @param  string $class the class name of migration
     * @param  string $package yii alias for importing class
     * @return CDbMigration        migration instance
     */
    public function instantinateMigration($class, $package = false)
    {
        if($package && !class_exists($class, false)) {
            Yii::import($this->getMigrationAlias($package).'.*');
        }

        try {
            if(!is_subclass_of($class, 'CDbMigration')) { // allow only CDbMigration children
                return null;
            }
            $migration = new $class();
        } catch(Exception $e) {
            if(!class_exists($class, false)) {
                return null;
            }
            throw $e;
        }

        $migration->setDbConnection($this->db);

        return $migration;
    }

    /**
     * Returns alias to directory that holds migration for the cpecified package
     * @param  string $package package alias
     * @return string          migration alias
     */
    public function getMigrationAlias($package)
    {
        return $package.(!empty($this->migrationAlias) ? '.'.$this->migrationAlias : '');
    }

    public function getDb()
    {
        return Yii::app()->db;
    }

    /**
     * Ensures that we have the table for logging the applied migrations
     */
    protected function ensureTable() 
    {
        if(!$this->db->schema->getTable(self::$tableName)) {
            $this->db->createCommand()->createTable(self::$tableName, array(
                'id' => 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'package' => 'VARCHAR(255) NOT NULL',
                'version' => 'VARCHAR(255) NOT NULL',
                'major' => 'INT NOT NULL',
                'minor' => 'INT NOT NULL',
                'patch' => 'INT NOT NULL',
                'comment' => 'VARCHAR(255) NOT NULL',
                'date' => 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP',
            ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

            $this->db->createCommand()->createIndex('unique_migrations', self::$tableName, 'package,version', true);
        }
    }
}

class MDMException extends CException {}