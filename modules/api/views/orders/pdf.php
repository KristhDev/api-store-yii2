<?php 
use yii\helpers\Html;

$this->title = 'Lista de pedidos';
$this->params['breadcrumbs'][] = $this->title;
?>


<style>
    * {
        font-family: Arial, Helvetica, sans-serif;
    }
    .pdf-container {
        width: 100%;
    }

    .pdf-title {
        margin-bottom: 20px;
        width: 100%;
    }

    .pdf-title h3 {
        position: absolute;
        top: 0px;
        right: 0px;
    }

    .pdf-title h3, .pdf-title h2 {
        margin: 0;
    }
    tr, td, th, table {
        border: 0px;
        border-style: none;
        border-collapse: collapse;
    }
    #table .thead {
        background-color: #0089F5;
    }

    #table .thead th {
        border: 1px solid #0089F5;
        color: #FFFFFF;
        font-size: 13.5px;
        padding: 0.2rem 0.5rem;
    }

    #table .tbody td {
        padding: 0.3rem;
        text-align: center;
    }
</style>

<div class="container-pdf">
    <div class="pdf-title">
        <h2><?= Html::encode($this->title) ?></h2>
        <h3><?= date('d-m-Y') ?></h3>
    </div>

    <table id="table">
        <tr class="thead">
            <th>N°</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Fecha de pedido</th>
            <th>Fecha de pago</th>
            <th>Precio por Unidad</th>
            <th>Precio Total</th>
        </tr>

        <?php $index = 1; foreach ($orders as $order): $bgColor = ($index % 2 === 0) ? 'white' : '#E5E5E5;'; ?>
            <tr class="tbody" style="background-color: <?=$bgColor ?>;">
                <td><?= $index ?></td>
                <td><?= $order->product->name ?></td>
                <td><?= $order->quantity ?></td>
                <td>
                    <?= Yii::$app->formatter->asDate($order->date, 'php:m-d-Y') ?>
                </td>
                <td>
                    <?= Yii::$app->formatter->asDate($order->date_confirm, 'php:m-d-Y') ?>
                </td>
                <td>
                    <?= Yii::$app->formatter->asDecimal($order->product->price, 2) ?>
                </td>
                <td>
                    <?= Yii::$app->formatter->asDecimal($order->total_to_pay, 2) ?>
                </td>
            </tr>
        <?php $index++; endforeach;  ?>

        <tr class="thead">
            <th style="text-align: right;" colspan="2">Total productos:</th>
            <th><?= $totalPayed->quantity ?: 0 ?></th>
            <th></th>
            <th colspan="2" style="text-align: right;">Total pagado: </th>
            <th><?= Yii::$app->formatter->asDecimal($totalPayed->total_to_pay ?: 0, 2)  ?></th>
        </tr>
    </table>
</div>