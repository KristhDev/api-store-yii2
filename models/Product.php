<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "products".
 *
 * @property int $id
 * @property int|null $category_id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property string $image
 * @property int $stock
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Categories $category
 */
class Product extends \yii\db\ActiveRecord
{
    public $file;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products';
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
            [['category_id', 'stock', 'status', 'created_at', 'updated_at'], 'integer'],
            [['description', 'image'], 'string'],
            [['price'], 'number'],
            [['name'], 'string', 'max' => 100],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['category_id' => 'id']],
        ];

        if ($this->id !== null) {
            $rules = array_merge($rules, [
                [['name', 'description', 'price', 'stock', 'file', 'image'], 'default'],
                [['file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'jpg,png'],
            ]);
        }
        else {
            $rules = array_merge($rules, [
                [['name', 'description', 'price', 'stock', 'category_id', 'file'], 'required'],
                [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'jpg,png'],
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
            'category_id' => 'Category ID',
            'name' => 'Name',
            'description' => 'Description',
            'price' => 'Price',
            'stock' => 'Stock',
            'image' => 'Image',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\CategoriesQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\ProductQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ProductQuery(get_called_class());
    }
}
