# NaLi Custom Blocks

Tập hợp các Gutenberg custom blocks được phát triển bởi NaLi.

## Tổng quan

**NaLi Custom Blocks** là một WordPress plugin cung cấp các custom blocks cho Gutenberg editor. Plugin được phát triển theo các tiêu chuẩn hiện đại của WordPress và sử dụng block API version 3.

### Thông tin kỹ thuật

- **Block namespace:** `chuyennhanali`
- **PHP namespace:** `ChuyenNhaNaLi`
- **Function prefix:** `chuyennhanali_`
- **CSS class prefix:** `chuyennhanali-`
- **Text domain:** `nali-custom-block`
- **License:** GPL-2.0-or-later

### Yêu cầu hệ thống

- WordPress 6.0 trở lên
- PHP 7.4 trở lên
- Node.js 18 LTS

## Cài đặt môi trường phát triển

### Bước 1: Clone repository

```bash
git clone https://github.com/thuanxt/nali-custom-block.git
cd nali-custom-block
```

### Bước 2: Cài đặt dependencies

```bash
npm ci
```

### Bước 3: Khởi động môi trường development

```bash
npm run start
```

Lệnh này sẽ khởi động webpack trong chế độ watch, tự động build lại khi có thay đổi.

### Bước 4: Build cho production

```bash
npm run build
```

## Cài đặt plugin vào WordPress

### Phương pháp 1: Upload file zip

1. Build plugin: `npm run build`
2. Tạo file zip của thư mục plugin (loại trừ `node_modules`)
3. Vào WordPress Admin > Plugins > Add New > Upload Plugin
4. Upload file zip và kích hoạt

### Phương pháp 2: Symlink (cho development)

```bash
ln -s /đường/dẫn/đến/nali-custom-block /đường/dẫn/đến/wordpress/wp-content/plugins/nali-custom-block
```

Sau đó kích hoạt plugin trong WordPress Admin.

## Cấu trúc thư mục

```
nali-custom-block/
├── .github/
│   └── workflows/
│       └── ci.yml              # GitHub Actions CI/CD
├── blocks/
│   └── example-block/          # Block mẫu
│       ├── src/
│       │   ├── index.js        # Entry point
│       │   └── edit.js         # Edit component
│       ├── block.json          # Block metadata
│       ├── style.css           # Frontend styles
│       ├── editor.css          # Editor styles
│       └── render.php          # Server-side render
├── includes/
│   └── loader.php              # Auto-register blocks
├── .editorconfig               # Editor config
├── .eslintrc.js                # ESLint config
├── .prettierrc.json            # Prettier config
├── LICENSE                     # GPL-2.0 license
├── nali-custom-block.php       # Main plugin file
├── package.json                # NPM dependencies
└── README.md                   # Documentation
```

## Cách thêm block mới

1. Tạo thư mục mới trong `blocks/`, ví dụ: `blocks/my-new-block/`

2. Tạo file `block.json` với cấu trúc:

```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "chuyennhanali/my-new-block",
  "title": "My New Block",
  "category": "widgets",
  "icon": "smiley",
  "description": "Mô tả block của bạn",
  "textdomain": "nali-custom-block",
  "editorScript": "file:./build/index.js",
  "style": "file:./style.css",
  "editorStyle": "file:./editor.css",
  "render": "file:./render.php",
  "attributes": {
    // Định nghĩa attributes
  }
}
```

3. Tạo thư mục `src/` và các file:
   - `src/index.js` - Entry point
   - `src/edit.js` - Edit component

4. Tạo các file styles:
   - `style.css` - Frontend styles
   - `editor.css` - Editor styles

5. Tạo file `render.php` cho server-side rendering

6. Block sẽ được tự động đăng ký khi plugin load

## Scripts NPM

- `npm run start` - Khởi động development mode với watch
- `npm run build` - Build cho production
- `npm run lint` - Kiểm tra code với ESLint
- `npm run format` - Format code với Prettier

## CI/CD

Plugin sử dụng GitHub Actions để tự động:
- Lint code
- Build plugin
- Tạo file zip artifact

Workflow được trigger khi push hoặc tạo pull request.

## License

GPL-2.0-or-later

## Author

NaLi - https://chuyennhanali.com