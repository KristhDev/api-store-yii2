<?php 
use yii\helpers\Html;

$this->title = 'Inventario';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="intentory">
    <h2><?= Html::encode($this->title) ?></h2>

    <?php foreach ($inventory as $item): ?>
        <table>
            <tr class="table-title">
                <th colspan="8">
                    <h3><?= strtoupper($item['category']->name) ?></h3>
                </th>
            </tr>

            <tr class="table-fields">
                <th>Id</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Pedidos</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Fecha</th>
            </tr>

            <?php $index = 1; foreach ($item['products'] as $product): $bgColor = ($index % 2 === 0) ? 'white' : '#E5E5E5'; ?>
                <tr class="table-body" style="background-color: <?=$bgColor ?>;">
                    <td><?= $product['id'] ?></td>
                    <td><?= $product['name'] ?></td>
                    <td><?= $product['description'] ?></td>
                    <td><?= (int) $product['stock'] ?></td>
                    <td><?= (int) $product['orders'] ?></td>
                    <td><?= Yii::$app->formatter->asDecimal( (int) $product['price'], 2) ?></td>
                    <td><?= ( (int) $product['status'] === 1) ? 'Disponible' : 'Inhabilitado' ?></td>
                    <td><?= Yii::$app->formatter->asDate($product['created_at'], 'php:m-d-Y') ?></td>
                </tr>
            <?php $index++; endforeach; ?>

            <tr class="table-fields">
                <th>Totales:</th>
                <th><?= $item['total_products'] ?></th>
                <th></th>
                <th><?= $item['total_quantity'] ?></th>
                <th><?= $item['total_orders'] ?></th>
                <th colspan="3"></th>
            </tr>
        </table>

        <br>
    <?php endforeach; ?>
</div>

<style>
    tr, td, th, table {
        border: 0px;
        border-style: none;
        border-collapse: collapse;
    }

    td, th {
        text-align: center;
    }

    .inventory h2 {
        font-size: 30px;
    }

    .table-title {
        background-color: #959595;
    }

    .table-title th {
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }

    .table-body td {
        padding: 0.3rem;
    }

    .table-fields {
        background-color: #0089F5;
    }

    .table-fields th {
        border: 1px solid #0089F5;
        color: #FFFFFF;
        font-size: 13.5px;
        padding: 0.2rem 0.5rem;
    }

</style>