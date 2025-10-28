# Hướng dẫn sử dụng NaLi Sidebar Menu Block

## Tổng quan

**NaLi Sidebar Menu Block** là một custom block cho Gutenberg editor, cho phép tạo menu sidebar dạng dashboard với các tính năng mạnh mẽ và dễ sử dụng.

## Tính năng chính

✅ **Cấu hình tên menu** - Có thể đặt tên cho từng mục menu  
✅ **Linh hoạt trong link** - Hỗ trợ WordPress page, post, hoặc bất kỳ URL nào  
✅ **Tự động highlight menu active** - Menu hiện tại được làm nổi bật tự động  
✅ **Responsive design** - Tương thích với tất cả thiết bị  
✅ **Hỗ trợ dark theme** - Tự động thích ứng với chế độ tối  
✅ **Keyboard navigation** - Hỗ trợ điều hướng bằng bàn phím  

## Cách sử dụng

### 1. Thêm block vào trang

1. Mở Gutenberg editor
2. Click **+** để thêm block mới
3. Tìm kiếm "**NaLi Side Bar Menu Block**" 
4. Click để thêm block

### 2. Cấu hình menu

#### Cài đặt cơ bản
- **Tiêu đề Menu**: Đặt tên cho menu (ví dụ: "Dashboard", "Menu Tài khoản")

#### Thêm/sửa menu items
1. Trong panel bên phải, mở **"Danh sách Menu"**
2. Click **"Chỉnh sửa"** trên menu item muốn cấu hình
3. Điền thông tin:
   - **Tên menu**: Tên hiển thị của menu
   - **Link**: URL của menu (xem chi tiết bên dưới)
   - **Menu đang được chọn**: Bật để highlight menu này

#### Quản lý thứ tự menu
- **↑ Lên / ↓ Xuống**: Di chuyển menu lên/xuống
- **🗑 Xóa**: Xóa menu item
- **+ Thêm menu mới**: Thêm menu item mới

### 3. Cấu hình Link Menu

Block hỗ trợ nhiều format link linh hoạt:

#### URL đầy đủ
```
https://example.com/page
https://chuyennhanali.com/contact
```

#### Đường dẫn tương đối
```
/profile
/dashboard
/settings
```

#### ID của Page/Post WordPress
```
123
456
```

#### Slug của Page/Post WordPress
```
about-us
contact
user-profile
```

#### Placeholder
```
#
```

### 4. Ví dụ sử dụng thực tế

#### Menu Dashboard cho User Profile
```
- Tiêu đề: "Tài khoản của tôi"
- Menu items:
  1. Dashboard → /user/dashboard
  2. Hồ sơ cá nhân → /user/profile  
  3. Đơn hàng → /user/orders
  4. Cài đặt → /user/settings
  5. Đăng xuất → /wp-login.php?action=logout
```

#### Menu điều hướng chính
```
- Tiêu đề: "Menu chính"  
- Menu items:
  1. Trang chủ → /
  2. Giới thiệu → about-us (slug)
  3. Dịch vụ → 456 (page ID)
  4. Liên hệ → https://example.com/contact
```

## Tính năng nâng cao

### Tự động detect menu active

Block tự động phát hiện menu đang được truy cập dựa trên:
- URL hiện tại của trang
- Pathname matching
- Exact URL matching

### Keyboard Navigation

- **Arrow Up/Down**: Di chuyển giữa các menu
- **Home**: Đi đến menu đầu tiên  
- **End**: Đi đến menu cuối cùng
- **Enter/Space**: Kích hoạt link

### JavaScript API (cho Developer)

Cập nhật menu active từ JavaScript:
```javascript
// Cập nhật menu active cho SPA
window.chuyennhanaliUpdateActiveMenu('/new-url');
```

## Styling và Customization

### CSS Classes chính

```css
.chuyennhanali-sidebar-menu-block      /* Container chính */
.chuyennhanali-menu-title              /* Tiêu đề menu */
.chuyennhanali-menu-nav                /* Navigation wrapper */
.chuyennhanali-menu-list               /* List container */
.chuyennhanali-menu-item               /* Menu item */
.chuyennhanali-menu-item.is-active     /* Menu active */
.chuyennhanali-menu-link               /* Menu link */
.active-indicator                      /* Indicator cho menu active */
```

### Customize CSS

Thêm CSS tùy chỉnh vào theme:

```css
/* Thay đổi màu chủ đạo */
.chuyennhanali-menu-title {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
}

.chuyennhanali-menu-item.is-active .chuyennhanali-menu-link {
    background-color: #ff6b6b;
    border-right-color: #ee5a52;
}

/* Thay đổi kích thước */
.chuyennhanali-sidebar-menu-block {
    min-width: 280px;
    max-width: 320px;
}

/* Thay đổi font */
.chuyennhanali-menu-link {
    font-family: 'Roboto', sans-serif;
    font-size: 15px;
}
```

## Best Practices

### 1. Đặt tên menu rõ ràng
❌ **Không nên**: "Link 1", "Page", "Menu"  
✅ **Nên**: "Dashboard", "Hồ sơ cá nhân", "Cài đặt tài khoản"

### 2. Sử dụng link phù hợp
- **URL đầy đủ**: Cho link external hoặc subdomain
- **Đường dẫn tương đối**: Cho các page trong cùng site  
- **Page ID/Slug**: Cho WordPress pages/posts (tự động update khi change URL)

### 3. Sắp xếp menu logic
- Đặt menu quan trọng nhất lên đầu
- Nhóm các menu liên quan gần nhau
- Menu "Đăng xuất" nên để cuối cùng

### 4. Responsive design
- Test trên các kích thước màn hình khác nhau
- Đảm bảo menu hoạt động tốt trên mobile
- Kiểm tra touch interaction trên tablet/phone

## Troubleshooting

### Menu không highlight đúng
1. Kiểm tra URL trong cấu hình menu
2. Đảm bảo URL match với page hiện tại
3. Thử sử dụng đường dẫn tương đối thay vì URL đầy đủ

### Style không hiển thị đúng  
1. Xóa cache WordPress và browser
2. Kiểm tra conflict với theme CSS
3. Rebuild plugin: `npm run build`

### Menu không hoạt động trên mobile
1. Kiểm tra touch events
2. Đảm bảo không có CSS conflict
3. Test trên device thật, không chỉ browser dev tools

## Support

Nếu cần hỗ trợ, vui lòng:
1. Kiểm tra console browser có error không
2. Thử disable các plugin khác để test conflict  
3. Liên hệ team NaLi với thông tin chi tiết về issue

---

**Developed by NaLi Team**  
Website: https://chuyennhanali.com