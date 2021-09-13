<?php

namespace app\models;

use Yii;
use app\modules\api\resources\ProductResource;

/**
 * This is the model class for table "orders".
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property string $direction
 * @property int $quantity
 * @property int $total_to_pay
 * @property string|null $status
 * @property string $date
 * @property string $date_confirm
 *
 * @property Products $product
 * @property Users $user
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['user_id', 'product_id', 'quantity', 'total_to_pay'], 'integer'],
            [['direction', 'status'], 'string'],
            [['date', 'date_confirm'], 'safe'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];

        if ($this->id !== null) {
            $rules = array_merge($rules, [
                [['user_id', 'product_id', 'direction', 'quantity', 'total_to_pay', 'date', 'date_confirm'], 'default']
            ]);
        }
        else {
            $rules = array_merge($rules, [
                [['user_id', 'product_id', 'direction', 'quantity'], 'required']
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
            'direction' => 'Direction',
            'quantity' => 'Quantity',
            'total_to_pay' => 'Total To Pay',
            'status' => 'Status',
            'date' => 'Date',
            'date_confirm' => 'Date Confirm',
        ];
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\ProductsQuery
     */
    public function getProduct()
    {
        return $this->hasOne(ProductResource::class, ['id' => 'product_id']);
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
     * @return \app\models\query\OrderQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\OrderQuery(get_called_class());
    }
}
