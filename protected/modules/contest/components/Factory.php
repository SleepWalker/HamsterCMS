<?php
namespace contest\components;

use contest\models\view\ApplyForm;
use contest\models\Settings;
use CHttpRequest;

class Factory
{
    public function getSettings(): Settings
    {
        return Settings::getInstance();
    }

    public function createApplyForm(CHttpRequest $request = null): ApplyForm
    {
        $form = new ApplyForm();

        if ($request) {
            $form->load($request);
        }

        return $form;
    }
}
