<?php

namespace app\modules\api\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use app\models\LoginForm;

use app\modules\api\models\SignUp;
use app\modules\api\resources\UserResource;

class UsersController extends ApiController 
{
    public $modelClass = UserResource::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['only'] = ['update', 'delete'];

        return $behaviors;
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['update', 'delete']) && Yii::$app->user->id === $model->id) {
            return;
        }
        else if (in_array($action, ['delete']) && Yii::$app->user->identity->type === 'admin') {
            return;
        }
        else if (in_array($action, ['view'])) {
            return;
        }

        throw new ForbiddenHttpException('You do not have permission to change this record');
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

    public function actionSignup() 
    {
        $model = new SignUp();

        if ($model->load($this->request->post(), '') && $model->register()) {
            return $this->successResponse('You have successfully registered');
        }

        return $this->hasError($model); 
    }

    public function actionLogin()
    {
        $model = new LoginForm();
        $model->load($this->request->post(), '');

        if ($model->validate() && $model->login()) {
            return [
                'user' => $model->_user,
                'message' => 'You have successfully logged in'
            ];
        }

        return $this->hasError($model);
    }

    public function actionView($id)
    {
        return $this->findModel(UserResource::class, ['id' => $id, 'status' => 1]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel(UserResource::class, ['id' => $id, 'status' => 1]);
        $this->checkAccess('update', $model);

        return $this->saveOrUpdateModel(
            $model, 
            'You have successfully updated your profile', 
            'uploads/users', 
            'file'
        );
    }

    public function actionDelete($id)
    {
        $model = $this->findModel(UserResource::class, ['id' => $id, 'status' => 1]);
        $this->checkAccess('delete', $model);

        return $this->deleteModel(
            $model, 
            'User removed successfully', 
            ['status' => 0, 'image' => 'image deleted']
        );
    }
}