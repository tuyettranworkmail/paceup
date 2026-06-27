# PROJECT CONTEXT – PaceUp MVC PHP

## Kiến trúc
- MVC thuần PHP, không dùng framework
- Entry point: index.php (Front Controller)
- Autoloader: namespace App\ → thư mục app/
- DB: App\Models\Database (PDO Singleton)
- BaseModel: kế thừa để dùng getAll/getById/insert/update/delete/softDelete
- Session: $_SESSION['user_id'], ['user_role'], ['user_name']
- Redirect: header('Location: ' . BASE_URL . 'path'); exit;
- BASE_URL đã define sẵn trong index.php
- Routes đăng ký trong index.php bằng: $router->add('/path', 'Controller', 'method');
- Controller trong subfolder: $router->add('/path', 'Admin/UserController', 'method');

## Quy tắc làm việc
1. Đọc toàn bộ codebase TRƯỚC khi viết bất kỳ dòng code nào
2. Chỉ tạo/sửa file được yêu cầu trong task, KHÔNG tự ý sửa file khác
3. Sau khi xong báo lại: danh sách file đã tạo, file đã sửa, route đã thêm
4. Nếu method đã tồn tại trong file thì CHỈ bổ sung, không xóa code cũ
5. Comment bằng tiếng Việt

# TASK 1 – Shared Services (Thuỳ Anh khởi tạo)

Đọc trước: app/Models/Database.php, config/database.php

Tạo 3 file sau:

---

### File 1: app/Services/LoggingService.php
- Static class, không cần khởi tạo
- Method: LoggingService::write($actorId, $action, $description, $targetId = null)
  + Ghi vào bảng `logs` trong DB
  + Cột cần: actor_id, action, description, target_id, created_at
  + Dùng PDO từ App\Models\Database::getInstance()->getConnection()
  + Bắt exception, không để lỗi log làm crash app

---

### File 2: app/Middleware/AuthMiddleware.php
- Static class
- Method requireLogin():
  + Kiểm tra isset($_SESSION['user_id'])
  + Nếu chưa login → SessionHelper::setFlash('error', 'Vui lòng đăng nhập') → redirect '/login'
- Method requireAdmin():
  + Gọi requireLogin() trước
  + Kiểm tra $_SESSION['user_role'] === 'admin'
  + Nếu không phải admin → redirect '/'

---

### File 3: app/Helpers/SessionHelper.php
- Static class
- Method setFlash($type, $message): lưu vào $_SESSION['flash'][$type]
- Method getFlash($type): lấy và XÓA flash message (chỉ hiện 1 lần)
- Method getAllFlash(): lấy toàn bộ flash rồi xóa
- Method redirect($path): header('Location: ' . BASE_URL . ltrim($path, '/')); exit;

---

Sau khi xong báo lại 3 file đã tạo.