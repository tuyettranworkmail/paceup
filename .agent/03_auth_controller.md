# TASK 3 – AuthController

Đọc trước:
- app/Models/UserModel.php (vừa tạo ở Task 2)
- app/Helpers/SessionHelper.php
- app/Views/login.php (xem name attribute của form fields)
- app/Views/register.php (xem name attribute của form fields)

Tạo hoặc bổ sung file: app/Controller/AuthController.php

### Method login():
- GET: require view login.php
- POST:
  + Lấy email, password từ $_POST, trim()
  + Validate không rỗng → setFlash error nếu thiếu
  + Gọi UserModel::findByEmail($email)
  + Nếu không tìm thấy → setFlash('error', 'Email không tồn tại') → redirect '/login'
  + Nếu status = 0 → setFlash('error', 'Tài khoản đã bị khóa') → redirect '/login'
  + password_verify($password, $user['password'])
  + Nếu sai → setFlash('error', 'Mật khẩu không đúng') → redirect '/login'
  + Nếu đúng: set $_SESSION['user_id'], ['user_role'], ['user_name']
  + Ghi log: LoggingService::write($user['id'], 'login', 'Đăng nhập thành công')
  + Nếu role=admin → redirect '/admin', ngược lại → redirect '/'
- Nếu đã login rồi → redirect '/'

### Method register():
- GET: require view register.php
- POST:
  + Lấy full_name, email, password, confirm_password từ $_POST, trim()
  + Validate:
    - Tất cả không được rỗng
    - Email đúng định dạng (filter_var FILTER_VALIDATE_EMAIL)
    - Password >= 8 ký tự
    - password === confirm_password
  + Nếu lỗi: setFlash('error', $message) → redirect '/register'
  + Kiểm tra email tồn tại: UserModel::findByEmail($email)
  + Nếu đã tồn tại: setFlash('error', 'Email đã được sử dụng') → redirect '/register'
  + Hash password: password_hash($password, PASSWORD_DEFAULT)
  + UserModel::create([...])
  + LoggingService::write($newId, 'register', 'Đăng ký tài khoản mới')
  + setFlash('success', 'Đăng ký thành công, vui lòng đăng nhập')
  + redirect '/login'
- Nếu đã login rồi → redirect '/'

### Method logout():
- Ghi log trước: LoggingService::write($_SESSION['user_id'], 'logout', 'Đăng xuất')
- session_destroy()
- redirect '/login'

### Method changePassword():
- Yêu cầu đăng nhập: AuthMiddleware::requireLogin()
- POST only:
  + Lấy current_password, new_password, confirm_new_password
  + Tìm user hiện tại theo $_SESSION['user_id']
  + Verify current_password với hash trong DB
  + Validate new_password >= 8 ký tự, khớp confirm
  + Hash và UserModel::updatePassword($_SESSION['user_id'], $hash)
  + LoggingService::write(...)
  + setFlash('success', 'Đổi mật khẩu thành công')
  + redirect '/account'

Thêm vào index.php:
$router->add('/login', 'AuthController', 'login');
$router->add('/register', 'AuthController', 'register');
$router->add('/logout', 'AuthController', 'logout');
$router->add('/change-password', 'AuthController', 'changePassword');

Sau khi xong báo lại file đã tạo/sửa và routes đã thêm.