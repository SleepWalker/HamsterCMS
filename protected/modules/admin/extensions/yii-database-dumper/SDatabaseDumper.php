<?php

/**
 * Creates DB dump.
 *
 * Usage:
 * <pre>
 *      Yii::import('ext.yii-database-dumper.SDatabaseDumper');
 *      $dumper = new SDatabaseDumper;
 *      // Get path to backup file
 *      $file = Yii::getPathOfAlias('webroot.protected.backups').DIRECTORY_SEPARATOR.'dump_'.date('Y-m-d_H_i_s').'.sql';
 *
 *      // Gzip dump
 *      if(function_exists('gzencode'))
 *          file_put_contents($file.'.gz', gzencode($dumper->getDump()));
 *      else
 *          file_put_contents($file, $dumper->getDump());
 * </pre>
 */
class SDatabaseDumper
{
    private $_db;

    public function __construct($db)
    {
        $this->setDbConnection($db);
    }

    /**
     * Dump all tables
     *
     * @param  array $options
     *     tableFilter => function filter function to select tables needed
     *       'tableFilter' => function ($tableName) {
     *           return preg_match('/page|block|content_list/', $tableName);
     *       }
     *
     * @return string sql structure and data
     */
    public function getDump($options = array())
    {
        ob_start();
        // что бы при восстановлении бекапа не вылазили ошибки №150 в mysql
        // добавляем строки, которые отключают проверку внешних ключей на время восстановления бекапа
        echo 'SET FOREIGN_KEY_CHECKS = 0;' . PHP_EOL;
        foreach ($this->getTables() as $tableName => $val) {
            if (isset($options['tableFilter']) && is_callable($options['tableFilter'])) {
                if (!$options['tableFilter']($tableName)) {
                    continue;
                }
            }
            $this->dumpTable($tableName);
        }
        echo 'SET FOREIGN_KEY_CHECKS = 1;' . PHP_EOL;
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    /**
     * Create table dump
     * @param $tableName
     * @return mixed
     */
    public function dumpTable($tableName)
    {
        $db = $this->getDbConnection();
        $pdo = $db->getPdoInstance();
        echo '
        --
        -- Structure for table ' . $tableName . '
        --
        ' . PHP_EOL;
        echo 'DROP TABLE IF EXISTS ' . $db->quoteTableName($tableName) . ';' . PHP_EOL;
        $q = $db->createCommand('SHOW CREATE TABLE ' . $db->quoteTableName($tableName) . ';')->queryRow();
        echo $q['Create Table'] . ';' . PHP_EOL . PHP_EOL;
        $rows = $db->createCommand('SELECT * FROM ' . $db->quoteTableName($tableName) . ';')->queryAll();
        if (empty($rows)) {
            return;
        }

        echo '
        --
        -- Data for table ' . $tableName . '
        --
        ' . PHP_EOL;
        $attrs = array_map(array($db, 'quoteColumnName'), array_keys($rows[0]));
        //echo 'INSERT INTO '.$db->quoteTableName($tableName).''." (", implode(', ', $attrs), ') VALUES';
        $i = 0;
        $j = 0;
        $rowsCount = count($rows);
        foreach ($rows as $row) {
            if ($j == 1000) {
                $j = 0;
            }

            if ($j == 0) {
                echo 'INSERT INTO ' . $db->quoteTableName($tableName) . '' . " (", implode(', ', $attrs), ') VALUES ' . PHP_EOL;
            }

            // Process row
            foreach ($row as $key => $value) {
                if ($value === null) {
                    $row[$key] = 'NULL';
                } else {
                    $row[$key] = $pdo->quote($value);
                }
            }

            echo " (", implode(', ', $row), ')';
            if ($j == 999) {
                echo ';';
            } elseif ($i < $rowsCount - 1) {
                echo ',';
            } else {
                echo ';';
            }

            echo PHP_EOL;
            $i++;
            $j++;
        }
        echo PHP_EOL;
        echo PHP_EOL;
    }

    /**
     * Get mysql tables list
     * @return array
     */
    public function getTables()
    {
        $db = $this->getDbConnection();
        return $db->getSchema()->getTables();
    }

    /**
     * Flush DB
     *
     * @author Sviatoslav Danylenko
     * @return namber of affected rows
     */
    public function flushDb()
    {
        ob_start();
        $db = $this->getDbConnection();
        // что бы при восстановлении бекапа не вылазили ошибки №150 в mysql
        // добавляем строки, которые отключают проверку внешних ключей на время восстановления бекапа
        echo 'SET FOREIGN_KEY_CHECKS = 0;' . PHP_EOL;
        foreach ($this->getTables() as $key => $val) {
            $tables[] = $db->quoteTableName($key);
        }

        echo 'DROP TABLE ';
        echo implode(', ', $tables) . ';' . PHP_EOL;
        echo 'SET FOREIGN_KEY_CHECKS = 1;' . PHP_EOL;
        $sql = ob_get_contents();
        ob_end_clean();

        $command = $db->createCommand($sql);
        $rowCount = $command->execute();

        return $rowCount;
    }

    /**
     * @return CDbConnection the currently active database connection
     */
    public function getDbConnection()
    {
        return $this->_db;
    }

    /**
     * Sets the currently active database connection.
     * The database connection will be used by the methods such as {@link insert}, {@link createTable}.
     *
     * @param CDbConnection $db the database connection component
     */
    public function setDbConnection($db)
    {
        if (!($db instanceof \CDbConnection)) {
            throw new \InvalidArgumentException(
                \Yii::t(
                    'yii',
                    'The "db" application component must be configured to be a CDbConnection object.'
                )
            );
        }

        $this->_db = $db;
    }
}
