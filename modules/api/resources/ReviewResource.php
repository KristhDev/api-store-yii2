<?php

namespace app\modules\api\resources;

use app\models\Review;

class ReviewResource extends Review 
{
    public function fields()
    {
        return ['id', 'user', 'product_id', 'comment', 'starts', 'status', 'created_at'];
    }

    public function getUser()
    {
        return $this->hasOne(UserResource::class, ['id' => 'user_id']);
    }
}