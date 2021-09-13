<?php

namespace app\modules\api\resources;

use app\models\Product;

class ProductResource extends Product 
{
    public function fields()
    {
        return ['id', 'category_id', 'name', 'description', 'price', 'stock', 'image'];
    }

    public function extraFields()
    {
        return ['reviews'];
    }

    public function getReviews()
    {
        return $this->hasMany(ReviewResource::class, ['product_id' => 'id'])->where(['status' => '1']) ?: [];
    }
}