# HeraSpec: PostgreSQL Migration

## 1. Overview
This specification outlines the transition of PolyCMS's primary database from MySQL to PostgreSQL. This move aims to leverage PostgreSQL's robust feature set, including advanced JSON handling (JSONB), superior indexing, and strict data integrity.

## 2. Motivation
- **Enhanced Data Types**: native support for arrays, range types, and highly efficient JSONB.
- **Concurrency & Performance**: Better handling of high-concurrency writes and complex queries.
- **Advanced Search**: Superior built-in full-text search capabilities (GIST/GIN indexing).
- **Scalability**: Stronger support for complex schemas and large datasets.

## 3. Technical Impact & Changes

### 3.1 Framework Configuration
- **Driver**: Switch `DB_CONNECTION` from `mysql` to `pgsql` in `.env`.
- **Port**: Change default port from `3306` to `5432`.
- **Schema**: Configure `search_path` (default: `public`).

### 3.2 Migration & Schema Adjustments
While Laravel's Eloquent abstracts most differences, some adjustments are necessary:
- **String Lengths**: MySQL's `string` default (255) is fine, but PostgreSQL handles large text (TEXT) very efficiently without performance penalties.
- **Unique Indexes**: PostgreSQL has strict limits on index key size for B-tree indexes (approx 2700 bytes), which rarely affects normal slugs/codes but should be monitored.
- **Case Sensitivity**: PostgreSQL's `LIKE` is case-sensitive. We must use `ILIKE` for case-insensitive searches (Laravel's `where('field', 'ILIKE', '%query%')`).
- **Booleans**: Ensure migrations use `$table->boolean()` which maps to PostgreSQL's native `boolean` type (MySQL uses `tinyint(1)`).

### 3.3 Data Migration
- **Tooling**: Use tools like `pgloader` or custom Laravel commands to map and migrate data.
- **Auto-increments**: Convert MySQL `AUTO_INCREMENT` to PostgreSQL `SERIAL` or `IDENTITY`.
- **Sequences**: Reset sequences after manual data ID insertion.

## 4. Implementation Roadmap
1. **Infrastructure**: Provision PostgreSQL instances (Development, Staging, Production).
2. **Schema Validation**: Run `php artisan migrate:fresh` on a PostgreSQL instance to identify incompatible migration syntax.
3. **Data Transformation**: Script the export of MySQL data to PostgreSQL-compatible SQL or use an ETL tool.
4. **Code Refactoring**: Replace any raw MySQL queries with Eloquent or DB-agnostic SQL.
5. **Testing**: Comprehensive regression testing focusing on Search, Filters, and Data Persistence.

## 5. Risk Assessment
- **Downtime**: Data migration for large tables may require maintenance windows.
- **Query Performance**: Some MySQL-specific optimizations might need re-tuning for the PostgreSQL query planner.
- **Case Sensitivity**: Developers must be mindful of `LIKE` vs `ILIKE`.

## 6. Verification Plan
- Successful execution of all unit and feature tests using the `pgsql` driver.
- Manual verification of "Search" results across all modules (Products, Posts, Taxonomy).
- Data integrity check (row counts, checksums) between MySQL and PostgreSQL post-migration.

## 7. Module & Theme Considerations

### 7.1 Polyx Modules
All modules under `modules/Polyx` must be audited for raw MySQL queries or incompatible field types:
- **BannerSlider**: Review `banner_sliders` and related tables for any specific MySQL engine requirements (e.g., InnoDB specific features).
- **PolyFengshui**: Ensure `polyfengshui_tokens` table migrations are compatible. Any large JSON data stored in fengshui analysis should be converted to `JSONB` for performance.
- **XemTuoiXongDat**: Monitor configuration retrieval. Since it relies heavily on services and logic (likely using core settings), verify that the transition doesn't affect data serialization.
- **Payment Gateways (Sepay, Paypal)**: Strictly verify transaction logging and status updates. Ensure that `decimal` types for amounts are handled correctly by the PostgreSQL driver to avoid precision loss.

### 7.2 Themes (Flexiblog)
- **Flexiblog Theme**: While themes primarily handle presentation, any custom "Theme Options" stored in the database must be verified.
- **Custom Post Types/Taxonomies**: Ensure that any registrations done within the theme or its companion modules respect PostgreSQL's case-sensitivity (e.g., querying for tags or categories by slug).

## 8. Technical Documentation Updates

### 8.1 HeraSpec Alignment
Existing documentation that assumes MySQL as the primary stack must be updated:
- **`heraspec/project.md`**: 
    - Change `Database: MySQL/PostgreSQL (MySQL by default)` to `Database: PostgreSQL`.
    - Update example `.env` configurations to use `pgsql` driver and port `5432`.
    - Remove or deprecate MySQL-specific optimization guidelines.
- **`AGENTS.heraspec.md`**: Ensure agent instructions reflect the move to PostgreSQL for any schema or migration scaffolding tasks.

### 8.2 General Search & Replace
A full audit of the `heraspec/` directory will be performed to replace "MySQL" with "PostgreSQL" in any context describing the "Primary" or "Default" database stack.
