<?php
/**
 * The model for supporting registering for contest
 *
 * The followings are the available columns in table 'contest_request':
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $birthdate
 * @property string $email
 * @property string $phone
 * @property string $instrument
 * @property string $school
 * @property string $teacher
 * @property string $demos
 * @property string $date_created
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    contest.models
 * @copyright  Copyright &copy; 2015 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace contest\models;

class Request extends \CActiveRecord
{
    public $type = 'solo';

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('first_name, email', 'required'),
            array('last_name, instrument, birthdate', 'required', 'except' => array('group')),

            array('first_name, last_name, school, teacher', 'length', 'max' => 128),
            array('email, instrument', 'length', 'max' => 64),
            array('phone', 'length', 'max' => 25),
            array('phone', 'match', 'pattern' => '/\+38 \(\d{3}\) \d{3}\-\d{2}\-\d{2}/'),
            array('birthdate', 'match', 'pattern' => '/\d{2}\.\d{2}\.\d{4}/'),
            array('type', 'in', 'range' => array('solo', 'group')),
            array('email', 'email'),
            array('demos', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, first_name, last_name, birthdate, email, phone, instrument, school, teacher, demos, date_created', 'safe', 'on' => 'search'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'birthdate' => 'Дата рождения',
            'email' => 'Email',
            'phone' => 'Телефон',
            'instrument' => 'Инструмент',
            'school' => 'Школа',
            'teacher' => 'Преподаватель',
            'demos' => 'Ссылки на демо записи',
            'date_created' => 'Date Created',
        );
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{contest_request}}';
    }
}
