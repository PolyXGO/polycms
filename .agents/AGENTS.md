# PolyCMS Workspace Project Rules & Deploy Guidelines

## CyberPanel / OpenLiteSpeed Vite Asset Deployment Rules

1. **Permanent Symlink Requirement (ZERO Race Conditions)**:
   - On CyberPanel / OpenLiteSpeed Linux servers (`headrandom.com`), `/home/{domain}/public_html/build` **MUST BE A PERMANENT SYMLINK** pointing to `/home/{domain}/public_html/public/build`:
     `ln -sf /home/{domain}/public_html/public/build /home/{domain}/public_html/build`
   - **NEVER** treat `/home/{domain}/public_html/build` as a physical directory or run `cp -r` / `rm -rf` between the two paths! Physical duplication creates directory state mismatches, missing JS/CSS chunks, and 404 errors.

2. **Non-Destructive Asset Accumulation (Prevents 404s in Open Browser Tabs)**:
   - When deploying new Vite builds, **DO NOT** run `rm -rf /public_html/public/build/assets` on the server!
   - Upload new built chunk files alongside previous asset chunks. Retaining older chunk hashes guarantees that open browser sessions can lazy-load components without encountering 404 ERR_ABORTED errors.

3. **Mandatory Linux File Ownership (`{site_user}:nobody`)**:
   - Immediately after uploading or syncing built assets as `root`, run:
     `chown -h {site_user}:nobody /home/{domain}/public_html/build`
     `chown -R {site_user}:nobody /home/{domain}/public_html/public/build`
     `chmod -R 755 /home/{domain}/public_html/public/build`
   - Files left owned by `root:root` will be blocked by OpenLiteSpeed with 403 Forbidden or 404 Not Found errors.

4. **Mandatory Cache Clearing & Verification**:
   - Always run `php artisan cache:clear && php artisan view:clear && php artisan config:clear` after any deploy.
   - Run `curl -I -k` to verify HTTP `200 OK` status on the main JS/CSS asset bundles before marking deployment as complete.
