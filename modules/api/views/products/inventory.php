<?php 
use yii\helpers\Html;

$this->title = 'Inventario';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container">
    <h2><?= Html::encode($this->title) ?></h2>

    <?php foreach ($inventory as $item): ?>
        <table>
            <tr>
                <th colspan="8">
                    <h3><?= $item['category']->name ?></h3>
                </th>
            </tr>

            <tr>
                <th>Id</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Pedidos</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Fecha</th>
            </tr>

            <?php foreach ($item['products'] as $product): ?>
                <tr>
                    <td><?= $product['id'] ?></td>
                    <td><?= $product['name'] ?></td>
                    <td><?= $product['description'] ?></td>
                    <td><?= (int) $product['stock'] ?></td>
                    <td><?= (int) $product['orders'] ?></td>
                    <td><?= Yii::$app->formatter->asDecimal( (int) $product['price'], 2) ?></td>
                    <td><?= ( (int) $product['status'] === 1) ? 'Disponible' : 'Inhabilitado' ?></td>
                    <td><?= Yii::$app->formatter->asDate($product['created_at'], 'php:m-d-Y') ?></td>
                </tr>
            <?php endforeach; ?>

            <tr>
                <th>Totales:</th>
                <th><?= $item['total_products'] ?></th>
                <th></th>
                <th><?= $item['total_quantity'] ?></th>
                <th><?= $item['total_orders'] ?></th>
            </tr>
        </table>

        <br>
    <?php endforeach; ?>
</div>

<style>
    td, th {
        text-align: center;
    }
</style>