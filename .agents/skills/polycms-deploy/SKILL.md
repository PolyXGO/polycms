---
name: polycms-deploy
description: CyberPanel OpenLiteSpeed live server deployment guidelines for PolyCMS, including Vite build sync, symlink rules, permissions, and cache clearing.
---

# PolyCMS Deployment Guidelines & CyberPanel OpenLiteSpeed Rules

## Root Cause Prevention Checklist

When deploying changes or Vite assets (`public/build`) to live servers (`headrandom.com` / CyberPanel / OpenLiteSpeed):

### 1. Permanent Symlink (`/public_html/build` -> `/public_html/public/build`)
- CyberPanel OpenLiteSpeed serves static asset requests (`/build/assets/...`) directly from `/home/{domain}/public_html/build/assets/`.
- Laravel's `vite` helper resolves assets from `/home/{domain}/public_html/public/build/manifest.json`.
- **MANDATORY**: `/home/{domain}/public_html/build` must be a **permanent symlink** pointing to `public/build`:
  ```bash
  rm -rf /home/headrandom.com/public_html/build
  ln -sf /home/headrandom.com/public_html/public/build /home/headrandom.com/public_html/build
  chown -h headr2549:nobody /home/headrandom.com/public_html/build
  ```
- **NEVER** copy files between physical `/public_html/build` and `/public_html/public/build` directories!

### 2. Non-Destructive Asset Accumulation (Prevents 404s in Open Browser Tabs)
- Do NOT delete existing assets inside `/home/{domain}/public_html/public/build/assets/` during deployment!
- Upload new asset files alongside existing ones. Keeping previous chunk hashes prevents 404 errors for users with open browser tabs while a deploy is in progress.

### 3. Mandatory Linux File Ownership & Permissions
- SCP uploads running under `root` leave files owned by `root:root`. OpenLiteSpeed (running as `{site_user}:nobody`) will return 403 or 404 for asset requests.
- **Action**: Immediately after uploading files, run:
  ```bash
  chown -R headr2549:nobody /home/headrandom.com/public_html/public/build
  chmod -R 755 /home/headrandom.com/public_html/public/build
  ```

### 4. Post-Deploy Cache Clear
- Always clear Laravel caches immediately after asset or controller changes:
  ```bash
  cd /home/headrandom.com/public_html
  php artisan cache:clear
  php artisan view:clear
  php artisan config:clear
  ```

### 5. Verification Protocol
- Run `curl -I -k https://headrandom.com/build/assets/{main-bundle}.js` and confirm HTTP status **`200 OK`**.
