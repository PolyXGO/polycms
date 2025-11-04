# Node.js Version Requirement

## Vấn đề

PolyCMS sử dụng Vite 7, yêu cầu:
- **Node.js 20.19+** hoặc **22.12+**
- Bạn đang dùng: **Node.js 18.17.0**

## Giải pháp

### Option 1: Upgrade Node.js (Khuyến khích)

#### Nếu bạn có Homebrew (macOS):
```bash
# Install Node.js 20 LTS
brew install node@20

# Hoặc nếu đã có node, upgrade:
brew upgrade node
```

#### Nếu bạn có nvm:
```bash
# Install Node.js 20
nvm install 20
nvm use 20
nvm alias default 20
```

#### Hoặc download từ nodejs.org:
1. Truy cập: https://nodejs.org/
2. Download Node.js 20.x LTS
3. Install và restart terminal

### Option 2: Sử dụng fnm (Fast Node Manager) - Khuyến khích

```bash
# Install fnm
brew install fnm

# Thêm vào shell profile (.zshrc hoặc .bash_profile)
echo 'eval "$(fnm env --use-on-cd)"' >> ~/.zshrc

# Reload shell
source ~/.zshrc

# Install và use Node.js 20
fnm install 20
fnm use 20
fnm default 20
```

### Kiểm tra sau khi upgrade:

```bash
node --version  # Should be v20.x.x or v22.x.x
npm --version
```

### Sau khi upgrade, rebuild:

```bash
# Clear node_modules và lock file (optional)
rm -rf node_modules package-lock.json

# Reinstall dependencies
npm install

# Build
npm run build

# Hoặc chạy dev mode
npm run dev
```

## Tạm thời

Nếu không thể upgrade ngay:
- **Dev mode** (`npm run dev`) có thể vẫn hoạt động với Node.js 18
- **Build production** sẽ fail cho đến khi upgrade Node.js
