<?php

namespace app\modules\api\controllers;

use Yii;
use yii\web\ForbiddenHttpException;

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

    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['delete']) && 
            (Yii::$app->user->identity->type === 'admin' || Yii::$app->user->id === $model->user_id)
        ) {
            return;
        }

        throw new ForbiddenHttpException('You do not have permission to change this record');
    }

    public function actionIndex($id)
    {
        $reviews = $this->findModels($this->modelClass, ['product_id' => $id, 'status' => '1']);

        return ($reviews !== []) 
            ? ['reviews' => $reviews, 'status' => 200]
            : ['message' => 'This product has no reviews', 'status' => 404];
    }

    public function actionCreate($id)
    {
        $model = new ReviewResource();
        $model->product_id = $id;
        $model->user_id = Yii::$app->user->id;

        return $this->saveOrUpdateModel(
            $model,
            'You have made your review satisfactorily',
            201
        );
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($this->modelClass, ['id' => $id, 'status' => '1']);

        return $this->saveOrUpdateModel(
            $model,
            'You have successfully updated your review',
            200
        );
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($this->modelClass, ['id' => $id, 'status' => '1']);

        return $this->deleteModel(
            $model, 
            'You have successfully removed this review', 
            ['status' => '0']
        );
    }
}