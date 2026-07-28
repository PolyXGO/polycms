<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    protected array $tables = [
        'posts',
        'categories',
        'products',
        'tags',
        'product_categories',
        'product_brands'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                // 1. Drop existing unique index on slug or [type, slug]
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
                $indexName = $table === 'categories' || $table === 'tags'
                    ? 'idx_' . $table . '_type_slug_locale_unique'
                    : 'idx_' . $table . '_slug_locale_unique';

                if (!$this->hasIndex($table, $indexName)) {
                    Schema::table($table, function (Blueprint $t) use ($table, $indexName) {
                        if ($table === 'categories' || $table === 'tags') {
                            $t->unique(['type', 'slug', 'locale'], $indexName);
                        } else {
                            $t->unique(['slug', 'locale'], $indexName);
                        }
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
                        if ($table === 'categories' || $table === 'tags') {
                            $t->dropUnique('idx_' . $table . '_type_slug_locale_unique');
                        } else {
                            $t->dropUnique('idx_' . $table . '_slug_locale_unique');
                        }
                    } catch (\Exception $e) {}

                    try {
                        $t->dropColumn(['locale', 'translation_group_id']);
                    } catch (\Exception $e) {}

                    try {
                        if ($table === 'categories' || $table === 'tags') {
                            $t->unique(['type', 'slug'], 'idx_type_slug_unique');
                        } else {
                            $t->unique('slug');
                        }
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
     * Dynamically find and drop a unique index containing only slug or [type, slug] columns.
     */
    protected function dropSlugUniqueIndex(string $table): void
    {
        $indexes = Schema::getIndexes($table);
        foreach ($indexes as $index) {
            if ($index['unique']) {
                $cols = $index['columns'];
                sort($cols);
                
                $target1 = ['slug'];
                $target2 = ['slug', 'type'];
                sort($target2);

                if ($cols === $target1 || $cols === $target2) {
                    Schema::table($table, function (Blueprint $t) use ($index) {
                        $t->dropUnique($index['name']);
                    });
                }
            }
        }
    }
};
