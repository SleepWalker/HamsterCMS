<?php
/**
 * HRatingBehavior поведение, добавляющее функционал рейтинга в модель
 *
 * @uses CActiveRecordBehavior
 * @package hamster.components.HRatingBehavior
 * @version $id$
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @copyright  Copyright &copy; 2013 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

use \application\models\Rating;

// TODO: аттрибут-флаг, который включит автоматиеческое создание поля $attribute, если его нету в бд
class HRatingBehavior extends CActiveRecordBehavior
{
    public $attribute = 'rating';
    public $ratingModelClass;

    public function attach($owner)
    {
        parent::attach($owner);

        if (!$this->owner->hasAttribute($this->attribute)) {
            throw new \CException('Не могу найти поле `' . $this->owner->tableName() . '`.`' . $this->attribute . '`. Создайте это поле или задайте правильное имя аттрибуту HRatingBehavior::attribute: ALTER TABLE `' . $this->owner->tableName() . '` ADD `' . $this->attribute . '` DECIMAL( 7, 3 ) UNSIGNED NOT NULL');
        }

        if (!isset($this->ratingModelClass) || !class_exists($this->ratingModelClass)) {
            throw new \CException('HRatingBehavior::ratingModelClass является обязательным');
        }

        if (is_subclass_of($this->ratingModelClass, '\aplication\models\Rating')) {
            throw new \CException('ratingModelClass должен наследовать \aplication\models\Rating');
        }
    }

    /**
     * Добавляет голос за текущую модель
     *
     * @param mixed $value оценка модели
     * @access public
     * @return void
     */
    public function addVote($value)
    {
        if (\Yii::app()->request->isAjaxRequest) {
            try {
                header('Content-Type: application/json');

                if (empty($value) || ($value < 1 || $value > 5)) {
                    throw new \CDbException('Нету всех необходимых параметров');
                }

                $value = round($value);

                $ratingModel = new $this->ratingModelClass();
                $ratingModel->attributes = array(
                    'source_id' => $this->owner->primaryKey,
                    'user_id' => \Yii::app()->user->id,
                    'value' => $value,
                    'ip' => \Yii::app()->request->getUserHostAddress(),
                );
                $ratingModel->save();
            } catch (\CDbException $e) {
                echo \CJSON::encode(array(
                    'status' => 'fail',
                    'answer' => 'Вы уже голосовали за этот продукт!',
                ));
                return;
            }

            if (empty($this->getRatingVal())) {
                $rating = '1.' . $value * 100;
            } else {
                $rating = ($this->getVotesCount() + 1) . '.' . (round(($this->getRatingVal() + $value) / 2, 2) * 100);
            }

            $this->owner->rating = (float) $rating;
            if ($this->owner->save()) {
                echo \CJSON::encode(array(
                    'status' => 'success',
                    'answer' => 'Спасибо, ваш голос учтен!',
                ));
            }

        }
    }

    /**
     *  Выводит виджет с рейтингом
     */
    public function ratingWidget($params = array(), $showTotalVotes = false)
    {
        // запрос на изменение рейтинга
        if (\Yii::app()->request->isAjaxRequest && !in_array('callbackUrl', $params) && isset($_GET['val'])) {
            while (@ob_end_clean());
            $this->addVote($_GET['val']);
            \Yii::app()->end();
        }

        $defaults = array(
            'model' => $this->owner,
            'attribute' => 'ratingVal', // mark 1...5
            'readOnly' => false,
            'callbackUrl' => '',
        );

        $params = \CMap::mergeArray($defaults, $params);

        \Yii::app()->controller->widget('ext.EStarRating', $params);

        if ($showTotalVotes) {
            echo '<span style="vertical-align: 3px;">(' . $this->getVotesCount() . ')</span>';
        }

        // микроразметка для поисковиков
        ?>
        <span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
        <meta itemprop="ratingValue" content="<?= $this->getRatingVal();?>">
        <meta itemprop="ratingCount" content="<?= $this->getVotesCount();?>"></span></span>
        <?php
    }

    /**
     *  @return string количество проголосовавших юзеров
     */
    public function getVotesCount()
    {
        $rating[0] = 0;
        if ($this->getRating()) {
            $rating = explode('.', (string) $this->getRating());
        }

        return $rating[0];
    }

    /**
     *  @return string рейтинг
     */
    public function getRatingVal()
    {
        $rating = explode('.', (string) $this->getRating());
        return $rating[1] / 100;
    }

    /**
     * Возвращает название таблицы в которой будет хранится подробная информация о каждом голосе
     *
     * @access public
     * @return void
     */
    public function getRatingTableName()
    {
        return $this->owner->tableName() . '_rating';
    }

    /**
     * Сокращение для получения текущего рейтинга модели
     *
     * @access protected
     * @return void
     */
    protected function getRating()
    {
        return $this->owner->{$this->attribute};
    }
}
