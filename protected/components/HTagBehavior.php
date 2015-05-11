<?php
/**
 * HTagBehavior поведение, добавляющее функционал тегов в модель
 *
 * @uses CActiveRecordBehavior
 * @package hamster.components.HTagBehavior
 * @version $id$
 * @author     Sviatoslav Danylenko <dev@udf.su>
 * @copyright  Copyright &copy; 2013 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace hamster\components;

// TODO: аттрибут-флаг, который включит автоматиеческое создание поля $attribute, если его нету в бд
class HTagBehavior extends \CActiveRecordBehavior
{
    public $attribute = 'tags';
    public $_oldTags; // @note: public, так как это свойство нужно редактировать через события

    public function attach($owner)
    {
        parent::attach($owner);

        if(!$this->owner->hasAttribute($this->attribute))
            throw new \CException('Не могу найти поле `' . $this->owner->tableName() . '`.`' . $this->attribute . '`. Создайте это поле или задайте правильное имя аттрибуту HTagBehavior::attribute: ALTER TABLE `' . $this->owner->tableName() . '` ADD `' . $this->attribute . '` TEXT NULL');

        $validators = $owner->getValidatorList();

        // валидация тегов
    /*
        $validator = CValidator::createValidator('match', $owner, 'tags', array('pattern'=>'/^[a-zA-Zа-яА-Я0-9_\s,\.]+$/u'));
    $validators->add($validator);
     */
        $validator = \CValidator::createValidator('normalizeTags', $this, 'tags');
        $validators->add($validator);

        // устанавливаем таблицу для текущего запроса
        Tag::$sessionTable = $this->getTagTableName();


        Tag::model()->findByPk(1);
    }

    public function afterSave($event)
    {
        $model = $event->sender;
        //При сохранении записи мы хотим также обновить информацию о частоте использования тегов (модель Tag)
        Tag::model()->updateFrequency($model->_oldTags, $model->tags);
    }

    public function afterFind($event)
    {
        $model = $event->sender;
        $model->_oldTags=$model->tags;
    }

    /**
     * Предварительная обработка тегов для сохранения в бд
     */
    public function normalizeTags()
    {
        $this->owner->tags=Tag::array2string(array_unique(Tag::string2array($this->owner->tags)));
    }

    /**
     *  @return array теги материала в виде массива
     */
    public function getTagsArr()
    {
        return Tag::string2array($this->owner->tags);
    }

    public function findTagWeights($maxTags)
    {
        return Tag::model()->findTagWeights($maxTags);
    }

    /**
     * Suggests a list of existing tags matching the specified keyword.
     * @param string the keyword to be matched
     * @param integer maximum number of tags to be returned
     * @return array list of matching tag names
     */
    public function suggestTags($term,$limit=20)
    {
        $model = Tag::model();
        $tags = $model->string2array($term); // работаем только с последним тегом из списка
        return $model->suggestTags(array_pop($tags));
    }

    /**
     * @return Tag модель тегов связанная с текущей
     */
    public function tagModel()
    {
        return Tag::model();
    }

    /**
     * Возвращает название таблицы в которой будет хранится подробная информация о каждом голосе
     *
     * @access public
     * @return void
     */
    public function getTagTableName()
    {
        return $this->owner->tableName() . '_tag';
    }
}






/**
 * This is the model class for Tag tables
 *
 * The followings are the available columns in table:
 * @property integer $id
 * @property string $name
 * @property integer $frequency
 */
