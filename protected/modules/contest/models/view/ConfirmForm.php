<?php

namespace contest\models\view;

class ConfirmForm extends \CFormModel
{
    public $hasMinus;
    public $needSoundcheck;
    public $willInviteFriends = 1;

    public function rules()
    {
        return [
            ['willInviteFriends', 'required'],
            ['hasMinus, needSoundcheck', 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'needSoundcheck' => 'Мне нужен саундчек',
            'hasMinus' => 'Я играю (или пою) под минус',
            'willInviteFriends' => 'Я обещаю '.\CHtml::link('пригласить', 'http://vk.com/event93888565').' на конкурс всю семью и всех друзей!',
        ];
    }
}
