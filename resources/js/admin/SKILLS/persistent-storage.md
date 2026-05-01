---
name: Persistent Storage Pattern
description: Standardized way to handle localStorage and browser persistence in PolyCMS
---

# Persistent Storage Pattern

To ensure consistency, type safety, and avoid key collisions, all browser storage operations in PolyCMS must use the `Storage` utility.

## Location
- Utility: `resources/js/admin/utils/storage.ts`
- Exported via: `resources/js/admin/utils/index.ts`

## Key Rules
1. **Always use the Utility**: Never call `localStorage` directly in components or stores.
2. **Key Prefixing**: The utility automatically adds a `polycms_` prefix to all keys. Do not add it manually.
3. **Type Safety**: Use the generic `get<T>` method to ensure return types are correct.
4. **Default Values**: Always provide a default value when using `Storage.get()` for critical settings.

## Usage Examples

### 1. Basic Get and Set
```typescript
import { Storage } from '@/admin/utils';

// Set a simple value
Storage.set('my_key', 'some_value');

// Get a value with a default
const myValue = Storage.get<string>('my_key', 'default_value');
```

### 2. Handling Complex Objects
The utility automatically handles `JSON.stringify` and `JSON.parse`.

```typescript
const userSettings = { theme: 'dark', notifications: true };
Storage.set('user_settings', userSettings);

// Retrieved value will be correctly parsed as an object
const settings = Storage.get<typeof userSettings>('user_settings');
```

### 3. Usage in Pinia Stores
```typescript
export const useMyStore = defineStore('myStore', {
    state: () => ({
        preference: Storage.get<string>('my_preference', 'default')
    }),
    actions: {
        setPreference(val: string) {
            this.preference = val;
            Storage.set('my_preference', val);
        }
    }
});
```

### 4. Usage in Vue Components with Watch
```typescript
const filterActive = ref(Storage.get<boolean>('filter_active', true));

watch(filterActive, (newVal) => {
    Storage.set('filter_active', newVal);
});
```

## Available Methods
- `Storage.get<T>(key: string, defaultValue?: T): T | null`
- `Storage.set(key: string, value: any): void`
- `Storage.remove(key: string): void`
- `Storage.clear(): void` (Clears only keys starting with `polycms_`)
