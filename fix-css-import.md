# CSS Import Issue Fix

## Problem
"Could not resolve ../css/app.css" when building with Node.js 18.17.0

## Root Cause
Node.js 18.17.0 is incompatible with Vite 7, which requires Node.js 20.19+ or 22.12+

## Verified
- ✅ CSS file exists: resources/css/app.css
- ✅ Import path is correct: '../css/app.css'
- ✅ Vite config is properly configured

## Solutions

### Option 1: Upgrade Node.js (REQUIRED for production build)
```bash
# Using Homebrew (macOS)
brew install node@20
# or
brew upgrade node

# Using fnm
fnm install 20
fnm use 20
fnm default 20
```

### Option 2: Use Dev Mode (temporary workaround)
```bash
npm run dev
```
Dev mode may work with Node.js 18, but production build will fail.

### Option 3: Add CSS to Vite input (if needed)
You can add CSS directly to vite.config.js input array, but this is not recommended.
