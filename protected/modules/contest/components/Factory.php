<?php
namespace contest\components;

use contest\models\view\ApplyForm;
use CHttpRequest;

// TODO: abstract request (may be using symfony foundation)

class Factory
{
    public function createApplyForm(CHttpRequest $request = null) : ApplyForm
    {
        $form = new ApplyForm();

        if ($request) {
            $form->load($request);
        }

        return $form;
    }
}
