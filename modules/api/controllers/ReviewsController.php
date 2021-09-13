<?php

namespace app\modules\api\controllers;

use app\modules\api\controllers\ApiController;
use app\modules\api\resources\ReviewResource;

class ReviewsController extends ApiController 
{
    public $modelClass = ReviewResource::class;

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
    }

    public function actionIndex($id)
    {
        $reviews = $this->findModels($this->modelClass, ['product_id' => $id, 'status' => '1']);

        return ($reviews !== []) 
            ? ['reviews' => $reviews, 'status' => 200]
            : ['message' => 'This product has no reviews', 'status' => 404];
    }
}