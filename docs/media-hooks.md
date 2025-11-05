# Media Management Hooks

PolyCMS provides a comprehensive hook system for extending media management functionality. This allows modules and themes to customize media operations, add custom processing, or integrate with external services.

## Available Hooks

### Filter Hooks

#### `media.upload.file`
Filter the file before upload processing. This allows modules to modify or validate the file.

**Parameters:**
- `UploadedFile $file` - The uploaded file
- `array $data` - Additional upload data (alt_text, caption, description)

**Return:** UploadedFile instance

**Example:**
```php
use App\Facades\Hook;

Hook::addFilter('media.upload.file', function ($file, $data) {
    // Custom file validation or processing
    if ($file->getSize() > 5 * 1024 * 1024) {
        throw new \Exception('File too large');
    }
    
    return $file;
}, 10);
```

#### `media.upload.data`
Filter the upload data (metadata) before creating media record.

**Parameters:**
- `array $data` - Upload metadata (alt_text, caption, description)
- `UploadedFile $file` - The uploaded file

**Return:** Array of data

**Example:**
```php
Hook::addFilter('media.upload.data', function ($data, $file) {
    // Add custom metadata
    $data['custom_field'] = 'custom_value';
    
    return $data;
}, 10);
```

#### `media.create.data`
Filter the media data before creating the database record.

**Parameters:**
- `array $data` - Media data to be saved
- `UploadedFile $file` - The uploaded file
- `array $validated` - Validated request data

**Return:** Array of media data

**Example:**
```php
Hook::addFilter('media.create.data', function ($data, $file, $validated) {
    // Modify media data before saving
    $data['custom_metadata'] = [
        'source' => 'custom_upload',
        'processed_at' => now()->toISOString(),
    ];
    
    return $data;
}, 10);
```

#### `media.url`
Filter the media URL. This allows modules to generate custom URLs (e.g., CDN URLs, signed URLs).

**Parameters:**
- `string $url` - The default media URL
- `Media $media` - The media model instance

**Return:** String URL

**Example:**
```php
Hook::addFilter('media.url', function ($url, $media) {
    // Use CDN URL instead
    return 'https://cdn.example.com/' . $media->path;
}, 10);
```

#### `media.delete.should`
Filter whether media should be deleted. Return `false` to prevent deletion.

**Parameters:**
- `bool $shouldDelete` - Whether to delete (default: true)
- `Media $media` - The media model instance

**Return:** Boolean

**Example:**
```php
Hook::addFilter('media.delete.should', function ($shouldDelete, $media) {
    // Prevent deletion of protected media
    if ($media->name === 'protected-image') {
        return false;
    }
    
    return $shouldDelete;
}, 10);
```

### Action Hooks

#### `media.uploaded`
Action hook fired after a media file is successfully uploaded.

**Parameters:**
- `Media $media` - The created media model
- `UploadedFile $file` - The uploaded file
- `array $validated` - Validated request data

**Example:**
```php
Hook::addAction('media.uploaded', function ($media, $file, $validated) {
    // Generate thumbnails
    // Sync to external storage
    // Send notifications
    \Log::info("Media uploaded: {$media->name}");
}, 10);
```

#### `media.deleting`
Action hook fired before media is deleted.

**Parameters:**
- `Media $media` - The media model being deleted

**Example:**
```php
Hook::addAction('media.deleting', function ($media) {
    // Clean up related resources
    // Remove from CDN
    // Log deletion
}, 10);
```

#### `media.deleted`
Action hook fired after media is successfully deleted.

**Parameters:**
- `Media $media` - The deleted media model (may be soft-deleted)

**Example:**
```php
Hook::addAction('media.deleted', function ($media) {
    // Clean up cache
    // Update related records
    \Log::info("Media deleted: {$media->name}");
}, 10);
```

## MediaService Facade

The `MediaService` facade provides a clean API for media operations:

```php
use App\Facades\MediaService;

// Upload media
$media = MediaService::upload($uploadedFile, [
    'alt_text' => 'Image description',
    'caption' => 'Image caption',
]);

// Get media URL
$url = MediaService::getUrl($media);

// Delete media
MediaService::delete($media);
```

## Usage in Modules

Modules can register hooks in their service provider's `boot()` method:

```php
namespace Modules\MyModule\Providers;

use App\Facades\Hook;
use Illuminate\Support\ServiceProvider;

class MyModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Add custom processing after upload
        Hook::addAction('media.uploaded', function ($media, $file, $validated) {
            // Custom logic here
            $this->processMedia($media);
        }, 10);

        // Customize media URLs
        Hook::addFilter('media.url', function ($url, $media) {
            return $this->getCdnUrl($media);
        }, 10);
    }
}
```

## Frontend Integration

The Vue.js media manager component (`MediaManager.vue`) provides:

- Upload from local files
- Upload from URL
- Filter by type (Image, Video, Document)
- Search functionality
- Grid/List view toggle
- Multi-select support for galleries

The `MediaPicker` component can be used in forms to select media:

```vue
<MediaPicker
    ref="mediaPickerRef"
    :multiple="false"
    :accepted-types="['image']"
    @select="handleMediaSelect"
/>
```
