<template>
    <!-- 
        Unified icon component — renders icons using the same SVG sprite 
        (/icons/heroicons.svg) as the frontend Blade templates.
        Mirrors PHP IconRegistry::render() + resolve() logic.
    -->
    <svg
        :width="size"
        :height="size"
        v-if="resolvedIcon && !isRawSvg"
    >
        <use :href="spriteHref" />
    </svg>
    <span v-else-if="isRawSvg" v-html="resolvedIcon" class="raw-icon"></span>
    <!-- Fallback: info circle -->
    <svg v-else :width="size" :height="size" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
    </svg>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    icon: string;
    size?: number;
}

const props = withDefaults(defineProps<Props>(), {
    size: 16
});

const SPRITE_PATH = '/icons/heroicons.svg';

/**
 * Alias map — mirrors IconRegistry::getAllAliases() in PHP
 * Maps legacy/short names to canonical Heroicon names
 */
const ALIASES: Record<string, string> = {
    'dashboard':        'Squares2x2Icon',
    'home':             'HomeIcon',
    'pencil':           'PencilIcon',
    'paint-brush':      'PaintBrushIcon',
    'user-circle':      'UserCircleIcon',
    'user':             'UserIcon',
    'logout':           'ArrowLeftOnRectangleIcon',
    'log-out':          'ArrowLeftOnRectangleIcon',
    'sign-out':         'ArrowLeftOnRectangleIcon',
    'document':         'DocumentTextIcon',
    'page':             'DocumentTextIcon',
    'product':          'CubeIcon',
    'product-category': 'RectangleGroupIcon',
    'product-brand':    'TagIcon',
    'brand':            'TagIcon',
    'category':         'FolderIcon',
    'shopping-cart':    'ShoppingCartIcon',
    'refresh':          'ArrowPathIcon',
    'key':              'KeyIcon',
    'link':             'LinkIcon',
    'menu':             'Bars3Icon',
    'globe':            'GlobeAltIcon',
    'cog':              'Cog6ToothIcon',
    'settings':         'Cog6ToothIcon',
    'archive':          'ArchiveBoxIcon',
    'bell':             'BellIcon',
    'check':            'CheckCircleIcon',
    'sync':             'ArrowPathIcon',
    'delete':           'TrashIcon',
    'trash':            'TrashIcon',
    'edit':             'PencilSquareIcon',
    'search':           'MagnifyingGlassIcon',
    'box':              'ArchiveBoxIcon',
    'plus':             'PlusIcon',
    'close':            'XMarkIcon',
    'x':                'XMarkIcon',
    'star':             'StarIcon',
    'heart':            'HeartIcon',
    'mail':             'EnvelopeIcon',
    'email':            'EnvelopeIcon',
    'phone':            'PhoneIcon',
    'camera':           'CameraIcon',
    'photo':            'PhotoIcon',
    'image':            'PhotoIcon',
    'calendar':         'CalendarIcon',
    'clock':            'ClockIcon',
    'cloud':            'CloudIcon',
    'folder':           'FolderIcon',
    'tag':              'TagIcon',
    'bookmark':         'BookmarkIcon',
    'flag':             'FlagIcon',
    'printer':          'PrinterIcon',
    'share':            'ShareIcon',
    'download':         'ArrowDownTrayIcon',
    'upload':           'ArrowUpTrayIcon',
    'play':             'PlayIcon',
    'pause':            'PauseIcon',
    'stop':             'StopIcon',
    'power':            'PowerIcon',
    'wifi':             'WifiIcon',
    'signal':           'SignalIcon',
    'gift':             'GiftIcon',
    'fire':             'FireIcon',
    'bolt':             'BoltIcon',
    'sun':              'SunIcon',
    'moon':             'MoonIcon',
    'map':              'MapIcon',
    'map-pin':          'MapPinIcon',
    'location':         'MapPinIcon',
    'truck':            'TruckIcon',
    'server':           'ServerIcon',
    'code':             'CodeBracketIcon',
    'terminal':         'CommandLineIcon',
    'wrench':           'WrenchIcon',
    'tools':            'WrenchScrewdriverIcon',
    'puzzle':           'PuzzlePieceIcon',
    'rocket':           'RocketLaunchIcon',
    'shield':           'ShieldCheckIcon',
    'lock':             'LockClosedIcon',
    'unlock':           'LockOpenIcon',
    'eye':              'EyeIcon',
    'film':             'FilmIcon',
    'chart':            'ChartBarIcon',
    'clipboard':        'ClipboardIcon',
    'newspaper':        'NewspaperIcon',
    'ticket':           'TicketIcon',
    'banknotes':        'BanknotesIcon',
    'chevron-down':     'ChevronDownIcon',
    'chevron-up':       'ChevronUpIcon',
    'chevron-left':     'ChevronLeftIcon',
    'chevron-right':    'ChevronRightIcon',
    'bars':             'Bars3Icon',
    'cube':             'CubeIcon',
};

/**
 * Resolve icon name — mirrors IconRegistry::resolve() logic:
 * 1. Check alias map
 * 2. If name already ends with 'Icon', use as-is
 * 3. Try appending 'Icon'
 * 4. Check if raw SVG path (starts with 'M' and contains space)
 */
const resolvedIcon = computed<string | null>(() => {
    const name = props.icon;
    if (!name) return null;

    // 1. Alias lookup
    if (ALIASES[name]) {
        return ALIASES[name];
    }

    // 2. Already a Heroicon name (ends with Icon)
    if (name.endsWith('Icon')) {
        return name;
    }

    // 3. Try appending Icon
    const withIcon = name + 'Icon';
    // We can't easily check if it exists in the sprite, but the <use> will
    // gracefully fail if the symbol doesn't exist. This matches the PHP behavior.
    if (name.charAt(0) === name.charAt(0).toUpperCase()) {
        return withIcon;
    }

    // 4. Raw SVG path (e.g. "M10 3a1 1 0 011 1v5h5...")
    if (name.startsWith('M') && name.includes(' ')) {
        return `<svg width="${props.size}" height="${props.size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="${name}" /></svg>`;
    }

    return null;
});

const isRawSvg = computed(() => {
    return resolvedIcon.value?.startsWith('<svg') ?? false;
});

const spriteHref = computed(() => {
    if (!resolvedIcon.value || isRawSvg.value) return '';
    return `${SPRITE_PATH}#${resolvedIcon.value}`;
});
</script>

<style scoped>
.raw-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.raw-icon :deep(svg) {
    display: block;
}
</style>
