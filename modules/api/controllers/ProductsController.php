<?php

namespace app\modules\api\controllers;

use yii\data\Pagination;

use Yii;
use yii\web\ForbiddenHttpException;

use app\modules\api\resources\ProductResource;
use app\modules\api\resources\ReviewResource;
use app\modules\api\controllers\ApiController;
use app\modules\api\resources\CategoryResource;
use app\modules\api\resources\OrderResource;

class ProductsController extends ApiController
{
    public $modelClass = ProductResource::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'best' => ['get'],
                'best-sellers' => ['get']
            ]
        ];

        return $behaviors;
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['create', 'update', 'delete', 'inventory']) && Yii::$app->user->identity->type !== 'admin') {
            throw new ForbiddenHttpException('You do not have permission to change this record');
        }
    }


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
            'file'
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
            'file'
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
            ->where(['status' => 1])->groupBy(['product_id'])->orderBy(['starts' => SORT_DESC])
            ->offset($pagination->offset)->limit($pagination->limit)->all() ?: [];

        foreach($reviews as $review) {
            if(($product = ProductResource::findOne(['id' => $review->product_id, 'status' => 1]))) {
                $products[] = ['product' => $product, 'totalPoints' => (int) $review->starts];
            }
        }

        return ['products' => $products, 'status' => 200];
    }

    public function actionBestSellers() 
    {
        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => ProductResource::find()->count()
        ]);

        $orders = OrderResource::find()->select(['SUM(IFNULL(quantity, 0)) AS quantity', 'product_id'])
            ->where(['status' => 'Confirmado'])->groupBy(['product_id'])->orderBy(['quantity' => SORT_DESC])
            ->offset($pagination->offset)->limit($pagination->limit)->all() ?: [];

        return ($orders !== []) 
            ? ['products' => $orders, 'status' => 200]
            : ['message' => 'No products have been purchased', 'status' => 200];
    }

    public function actionInventory()
    {
        $this->checkAccess('inventory');
        
        $categories = CategoryResource::find()->all(); 
        $inventory = [];

        foreach($categories as $category) {
            $products = (new \yii\db\Query())->from(['p' => 'products'])
            ->select(['p.*', 'IFNULL(COUNT(o.id), 0) as orders'])
            ->where(['p.category_id' => $category->id])
            ->leftJoin(['o' => 'orders'], 'o.product_id = p.id')
            ->groupBy(['p.id'])
            ->all();

            if ($products) {
                $total_products = (int) $this->modelClass::find()->where(['category_id' => $category->id])->count();
                $total_quantity = (new \yii\db\Query())->select(['SUM(IFNULL(stock, 0)) as stock'])->from('products')
                    ->where(['category_id' => $category->id])->one();

                $total_orders = (new \yii\db\Query)->from(['o' => 'orders'])
                    ->select(['COUNT(o.id) AS orders'])
                    ->where(['p.category_id' => $category->id])
                    ->innerJoin(['p' => 'products'], 'p.id = o.product_id')
                    ->one();

                $inventory[] = [
                    'category' => $category, 
                    'products' => $products, 
                    'total_products' => $total_products,
                    'total_quantity' => (int) $total_quantity['stock'],
                    'total_orders' => (int) $total_orders['orders']          
                ];
            }
        }

        $html = $this->renderPartial('inventory', ['inventory' => $inventory]);

        return $this->generatePdf($html); 
    }
}