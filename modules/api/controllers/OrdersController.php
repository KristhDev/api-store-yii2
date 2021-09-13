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

        $behaviors['authenticator']['only'] = ['index', 'view', 'create', 'update', 'delete'];
        $behaviors['authenticator']['authMethods'] = [
            HttpBearerAuth::class
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

    public function actionConfirm($id)
    {
        if (!$this->request->isPut) return $this->methodNotAllowed('PUT');

        $model = $this->findModel($this->modelClass, ['id' => $id, 'status' => 'Pendiente']);
        $this->checkAccess('confirm', $model);

        $model->status = 'Confirmado';
        $model->date_confirm = date('Y-m-d H:i:s');

        $product = ProductResource::find()->where(['id' => $model->product_id, 'status' => 1])->one();
        $product->stock -= $model->quantity;

        if ($model->validate() && $model->save() && $product->save()) {
            return $this->successResponse('You have successfully confirmed your order, we will notify you when it is close to arriving', 200);
        }

        return $this->errorResponse($model->errors, 500);
    }

    protected function saveOrUpdateOrder(OrderResource $model, $successMessage, $statusSuccess) 
    {
        if ($model->load($this->request->post(), '')) {
            $product = $model->getProduct()->one();
            $model->user_id = Yii::$app->user->id;
            
            if ($product !== null)
                $model->total_to_pay = $model->quantity * $product->price;
        }

        if ($model->validate() && $model->save()) {
            return $this->successResponse($successMessage, $statusSuccess);
        }

        return $this->errorResponse($model->errors, 400);
    }
}