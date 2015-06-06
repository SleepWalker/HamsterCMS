<?php
/**
 * This is the model class for table "shop_rating".
 *
 * The followings are the available columns in table 'shop_rating':
 * @property string $id
 * @property string $source_id
 * @property string $user_id
 * @property integer $value
 * @property integer $ip
 *
 * The followings are the available model relations:
 * @property User $user
 * @property Shop $prod
 */

namespace application\models;

abstract class Rating extends \CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return ShopRating the static model class
     */
    public static function model($className = __CLASS__)
    {
        $className = get_called_class();

        return parent::model($className);
    }

    public function getMetaData()
    {
        try {
            return parent::getMetaData();
        } catch (\CDbException $e) {
            $this->createDbTable();
            \Yii::app()->controller->refresh();
        }
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('source_id, user_id, value', 'required'),
            //array('prod_id, user_id', 'unique'),
            array('source_id, user_id', 'numerical', 'integerOnly' => true),
            array('value', 'numerical', 'min' => 1, 'max' => 5),
        );
    }

    /**
     * преобразовывает ip юзера из бд из int в строку
     *
     * @access protected
     * @return void
     */
    protected function afterFind()
    {
        parent::afterFind();
        $this->ip = long2ip($this->ip);
    }

    /**
     * преобразовывает ip юзера в int для того, что бы сохранить его бд
     */
    protected function beforeSave()
    {
        if (parent::beforeSave()) {
            if ($this->isNewRecord) {
                $this->ip = ip2long(\Yii::app()->request->getUserHostAddress());
            } else {
                throw new \Exception('Обновление модели невозможно. Значения рейтинга иммутабельны!');
                // $this->ip = ip2long($this->ip);
            }

            return true;
        } else {
            return false;
        }

    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'source_id' => 'Prod',
            'user_id' => 'User',
            'value' => 'Value',
        );
    }

    /**
     * creates table for holding provider bindings
     */
    private function createDbTable()
    {
        $this->getDbConnection()->createCommand()->createTable($this->tableName(), [
          'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
          'source_id' => 'int(10) unsigned NOT NULL',
          'user_id' => 'int(10) unsigned NOT NULL',
          'value' => 'double(2,2) NOT NULL',
          'ip' => 'int(10) unsigned NOT NULL',
          'PRIMARY KEY (`id`)',
          'UNIQUE KEY `source_id` (`source_id`,`user_id`,`ip`)',
          'KEY `user_id` (`user_id`)',
          'KEY `source_id_2` (`source_id`)'
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }
}
