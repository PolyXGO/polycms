<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    protected array $tables = [
        'post_tags',
        'product_tags'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                // 1. Drop existing unique index on slug
                $this->dropSlugUniqueIndex($table);

                // 2. Add columns if they do not exist
                Schema::table($table, function (Blueprint $t) {
                    if (!Schema::hasColumn($t->getTable(), 'locale')) {
                        $t->string('locale', 10)->default('en')->index();
                    }
                    if (!Schema::hasColumn($t->getTable(), 'translation_group_id')) {
                        $t->string('translation_group_id', 36)->nullable()->index();
                    }
                });

                // 3. Initialize translation_group_id for existing records
                $records = DB::table($table)->get();
                foreach ($records as $record) {
                    if (empty($record->translation_group_id)) {
                        DB::table($table)->where('id', $record->id)->update([
                            'locale' => $record->locale ?? 'en',
                            'translation_group_id' => (string) Str::uuid()
                        ]);
                    }
                }

                // 4. Add the composite unique index only once so a retry after an
                // interrupted migration is safe.
                $indexName = 'idx_' . $table . '_slug_locale_unique';
                if (!$this->hasIndex($table, $indexName)) {
                    Schema::table($table, function (Blueprint $t) use ($indexName) {
                        $t->unique(['slug', 'locale'], $indexName);
                    });
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    try {
                        $t->dropUnique('idx_' . $table . '_slug_locale_unique');
                    } catch (\Exception $e) {}

                    try {
                        $t->dropColumn(['locale', 'translation_group_id']);
                    } catch (\Exception $e) {}

                    try {
                        $t->unique('slug');
                    } catch (\Exception $e) {}
                });
            }
        }
    }

    /**
     * Determine whether the named index already exists before creating it.
     */
    protected function hasIndex(string $table, string $indexName): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['name'] ?? null) === $indexName) {
                return true;
            }
        }

        return false;
    }
    /**
     * Dynamically find and drop a unique index containing only slug.
     */
    protected function dropSlugUniqueIndex(string $table): void
    {
        $indexes = Schema::getIndexes($table);
        foreach ($indexes as $index) {
            if ($index['unique']) {
                $cols = $index['columns'];
                if ($cols === ['slug']) {
                    Schema::table($table, function (Blueprint $t) use ($index) {
                        $t->dropUnique($index['name']);
                    });
                }
            }
        }
    }
};
