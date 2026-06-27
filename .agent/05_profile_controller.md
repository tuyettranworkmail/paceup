# TASK 5 – ProfileController

Đọc trước:
- app/Models/UserModel.php
- app/Services/UploadService.php (do Tú tạo — nếu chưa có thì tự xử lý upload tạm)
- app/Middleware/AuthMiddleware.php
- app/Views/ (xem có view account chưa)

Tạo file: app/Controller/User/ProfileController.php

Mọi method đều gọi AuthMiddleware::requireLogin() đầu tiên.

### Method index():
- Lấy thông tin user: UserModel::findById($_SESSION['user_id'])
- Lấy địa chỉ: UserModel::getAddresses($_SESSION['user_id'])
- require view (tạo nếu chưa có) app/Views/account/profile.php
  + Hiển thị form thông tin cá nhân (full_name, phone, email readonly)
  + Hiển thị danh sách địa chỉ
  + Hiển thị flash message

### Method update():
- POST only
- Lấy full_name, phone từ $_POST, trim()
- Validate full_name không rỗng
- UserModel::updateProfile($_SESSION['user_id'], ['full_name' => ..., 'phone' => ...])
- Cập nhật $_SESSION['user_name'] = $full_name
- LoggingService::write(...)
- setFlash('success', 'Cập nhật thông tin thành công')
- redirect '/account'

### Method uploadAvatar():
- POST only, kiểm tra $_FILES['avatar'] tồn tại
- Validate:
  + Chỉ chấp nhận: jpg, jpeg, png, webp (kiểm tra extension và mime type)
  + Tối đa 2MB
- Đổi tên: uniqid('avatar_') . '.' . $ext
- Lưu vào: public/uploads/avatars/
- UserModel::updateAvatar($_SESSION['user_id'], $path)
- setFlash('success', 'Cập nhật ảnh đại diện thành công')
- redirect '/account'

### Method addAddress():
- POST only
- Lấy: recipient_name, phone, address, city, is_default từ $_POST
- Validate recipient_name, phone, address, city không rỗng
- UserModel::addAddress($_SESSION['user_id'], $data)
- setFlash('success', 'Thêm địa chỉ thành công')
- redirect '/account'

### Method setDefaultAddress():
- POST only
- Lấy address_id từ $_POST
- UserModel::setDefaultAddress($_SESSION['user_id'], $address_id)
- redirect '/account'

### Method deleteAddress():
- POST only
- Lấy address_id từ $_POST
- UserModel::deleteAddress($address_id, $_SESSION['user_id'])
  (truyền user_id để đảm bảo chỉ xóa địa chỉ của chính mình)
- setFlash('success', 'Đã xóa địa chỉ')
- redirect '/account'

Thêm vào index.php:
$router->add('/account', 'User/ProfileController', 'index');
$router->add('/account/update', 'User/ProfileController', 'update');
$router->add('/account/avatar', 'User/ProfileController', 'uploadAvatar');
$router->add('/account/addresses/add', 'User/ProfileController', 'addAddress');
$router->add('/account/addresses/default', 'User/ProfileController', 'setDefaultAddress');
$router->add('/account/addresses/delete', 'User/ProfileController', 'deleteAddress');

Sau khi xong báo lại.