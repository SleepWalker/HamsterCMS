<?php
/**
 * The class to handle request persistence
 */

namespace contest\crud;

use contest\models\view\ApplyForm;
use contest\models\Request;
use contest\models\Musician;
use contest\models\Composition;
use contest\models\ContestId;
use KoKoKo\assert\Assert;

class RequestCrud
{
    /**
     * @param  ApplyForm $form
     *
     * @throws Exception if can not create record
     *
     * @return Request
     */
    public function create(ApplyForm $form) : Request
    {
        $transaction = \Yii::app()->db->beginTransaction();

        try {
            $request = $form->request;

            self::saveOrThrow($request);

            foreach ($form->compositions as $composition) {
                $composition->request_id = $request->primaryKey;

                self::saveOrThrow($composition);
            }

            foreach ($form->musicians as $musician) {
                if ($musician->isEmpty()) {
                    continue;
                }

                $musician->request_id = $request->primaryKey;

                self::saveOrThrow($musician);
            }

            $transaction->commit();

            return $request;
        } catch (\Exception $e) {
            $transaction->rollBack();

            \Yii::log('Error adding request: ' . $e->getMessage(), \CLogger::LEVEL_ERROR);

            throw new \Exception('Error saving data', 0, $e);
        }
    }

    /**
     * @param  Request $request
     *
     * @throws Exception if can not update record
     *
     * @return Request
     */
    public static function update(Request $request)
    {
        $transaction = \Yii::app()->db->beginTransaction();

        try {
            self::saveOrThrow($request);

            foreach ($request->compositions as $index => $composition) {
                self::saveOrThrow($composition);
            }

            foreach ($request->musicians as $index => $musician) {
                self::saveOrThrow($musician);
            }

            $transaction->commit();

            return $request;
        } catch (\Exception $e) {
            $transaction->rollBack();

            \Yii::log('Error saving request: ' . $e->getMessage(), \CLogger::LEVEL_ERROR);

            throw new \Exception('Error saving data', 0, $e);
        }
    }

    public static function findByPk($pk)
    {
        return Request::model()->with('compositions', 'musicians')->findByPk($pk);
    }

    public static function findAll(ContestId $contestId = null, array $attributes = [])
    {
        if ($contestId) {
            $attributes['contest_id'] = $contestId->getValue();
        }

        return Request::model()
            ->with('compositions', 'musicians')
            ->findAllByAttributes($attributes);
    }

    public static function findNotConfirmed(ContestId $contestId = null)
    {
        $attributes = [
            'status' => Request::STATUS_ACCEPTED,
        ];
        if ($contestId) {
            $attributes['contest_id'] = $contestId->getValue();
        }

        return Request::model()
            ->with('compositions', 'musicians')
            ->findAllByAttributes($attributes);
    }

    public static function findAccepted(ContestId $contestId = null)
    {
        $attributes = [
            'status' => [
                Request::STATUS_ACCEPTED,
                Request::STATUS_WAIT_CONFIRM,
                Request::STATUS_CONFIRMED,
            ],
        ];
        if ($contestId) {
            $attributes['contest_id'] = $contestId->getValue();
        }

        return Request::model()
            ->with('compositions', 'musicians')
            ->findAllByAttributes($attributes);
    }

    public static function decline($pk)
    {
        $request = Request::model()->findByPk($pk);
        if (!$request) {
            throw new \Exception("Can't find request with id $pk");
        }

        $request->status = Request::STATUS_DECLINED;

        if (!$request->save()) {
            throw new \Exception('Error saving request: ' . var_export($request->getErrors(), true));
        }
    }

    public static function accept($pk)
    {
        $request = Request::model()->findByPk($pk);
        if (!$request) {
            throw new \Exception("Can't find request with id $pk");
        }

        $request->status = Request::STATUS_ACCEPTED;

        if (!$request->save()) {
            throw new \Exception('Error saving request: ' . var_export($request->getErrors(), true));
        }
    }

    private static function saveOrThrow(\CActiveRecord $model)
    {
        if (!$model->save()) {
            throw new \Exception(
                'Error saving ' . get_class($model) . ': '
                . var_export($model->getErrors(), true)
            );
        }
    }
}
