<?php 
use yii\helpers\Html;

$this->title = 'Lista de pedidos';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container">
    <div>
        <h2><?= Html::encode($this->title) ?></h2>
        <h3><?= date('d-m-Y') ?></h3>
    </div>

    <table id="table">
        <thead>
            <tr>
                <th>N° Pe</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio por Unidad</th>
                <th>Precio Total</th>
                <th>Fecha de pedido</th>
                <th>Fecha de pago</th>
            </tr>
        </thead>

        <tbody>
            <?php $index = 1; foreach ($orders as $order): ?>
                <tr>
                    <td><?= $index ?></td>
                    <td><?= $order->product->name ?></td>
                    <td><?= $order->quantity ?></td>
                    <td><?= $order->product->price ?></td>
                    <td><?= $order->total_to_pay ?></td>
                    <td>
                        <?= Yii::$app->formatter->asDate($order->date, 'php:m-d-Y') ?>
                    </td>
                    <td>
                        <?= Yii::$app->formatter->asDate($order->date_confirm, 'php:m-d-Y') ?>
                    </td>
                </tr>
            <?php $index++; endforeach;  ?>

            <tr>
                <td colspan="6">Total pagado: </td>
                <td><?= $totalPayed['total_payed'] ?></td>
            </tr>
        </tbody>
    </table>
</div>