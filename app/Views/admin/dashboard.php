<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="container-fluid mt-4">

    <h2 class="mb-4">Dashboard</h2>

    <div class="row">

        <div class="col-md-3 mb-3">
            <div class="card shadow">
                <div class="card-body">
                    <h6>Tổng doanh thu</h6>
                    <h3><?= number_format($stats['revenue']) ?> ₫</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow">
                <div class="card-body">
                    <h6>Doanh thu hôm nay</h6>
                    <h3><?= number_format($stats['todayRevenue']) ?> ₫</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow">
                <div class="card-body">
                    <h6>Doanh thu tháng</h6>
                    <h3><?= number_format($stats['monthRevenue']) ?> ₫</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow">
                <div class="card-body">
                    <h6>Đơn chờ xử lý</h6>
                    <h3><?= $stats['pendingOrders'] ?></h3>
                </div>
            </div>
        </div>

    </div>

    <div class="row">

        <div class="col-md-4 mb-3">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h6>Tổng đơn hàng</h6>
                    <h2><?= $stats['orders'] ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h6>Khách hàng</h6>
                    <h2><?= $stats['customers'] ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h6>Sản phẩm</h6>
                    <h2><?= $stats['products'] ?></h2>
                </div>
            </div>
        </div>

    </div>

    <div class="row">

        <div class="col-lg-6">

            <div class="card shadow mb-4">

                <div class="card-header">
                    <strong>Top sản phẩm bán chạy</strong>
                </div>

                <div class="card-body">

                    <table class="table table-bordered">

                        <thead>

                        <tr>
                            <th>#</th>
                            <th>Tên</th>
                            <th>Đã bán</th>
                        </tr>

                        </thead>

                        <tbody>

                        <?php foreach($bestSellers as $i=>$item): ?>

                        <tr>

                            <td><?= $i+1 ?></td>

                            <td><?= htmlspecialchars($item['name']) ?></td>

                            <td><?= $item['sold_count'] ?></td>

                        </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <div class="col-lg-6">

            <div class="card shadow mb-4">

                <div class="card-header">
                    <strong>Đơn hàng mới nhất</strong>
                </div>

                <div class="card-body">

                    <table class="table table-striped">

                        <thead>

                        <tr>

                            <th>Mã</th>
                            <th>Khách</th>
                            <th>Tiền</th>
                            <th>Trạng thái</th>

                        </tr>

                        </thead>

                        <tbody>

                        <?php foreach($recentOrders as $order): ?>

                        <tr>

                            <td><?= $order['order_code'] ?></td>

                            <td><?= htmlspecialchars($order['shipping_name']) ?></td>

                            <td><?= number_format($order['final_amount']) ?> ₫</td>

                            <td><?= $order['status'] ?></td>

                        </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

    <div class="card shadow">

        <div class="card-header">
            <strong>Biểu đồ doanh thu theo tháng</strong>
        </div>

        <div class="card-body">

            <canvas id="revenueChart"></canvas>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const revenueData = <?= json_encode($monthlyRevenue) ?>;

const labels = revenueData.map(item => 'T' + item.month);

const values = revenueData.map(item => item.revenue);

new Chart(document.getElementById('revenueChart'),{

    type:'bar',

    data:{
        labels:labels,
        datasets:[{
            label:'Doanh thu',
            data:values
        }]
    }

});

</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>