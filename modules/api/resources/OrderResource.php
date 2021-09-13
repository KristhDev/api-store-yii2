<?php

namespace app\modules\api\resources;

use app\models\Order;

class OrderResource extends Order 
{
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['product_id']);
        $fields = array_merge($fields, ['product']);

        return $fields;
    }

    public function getProduct()
    {
        return $this->hasOne(ProductResource::class, ['id' => 'product_id'])->where(['status' => 1]);
    }
}