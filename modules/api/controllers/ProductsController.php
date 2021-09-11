<?php

namespace app\modules\api\controllers;

use app\modules\api\resources\ProductResource;
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
            ProductResource::class, 
            ['status' => 1], 
            'id desc', true, 
            $search, 
            ['name', 'description', 'price']
        );
        
        return ($products !== [])
            ? ['products' => $products, 'status' => 'ok']
            : ['message' => 'No results found', 'status' => 'ops'];
    }

    public function actionView($id) 
    {
        return $this->findModel(ProductResource::class, ['id' => $id]);
    }

    public function actionCreate()
    {
        $this->checkAccess('create');
        $model = new ProductResource();

        return $this->saveOrUpdateModel(
            $model, 
            'Product created successfully', 
            'uploads/products', 
            'image'
        );
    }

    public function actionUpdate($id)
    {
        $this->checkAccess('update');
        $model = $this->findModel(ProductResource::class, ['id' => $id, 'status' => 1]);

        return $this->saveOrUpdateModel(
            $model, 
            'Product updated successfully', 
            'uploads/products', 
            'image'
        );
    }

    public function actionDelete($id)
    {
        $this->checkAccess('delete');
        $model = $this->findModel(ProductResource::class, ['id' => $id, 'status' => 1]);

        return $this->deleteModel(
            $model, 
            'Product removed successfully', 
            ['status' => 0, 'image' => 'image deleted']
        );
    }
}