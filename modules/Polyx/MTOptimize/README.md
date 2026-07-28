# MTOptimize

MTOptimize is a modular SEO and content optimization engine for PolyCMS.

## Features

- Context-aware meta resolution (title, description, robots, canonical)
- OpenGraph and Twitter metadata generation
- JSON-LD schema generation with schema piece registry
- Sitemap and robots generation with provider hooks
- URL normalization and canonical policy
- Precomputed SEO document storage and cache invalidation hooks
- Backward compatibility bridge for `cms_head` and `seo.canonical_url`

## Key Hooks

- `mtoptimize/context/*`
- `mtoptimize/meta/*`
- `mtoptimize/opengraph/*`
- `mtoptimize/twitter/*`
- `mtoptimize/schema/*`
- `mtoptimize/link/*`
- `mtoptimize/sitemap/*`
- `mtoptimize/robots/*`
- `mtoptimize/content/*`

## Module Structure

- `src/Services`: core engine and resolvers
- `src/Support`: helper APIs and hook helpers
- `src/Models`: persisted SEO document model
- `database/migrations`: seo document storage

## Notes

- Module is designed to integrate with core render ownership via `App\\Contracts\\SeoRenderContract`.
- Module works with current hook system and supports future add-on extensions.


---

## Live Demo

Explore PolyCMS with a ready-to-use live demo and walk through the admin dashboard, customer portal, content tools, e-commerce flows, theme system, and module features.

- **Admin demo**: [https://polycms.org/admin/login](https://polycms.org/admin/login)
- **Customer demo**: [https://polycms.org/account/login](https://polycms.org/account/login)

Try the demo, review the workflows, and see how PolyCMS can accelerate your next content, commerce, or SaaS project.

## 🌐 Resources & Links

- 🌐 **Official Website**: [https://polycms.org](https://polycms.org)
- 📚 **Documentation**: [https://headrandom.com/_EpBecN8](https://headrandom.com/_EpBecN8)
- 🐙 **GitHub Repository**: [https://github.com/PolyXGO/polycms](https://github.com/PolyXGO/polycms)
- 🐛 **Issue Tracker**: [https://github.com/PolyXGO/polycms/issues](https://github.com/PolyXGO/polycms/issues)
