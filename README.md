# PolyCMS - Modern Modular CMS

PolyCMS is a high-performance, modular content management system built with **Laravel 11**, **Vue 3 (Composition API)**, and **Tailwind CSS**. It is designed for multi-language projects, e-commerce, and scalable international web applications.

## 🚀 Key Features

- **🛡️ Modular Architecture**: Highly extensible via modules and themes (Polyx modules).
- **🌐 Advanced Multi-language Support**: Standardized i18n & SEO handling with support for regional variants.
- **⚡ Modern Frontend**: Built with Vue 3 and Vite for a seamless, fast admin experience.
- **📊 Database Flexibility**: Optimized for **PostgreSQL** (Support for MySQL).
- **🎨 Custom Theme System**: Flexible theme management with advanced block editors (Flexiblog theme).
- **💳 Integrated E-commerce**: Built-in support for multiple payment gateways (SePay, PayPal, etc.).

## 🛠️ Technology Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Vue.js 3, Tailwind CSS, Headless UI
- **Database**: PostgreSQL (Recommended) / MySQL
- **Build Tool**: Vite
- **Development Process**: [HeraSpec](https://github.com/PolyXGO/polycms) (AI-Driven Development)

## 📦 Installation

```bash
# Clone the repository
git clone https://github.com/PolyXGO/polycms.git

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Setup Database (PostgreSQL recommended)
# Update .env with your DB credentials
php artisan migrate
```

## 🤖 AI-Driven Development (HeraSpec)

PolyCMS uses **HeraSpec** to coordinate development through precise specifications and AI agent instructions. This ensures high-quality code and consistent architectural patterns.

- [AI Agent Instructions](file:///Applications/XAMPP/xamppfiles/htdocs/PolyCMS/polycms/AGENTS.heraspec.md)
- [Project Specifications](file:///Applications/XAMPP/xamppfiles/htdocs/PolyCMS/polycms/heraspec/project.md)

### Development Workflow:
1. View active tasks: `heraspec list`
2. Archive completed changes: `heraspec archive <slug>`

## 🇻🇳 Phiên bản Tiếng Việt

**PolyCMS** là một hệ quản trị nội dung hiện đại, đa ngôn ngữ, được thiết kế tối ưu cho các dự án quốc tế và thương mại điện tử.

- **Kiến trúc module**: Dễ dàng mở rộng và bảo trì.
- **Chuẩn SEO & i18n**: Tự động xử lý các thuộc tính `lang` và các biến thể ngôn ngữ vùng miền (Locale).
- **Giao diện hiện đại**: Trải nghiệm admin mượt mà với Vue 3.
- **Hỗ trợ PostgreSQL**: Tối ưu cho hiệu suất và tính toàn vẹn dữ liệu.

---

## License

The PolyCMS is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
