<?php

$bank = "MB";

$account = "123456789";

$name = "NGUYEN VAN A";

$content = $orderCode;

$qr = "https://img.vietqr.io/image/{$bank}-{$account}-compact2.png?amount={$amount}&addInfo={$content}&accountName=".urlencode($name);

?>

<div style="width:600px;margin:40px auto;text-align:center">

    <h2>Quét mã QR để thanh toán</h2>

    <img src="<?= $qr ?>" width="320">

    <h3><?= number_format($amount) ?> đ</h3>

    <p>Mã đơn hàng: <?= $orderCode ?></p>

    <form action="<?= BASE_URL ?>payment/add" method="POST">

    <input type="hidden" name="order_code" value="<?= $orderCode ?>">
    <input type="hidden" name="amount" value="<?= $amount ?>">

    <button type="submit" class="btn btn-success">
        Xác nhận đã thanh toán
    </button>

</form>

</div>