class Tag extends \CActiveRecord
{
    /**
     * @property string $sessionTable Таблица, которая будет использоваться на протяжении запроса. (эта таблица будет менять свое имя в зависимости от модуля)
     */
    public static $sessionTable;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Returns the meta-data for this AR
     * Модификация метода для автоматического создания таблицы
     * @return CActiveRecordMetaData the meta for this AR class.
     */
    public function getMetaData()
    {
        try
        {
            return parent::getMetaData();
        }
        catch(\CDbException $e)
        {
            self::createDbTable();
            $this->refreshMetaData();

            return parent::getMetaData();
        }
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return Tag::$sessionTable;
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name', 'required'),
            array('frequency', 'numerical', 'integerOnly'=>true),
            array('name', 'length', 'max'=>128),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, name, frequency', 'safe', 'on'=>'search'),
        );
    }

    public function scopes()
    {
        return array(
            'default'=>array(
                'order'=>'frequency DESC',
            ),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'name' => 'Name',
            'frequency' => 'Frequency',
        );
    }

    /**
     * Returns tag names and their corresponding weights.
     * Only the tags with the top weights will be returned.
     * @param integer the maximum number of tags that should be returned
     * @return array weights indexed by tag names.
     */
    public function findTagWeights($limit=20)
    {
        $models=$this->findAll(array(
            'order'=>'frequency DESC',
            'limit'=>$limit,
        ));

        $total=0;
        foreach($models as $model)
            $total+=$model->frequency;

        $tags=array();
        if($total>0)
        {
            foreach($models as $model)
                $tags[$model->name]=8+(int)(16*$model->frequency/($total+10));
            ksort($tags);
        }
        return $tags;
    }

    /**
     * Suggests a list of existing tags matching the specified keyword.
     * @param string the keyword to be matched
     * @param integer maximum number of tags to be returned
     * @return array list of matching tag names
     */
    public function suggestTags($keyword,$limit=20)
    {
        $tags=$this->findAll(array(
            'condition'=>'name LIKE :keyword',
            'order'=>'frequency DESC, Name',
            'limit'=>$limit,
            'params'=>array(
                ':keyword'=>'%'.strtr($keyword,array('%'=>'\%', '_'=>'\_', '\\'=>'\\\\')).'%',
            ),
        ));
        $names=array();
        foreach($tags as $tag)
            $names[]=$tag->name;
        return $names;
    }

    public static function string2array($tags)
    {
        return preg_split('/\s*,\s*/',trim($tags),-1,PREG_SPLIT_NO_EMPTY);
    }

    public static function array2string($tags)
    {
        return implode(', ',$tags);
    }

    public function updateFrequency($oldTags, $newTags)
    {
        $oldTags=self::string2array($oldTags);
        $newTags=self::string2array($newTags);
        $this->addTags(array_values(array_diff($newTags,$oldTags)));
        $this->removeTags(array_values(array_diff($oldTags,$newTags)));
    }

    public function addTags($tags)
    {
        $criteria=new \CDbCriteria();
        $criteria->addInCondition('name',$tags);
        $this->updateCounters(array('frequency'=>1),$criteria);
        foreach($tags as $name)
        {
            if(!$this->exists('name=:name',array(':name'=>$name)))
            {
                $tag=new Tag();
                $tag->name=$name;
                $tag->frequency=1;
                $tag->save();
            }
        }
    }

    public function removeTags($tags)
    {
        if(empty($tags))
            return;
        $criteria=new \CDbCriteria();
        $criteria->addInCondition('name',$tags);
        $this->updateCounters(array('frequency'=>-1),$criteria);
        $this->deleteAll('frequency<=0');
    }

    /**
    public function tagViewUrl($tag = false)
    {
        return \Yii::app()->createUrl('blog/blog/index', array('tag' => $tag));
    }

    public function tagRssViewUrl($tag = false)
    {
        if($tag) $params = array('tag' => $tag);
        return \Yii::app()->createUrl('blog/blog/rss', $params);
    }
    */

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new \CDbCriteria();

        $criteria->compare('id',$this->id);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('frequency',$this->frequency);

        return new \CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * creates table for holding provider bindings
     */
    protected static function createDbTable()
    {
        $sql = Tag::getTableSql();
        $sql = strtr($sql, array('{{tableName}}' => \Yii::app()->db->tablePrefix . Tag::$sessionTable));
        \Yii::app()->db->createCommand($sql)->execute();
    }

    protected static function getTableSql()
    {
        ob_start();
?>
SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `{{tableName}}` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
    `frequency` int(11) DEFAULT '1',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS=1;
<?php
        return ob_get_clean();
    }
}
