<?php

namespace app\modules\api\controllers;

use app\modules\api\resources\CategoryResource;
use app\modules\api\resources\OrderResource;
use app\modules\api\resources\ProductResource;
use yii\data\Pagination;

class CategoriesController extends ApiController 
{
    public $modelClass = CategoryResource::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'products' => ['get'],
            ]
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
    }

    public function actionIndex()
    {
        $categories = $this->findModels($this->modelClass, ['status' => 1], 'id desc', false);

        return ($categories !== []) 
            ? ['categories' => $categories, 'status' => 200]
            : ['message' => 'No results found', 'status' => 404];
    }

    public function actionView($id) 
    {
        return $this->findModel($this->modelClass, ['id' => $id]);
    }

    public function actionCreate()
    {
        $this->checkAccess('create');
        $model = new CategoryResource();

        return $this->saveOrUpdateModel($model, 'Category created successfully', 201);
    }

    public function actionUpdate($id)
    {
        $this->checkAccess('update');
        $model = $this->findModel($this->modelClass, ['id' => $id, 'status' => 1]);
        
        return $this->saveOrUpdateModel($model, 'Category updated successfully', 200);
    }

    public function actionDelete($id)
    {
        $this->checkAccess('delete');
        $model = $this->findModel($this->modelClass, ['id' => $id, 'status' => 1]);

        $products = ProductResource::findAll(['category_id' => $id, 'status' => 1]);

        foreach ($products as $product) {
            $this->deleteModel(
                $product, 
                'Product removed successfully', 
                ['status' => 0, 'image' => 'image deleted']
            );
        }
        
        return $this->deleteModel(
            $model, 
            'Category removed successfully', 
            ['status' => 0]
        );
    }

    public function actionProducts($id) 
    {
        $products = $this->findModels(ProductResource::class, ['category_id' => $id, 'status' => 1], 'id desc');

        return ($products !== []) 
            ? ['products' => $products, 'status' => 200] 
            : ['message' => 'There are no products for this category.', 'status' => 404];
    }

    public function actionBest($id) 
    {
        $products = [];

        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => ProductResource::find()->count()
        ]);

        $reviews = (new \yii\db\Query())->from(['p' => 'products'])
            ->select(['p.id AS product_id', 'SUM(IFNULL(r.starts, 0)) AS starts'])
            ->where(['p.category_id' => $id, 'p.status' => 1])
            ->innerJoin(['r' => 'reviews'], 'p.id = r.product_id')
            ->groupBy('p.id')->orderBy(['starts' => SORT_DESC])
            ->offset($pagination->offset)->limit($pagination->limit)
            ->all() ?: [];

        foreach ($reviews as $review) {
            if ($product = ProductResource::findOne(['id' => $review['product_id'], 'status' => 1])) {
                $products[] = ['product' => $product, 'starts' => (int) $review['starts']];
            }
        }

        return ($products !== []) 
            ? ['products' => $products, 'status' => 200]
            : ['message' => 'This category has no rated products', 'status' => 200];
    }

    public function actionBestSellers($id) 
    {
        $products = [];

        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => ProductResource::find()->count()
        ]);

        $orders = (new \yii\db\Query())->from(['p' => 'products'])
        ->select(['p.id AS product_id', 'p.category_id', 'SUM(IFNULL(o.quantity, 0)) AS total_sold'])
        ->where(['p.category_id' => $id, 'p.status' => 1, 'o.status' => 'Confirmado'])
        ->innerJoin(['o' => 'orders'], 'o.product_id = p.id')
        ->groupBy('p.id')->orderBy(['total_sold' => SORT_DESC])
        ->offset($pagination->offset)->limit($pagination->limit)
        ->all() ?: [];

        foreach ($orders as $order) {
            if ($product = ProductResource::findOne(['id' => $order['product_id'], 'status' => 1])) {
                $products[] = ['product' => $product, 'total_sold' => (int) $order['total_sold']];
            }
        }

        return ($products !== [])
            ? ['products' => $products, 'status' => 200]
            : ['message' => 'This category has no purchased products', 'status' => 200];
    }
}