<?php

namespace app\modules\api\controllers;

use app\modules\api\resources\CategoryResource;
use app\modules\api\resources\ProductResource;

class CategoriesController extends ApiController 
{
    public $modelClass = CategoryResource::class;

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
        $categories = $this->findModels(CategoryResource::class, ['status' => 1], 'id desc', false);

        return ($categories !== []) 
            ? ['categories' => $categories, 'status' => 'ok']
            : ['message' => 'No results found', 'status' => 'ops'];
    }

    public function actionView($id) 
    {
        return $this->findModel(CategoryResource::class, ['id' => $id]);
    }

    public function actionCreate()
    {
        $this->checkAccess('create');
        $model = new CategoryResource();

        return $this->saveOrUpdateModel(
            $model, 
            'Category created successfully'
        );
    }

    public function actionUpdate($id)
    {
        $this->checkAccess('update');
        $model = $this->findModel(CategoryResource::class, ['id' => $id, 'status' => 1]);
        
        return $this->saveOrUpdateModel(
            $model, 
            'Category updated successfully'
        );
    }

    public function actionDelete($id)
    {
        $this->checkAccess('delete');
        $model = $this->findModel(CategoryResource::class, ['id' => $id, 'status' => 1]);
        
        return $this->deleteModel(
            $model, 
            'Category removed successfully', 
            ['status' => 0]
        );
    }

    public function actionProducts($id) {
        $products = $this->findModels(ProductResource::class, ['category_id' => $id, 'status' => 1], 'id desc');

        return ($products !== []) 
            ? ['products' => $products, 'status' => 'ok' ] 
            : ['status' => 'ops', 'message' => 'There are no products for this category.'];
    }
}