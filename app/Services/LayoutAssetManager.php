<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LayoutAsset;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LayoutAssetManager
{
    /**
     * @var array<string, array<string, array<string, mixed>>>
     */
    protected array $registered = [
        'part' => [],
        'template' => [],
    ];

    public function __construct(
        protected ContentRenderer $renderer
    ) {}

    public function registerPart(string $key, array $options): void
    {
        $this->register('part', $key, $options);
    }

    public function registerTemplate(string $key, array $options): void
    {
        $this->register('template', $key, $options);
    }

    public function getRegistered(string $kind): array
    {
        return $this->registered[$kind] ?? [];
    }

    public function ensureStorageReady(): void
    {
        if (Schema::hasTable('layout_assets')) {
            return;
        }

        $hasUsersTable = Schema::hasTable('users');

        Schema::create('layout_assets', function (Blueprint $table) use ($hasUsersTable): void {
            $table->id();

            if ($hasUsersTable) {
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            } else {
                $table->unsignedBigInteger('user_id')->nullable();
            }

            $table->string('kind', 32);
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('key')->nullable()->unique();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->string('layout', 32)->default('landing');
            $table->json('content_raw')->nullable();
            $table->longText('content_html')->nullable();
            $table->string('preview_image')->nullable();
            $table->json('meta')->nullable();
            $table->json('applies_to')->nullable();
            $table->boolean('is_system')->default(false);
            $table->string('source_type', 32)->nullable();
            $table->string('source_name')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kind', 'category']);
            $table->index(['kind', 'is_system']);
        });
    }

    public function syncDatabase(): void
    {
        $this->ensureStorageReady();

        foreach ($this->registered as $kind => $definitions) {
            foreach ($definitions as $key => $definition) {
                $existing = LayoutAsset::withTrashed()
                    ->where('kind', $kind)
                    ->where('key', $key)
                    ->first();

                $payload = [
                    'kind' => $kind,
                    'name' => $definition['name'],
                    'slug' => $definition['slug'],
                    'key' => $key,
                    'category' => $definition['category'] ?? null,
                    'description' => $definition['description'] ?? null,
                    'layout' => $definition['layout'] ?? 'landing',
                    'preview_image' => $definition['preview_image'] ?? null,
                    'meta' => $definition['meta'] ?? [],
                    'applies_to' => $kind === 'template' ? array_values($definition['applies_to'] ?? ['page']) : null,
                    'is_system' => true,
                    'source_type' => $definition['source_type'] ?? 'core',
                    'source_name' => $definition['source_name'] ?? 'PolyCMS',
                    'deleted_at' => null,
                ];

                if (!$existing) {
                    $contentRaw = $definition['content_raw'] ?? null;

                    LayoutAsset::create($payload + [
                        'content_raw' => $contentRaw,
                        'content_html' => is_array($contentRaw) ? $this->renderer->render($contentRaw) : null,
                    ]);
                    continue;
                }

                $updatePayload = $payload;

                if (!empty($definition['content_raw'])) {
                    $updatePayload['content_raw'] = $definition['content_raw'];
                    $updatePayload['content_html'] = $this->renderer->render($definition['content_raw']);
                }

                $existing->fill($updatePayload);
                $existing->save();
            }
        }
    }

    public function duplicate(LayoutAsset $asset, ?User $user = null, ?string $name = null): LayoutAsset
    {
        $baseName = $name ?: sprintf('%s Copy', $asset->name);

        $copy = LayoutAsset::create([
            'user_id' => $user?->id,
            'kind' => $asset->kind,
            'name' => $baseName,
            'slug' => $this->generateUniqueSlug($baseName),
            'key' => null,
            'category' => $asset->category,
            'description' => $asset->description,
            'layout' => $asset->layout,
            'content_raw' => $asset->content_raw,
            'content_html' => $asset->content_html,
            'preview_image' => $asset->preview_image,
            'meta' => $asset->meta,
            'applies_to' => $asset->applies_to,
            'is_system' => false,
            'source_type' => 'custom',
            'source_name' => $user?->name ?? 'Custom',
        ]);

        if (is_array($copy->content_raw)) {
            $copy->content_html = $this->renderer->render($copy->content_raw);
            $copy->save();
        }

        return $copy;
    }

    public function renderContent(?array $contentRaw): ?string
    {
        if (!$contentRaw) {
            return null;
        }

        return $this->renderer->render($contentRaw);
    }

    public function generateUniqueSlug(string $seed, ?int $ignoreId = null): string
    {
        $base = Str::slug($seed) ?: 'layout-asset';
        $slug = $base;
        $suffix = 2;

        while (
            LayoutAsset::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = sprintf('%s-%d', $base, $suffix);
            $suffix++;
        }

        return $slug;
    }

    protected function register(string $kind, string $key, array $options): void
    {
        $normalizedKind = $kind === 'template' ? 'template' : 'part';
        $name = $options['name'] ?? $options['label'] ?? Str::headline(Str::afterLast($key, '.'));

        $this->registered[$normalizedKind][$key] = [
            'name' => $name,
            'slug' => $options['slug'] ?? Str::slug($name),
            'description' => $options['description'] ?? null,
            'category' => $options['category'] ?? null,
            'layout' => $options['layout'] ?? 'landing',
            'content_raw' => Arr::get($options, 'content_raw'),
            'preview_image' => $options['preview_image'] ?? null,
            'applies_to' => $normalizedKind === 'template' ? (array) ($options['applies_to'] ?? ['page']) : [],
            'meta' => (array) ($options['meta'] ?? []),
            'source_type' => $options['source_type'] ?? 'core',
            'source_name' => $options['source_name'] ?? 'PolyCMS',
        ];
    }
}
