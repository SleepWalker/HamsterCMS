<?php
namespace contest\components;

use contest\models\view\ApplyForm;
use contest\models\Settings;
use CHttpRequest;

// TODO: abstract request (may be using symfony foundation)

class Factory
{
    public function createApplyForm(CHttpRequest $request = null): ApplyForm
    {
        $form = new ApplyForm();
        $contest = Settings::getInstance()->getActiveContest();

        if (!$contest || !$contest->canApply()) {
            throw new \DomainException('Can not create apply form. No active contests to apply to');
        }

        if ($request) {
            $form->load($request);
        }

        $form->request->contest_id = $contest->primaryKey;

        return $form;
    }
}
