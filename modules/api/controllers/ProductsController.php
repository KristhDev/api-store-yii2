<?php

namespace app\modules\api\controllers;

use yii\data\Pagination;

use app\modules\api\resources\ProductResource;
use app\modules\api\resources\ReviewResource;
use app\modules\api\controllers\ApiController;

class ProductsController extends ApiController
{
    public $modelClass = ProductResource::class;

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);

        return $actions;
    }

    public function actionIndex()
    {
        $search = $this->request->get('search');
        $products = $this->findModels(
            $this->modelClass, 
            ['status' => 1], 
            'id desc', true, 
            $search, 
            ['name', 'description', 'price']
        );
        
        return ($products !== [])
            ? ['products' => $products, 'status' => 200]
            : ['message' => 'No results found', 'status' => 404];
    }

    public function actionView($id) 
    {
        return $this->findModel($this->modelClass, ['id' => $id]);
    }

    public function actionCreate()
    {
        $this->checkAccess('create');
        $model = new ProductResource();

        return $this->saveOrUpdateModel(
            $model, 
            'Product created successfully', 
            201,
            'uploads/products', 
            'image'
        );
    }

    public function actionUpdate($id)
    {
        $this->checkAccess('update');
        $model = $this->findModel($this->modelClass, ['id' => $id, 'status' => 1]);

        return $this->saveOrUpdateModel(
            $model, 
            'Product updated successfully', 
            200,
            'uploads/products', 
            'image'
        );
    }

    public function actionDelete($id)
    {
        $this->checkAccess('delete');
        $model = $this->findModel($this->modelClass, ['id' => $id, 'status' => 1]);

        return $this->deleteModel(
            $model, 
            'Product removed successfully', 
            ['status' => 0, 'image' => 'image deleted']
        );
    }

    public function actionBest()
    {
        $products = [];

        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => ProductResource::find()->count()
        ]);

        $reviews = ReviewResource::find()->select(['SUM(IFNULL(starts, 0)) as starts', 'product_id'])
            ->where(['status' => 1])->groupBy(['product_id'])->orderBy(['starts'=>SORT_DESC])
            ->offset($pagination->offset)->limit($pagination->limit)->all() ?: [];

        foreach($reviews as $review) {
            if(($producto = ProductResource::findOne(['id' => $review->product_id, 'status' => 1]))) {
                $products[] = ['product' => $producto, 'totalPoints' => (int) $review->starts];
            }
        }

        return ['products' => $products, 'status' => 200];
    }
}