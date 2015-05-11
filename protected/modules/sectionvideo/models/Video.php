<?php

/**
 * This is the model class for table "section_video".
 *
 * The followings are the available columns in table 'section_video':
 * @property string $id
 * @property string $composition_author
 * @property string $composition_name
 * @property string $event
 * @property string $video_url
 * @property string $thumbnail
 * @property string $title
 * @property string $description
 * @property string $rating
 * @property string $tags
 * @property string $create_date
 */

namespace hamster\modules\sectionvideo\models;

use \CActiveDataProvider;
use \CDbCriteria;
use \CHtml;
use \CJavaScriptExpression;
use \Event;
//use application\modules\event\models\Event as Event;
\Yii::import('application.modules.event.models.Event');
use \Yii;

class Video extends \CActiveRecord
{
    const TYPE_SOLO = 1;
    const TYPE_GROUP = 2;
    const TYPE_CONCERT = 3;

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
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('event, video_url, type', 'required'),
            array('event_id', 'numerical', 'integerOnly' => true),
            array('composition_author, composition_name, event, title', 'length', 'max' => 128),
            array('video_url', 'url', 'defaultScheme' => 'http'),
            array('rating', 'length', 'max' => 7),
            array('description, tags', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, composition_author, composition_name, event, video, thumbnail, description, rating, tags, date_create', 'safe', 'on' => 'search'),
        );
    }

    public function behaviors()
    {
        return array(
            'HRating' => array(
                'class' => 'HRatingBehavior',
            ),
            'HTag' => array(
                'class' => '\hamster\components\HTagBehavior',
            ),
            'HtmlEncode' => array(
                'class' => 'HtmlEncodeBehavior',
                'attributes' => array(
                    'composition_author',
                    'composition_name',
                    'title',
                ),
            ),
        );
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
            }

            $entry = self::getYtVideoEntry($this->video_url);
            $this->thumbnail = self::getYtVideoThumbnail($entry);

            return true;
        } else {
            return false;
        }

    }

    public function defaultScope()
    {
        return array(
            'order' => 'date_create DESC',
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
            'musicians' => array(self::HAS_MANY, 'hamster\modules\sectionvideo\models\VideoMusicians', 'video_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'type' => 'Тип видео',
            'title' => 'Название концерта / Имя группы',
            'composition_author' => 'Автор композиции',
            'composition_name' => 'Название композиции',
            'event' => 'Название или id мероприятия',
            'video_url' => 'Ссылка на ролик Youtube',
            'thumbnail' => 'thumbnail',
            'description' => 'Описание',
            'rating' => 'Rating',
            'tags' => 'Теги',
            'date_create' => 'Add Date',
        );
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
     * @return integer кол-во просмотров на youtube
     */
    public function getViews()
    {
        $cacheId = $this->video_url . '#views';
        if (($views = \Yii::app()->cache->get($cacheId)) === false) {
            $video = $this->getGoogleYoutubeVideo();
            $views = $video['statistics']['viewCount'];
            \Yii::app()->cache->set($cacheId, $views);
        }

        return $views;
    }

    /**
     * @return integer рейтинг (кол-во лайков на youtube)
     */
    public function getLikes()
    {
        // TODO: пускай эти лайки смешиваются с yt лайками
        $cacheId = $this->video_url . '#likes';
        if (($likes = \Yii::app()->cache->get($cacheId)) === false) {
            $video = $this->getGoogleYoutubeVideo();
            $likes = $video['statistics']['likeCount'];
            \Yii::app()->cache->set($cacheId, $likes);
        }

        return $likes;
    }

    public function ratingWidget()
    {
        ?>
        <div class="sharing-rocks">
            <div class="this-rocks">
                <span class="this-rocks__counter"><?=$this->likes?></span>
                <span class="this-rocks__icon"></span>
            </div>
        </div>
        <?php
    }

    protected function getGoogleYoutubeVideo()
    {
        $youtube = new \Google_Service_YouTube($this->getGoogleClient());

        $listResponse = $youtube->videos->listVideos("statistics", array('id' => self::getYtVideoId($this->video_url)));

        if (empty($listResponse)) {
            throw new \CException(get_class($this) . "::getThumbnails(): No data for specified video, probably wrong id");
        }

        return $listResponse[0];
    }

    protected function getGoogleClient()
    {
        $client = new \Google_Client();
        $client->setApplicationName("estrocksection.kiev.ua");
        $client->setDeveloperKey("AIzaSyD7QiC2AO4PUtiMRN9i5SfZOAhZLvSnGzw");

        return $client;
    }

    /**
     * @param  string $size размер изображения. Доступные размеры: full, list, grid
     * @return string url изображения
     */
    public function getImageSrc($size = null)
    {
        $cacheId = $this->video_url . '#src';
        if (($src = \Yii::app()->cache->get($cacheId)) === false) {
            $videoImage = new \sectionvideo\components\VideoImage(self::getYtVideoId($this->video_url));
            $src = $videoImage->get($size);
            \Yii::app()->cache->set($cacheId, $src);
        }

        return $src;
    }

    /**
     * Возвращает код видеоплеера Youtube
     * @param string $url ссылка на ролик Youtube
     * @param array $params дополнительные параметры, такие как размер видео TODO
     * @see Video::getYtVideoCode()
     * @return string html код плеера
     */
    public function getVideoCode($params = array())
    {
        return self::getYtVideoCode($this->video_url, $params);
    }

    /**
     * Возвращает код видеоплеера Youtube
     * @param string $url ссылка на ролик Youtube
     * @param array $params дополнительные параметры, такие как размер видео TODO
     * @return string html код плеера
     */
    public static function getYtVideoCode($url, $params = array())
    {
        $id = self::getYtVideoId($url);
        // html5=1 - исправляет баг с нерабочим видео в firefox в случае если оно вставлялось на страницу после ее загрузки (к примеру через ajax)
        return '<iframe width="420" height="315" src="//www.youtube.com/embed/' . $id . '?html5=1" frameborder="0" allowfullscreen></iframe>';
    }

    /**
     * Возвращает ссылку на изображение - превью видеоролика
     * @param Zend_Gdata_YouTube_VideoEntry $entry обьект представляющий информацию о видео записи
     * @return string url картинки
     */
    public static function getYtVideoThumbnail($entry)
    {
        // TODO: возможность менять размеры и, как вариант, сохранение картинки на сайте
        //$thumbnails = $entry->getVideoThumbnails();
        return $entry->mediaGroup->thumbnail[0]->url;
    }

    /**
     * Возвращает обьект, описывающий YouTube видео
     *
     * @param string $url url ролика
     * @return Zend_Gdata_YouTube_VideoEntry $entry обьект видео записи
     */
    public static function getYtVideoEntry($url)
    {
        $videoId = self::getYtVideoId($url);

        Yii::import('application.vendor.*');
        require_once 'Zend/Loader.php';

        \Zend_Loader::loadClass('Zend_Gdata_YouTube');

        $yt = new \Zend_Gdata_YouTube();

        $entry = $yt->getVideoEntry($videoId);

        return $entry;
    }

    /**
     * Возвращает id видеоролика с youtube. URL
     * @param string $url url видеоролика (например: http://www.youtube.com/watch?v=EB4ljWxG5P4)
     * @return string video id
     */
    public static function getYtVideoId($url)
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $params);

        return $params['v'];
    }

    /**
     * Форматирует название композиции
     */
    public function getComposition()
    {
        $compositionName = array();
        if (!empty($this->composition_author)) {
            $compositionName[] = $this->composition_author;
        }

        if (!empty($this->composition_name)) {
            $compositionName[] = $this->composition_name;
        }

        if (!count($compositionName)) {
            return false;
        }

        return implode(' - ', $compositionName);
    }

    /**
     * Вовзращает ссылку на ивент, если он есть в базе данных
     */
    public function getEventUrl()
    {
        $event = Event::model()->findByAttributes(array('name' => $this->event));
        return $event ? $event->viewUrl : null;
    }

    /**
     * Возвращает url страницы материала
     */
    public function getViewUrl()
    {
        return Yii::app()->createUrl('sectionvideo/sectionvideo/view', array('id' => $this->primaryKey));
    }

    public function tagViewUrl($tag = false)
    {
        return Yii::app()->createUrl('sectionvideo/sectionvideo/index', array('tag' => $tag));
    }

    public static function getTypesList()
    {
        return array(
            self::TYPE_SOLO => 'Сольный номер',
            self::TYPE_GROUP => 'Группа/Ансамбль',
            self::TYPE_CONCERT => 'Концерт',
        );
    }

    public function getTypeString()
    {
        $types = self::getTypesList();
        return isset($types[$this->type]) ? $types[$this->type] : '';
    }

    /**
     * @return array типы полей для форм администрирования модуля
     */
    public function getFieldTypes()
    {
        return array(
            'composition_name' => 'text',
            'composition_author' => 'text',
            'type' => array(
                'type' => 'dropdownlist',
                'items' => $this->typesList,
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
            ),
            'title' => 'text',
            'VideoMusicians' => 'hasManyForm',
            'video_url' => 'text',
            'event_id' => 'hidden',
            'event' => array(
                'type' => 'ext.fields.jui.JuiAutoDepComplete',
                'source' => '/admin/sectionvideo/acevent',
                'select' => array(
                    'id' => 'event_id',
                    'value' => 'event',
                ),
                'iconOptions' => array(
                    'class' => 'icon_delete',
                ),
            ),

            'tags' => 'tags',
            'description' => 'markdown',
        );
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
        $criteria->compare('rating', $this->rating, true);
        $criteria->compare('tags', $this->tags, true);
        $criteria->compare('date_create', $this->date_create, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }
}
