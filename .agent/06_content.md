# TASK 6 – ContentModel + ContentController

Đọc trước:
- Database/127_0_0_1.sql (cấu trúc bảng posts, post_categories, banner, settings)
- app/Models/BaseModel.php
- app/Services/UploadService.php (để upload thumbnail bài viết và ảnh banner)
- app/Middleware/AuthMiddleware.php

---

### Tạo file: app/Models/ContentModel.php
Kế thừa BaseModel. Quản lý 4 bảng:

#### Bảng posts:
- getAllPosts($filters=[], $page=1, $limit=10):
  + JOIN post_categories để lấy tên danh mục
  + Filter theo: category_id, status, keyword (tìm title)
  + Phân trang LIMIT OFFSET
  + Trả về ['data'=>[...], 'total'=>N]
- getPostById($id): SELECT JOIN categories WHERE id=?
- createPost($data): INSERT title, slug, content, thumbnail, category_id, author_id, status
  + slug tự tạo từ title (chuyển thường, bỏ dấu, thay space bằng -)
- updatePost($id, $data): UPDATE WHERE id=?
- deletePost($id): UPDATE status=0 WHERE id=? (soft delete)

#### Bảng post_categories:
- getAllCategories(): SELECT * WHERE status=1
- createCategory($data): INSERT name, slug
- updateCategory($id, $data): UPDATE WHERE id=?
- deleteCategory($id): kiểm tra còn bài viết không trước khi xóa

#### Bảng banner:
- getAllBanners(): SELECT * ORDER BY sort_order
- getActiveBanners(): SELECT * WHERE status=1 AND start_date<=NOW() AND end_date>=NOW()
- createBanner($data): INSERT title, image_url, link_url, position, start_date, end_date
- updateBanner($id, $data): UPDATE WHERE id=?
- deleteBanner($id): UPDATE status=0 WHERE id=?

#### Bảng settings:
- getSetting($key): SELECT value WHERE key_name=?
- getAllSettings(): SELECT * ORDER BY key_name
- saveSetting($key, $value): UPDATE value WHERE key_name=? (nếu không có thì INSERT)

---

### Tạo file: app/Controller/Admin/ContentController.php
Mọi method gọi AuthMiddleware::requireAdmin() đầu tiên.

#### posts(): GET /admin/content/posts
- ContentModel::getAllPosts($filters từ $_GET, $page từ $_GET)
- require view (tạo nếu chưa có) app/Views/admin/content/posts.php

#### createPost(): GET/POST /admin/content/posts/create
- GET: lấy danh mục, require view form tạo bài viết
- POST:
  + Validate title, content, category_id không rỗng
  + Upload thumbnail nếu có: UploadService::image($_FILES['thumbnail'], 'posts')
  + Tạo slug từ title
  + ContentModel::createPost([..., 'author_id' => $_SESSION['user_id']])
  + LoggingService::write(...)
  + setFlash('success', ...) → redirect '/admin/content/posts'

#### editPost(): GET/POST /admin/content/posts/edit
- Lấy id từ $_GET hoặc $_POST
- GET: lấy data bài viết + danh mục → require view form sửa
- POST: xử lý tương tự createPost, gọi ContentModel::updatePost($id, $data)

#### deletePost(): POST /admin/content/posts/delete
- Lấy id từ $_POST
- ContentModel::deletePost($id)
- LoggingService::write(...)
- redirect '/admin/content/posts'

#### categories(): GET /admin/content/categories
- ContentModel::getAllCategories() → require view

#### createCategory() / deleteCategory(): POST
- Tương tự trên, validate tên không rỗng

#### banners(): GET /admin/content/banners
- ContentModel::getAllBanners() → require view

#### createBanner(): GET/POST /admin/content/banners/create
- POST: validate title, upload image (UploadService), ContentModel::createBanner()

#### deleteBanner(): POST /admin/content/banners/delete
- ContentModel::deleteBanner($id)

#### settings(): GET/POST /admin/settings
- GET: ContentModel::getAllSettings() → group theo nhóm → require view
- POST: loop $_POST['settings'] → ContentModel::saveSetting($key, $value) cho từng cái
- LoggingService::write(...)
- setFlash('success', 'Đã lưu cài đặt') → redirect '/admin/settings'

Thêm vào index.php:
$router->add('/admin/content/posts', 'Admin/ContentController', 'posts');
$router->add('/admin/content/posts/create', 'Admin/ContentController', 'createPost');
$router->add('/admin/content/posts/edit', 'Admin/ContentController', 'editPost');
$router->add('/admin/content/posts/delete', 'Admin/ContentController', 'deletePost');
$router->add('/admin/content/categories', 'Admin/ContentController', 'categories');
$router->add('/admin/content/categories/create', 'Admin/ContentController', 'createCategory');
$router->add('/admin/content/categories/delete', 'Admin/ContentController', 'deleteCategory');
$router->add('/admin/content/banners', 'Admin/ContentController', 'banners');
$router->add('/admin/content/banners/create', 'Admin/ContentController', 'createBanner');
$router->add('/admin/content/banners/delete', 'Admin/ContentController', 'deleteBanner');
$router->add('/admin/settings', 'Admin/ContentController', 'settings');

Sau khi xong báo lại.