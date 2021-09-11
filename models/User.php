<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class User
 * 
 * @package app\models
 * @property integer $id
 * @property string $name
 * @property string $surname
 * @property string $email
 * @property string $password
 * @property string $image
 * @property string $access_token
 * @property string $type
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 */
class User extends ActiveRecord implements \yii\web\IdentityInterface
{
    public $file;
    public $username;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    public static function tableName()
    {
        return '{{%users}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules()
    {
        $rules = [];

        if ($this->getId() !== null) {
            $rules = [
                [
                    'email', 'unique',
                    'targetClass' => '\app\modeles\User',
                    'message' => Yii::t('app', 'This email already belongs to a user'),
                    'when' => function ($model, $attribute) {
                        return $model->{$attribute} !== $model->getOldAttribute($attribute);
                    }
                ],
                [['name', 'surname', 'email', 'file', 'image'], 'default'],
                ['image', 'string'],
                ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
                [['file'], 'file', 'extensions' => 'jpg, png'],
            ];
        }
        else {
            $rules = [
                ['status', 'default', 'value' => self::STATUS_ACTIVE],
                ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]]
            ];
        }

        return $rules;
    }

    public function fields()
    {
        return ['id', 'name', 'surname', 'email', 'image', 'access_token', 'created_at'];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return self::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]) ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return self::findOne(['access_token' => $token, 'status' => self::STATUS_ACTIVE]) ?: null;
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return self::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]) ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return null;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAccessToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString();
    }
}
