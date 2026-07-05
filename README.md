# PaceUP:Accessories
A web-based system for managing and selling shoes and sports clothes. This project is developed as part of the Web Programming course assignment.

#PROJECT STRUCTURE
```text
/shop-giay-the-thao
├── /app
│   ├── /Controllers # Nhận request, xử lý logic và gọi Model/View
│   │   ├── /Admin # Các Controller cho quản trị viên.
│   │   └── /Client # Các Controller cho người dùng.
│   ├── /Models # Tương tác trực tiếp với cơ sở dữ liệu MySQL
│   ├── /Views # Chứa giao diện (HTML/PHP)
│   │   ├── /admin # Giao diện Admin Dashboard
│   │   ├── /client # Giao diện Front-end
│   │   └── /layouts # Các thành phần tái sử dụng (header,footer,sidebar,admin)
│   └── /Core # Lõi điều khiển hệ thống
│       ├── App.php # Khởi tạo ứng dụng, cấu hình cơ bản
│       └── Router.php # Phân tích URL và điều hướng đến Controller tương ứng
├── /config # Chứa các file cấu hình
│   └── database.php # Định nghĩa hằng số kết nối DB (DB_HOST, DB_USER, DB_PASS, DB_NAME)
├── /public
│   ├── /assets   # Chứa tài nguyên tĩnh cho Front-end
│   ├── /uploads  # Nơi lưu trữ hình ảnh sản phẩm do Admin tải lên
│   └── index.php # Entry point: Nơi tiếp nhận mọi Request đầu tiên
└── .htaccess # File cấu hình URL Rewrite (giúp URL đẹp và bảo mật hơn)
