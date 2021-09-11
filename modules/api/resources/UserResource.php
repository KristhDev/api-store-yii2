<?php

namespace app\modules\api\resources;

use app\models\User;

class UserResource extends User
{
    public function fields()
    {
        return ['id', 'name', 'surname', 'email', 'image', 'created_at'];
    }
}