<?php
require_once __DIR__ . '/../_helpers.php';
adminStart('Quản Lý Đơn Hàng', 'orders', $flash ?? null);

$currentStatus = $_GET['status'] ?? 'All';
$statuses = ['All', 'Pending', 'Confirmed', 'Shipping', 'Delivered', 'Cancelled'];
?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
    <div style="background: #fff; padding: 1.5rem; border: 1px solid #eee; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
        <span style="color: #666; font-size: 0.85rem; text-transform: uppercase; font-weight: 600;">Tổng số đơn</span>
        <h2 style="margin: 0.5rem 0 0; font-size: 1.8rem; font-weight: bold; color: #111;"><?= number_format($stats['total_orders'] ?? 0) ?></h2>
    </div>
    <div style="background: #fff; padding: 1.5rem; border: 1px solid #eee; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
        <span style="color: #666; font-size: 0.85rem; text-transform: uppercase; font-weight: 600;">Tổng doanh thu</span>
        <h2 style="margin: 0.5rem 0 0; font-size: 1.8rem; font-weight: bold; color: #2ecc71;"><?= number_format($stats['revenue'] ?? 0) ?> đ</h2>
    </div>
    <div style="background: #fff; padding: 1.5rem; border: 1px solid #eee; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
        <span style="color: #666; font-size: 0.85rem; text-transform: uppercase; font-weight: 600;">Đơn chờ xử lý</span>
        <h2 style="margin: 0.5rem 0 0; font-size: 1.8rem; font-weight: bold; color: #e67e22;"><?= number_format($stats['pending'] ?? 0) ?></h2>
    </div>
    <div style="background: #fff; padding: 1.5rem; border: 1px solid #eee; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
        <span style="color: #666; font-size: 0.85rem; text-transform: uppercase; font-weight: 600;">Số lượng khách hàng</span>
        <h2 style="margin: 0.5rem 0 0; font-size: 1.8rem; font-weight: bold; color: #3498db;"><?= number_format($stats['customers'] ?? 0) ?></h2>
    </div>
</div>

<div style="display: flex; gap: 0.8rem; margin-bottom: 2rem; flex-wrap: wrap;">
    <?php foreach ($statuses as $st): ?>
        <a href="?status=<?= $st ?>" style="padding: 0.6rem 1.5rem; border-radius: 30px; border: 1px solid <?= strtolower($currentStatus) === strtolower($st) ? '#111' : '#eee' ?>; background: <?= strtolower($currentStatus) === strtolower($st) ? '#111' : '#fff' ?>; color: <?= strtolower($currentStatus) === strtolower($st) ? '#fff' : '#666' ?>; text-decoration: none; font-weight: 500; font-size: 0.9rem; transition: all 0.2s ease;">
            <?= $st ?>
        </a>
    <?php endforeach; ?>
</div>

<form class="admin-panel admin-grid" method="get" action="" style="margin-bottom: 2rem; background: #fafafa; padding: 1.5rem; border-radius: 8px;">
    <input type="hidden" name="status" value="<?= adminE($currentStatus) ?>">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; width: 100%;">
        <div class="admin-field">
            <label style="display:block; margin-bottom:0.5rem; font-size:0.85rem; color:#555;">Mã đơn hàng</label>
            <input type="text" name="order_code" value="<?= adminE($_GET['order_code'] ?? '') ?>" placeholder="Ví dụ: ORD-1234" style="width:100%; padding:0.6rem; border:1px solid #ccc; border-radius:4px;">
        </div>
        <div class="admin-field">
            <label style="display:block; margin-bottom:0.5rem; font-size:0.85rem; color:#555;">Từ ngày</label>
            <input type="date" name="start_date" value="<?= adminE($_GET['start_date'] ?? '') ?>" style="width:100%; padding:0.6rem; border:1px solid #ccc; border-radius:4px;">
        </div>
        <div class="admin-field">
            <label style="display:block; margin-bottom:0.5rem; font-size:0.85rem; color:#555;">Đến ngày</label>
            <input type="date" name="end_date" value="<?= adminE($_GET['end_date'] ?? '') ?>" style="width:100%; padding:0.6rem; border:1px solid #ccc; border-radius:4px;">
        </div>
        <div class="admin-field" style="align-self: end;">
            <button class="admin-btn" type="submit" style="background:#111; color:#fff; border:none; padding:0.65rem 1.5rem; border-radius:4px; cursor:pointer; font-weight:500;">Tìm kiếm</button>
        </div>
    </div>
</form>

