<?php
namespace contest\models;

use contest\models\Contest;

/**
 * The followings are the available columns in table:
 * @property string $id
 * @property string $key
 * @property string $value
 */
class Settings extends \CActiveRecord
{
    public $key = 'settings';
    public $value = '{}';

    public $nextContestId;

    /**
     * @return string the associated database table name
     */
    public function tableName(): string
    {
        return '{{contest_store}}';
    }

    public static function getInstance(): Settings
    {
        static $cache;

        if (!$cache) {
            $cache = Settings::model()->findByAttributes(['key' => 'settings']);
        }

        if (!$cache) {
            $cache = new Settings();
        }

        return $cache;
    }

    public function getActiveContest(): ?Contest
    {
        if ($this->nextContestId) {
            return Contest::model()->findByPk($this->nextContestId);
        }

        return null;
    }

    protected function beforeSave()
    {
        if (!parent::beforeSave()) {
            return false;
        }

        $this->value = json_encode([
            'nextContestId' => $this->nextContestId,
        ]);

        if (!$this->value) {
            throw new \CException('Can not serialize settings. Code: ' . json_last_error_msg(), json_last_error());
        }

        if ($this->key !== 'settings') {
            throw new \DomainException('Wrong settings store key: ' . $this->key);
        }

        return true;
    }

    protected function afterConstruct()
    {
        $this->unserialize();

        parent::afterConstruct();
    }

    protected function afterFind()
    {
        $this->unserialize();

        parent::afterFind();
    }

    private function unserialize()
    {
        $this->key = 'settings';

        if ($this->value) {
            $value = json_decode($this->value, true);
        }

        if (!$value) {
            $value = [];
        }

        foreach($value as $attribute => $value) {
            if (property_exists($this, $attribute)) {
                $this->setAttribute($attribute, $value);
            }
        }
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules(): array
    {
        return [
            ['nextContestId', 'numerical', 'min' => 1, 'allowEmpty' => true],
            ['nextContestId', 'validateContestId'],
        ];
    }

    public function getFieldTypes(): array
    {
        return [
            'nextContestId' => 'autocomplete',
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'nextContestId' => 'Близжайший конкурс',
        ];
    }

    public function validateContestId(string $attribute, array $params = [])
    {
        if (empty($this->$attribute)) {
            $this->$attribute = null;
        } else if (!Contest::model()->findByPk($this->$attribute)) {
            $this->addError($attribute, 'Не правильный идентификатор мероприятия');
        }
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
