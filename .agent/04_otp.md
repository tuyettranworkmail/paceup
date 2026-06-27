# TASK 4 – Quên mật khẩu bằng OTP

Đọc trước:
- app/Models/UserModel.php (createOtp, verifyOtp, deleteOtp, updatePassword)
- app/Helpers/SessionHelper.php

Bổ sung vào app/Controller/AuthController.php các method sau:

### Method forgotPassword():
- GET: require (hoặc tạo mới) view app/Views/forgot_password.php
  + View chỉ cần form 1 input email + submit
- POST:
  + Lấy email, validate không rỗng và đúng định dạng
  + UserModel::findByEmail($email)
  + Nếu không tìm thấy: setFlash('error', 'Email không tồn tại') → redirect '/forgot-password'
  + Tạo OTP: $otp = rand(100000, 999999)
  + $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'))
  + UserModel::createOtp($email, $otp, $expiresAt)
  + Môi trường dev: $_SESSION['dev_otp'] = $otp (hiển thị ra view để test)
  + setFlash('info', "OTP của bạn là: $otp (chỉ dùng khi dev)")
  + $_SESSION['otp_email'] = $email
  + redirect '/verify-otp'

### Method verifyOtp():
- Kiểm tra $_SESSION['otp_email'] tồn tại, không thì redirect '/forgot-password'
- GET: require view app/Views/verify_otp.php
  + Form nhập 6 số OTP, hiển thị email đang xác thực
- POST:
  + Lấy otp từ $_POST
  + UserModel::verifyOtp($_SESSION['otp_email'], $otp)
  + Nếu sai hoặc hết hạn: setFlash('error', 'OTP không hợp lệ hoặc đã hết hạn') → redirect '/verify-otp'
  + Nếu đúng: $_SESSION['reset_verified_email'] = $_SESSION['otp_email']
  + Xóa $_SESSION['otp_email']
  + redirect '/reset-password'

### Method resetPassword():
- Kiểm tra $_SESSION['reset_verified_email'] tồn tại, không thì redirect '/forgot-password'
- GET: require view app/Views/reset_password.php
  + Form nhập new_password + confirm_password
- POST:
  + Validate password >= 8 ký tự, khớp confirm
  + Hash và UserModel::updatePassword() theo email
  + UserModel::deleteOtp($_SESSION['reset_verified_email'])
  + Xóa $_SESSION['reset_verified_email']
  + LoggingService::write(null, 'reset_password', 'Đặt lại mật khẩu qua OTP')
  + setFlash('success', 'Đặt lại mật khẩu thành công')
  + redirect '/login'

Tạo 3 view đơn giản nếu chưa có:
- app/Views/forgot_password.php
- app/Views/verify_otp.php
- app/Views/reset_password.php
(Chỉ cần form HTML cơ bản, hiển thị flash message, dùng style có sẵn của project)

Thêm vào index.php:
$router->add('/forgot-password', 'AuthController', 'forgotPassword');
$router->add('/verify-otp', 'AuthController', 'verifyOtp');
$router->add('/reset-password', 'AuthController', 'resetPassword');

Sau khi xong báo lại.