<?php

namespace app\modules\api\controllers;

use Yii;
use yii\data\Pagination;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

use app\modules\api\controllers\ApiController;
use app\modules\api\resources\OrderResource;
use app\modules\api\resources\ProductResource;

class OrdersController extends ApiController
{
    public $modelClass = OrderResource::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['only'] = [
            'index', 'view', 'create', 'update', 'delete', 'delete-all', 'confirm', 'confirm-all', 'pdf'
        ];
        $behaviors['authenticator']['authMethods'] = [
            HttpBearerAuth::class
        ];
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'confirm' => ['put'],
                'confirm-all' => ['put'],
                'delete-all' => ['delete'],
                'pdf' => ['get']
            ]
        ];
        
        return $behaviors;
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['create']) || $model->user_id === Yii::$app->user->id) {
            return;
        }

        throw new ForbiddenHttpException('You do not have access to this record');
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
        $orders = $this->modelClass::find();

        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $orders->count()
        ]);

        $orders = $orders->where(['user_id' => Yii::$app->user->id])
            ->where(['<>', 'status', 'Cancelado'])->offset($pagination->offset)
            ->limit($pagination->limit)->orderBy('id desc')->all();

        return ($orders !== [])
            ? ['orders' => $orders, 'status' => 200]
            : ['message' => 'You have not placed any order', 'status' => 404];
    }

    public function actionView($id)
    {
        $order = $this->findModel($this->modelClass, ['id' => $id]);
        $this->checkAccess('view', $order);

        return $order;
    }

    public function actionCreate()
    {
        $this->checkAccess('create');
        $model = new OrderResource();
        
        return $this->saveOrUpdateOrder(
            $model, 
            'Your order has been made satisfactorily, please confirm by making your payment to make the shipment',
            201
        );
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($this->modelClass, ['id' => $id, 'status' => 'Pendiente']);
        $this->checkAccess('update', $model);

        return $this->saveOrUpdateOrder($model, 'You have successfully updated the order information', 200);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($this->modelClass, ['id' => $id, 'status' => 'Pendiente']);
        $this->checkAccess('delete', $model);

        return $this->deleteModel(
            $model, 
            'You have successfully canceled your order', 
            ['status' => 'Cancelado']
        );
    }

    public function actionDeleteAll()
    {
        $models = $this->modelClass::findAll(['user_id' => Yii::$app->user->id, 'status' => 'Pendiente']);

        if ($models === []) {
            return $this->successResponse('You have no orders to cancel', 200);
        }

        foreach ($models as $model) {
            $model->status = 'Cancelado';

            if (!$model->save()) return $this->errorResponse(
                ['message' => 'Could not cancel one of your orders, please contact the administrator'], 
                500
            );
        }

        return $this->successResponse('You have successfully canceled all your orders', 200);
    }

    public function actionConfirm($id)
    {
        $model = $this->findModel($this->modelClass, ['id' => $id, 'status' => 'Pendiente']);
        $this->checkAccess('confirm', $model);

        $product = $model->getProduct()->one();

        if ($product === null) {
            return $this->errorResponse(
                ['message' => 'Sorry, but this product is no longer available'],
                400
            );
        }
        else if ($product->stock === 0 ) {
            return $this->errorResponse(
                ['message' => 'We are sorry, but we no longer have this product. You can wait and we will notify you when there is more of this product or you can cancel your order'],
                400
            );
        }

        $model->status = 'Confirmado';
        $model->date_confirm = date('Y-m-d H:i:s');

        $product = $this->findModel(ProductResource::class, ['id' => $model->product_id, 'status' => 1]);
        $product->stock -= $model->quantity;

        if ($model->validate() && $model->save() && $product->save()) {
            return $this->successResponse('You have successfully confirmed your order, we will notify you when it is close to arriving', 200);
        }

        return $this->errorResponse($model->errors, 500);
    } 
    
    public function actionConfirmAll() 
    {
        if (!$this->request->isPut) return $this->methodNotAllowed('PUT');

        $models = $this->modelClass::findAll(['user_id' => Yii::$app->user->id, 'status' => 'Pendiente']);

        if ($models === []) {
            return $this->successResponse('You have no orders to confirm', 200);
        }

        foreach ($models as $model) {
            $model->status = 'Confirmado';
            $model->date_confirm = date('Y-m-d H:i:s');

            $product = ProductResource::findOne(['id' => $model->product_id, 'status' => 1]);
            $product->stock -= $model->quantity;

            if (!$product->save()) {
                return $this->errorResponse($product->errors, 500);
            }
            else if (!$model->save()) {
                return $this->errorResponse($model->errors, 500);
            }
        }

        return $this->successResponse(
            'You have confirmed all your orders successfully, we will notify you when they are close to arrival', 
            200
        );
    }

    public function actionPdf()
    {
        $orders = $this->modelClass::findAll(['user_id' => Yii::$app->user->id, 'status' => 'Confirmado']);
        $query = (new \yii\db\Query())->select(["SUM(total_to_pay) AS 'total_payed'"])->from('orders')
            ->where(['user_id' => Yii::$app->user->id, 'status' => 'Confirmado']);

        $command = $query->createCommand();
        $totalPayed = $command->queryOne();
        
        $html = $this->renderPartial('pdf', ['orders' => $orders, 'totalPayed' => $totalPayed]);

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->showImageErrors = true;
        $mpdf->SetDisplayMode('fullpage', 'two');
        $mpdf->list_indent_first_level = 0;
        $mpdf->WriteHTML($html);
        $mpdf->Output();
        exit;
    }

    protected function saveOrUpdateOrder(OrderResource $model, $successMessage, $statusSuccess) 
    {
        if ($model->load($this->request->post(), '')) {
            $product = $model->getProduct()->one();
            $model->user_id = Yii::$app->user->id;
            
            if ($product === null) {
                return $this->errorResponse(
                    ['message' => 'Sorry, this product is not available'], 
                    404
                );
            }
            else if ($product->stock === 0) {
                return $this->errorResponse(
                    ['message' => 'Sorry this product is out of stock, we will have more of this product soon'], 
                    404
                );
            }
            else if ($model->quantity > $product->stock) {
                return $this->errorResponse(
                    ['message' => 'Sorry, but we dont have that much product available'], 
                    404
                );
            }
            
            $model->total_to_pay = $model->quantity * $product->price;
        }

        if ($model->validate() && $model->save()) {
            return $this->successResponse($successMessage, $statusSuccess);
        }

        return $this->errorResponse($model->errors, 400);
    }
}