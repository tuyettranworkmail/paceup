# TASK 7 – Admin UserController

Đọc trước:
- app/Models/UserModel.php
- app/Middleware/AuthMiddleware.php

Tạo file: app/Controller/Admin/UserController.php
Mọi method gọi AuthMiddleware::requireAdmin() đầu tiên.

### index(): GET /admin/users
- Lấy filters từ $_GET: keyword, role, status, page
- UserModel::getAll($filters, $page)
- require view app/Views/admin/users/index.php
  + Bảng danh sách user: avatar, tên, email, role, status, ngày tạo, actions
  + Form search/filter
  + Phân trang

### show(): GET /admin/users/show?id=X
- Lấy id từ $_GET
- UserModel::findById($id)
- Nếu không tìm thấy: setFlash('error', 'Không tìm thấy user') → redirect '/admin/users'
- require view chi tiết user

### updateRole(): POST /admin/users/role
- Lấy user_id, role từ $_POST
- Validate role chỉ là 'admin' hoặc 'user'
- Không cho phép tự đổi role của chính mình:
  if ($user_id == $_SESSION['user_id']) → setFlash('error', ...) → redirect
- UserModel::updateRole($user_id, $role)
- LoggingService::write($_SESSION['user_id'], 'update_role', "Đổi role user #$user_id thành $role", $user_id)
- setFlash('success', 'Đã cập nhật quyền') → redirect '/admin/users'

### toggleBan(): POST /admin/users/ban
- Lấy user_id từ $_POST
- Không cho phép ban chính mình
- UserModel::findById($user_id) để lấy status hiện tại
- Toggle: status hiện tại = 1 thì set 0, ngược lại set 1
- UserModel::updateStatus($user_id, $newStatus)
- $action = $newStatus === 0 ? 'ban' : 'unban'
- LoggingService::write($_SESSION['user_id'], $action, "Admin $action user #$user_id", $user_id)
- setFlash('success', $newStatus === 0 ? 'Đã khóa tài khoản' : 'Đã mở khóa tài khoản')
- redirect '/admin/users'

Thêm vào index.php:
$router->add('/admin/users', 'Admin/UserController', 'index');
$router->add('/admin/users/show', 'Admin/UserController', 'show');
$router->add('/admin/users/role', 'Admin/UserController', 'updateRole');
$router->add('/admin/users/ban', 'Admin/UserController', 'toggleBan');

Sau khi xong báo lại toàn bộ file đã tạo trong suốt dự án phần Thuỳ Anh.