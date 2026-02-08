# HeraSpec: PostgreSQL Migration (Bản tiếng Việt)

## 1. Tổng quan
Đề xuất này phác thảo quá trình chuyển đổi cơ sở dữ liệu chính của PolyCMS từ MySQL sang PostgreSQL. Mục tiêu là tận dụng các tính năng mạnh mẽ của PostgreSQL như xử lý JSON nâng cao (JSONB), hệ thống index vượt trội và tính toàn vẹn dữ liệu nghiêm ngặt.

## 2. Lý do chuyển đổi
- **Kiểu dữ liệu nâng cao**: Hỗ trợ tốt cho mảng (arrays), các kiểu phạm vi (range) và đặc biệt là JSONB hiệu suất cao.
- **Đồng thời & Hiệu suất**: Xử lý tốt hơn các tác vụ ghi đồng thời cao và các truy vấn phức tạp.
- **Tìm kiếm nâng cao**: Khả năng tìm kiếm toàn văn (Full-text search) tích hợp sẵn mạnh mẽ với index GIST/GIN.
- **Khả năng mở rộng**: Hỗ trợ tốt hơn cho các schema phức tạp và tập dữ liệu lớn (Big Data).

## 3. Tác động kỹ thuật & Thay đổi

### 3.1 Cấu hình Framework
- **Driver**: Thay đổi `DB_CONNECTION` từ `mysql` sang `pgsql` trong tệp `.env`.
- **Cổng (Port)**: Chuyển cổng mặc định từ `3306` sang `5432`.
- **Schema**: Cấu hình `search_path` (mặc định là `public`).

### 3.2 Điều chỉnh Migration & Schema
Mặc dù Eloquent của Laravel giúp trừu tượng hóa hầu hết các khác biệt, vẫn cần một số điều chỉnh:
- **Độ dài chuỗi**: PostgreSQL xử lý kiểu `TEXT` rất hiệu quả, không cần quá lo lắng về giới hạn 255 ký tự như MySQL.
- **Index Unique**: PostgreSQL có giới hạn nghiêm ngặt về kích thước key cho B-tree index, cần lưu ý với các trường có dữ liệu quá dài.
- **Phân biệt hoa thường**: `LIKE` trong PostgreSQL phân biệt hoa thường. Cần sử dụng `ILIKE` cho các tìm kiếm không phân biệt hoa thường (Laravel: `where('field', 'ILIKE', '%query%')`).
- **Boolean**: Đảm bảo sử dụng `$table->boolean()`, PostgreSQL sử dụng kiểu `boolean` thực thụ (MySQL dùng `tinyint(1)`).

### 3.3 Di chuyển dữ liệu (Data Migration)
- **Công cụ**: Sử dụng `pgloader` hoặc các lệnh Laravel tùy chỉnh để ánh xạ và chuyển dữ liệu.
- **Auto-increments**: Chuyển đổi MySQL `AUTO_INCREMENT` sang PostgreSQL `SERIAL` hoặc `IDENTITY`.
- **Sequences**: Đặt lại các sequence sau khi chèn dữ liệu thủ công có kèm ID.

## 4. Lộ trình thực hiện
1. **Hạ tầng**: Thiết lập các instance PostgreSQL (Phát triển, Staging, Product).
2. **Kiểm tra Schema**: Chạy `php artisan migrate:fresh` trên PostgreSQL để phát hiện lỗi cú pháp migration.
3. **Chuyển đổi dữ liệu**: Viết script xuất dữ liệu MySQL sang SQL tương thích PostgreSQL hoặc dùng công cụ ETL.
4. **Refactor Code**: Thay thế các truy vấn thuần (Raw SQL) MySQL bằng Eloquent hoặc SQL chuẩn.
5. **Kiểm thử**: Kiểm thử hồi quy toàn diện, tập trung vào Tìm kiếm, Bộ lọc và Lưu trữ dữ liệu.

## 5. Đánh giá rủi ro
- **Thời gian ngừng hoạt động (Downtime)**: Việc chuyển dữ liệu lớn có thể yêu cầu thời gian bảo trì.
- **Hiệu suất truy vấn**: Một số tối ưu hóa riêng cho MySQL có thể cần điều chỉnh lại cho bộ lập lịch truy vấn của PostgreSQL.
- **Phân biệt hoa thường**: Lập trình viên cần lưu ý sự khác biệt giữa `LIKE` và `ILIKE`.

## 6. Kế hoạch xác minh
- Chạy thành công tất cả unit và feature tests với driver `pgsql`.
- Xác minh thủ công kết quả "Tìm kiếm" trên tất cả các module (Sản phẩm, Bài viết, Phân loại).
- Kiểm tra tính toàn vẹn dữ liệu (đếm số dòng, checksum) giữa MySQL và PostgreSQL sau khi di chuyển.

## 7. Lưu ý dành cho Module & Theme

### 7.1 Các Module Polyx
Tất cả các module trong thư mục `modules/Polyx` cần được kiểm tra các truy vấn MySQL thuần hoặc các kiểu dữ liệu không tương thích:
- **BannerSlider**: Kiểm tra bảng `banner_sliders` và các bảng liên quan để đảm bảo không sử dụng các tính năng đặc thù chỉ có trên InnoDB/MySQL.
- **PolyFengshui**: Đảm bảo migration cho bảng `polyfengshui_tokens` tương thích. Các dữ liệu JSON lớn trong phân tích phong thủy nên được chuyển sang `JSONB` để đạt hiệu suất tốt nhất.
- **XemTuoiXongDat**: Kiểm tra việc truy xuất cấu hình. Vì module này phụ thuộc nhiều vào logic dịch vụ, cần xác nhận việc chuyển đổi không ảnh hưởng đến quá trình serialization dữ liệu.
- **Cổng thanh toán (Sepay, Paypal)**: Kiểm tra kỹ việc ghi log giao dịch và cập nhật trạng thái. Đảm bảo kiểu `decimal` cho số tiền được xử lý chính xác bởi driver PostgreSQL để tránh mất độ chính xác.

### 7.2 Giao diện (Flexiblog)
- **Theme Flexiblog**: Mặc dù theme chủ yếu xử lý hiển thị, nhưng các cấu hình "Theme Options" lưu trong database cần được xác nhận tính chính xác sau khi chuyển đổi.
- **Custom Post Types/Taxonomies**: Đảm bảo việc đăng ký và truy vấn (ví dụ tìm slug Category/Tag) tuân thủ quy tắc phân biệt hoa thường của PostgreSQL.

## 8. Cập nhật tài liệu kỹ thuật

### 8.1 Đồng bộ hóa HeraSpec
Các tài liệu hiện tại đang mặc định sử dụng MySQL cần được cập nhật:
- **`heraspec/project.md`**: 
    - Thay đổi `Database: MySQL/PostgreSQL (MySQL by default)` thành `Database: PostgreSQL`.
    - Cập nhật các ví dụ cấu hình `.env` để sử dụng driver `pgsql` và cổng `5432`.
    - Loại bỏ hoặc chuyển đổi các hướng dẫn tối ưu hóa dành riêng cho MySQL.
- **`AGENTS.heraspec.md`**: Đảm bảo các hướng dẫn dành cho AI Agent phản ánh việc chuyển sang PostgreSQL khi thực hiện các tác vụ tạo schema hoặc migration.

### 8.2 Kiểm tra & Thay thế tổng thể
Thực hiện kiểm tra toàn bộ thư mục `heraspec/` để thay thế "MySQL" bằng "PostgreSQL" trong tất cả các bối cảnh mô tả về "Primary Stack" hoặc "Default Database".
