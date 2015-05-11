<?php

/**
 * This is the model class for table "blog_tag".
 *
 * The followings are the available columns in table 'blog_tag':
 * @property integer $id
 * @property string $name
 * @property integer $frequency
 */

namespace blog\models;

class Tag extends \CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Tag the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{blog_tag}}';
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
            array('frequency', 'numerical', 'integerOnly' => true),
            array('name', 'length', 'max' => 128),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, name, frequency', 'safe', 'on' => 'search'),
        );
    }

    public function scopes()
    {
        return array(
            'default' => array(
                'order' => 'frequency DESC',
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
    public function findTagWeights($limit = 20)
    {
        $models = $this->findAll(array(
            'order' => 'frequency DESC',
            'limit' => $limit,
        ));

        $total = 0;
        foreach ($models as $model) {
            $total += $model->frequency;
        }

        $tags = array();
        if ($total > 0) {
            foreach ($models as $model) {
                $tags[$model->name] = 8 + (int) (16 * $model->frequency / ($total + 10));
            }

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
    public function suggestTags($keyword, $limit = 20)
    {
        $tags = $this->findAll(array(
            'condition' => 'name LIKE :keyword',
            'order' => 'frequency DESC, Name',
            'limit' => $limit,
            'params' => array(
                ':keyword' => '%' . strtr($keyword, array('%' => '\%', '_' => '\_', '\\' => '\\\\')) . '%',
            ),
        ));
        $names = array();
        foreach ($tags as $tag) {
            $names[] = $tag->name;
        }

        return $names;
    }

    public static function string2array($tags)
    {
        return preg_split('/\s*,\s*/', trim($tags), -1, PREG_SPLIT_NO_EMPTY);
    }

    public static function array2string($tags)
    {
        return implode(', ', $tags);
    }

    public function updateFrequency($oldTags, $newTags)
    {
        $oldTags = self::string2array($oldTags);
        $newTags = self::string2array($newTags);
        $this->addTags(array_values(array_diff($newTags, $oldTags)));
        $this->removeTags(array_values(array_diff($oldTags, $newTags)));
    }

    public function addTags($tags)
    {
        $criteria = new CDbCriteria;
        $criteria->addInCondition('name', $tags);
        $this->updateCounters(array('frequency' => 1), $criteria);
        foreach ($tags as $name) {
            if (!$this->exists('name=:name', array(':name' => $name))) {
                $tag = new Tag();
                $tag->name = $name;
                $tag->frequency = 1;
                $tag->save();
            }
        }
    }

    public function removeTags($tags)
    {
        if (empty($tags)) {
            return;
        }

        $criteria = new CDbCriteria;
        $criteria->addInCondition('name', $tags);
        $this->updateCounters(array('frequency' => -1), $criteria);
        $this->deleteAll('frequency<=0');
    }

    public function tagViewUrl($tag = false)
    {
        return Yii::app()->createUrl('blog/blog/index', array('tag' => $tag));
    }

    public function tagRssViewUrl($tag = false)
    {
        if ($tag) {
            $params = array('tag' => $tag);
        }

        return Yii::app()->createUrl('blog/blog/rss', $params);
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new \CDbCriteria();

        $criteria->compare('id', $this->id);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('frequency', $this->frequency);

        return new \CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }
}
