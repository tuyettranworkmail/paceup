# TASK 2 – UserModel

Đọc trước:
- app/Models/BaseModel.php (xem các method kế thừa được)
- app/Models/User.php (nếu có, chỉ bổ sung vào, không xóa)
- Database/127_0_0_1.sql (xem cấu trúc bảng users, user_addresses, password_reset_otp)

Tạo hoặc bổ sung file: app/Models/UserModel.php
Kế thừa BaseModel. Quản lý 3 bảng: users, user_addresses, password_reset_otp

### Nhóm bảng users:
- findById($id): SELECT * FROM users WHERE id = ?
- findByEmail($email): SELECT * FROM users WHERE email = ?
- create($data): INSERT full_name, email, password(đã hash), phone, role='user', status=1
- updateProfile($id, $data): UPDATE full_name, phone WHERE id = ?
- updateAvatar($id, $path): UPDATE avatar WHERE id = ?
- updatePassword($id, $hash): UPDATE password WHERE id = ?
- updateStatus($id, $status): UPDATE status WHERE id = ? (0=ban, 1=active)
- updateRole($id, $role): UPDATE role WHERE id = ?
- getAll($filters = [], $page = 1, $limit = 20):
  + Filter theo: keyword (tìm full_name hoặc email), role, status
  + Phân trang bằng LIMIT OFFSET
  + Trả về ['data' => [...], 'total' => N]

### Nhóm bảng user_addresses:
- getAddresses($userId): SELECT * WHERE user_id = ? AND status = 1
- addAddress($userId, $data): INSERT recipient_name, phone, address, city, is_default
  + Nếu is_default = 1: UPDATE user_addresses SET is_default = 0 WHERE user_id = ? trước
- setDefaultAddress($userId, $addressId):
  + UPDATE SET is_default = 0 WHERE user_id = ?
  + UPDATE SET is_default = 1 WHERE id = ? AND user_id = ?
- deleteAddress($addressId, $userId): UPDATE status = 0 WHERE id = ? AND user_id = ?
  (có user_id để bảo mật, tránh xóa của người khác)

### Nhóm bảng password_reset_otp:
- createOtp($email, $otp, $expiresAt): INSERT hoặc UPDATE nếu email đã có
- verifyOtp($email, $otp): SELECT WHERE email=? AND otp=? AND expires_at > NOW()
- deleteOtp($email): DELETE WHERE email = ?

Sau khi xong báo lại file đã tạo và danh sách method.