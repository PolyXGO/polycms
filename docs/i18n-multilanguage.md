# Đa Ngôn Ngữ (i18n) - PolyCMS

PolyCMS hỗ trợ đa ngôn ngữ với hệ thống tương tự WordPress, cho phép modules và themes dễ dàng tích hợp localization.

## Cấu Trúc

### 1. Translation Files

Translation files được lưu trong thư mục `lang/`:

```
polycms/
├── lang/
│   ├── en.php (default, không cần thiết)
│   ├── vi.php
│   ├── zh.php
│   └── ...
├── modules/
│   └── ModuleName/
│       └── lang/
│           ├── vi.php
│           └── ...
└── themes/
    └── ThemeName/
        └── lang/
            ├── vi.php
            └── ...
```

### 2. Format Translation File

File translation là PHP array:

```php
<?php
// polycms/lang/vi.php
return [
    'Hello World' => 'Xin chào thế giới',
    'Welcome' => 'Chào mừng',
    'Dashboard' => 'Bảng điều khiển',
];
```

### 3. Sử dụng Helper Functions

#### Trong PHP/Blade Templates:

```php
// Trả về translated string
echo _l('Hello World'); // Returns: "Xin chào thế giới" (nếu locale = vi)

// Echo trực tiếp
_e('Welcome'); // Echo: "Chào mừng"

// Với locale cụ thể
echo _l('Hello World', 'zh'); // Returns Chinese translation
```

#### Trong Blade Templates:

```blade
<h1>{{ _l('Welcome') }}</h1>
<p>{{ _l('This is a test message') }}</p>

{{-- Hoặc echo trực tiếp --}}
{!! _e('Click here') !!}
```

### 4. Trong Modules

Modules có thể tự động load translations từ thư mục `lang/`:

```php
// modules/Polyx/MyModule/lang/vi.php
return [
    'My Module Setting' => 'Thiết lập Module của tôi',
    'Save Changes' => 'Lưu thay đổi',
];
```

### 5. Trong Themes

Themes cũng có thể có translations:

```php
// themes/my-theme/lang/vi.php
return [
    'Read More' => 'Đọc thêm',
    'Previous Post' => 'Bài trước',
    'Next Post' => 'Bài sau',
];
```

### 6. Register Translations Programmatically

```php
use App\Helpers\LanguageHelper;

LanguageHelper::register([
    'Hello' => 'Xin chào',
    'World' => 'Thế giới',
], 'vi');
```

### 7. Language Direction (RTL/LTR)

PolyCMS tự động áp dụng CSS direction dựa trên setting `site_language_direction`:

- **LTR** (Left to Right): Mặc định cho tiếng Anh, Việt, Trung, Nhật, etc.
- **RTL** (Right to Left): Cho tiếng Ả Rập, Hebrew, etc.

CSS direction được áp dụng vào `<html>` và `<body>` tags.

### 8. Get Current Language

```php
use App\Helpers\LanguageHelper;

$lang = LanguageHelper::getCurrentLanguage(); // 'en', 'vi', etc.
$direction = LanguageHelper::getLanguageDirection(); // 'ltr' or 'rtl'
```

## Ví Dụ

### Module Example

```php
// modules/Polyx/MyModule/src/Controllers/MyController.php
public function index()
{
    return view('my-module::index', [
        'title' => _l('My Module Title'),
        'description' => _l('My Module Description'),
    ]);
}
```

### Theme Example

```blade
{{-- themes/my-theme/resources/views/partials/header.blade.php --}}
<header>
    <h1>{{ _l('Site Title') }}</h1>
    <nav>
        <a href="/">{{ _l('Home') }}</a>
        <a href="/about">{{ _l('About') }}</a>
    </nav>
</header>
```

## Best Practices

1. **Luôn dùng _l() cho text cần translate**: Không hardcode text
2. **English làm default**: English text là key trong translation files
3. **Organize translations**: Nhóm translations theo module/theme
4. **Cache translations**: Translations được cache để tối ưu performance

