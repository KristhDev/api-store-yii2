<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "categories".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Products[] $products
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'categories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['name'], 'string', 'max' => 50],
            [['description'], 'string'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [
                'name', 'unique',
                'targetClass' => '\app\models\Category',
                'message' => Yii::t('app', 'This name already belongs to a category'),
                'when' => function ($model, $attribute) {
                    return $model->{$attribute} !== $model->getOldAttribute($attribute);
                }
            ],
        ];

        if ($this->id !== null) {
            $rules = array_merge($rules, [
                [['name', 'description'], 'default']
            ]);
        }
        else {
            $rules = array_merge($rules, [
                [['name', 'description'], 'required']
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
            'name' => 'Name',
            'description' => 'Description',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\ProductsQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['category_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\CategoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\CategoryQuery(get_called_class());
    }
}
