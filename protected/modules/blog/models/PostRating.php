<?php

namespace blog\models;

class PostRating extends \application\models\Rating
{
    public function tableName()
    {
        return '{{blog_post_rating}}';
    }
}
