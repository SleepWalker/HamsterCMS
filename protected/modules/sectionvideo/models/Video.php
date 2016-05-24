<?php

/**
 * This is the model class for table "section_video".
 *
 * The followings are the available columns in table 'section_video':
 * @property integer $id
 * @property string $composition_author
 * @property string $composition_name
 * @property string $event
 * @property string $video_url
 * @property string $thumbnail
 * @property string $title
 * @property string $description
 * @property integer $likes
 * @property integer $views
 * @property string $tags
 * @property string $create_date
 */

namespace sectionvideo\models;

use \CActiveDataProvider;
use \CDbCriteria;
use \CHtml;
use \CJavaScriptExpression;
use \event\models\Event;
use \Yii;

class Video extends \CActiveRecord
{
    const TYPE_SOLO = 1;
    const TYPE_GROUP = 2;
    const TYPE_CONCERT = 3;

    const STATUS_REMOVED = 0;
    const STATUS_DRAFT = 1;
    const STATUS_PUBLISHED = 2;

    public $status = self::STATUS_DRAFT;

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['event, video_url, type, status', 'required'],
            ['event_id', 'numerical', 'integerOnly' => true],
            ['composition_author, composition_name, event, title', 'length', 'max' => 128],
            ['video_url', 'url', 'defaultScheme' => 'http'],
            ['likes', 'length', 'max' => 7],
            ['description, tags', 'safe'],
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            ['id, composition_author, composition_name, event, video, thumbnail, description, likes, tags, date_create', 'safe', 'on' => 'search'],
        ];
    }

    public function behaviors()
    {
        return [
            'HRating' => [
                'class' => 'HRatingBehavior',
                'attribute' => 'likes',
                'ratingModelClass' => '\sectionvideo\models\VideoRating',
            ],
            'HTag' => [
                'class' => '\hamster\components\HTagBehavior',
            ],
            'HtmlEncode' => [
                'class' => 'HtmlEncodeBehavior',
                'attributes' => [
                    'composition_author',
                    'composition_name',
                    'title',
                ],
            ],
        ];
    }

    /**
     *  Обновляем даты
     *  Генерируем HTML код видеоплеера и заполняем поле превьюшки видео ролика через YouTube API
     */
    protected function beforeSave()
    {
        if (parent::beforeSave()) {
            if ($this->isNewRecord) {
                $this->date_create = new \CDbExpression('NOW()');
                $this->date_update = new \CDbExpression('NOW()');
            } else {
                $this->date_update = date('Y-m-d H:i:s');
            }

            // TODO: это должно устанавливаться извне. к примеру в репозитории. Причем сама картинка должна попадать из сервиса
            $this->thumbnail = $this->getImageSrc();

            return true;
        } else {
            return false;
        }
    }

    public function defaultScope()
    {
        $alias = $this->getTableAlias(true, false);
        return [
            'order' => $alias . '.date_create DESC',
        ];
    }

    public function scopes()
    {
        return [
            'published' => [
                // TODO: need BL improvement here
                'condition' => \Yii::app()->user->isGuest ? 'status = ' . self::STATUS_PUBLISHED : '',
            ],
            'latest' => [
                'order' => 'date_create DESC',
            ],
        ];
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return [
            'musicians' => [self::HAS_MANY, VideoMusicians::class, 'video_id'],
        ];
    }

    protected function beforeDelete()
    {
        if (parent::beforeDelete()) {
            VideoMusicians::model()->deleteAllByAttributes(['video_id' => $this->primaryKey]);
            return true;
        }

        return false;
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Тип видео',
            'title' => 'Название концерта / Имя группы',
            'composition_author' => 'Автор композиции',
            'composition_name' => 'Название композиции',
            'event' => 'Название или id мероприятия',
            'video_url' => 'Ссылка на ролик Youtube',
            'thumbnail' => 'thumbnail',
            'description' => 'Описание',
            'likes' => 'Лайки',
            'views' => 'Просмотры',
            'tags' => 'Теги',
            'date_create' => 'Дата добавления',
        ];
    }

    public function getFullTitle()
    {
        $caption = $this->getCaption();
        $composition = $this->getComposition();

        if (!empty($composition)) {
            return $composition . (!empty($caption) ? " ($caption)" : '');
        } else {
            return $caption;
        }
    }

    /**
     * @return string возвращает заголовок для видео в зависимости от доступных данных
     */
    public function getCaption()
    {
        if (empty($this->title)) {
            $musicians = $this->musicians;
            return count($musicians) == 1 ? $musicians[0]->musician->name : null;
        } else {
            return $this->title;
        }
    }

    /**
     * @param  string $size размер изображения. Доступные размеры: full, list, grid
     * @return string url изображения
     */
    public function getImageSrc($size = null)
    {
        $cacheId = $this->video_url . '#src';
        if (($src = \Yii::app()->cache->get($cacheId)) === false) {
            $src = $this->getGoogleYoutubeVideo()->getThumbnail($size);
            \Yii::app()->cache->set($cacheId, $src);
        }

        return $src;
    }

    protected function getGoogleYoutubeVideo()
    {
        // TODO: связь с youtube должна происходить снаружи
        return \Yii::app()->getModule('sectionvideo')->externalVideo->create($this->video_url);
    }

    /**
     * Возвращает код видеоплеера Youtube
     * @param string $url ссылка на ролик Youtube
     * @param array $params дополнительные параметры, такие как размер видео TODO
     * @see Video::getYtVideoCode()
     * @return string html код плеера
     */
    public function getVideoCode($params = [])
    {
        return $this->getGoogleYoutubeVideo()->getPlayerCode();
    }

    /**
     * Форматирует название композиции
     */
    public function getComposition()
    {
        $compositionName = [];
        if (!empty($this->composition_author)) {
            $compositionName[] = $this->composition_author;
        }

        if (!empty($this->composition_name)) {
            $compositionName[] = $this->composition_name;
        }

        if (!count($compositionName)) {
            return false;
        }

        return implode(' — ', $compositionName);
    }

    /**
     * Вовзращает ссылку на ивент, если он есть в базе данных
     */
    public function getEventUrl()
    {
        $event = Event::model()->findByAttributes(['name' => $this->event]);
        return $event ? $event->viewUrl : null;
    }

    /**
     * Возвращает url страницы материала
     */
    public function getViewUrl()
    {
        return Yii::app()->createUrl('sectionvideo/sectionvideo/view', ['id' => $this->primaryKey]);
    }

    public function tagViewUrl($tag = false)
    {
        return Yii::app()->createUrl('sectionvideo/sectionvideo/index', ['tag' => $tag]);
    }

    public static function getTypesList()
    {
        return [
            self::TYPE_SOLO => 'Сольный номер',
            self::TYPE_GROUP => 'Группа/Ансамбль',
            self::TYPE_CONCERT => 'Концерт',
        ];
    }

    public function getTypeString()
    {
        $types = self::getTypesList();
        return isset($types[$this->type]) ? $types[$this->type] : '';
    }

    public static function getStatusesList()
    {
        return [
            self::STATUS_DRAFT => 'Черновик',
            self::STATUS_PUBLISHED => 'Опубликовано',
        ];
    }

    /**
     * @return array типы полей для форм администрирования модуля
     */
    public function getFieldTypes()
    {
        return [
            'status' => [
                'type' => 'dropdownlist',
                'items' => $this->getStatusesList(),
                'prompt' => '--Выберите--',
            ],
            'composition_name' => 'text',
            'composition_author' => 'text',
            'type' => [
                'type' => 'dropdownlist',
                'items' => $this->getTypesList(),
                'prompt' => '--Выберите--',
                'js' => new CJavaScriptExpression('
					var $title = $("#' . CHtml::activeId($this, 'title') . '");
					$("#' . CHtml::activeId($this, 'type') . '").change(function() {
						switch($(this).val())
						{
							case "' . self::TYPE_CONCERT . '":
							case "' . self::TYPE_GROUP . '":
								$title.parent().show();
							break;
							case "' . self::TYPE_SOLO . '":
							default:
								$title.parent().hide();
							break;
						}
					}).change();'),
            ],
            'title' => 'text',
            'VideoMusicians' => 'hasManyForm',
            'video_url' => 'text',
            'event_id' => 'hidden',
            'event' => [
                'type' => 'ext.fields.jui.JuiAutoDepComplete',
                'source' => '/admin/sectionvideo/acevent',
                'select' => [
                    'id' => 'event_id',
                    'value' => 'event',
                ],
                'iconOptions' => [
                    'class' => 'icon_delete',
                ],
            ],

            'tags' => 'tags',
            'description' => 'markdown',
        ];
    }

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return sectionvideo the static model class
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
        return '{{section_video}}';
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id, true);
        $criteria->compare('composition_author', $this->composition_author, true);
        $criteria->compare('composition_name', $this->composition_name, true);
        $criteria->compare('event', $this->event, true);
        $criteria->compare('title', $this->title, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('likes', $this->likes, true);
        $criteria->compare('tags', $this->tags, true);
        $criteria->compare('date_create', $this->date_create, true);

        return new CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }
}
