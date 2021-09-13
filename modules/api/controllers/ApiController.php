<?php

namespace app\modules\api\controllers;

use Yii;
use yii\data\Pagination;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class ApiController extends ActiveController {
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['only'] = ['create', 'update', 'delete'];
        $behaviors['authenticator']['authMethods'] = [
            HttpBearerAuth::class
        ];

        return $behaviors;
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['create', 'update', 'delete']) && Yii::$app->user->identity->type !== 'admin') {
            throw new ForbiddenHttpException('You do not have permission to change this record');
        }
    }

    public function methodNotAllowed($method)
    {
        return [
            'name' => 'Method Not Allowed',
            'message' => 'Method Not Allowed. This URL can only handle the following request methods: ' . $method,
            'code' => 0,
            'status' => 405,
            'type' => 'yii\\web\\MethodNotAllowedHttpException'
        ];
    }

    public function successResponse($message, $status){
        return [
            'message' => $message,
            'status' => $status
        ];
    }

    public function errorResponse($errors, $status)
    {
        $this->response->statusCode = $status;

        return [
            'errors' => $errors,
            'status' => $status
        ];
    }

    public function findModel($model, $condition)
    {
        $record = $model::findOne($condition);
        if ($record !== null) return $record;

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function findModels($model, $condition, $order = '', $paginate = true, $search = '', $fieldsSearch = [])
    {
        $model = $model::find();
        $pagination = false;
        $results = false;

        if ($paginate) {
            $pagination = new Pagination([
                'defaultPageSize' => 10,
                'totalCount' => $model->count()
            ]);
        }

        if ($paginate) {
            $results = $model;

            if (!empty($search)) {
                foreach ($fieldsSearch as $fieldSearch) {
                    $results = $results->orWhere(['like', $fieldSearch, $search]);
                }  
            }

            $results = $results->andWhere($condition)->offset($pagination->offset)
                ->limit($pagination->limit)->orderBy($order)->all();
        }
        else {
            $results = $model->where($condition)->orderBy($order)->all();
        }

        return $results;
    }

    public function saveOrUpdateModel($model, $successMsg, $imageStoragePath = null, $fileName = '', $statusSuccess) {
        //* Recomendable esta linea antes de subir cualquier archivo
        Yii::$app->request->getBodyParams();
        //* Para subir imagenes a rest usar getInstanceByName
        $file = UploadedFile::getInstanceByName($fileName);

        $model->load($this->request->post(), '');

        if ($imageStoragePath !== null) { 
            if ($file) {
                $model->file = $file;
                $this->uploadImage($model, $imageStoragePath);
            }
        }

        if ($model->validate()) {
            if ($model->save()) return $this->successResponse($successMsg, $statusSuccess);
        }

        return $this->errorResponse($model->errors, 400);
    }

    public function deleteModel($model, $successMsg, $updatedFields)
    {
        if (isset($model->image)) $this->deleteImage($model->image);

        if ($model->load($updatedFields, '')){
            if ($model->save()) return $this->successResponse($successMsg, 200);
        }
        
        return $this->errorResponse($model->errors, 500);
    }

    public function uploadImage($model, $basicPath)
    {
        if ($model->image) $this->deleteImage($model->image);
        
        if ($model->validate()) {
            if ($model->file) {
                $urlImage = $basicPath . '/' . time() . $model->file->baseName . '.' . $model->file->extension;
                $model->file->saveAs($urlImage, false);
                $model->image = $urlImage;
    
                return true;
            }
        }

        return false;
    }

    public function deleteImage($image)
    {
        if (file_exists($image)) unlink($image);
    }
}