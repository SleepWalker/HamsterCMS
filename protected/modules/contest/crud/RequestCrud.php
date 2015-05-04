<?php
/**
 * The class to handle request persistence
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @copyright  Copyright &copy; 2015 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace contest\crud;

class RequestCrud
{
    public static function save(\contest\models\view\Request $request)
    {
        $transaction = \Yii::app()->db->beginTransaction();
        try {
            $requestAR = new \contest\models\Request();
            $requestAR->attributes = $request->attributes;

            if (!$requestAR->save()) {
                throw new \Exception('Error saving request: ' . var_export($requestAR->getErrors(), true));
            }

            foreach ($request->compositions as $composition) {
                $compositionAR = new \contest\models\Composition();
                $compositionAR->attributes = $composition->attributes;
                $compositionAR->request_id = $requestAR->primaryKey;

                if (!$compositionAR->save()) {
                    throw new \Exception('Error saving composition: ' . var_export($compositionAR->getErrors(), true));
                }
            }

            foreach ($request->musicians as $musician) {
                if ($musician->isEmpty()) {
                    continue;
                }

                $musicianAR = new \contest\models\Musician();
                $musicianAR->attributes = $musician->attributes;
                $musicianAR->request_id = $requestAR->primaryKey;

                if (!$musicianAR->save()) {
                    throw new \Exception('Error saving musician: ' . var_export($musicianAR->getErrors(), true));
                }
            }

            $transaction->commit();
        } catch (\CException $e) {
            $transaction->rollBack();

            \Yii::log('Error adding request: ' . $e->getMessage(), \CLogger::LEVEL_ERROR);

            throw new \Exception('Error saving data', 0, $e);
        }
    }
}