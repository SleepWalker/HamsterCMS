<?php

/**
 * This is the model class for table "event".
 *
 * The followings are the available columns in table 'event':
 * @property string $id
 * @property string $image_id
 * @property string $name
 * @property string $desc
 * @property string $where
 * @property string $start_date
 * @property string $end_date
 * @property double $longitude
 * @property double $latitude
 */

namespace event\models;

use hamster\models\UploadedFile;

require_once \Yii::getPathOfAlias('application.vendor') . '/alphaID.inc.php';

class Event extends \CActiveRecord
{
    // В этой переменной будет хранится id ивента
    public $eventId;
    public $uploaded_image;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Event the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName(): string
    {
        return '{{event}}';
    }

    public function defaultScope()
    {
        $alias = $this->getTableAlias(true, false);

        return array(
            'order' => $alias . '.start_date DESC',
        );
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules(): array
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['name, desc, where, start_date, longitude, latitude', 'required'],
            ['longitude, latitude', 'numerical'],
            ['location, end_date', 'safe'],
            ['uploaded_image', 'file',
                'types' => 'jpg, gif, png',
                'maxSize' => 1024 * 1024 * 5, // 5 MB
                'allowEmpty' => true,
                'tooLarge' => 'Файл весит больше 5 MB. Пожалуйста, загрузите файл меньшего размера.',
                'safe'  =>  true,
			],
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            ['id, name, desc, where, start_date, end_date, longitude, latitude', 'safe', 'on' => 'search'],
        ];
    }

    /**
     * @return array relational rules.
     */
    public function relations(): array
    {
        return [
            'image' => [self::BELONGS_TO, UploadedFile::CLASS, 'image_id'],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Название мероприятия',
            'desc' => 'Описание мероприятия',
            'where' => 'Место проведения',
            'image' => 'Афиша мероприятия',
            'uploaded_image' => 'Афиша мероприятия',
            'start_date' => 'Дата начала',
            'end_date' => 'Дата конца',
            'longitude' => 'Coord X',
            'latitude' => 'Coord Y',
            'location' => 'Как добраться',
        ];
    }

    /**
     * @return array типы полей для форм администрирования модуля
     */
    public function getFieldTypes(): array
    {
        return [
            'name' => 'text',
            'uploaded_image' => [
                'type' => 'image',
                'relation' => 'image',
            ],
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'where' => 'address',
            'location' => 'textareaTiny',
            'desc' => 'textarea',
            'longitude' => 'hidden',
            'latitude' => 'hidden',
        ];
    }

    /**
     *  Заполняет {@link $eventId} необходимым id
     */
    protected function afterFind()
    {
        parent::afterFind();
        $this->eventId = alphaID($this->id, false, 4);
    }

    protected function beforeValidate(): bool
    {
        if (parent::beforeValidate()) {
            $image = \CUploadedFile::getInstance($this, 'uploaded_image');

            if ($image) {
                $this->uploaded_image = $image;
            }

            return true;
        }

        return false;
    }

    protected function beforeSave(): bool
    {
        if (parent::beforeSave()) {
            if (empty($this->end_date)) {
                $this->end_date = new \CDbExpression('NULL');
            }

            if ($this->uploaded_image) {
                $uploadedFile = $this->image ?? new UploadedFile();

                $uploadedFile->store($this->uploaded_image, 'event');
                $this->image_id = $uploadedFile->primaryKey;
            } else {
                // checking for the case if we need to remove file
                // 'delete' flag is set by HFileField, when user decieds to remove file

                // TODO: need to create custom validator, that will
                // pass some 'delete' mark through so that we can determine
                // whether user decided to remove file

                $modelName = \CHtml::modelName($this);
                $data = \Yii::app()->request->getParam($modelName);

                if (($data['uploaded_image'] ?? null) === 'delete' && $this->image) {
                    $this->image->delete();
                    $this->image_id = null;
                }
            }

            // force yii to repopulate relation
            unset($this->image);

            return true;
        }

        return false;
    }

    /**
     * Исчет мероприятие по его коду
     * @param string $id код мероприятия
     */
    public function findByEventId($id)
    {
        return $this->findByPk(alphaID($id, true, 4));
    }

    /**
     * Возвращает дату в красивом формате
     * @param string $date строка с датой из базы данных
     */
    public function prettyDate($date)
    {
        return \Yii::app()->dateFormatter->formatDateTime($date, 'medium', 'short');
    }

    public function getPrettyStartDate()
    {
        return $this->prettyDate($this->start_date);
    }

    public function getPrettyEndDate()
    {
        return $this->prettyDate($this->end_date);
    }

    /**
     * @return string Html код кнопки "добавить в гуглокалендарь"
     */
    public function getGCalUrl()
    {
        // конвертируем временные зоны в +00:00
        date_default_timezone_set('Europe/Kiev');
        $utc0 = new \DateTimeZone('Etc/GMT');
        $datetime = new \DateTime($this->start_date);
        $datetime->setTimezone($utc0);
        $start_date = $datetime->format('Y-m-d H:i:s');

        $datetime = new \DateTime($this->end_date);
        $datetime->setTimezone($utc0);
        $end_date = $datetime->format('Y-m-d H:i:s');

        $dates = date('Ymd\THis\Z', strtotime($start_date)) . '/' . date('Ymd\THis\Z', strtotime($end_date));
        $params = array(
            'action' => 'TEMPLATE',
            'text' => $this->name,
            'dates' => $dates, //dates=20121018T150000Z/20051231T230000Z
            'details' => $this->desc,
            'location' => $this->location,
            'trp' => false,
            //'sprop' => $event->viewUrl,
            'sprop' => 'name:' . \Yii::app()->name,
        );
        return 'http://www.google.com/calendar/event?' . http_build_query($params);
    }

    public function hasImage(): bool
    {
        return !!$this->image_id;
    }

    public function getThumbUrl()
    {
        if ($this->hasImage()) {
            return $this->image->getResizedUrl([
                'prefix' => 'th',
                'width' => 200,
            ]);
        }

        return null;
    }

    /**
     * Возвращает url страницы материала
     */
    public function getViewUrl()
    {
        return \Yii::app()->createUrl('event/event/view', [$this->eventId]);
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

        $criteria->compare('id', $this->id, true);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('desc', $this->desc, true);
        $criteria->compare('where', $this->where, true);
        $criteria->compare('start_date', $this->start_date, true);
        $criteria->compare('longitude', $this->longitude);
        $criteria->compare('latitude', $this->latitude);

        return new \CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }
}
