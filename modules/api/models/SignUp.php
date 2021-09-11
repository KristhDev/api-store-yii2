<?php

namespace app\modules\api\models;

use app\modules\api\resources\UserResource;
use yii\base\Model;

/**
 * class SignUp
 * 
 * @package app\modules\api\models
 */
class SignUp extends Model {
    public $name;
    public $surname;
    public $email;
    public $password;
    public $password_repeat;

    public $_user = false; 

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [
                'email', 'unique',
                'targetClass' => '\app\modules\api\resources\UserResource',
                'message' => 'This email already belongs to a user'
            ],
            [['name', 'surname', 'email' ,'password', 'password_repeat'], 'required'],
            ['email', 'email'],
            ['password', 'compare', 'compareAttribute' => 'password_repeat']
        ];
    }

    public function register()
    {
        $this->_user = new UserResource();

        if ($this->validate()) {
            $this->_user->name = $this->name;
            $this->_user->surname = $this->surname;
            $this->_user->email = $this->email;
            $this->_user->type = 'user';
            $this->_user->setPassword($this->password);
            $this->_user->generateAccessToken();

            return ($this->_user->save()) ? true : false;
        }

        return false;
    }
}