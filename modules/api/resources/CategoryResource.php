<?php

namespace app\modules\api\resources;

use app\models\Category;

class CategoryResource extends Category 
{
    public function fields()
    {
        return ['id', 'name', 'description', 'created_at'];
    }

    public function getProducts()
    {
        return $this->hasMany(ProductResource::class, ['category_id' => 'id', 'status' => 1]);
    }
}