<table class="admin-table" style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
    <thead>
        <tr style="background: #f4f4f4; border-bottom: 2px solid #ddd;">
            <th style="padding: 1rem; text-align: left;">Mã đơn hàng</th>
            <th style="padding: 1rem; text-align: left;">Mã khách hàng</th>
            <th style="padding: 1rem; text-align: left;">Tổng tiền</th>
            <th style="padding: 1rem; text-align: left;">Trạng thái</th>
            <th style="padding: 1rem; text-align: left;">Ngày tạo đơn</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 1rem; min-width: 450px;">
    <form action="<?= BASE_URL ?>admin/orders/update-status" method="POST" style="margin: 0;">
        
        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">

        <div style="display: flex; align-items: center; justify-content: space-between; position: relative; margin: 10px 0;">
            
            <div style="position: absolute; top: 14px; left: 20px; right: 20px; height: 4px; background: #e0e0e0; z-index: 1;"></div>
            
            <?php 
                $statusStr = strtolower($order['status']);
                $progressWidth = '0%';
                if ($statusStr === 'confirmed') $progressWidth = '33%';
                if ($statusStr === 'shipping') $progressWidth = '66%';
                if ($statusStr === 'delivered') $progressWidth = '100%';
                if ($statusStr === 'cancelled') $progressWidth = '100%'; 
            ?>
            <div style="position: absolute; top: 14px; left: 20px; width: <?= $progressWidth ?>; height: 4px; background: <?= $statusStr === 'cancelled' ? '#e74c3c' : '#2ecc71' ?>; z-index: 2;"></div>

            <button type="submit" name="status" value="pending" <?= $statusStr === 'pending' ? 'disabled' : '' ?>
                    style="z-index: 3; text-align: center; background: none; border: none; padding: 0; cursor: pointer; font-family: inherit;">
                <div style="width: 30px; height: 30px; border-radius: 50%; background: #e67e22; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold; margin: 0 auto 4px; box-shadow: 0 0 0 4px #fff;">1</div>
                <span style="font-size: 0.75rem; font-weight: 600; color: #e67e22; display: block;">Chờ xử lý</span>
            </button>

            <?php $isConfirmed = in_array($statusStr, ['confirmed', 'shipping', 'delivered']); ?>
            <button type="submit" name="status" value="processing" <?= $statusStr === 'confirmed' ? 'disabled' : '' ?>
                    style="z-index: 3; text-align: center; background: none; border: none; padding: 0; cursor: pointer; font-family: inherit;">
                <div style="width: 30px; height: 30px; border-radius: 50%; background: <?= $isConfirmed ? '#3498db' : '#fff' ?>; color: <?= $isConfirmed ? '#fff' : '#aaa' ?>; border: 2px solid <?= $isConfirmed ? '#3498db' : '#ccc' ?>; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold; margin: 0 auto 4px; box-shadow: 0 0 0 4px #fff;">2</div>
                <span style="font-size: 0.75rem; font-weight: 600; color: <?= $isConfirmed ? '#3498db' : '#999' ?>; display: block;">Đã tiếp nhận</span>
            </button>

            <?php $isShipping = in_array($statusStr, ['shipping', 'delivered']); ?>
            <button type="submit" name="status" value="shipping" <?= $statusStr === 'shipping' ? 'disabled' : '' ?>
                    style="z-index: 3; text-align: center; background: none; border: none; padding: 0; cursor: pointer; font-family: inherit;">
                <div style="width: 30px; height: 30px; border-radius: 50%; background: <?= $isShipping ? '#f1c40f' : '#fff' ?>; color: <?= $isShipping ? '#fff' : '#aaa' ?>; border: 2px solid <?= $isShipping ? '#f1c40f' : '#ccc' ?>; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold; margin: 0 auto 4px; box-shadow: 0 0 0 4px #fff;">3</div>
                <span style="font-size: 0.75rem; font-weight: 600; color: <?= $isShipping ? '#f1c40f' : '#999' ?>; display: block;">Đang giao</span>
            </button>

            <button type="submit" name="status" value="<?= $statusStr === 'cancelled' ? 'Cancelled' : 'Delivered' ?>" <?= ($statusStr === 'delivered' || $statusStr === 'cancelled') ? 'disabled' : '' ?>
                    style="z-index: 3; text-align: center; background: none; border: none; padding: 0; cursor: pointer; font-family: inherit;">
                <?php if ($statusStr === 'cancelled'): ?>
                    <div style="width: 30px; height: 30px; border-radius: 50%; background: #e74c3c; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold; margin: 0 auto 4px; box-shadow: 0 0 0 4px #fff;">✕</div>
                    <span style="font-size: 0.75rem; font-weight: 600; color: #e74c3c; display: block;">Đã huỷ đơn</span>
                <?php else: ?>
                    <?php $isDelivered = ($statusStr === 'delivered'); ?>
                    <div style="width: 30px; height: 30px; border-radius: 50%; background: <?= $isDelivered ? '#2ecc71' : '#fff' ?>; color: <?= $isDelivered ? '#fff' : '#aaa' ?>; border: 2px solid <?= $isDelivered ? '#2ecc71' : '#ccc' ?>; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold; margin: 0 auto 4px; box-shadow: 0 0 0 4px #fff;">4</div>
                    <span style="font-size: 0.75rem; font-weight: 600; color: <?= $isDelivered ? '#2ecc71' : '#999' ?>; display: block;">Đã giao</span>
                <?php endif; ?>
            </button>

            <?php if ($statusStr !== 'delivered' && $statusStr !== 'cancelled'): ?>
                <button type="submit" name="status" value="cancelled" title="Hủy đơn hàng này" 
                        style="z-index: 3; background: #fff; border: 1px dashed #e74c3c; color: #e74c3c; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; cursor: pointer; align-self: center; margin-left: 10px; font-weight: bold;">
                    Hủy đơn
                </button>
            <?php endif; ?>

        </div>
    </form>
