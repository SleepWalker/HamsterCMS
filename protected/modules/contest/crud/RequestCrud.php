<?php
/**
 * The class to handle request persistence
 */

namespace contest\crud;

use contest\models\view\ApplyForm;
use contest\models\Request;
use contest\models\Musician;
use contest\models\Composition;

class RequestCrud
{
    /**
     * @param  ApplyForm $form
     *
     * @throws Exception if can not create record
     *
     * @return Request
     */
    public static function create(ApplyForm $form)
    {
        $transaction = \Yii::app()->db->beginTransaction();
        try {
            $requestAR = new Request();
            $requestAR->attributes = $form->request->attributes;

            self::saveOrThrow($requestAR);

            foreach ($form->compositions as $composition) {
                $compositionAR = new Composition();
                $compositionAR->attributes = $composition->attributes;
                $compositionAR->request_id = $requestAR->primaryKey;

                self::saveOrThrow($compositionAR);
            }

            foreach ($form->musicians as $musician) {
                if ($musician->isEmpty()) {
                    continue;
                }

                $musicianAR = new Musician();
                $musicianAR->attributes = $musician->attributes;
                $musicianAR->request_id = $requestAR->primaryKey;

                self::saveOrThrow($musicianAR);
            }

            $transaction->commit();

            return $requestAR;
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

    private static function saveOrThrow(\CActiveRecord $model)
    {
        if (!$model->save()) {
            throw new \Exception(
                'Error saving ' . get_class($model) . ': '
                . var_export($model->getErrors(), true)
            );
        }
    }

    public static function findByPk($pk)
    {
        return Request::model()->with('compositions', 'musicians')->findByPk($pk);
    }

    public static function findAll()
    {
        return Request::model()->with('compositions', 'musicians')->findAll();
    }

    public static function findNotConfirmed()
    {
        return Request::model()->with('compositions', 'musicians')
            ->findAll('status = ' . Request::STATUS_ACCEPTED);
    }

    public static function findAccepted()
    {
        return Request::model()->with('compositions', 'musicians')
            ->findAll('status NOT IN (' . implode(', ', [Request::STATUS_NEW, Request::STATUS_DECLINED]) . ')');
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
}
