<div style="max-width: 900px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <h2 style="margin-bottom: 1.5rem;">Đơn hàng của tôi</h2>
    
    <?php if (empty($MyOrders)): ?>
        <p style="color: #666;">Bạn chưa có đơn hàng nào.</p>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="background: #f8f8f8; border-bottom: 2px solid #eee;">
                <th style="padding: 1rem; text-align: left;">Mã đơn</th>
                <th style="padding: 1rem; text-align: left;">Trạng thái</th>
                <th style="padding: 1rem; text-align: left;">Tổng tiền</th>
            </tr>
            <?php foreach ($MyOrders as $order): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 1rem;"><?= htmlspecialchars($order['order_code'] ?? 'ORD-' . $order['id']) ?></td>
                    <td style="padding: 1rem;">
                        <span style="padding: 5px 10px; border-radius: 4px; background: #eee; font-size: 0.85rem;">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </td>
                    <td style="padding: 1rem;"><?= number_format($order['total_amount']) ?> đ</td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>