</td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($orders)): ?>
            <tr>
                <td colspan="5" style="text-align: center; padding: 3rem; color: #999;">Không tìm thấy đơn hàng nào phù hợp với bộ lọc.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($totalPages > 1): ?>
    <div style="display: flex; gap: 0.5rem; margin-top: 2rem; justify-content: center;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?status=<?= urlencode($currentStatus) ?>&order_code=<?= urlencode($_GET['order_code'] ?? '') ?>&start_date=<?= urlencode($_GET['start_date'] ?? '') ?>&end_date=<?= urlencode($_GET['end_date'] ?? '') ?>&page=<?= $i ?>" 
               style="padding: 0.5rem 1rem; border: 1px solid #ddd; background: <?= ((int)($_GET['page'] ?? 1) === $i) ? '#111' : '#fff' ?>; color: <?= ((int)($_GET['page'] ?? 1) === $i) ? '#fff' : '#111' ?>; text-decoration: none; border-radius: 4px; font-weight: 500;">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const statusSelects = document.querySelectorAll(".status-select");

    statusSelects.forEach(select => {
        select.addEventListener("change", function() {
            const orderId = this.getAttribute("data-id");
            const newStatus = this.value;

            const formData = new FormData();
            formData.append("id", orderId);
            formData.append("status", newStatus);

            fetch("<?= BASE_URL ?>admin/orders/update-status", {
                method: "POST",
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Mạng có sự cố, không thể kết nối server.");
                }
                return response.json(); 
            })
            .then(data => {
                if (data.success) {
                    // Thông báo thành công 
                    alert("🎉 " + data.message);
                } else {
                    alert(" Lỗi: " + data.message);
                }
            })
            .catch(error => {
                console.error("Lỗi hệ thống:", error);
                alert("Đã xảy ra lỗi kết nối khi cập nhật đơn hàng.");
            });
        });
    });

    // Hàm hiển thị trang danh sách đơn hàng & Thống kê dành cho Admin
    public function indexAdmin() {
        $status = $_GET['status'] ?? 'All';
        $orderCode = trim($_GET['order_code'] ?? '');

        $filters = [];
        if ($status !== 'All') {
            $filters['status'] = $status;
        }
        if (!empty($orderCode)) {
            $filters['order_code'] = $orderCode;
        }

        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Gọi các hàm xử lý từ Model Order 
        $orders = $this->orderModel->getAdminOrders($filters, $limit, $offset);
        $totalOrdersCount = $this->orderModel->countAdminOrders($filters);
        $totalPages = ceil($totalOrdersCount / $limit);

        // Lấy số liệu động cho 4 khối Dashboard 
        $stats = [
            'total_orders' => $this->orderModel->countAdminOrders([]),
            'revenue'      => $this->orderModel->getTotalRevenue(),
            'pending'      => $this->orderModel->countAdminOrders(['status' => 'pending']),
            'customers'    => $this->orderModel->countUniqueCustomers()
        ];

        require __DIR__ . '/../../Views/admin/orders/index.php';
    }

    public function updateStatusApi() {
        header('Content-Type: application/json');

        $id = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');

        if ($id > 0 && !empty($status)) {
            $success = $this->orderModel->updateOrderStatus($id, $status);
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái đơn hàng thành công!']);
                exit;
            }
        }

        echo json_encode(['success' => false, 'message' => 'Không thể cập nhật trạng thái.']);
        exit;
    }
});
</script>

<?php adminEnd(); ?>
