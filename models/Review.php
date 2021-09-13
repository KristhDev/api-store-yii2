<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "reviews".
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property string $comment
 * @property string $starts
 * @property string|null $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Products $product
 * @property Users $user
 */
class Review extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reviews';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['user_id', 'product_id', 'created_at', 'updated_at'], 'integer'],
            [['comment', 'starts', 'status'], 'string'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];

        if ($this->id !== null) {
            $rules = array_merge($rules, [
                [['user_id', 'product_id', 'comment', 'starts', 'created_at', 'updated_at'], 'default']
            ]);
        }
        else {
            $rules = array_merge($rules, [
                [['user_id', 'product_id', 'comment', 'starts', 'created_at', 'updated_at'], 'required']
            ]);
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'product_id' => 'Product ID',
            'comment' => 'Comment',
            'starts' => 'Starts',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\ProductsQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\UsersQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\ReviewQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ReviewQuery(get_called_class());
    }
}
