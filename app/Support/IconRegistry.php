<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Centralized Icon Registry for PolyCMS
 *
 * Icons are served from the SVG sprite file at /icons/heroicons.svg
 * This class handles alias resolution and icon name management.
 *
 * To regenerate the sprite, run: node /tmp/generate-heroicon-sprite.js
 */
class IconRegistry
{
    /**
     * Known icon names (matches symbol IDs in the sprite file)
     * @var array<string, bool>
     */
    protected static array $iconNames = [];

    /**
     * Additional icons registered by modules/themes (raw SVG strings)
     * @var array<string, string>
     */
    protected static array $customIcons = [];

    /**
     * Whether core icon names have been initialized
     */
    protected static bool $initialized = false;

    /**
     * Path to the SVG sprite file (relative to public/)
     */
    public const SPRITE_PATH = '/icons/heroicons.svg';

    /**
     * Get an icon's resolved name. Returns the canonical icon name
     * for use with the SVG sprite, or a raw SVG string for custom/path icons.
     */
    public static function get(string $name): ?string
    {
        self::ensureInitialized();

        // 1. Check custom icons first (raw SVG strings from modules)
        if (isset(self::$customIcons[$name])) {
            return self::$customIcons[$name];
        }

        // 2. Direct match in known names
        if (isset(self::$iconNames[$name])) {
            return $name;
        }

        // 3. Normalize and check aliases
        $resolved = self::resolve($name);
        if ($resolved && isset(self::$iconNames[$resolved])) {
            return $resolved;
        }

        // 4. Handle raw SVG path (starts with M and contains spaces)
        if (str_starts_with($name, 'M') && str_contains($name, ' ')) {
            return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="' . e($name) . '" /></svg>';
        }

        return null;
    }

    /**
     * Render an icon as an SVG <use> reference or raw SVG.
     * This is the primary method for Blade templates.
     *
     * @param string $name   Icon name
     * @param string $class  CSS class(es) to apply
     * @param int    $width  Width in pixels
     * @param int    $height Height in pixels
     */
    public static function render(string $name, string $class = '', int $width = 16, int $height = 16): string
    {
        $resolved = self::get($name);

        if ($resolved === null) {
            // Fallback info icon
            return '<svg width="' . $width . '" height="' . $height . '" viewBox="0 0 20 20" fill="currentColor"' . ($class ? ' class="' . e($class) . '"' : '') . '><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>';
        }

        // If the resolved value is a raw SVG string (custom icon or raw path)
        if (str_starts_with($resolved, '<svg')) {
            return $resolved;
        }

        // Otherwise, render a <use> reference to the sprite
        $classAttr = $class ? ' class="' . e($class) . '"' : '';
        return '<svg width="' . $width . '" height="' . $height . '"' . $classAttr . '><use href="' . self::SPRITE_PATH . '#' . e($resolved) . '"/></svg>';
    }

    /**
     * Register a custom icon SVG (for modules/themes)
     */
    public static function register(string $name, string $svg): void
    {
        self::ensureInitialized();
        self::$customIcons[$name] = $svg;
    }

    /**
     * Get all registered icon names
     * @return array<string>
     */
    public static function allNames(): array
    {
        self::ensureInitialized();
        return array_keys(self::$iconNames);
    }

    /**
     * Get all registered icons as name => SVG reference map
     * Kept for backward compatibility (returns name => name for sprite icons)
     * @return array<string, string>
     */
    public static function all(): array
    {
        self::ensureInitialized();

        $result = [];
        foreach (self::$iconNames as $name => $_) {
            $result[$name] = $name;
        }

        return array_merge($result, self::$customIcons);
    }

    /**
     * Get all registered aliases
     */
    public static function getAllAliases(): array
    {
        return [
            // Legacy system names -> Heroicon names
            'dashboard'            => 'Squares2x2Icon',
            'home'                 => 'HomeIcon',
            'pencil'               => 'PencilIcon',
            'paint-brush'          => 'PaintBrushIcon',
            'user-circle'          => 'UserCircleIcon',
            'user'                 => 'UserIcon',
            'logout'               => 'ArrowLeftOnRectangleIcon',
            'log-out'              => 'ArrowLeftOnRectangleIcon',
            'sign-out'             => 'ArrowLeftOnRectangleIcon',
            'document'             => 'DocumentTextIcon',
            'page'                 => 'DocumentTextIcon',
            'product'              => 'CubeIcon',
            'product-category'     => 'RectangleGroupIcon',
            'product-brand'        => 'TagIcon',
            'brand'                => 'TagIcon',
            'category'             => 'FolderIcon',
            'shopping-cart'        => 'ShoppingCartIcon',
            'refresh'              => 'ArrowPathIcon',
            'key'                  => 'KeyIcon',
            'link'                 => 'LinkIcon',
            'menu'                 => 'Bars3Icon',
            'globe'                => 'GlobeAltIcon',
            'cog'                  => 'Cog6ToothIcon',
            'settings'             => 'Cog6ToothIcon',
            'archive'              => 'ArchiveBoxIcon',
            'bell'                 => 'BellIcon',
            'check'                => 'CheckCircleIcon',
            'sync'                 => 'ArrowPathIcon',
            'delete'               => 'TrashIcon',
            'trash'                => 'TrashIcon',
            'edit'                 => 'PencilSquareIcon',
            'search'               => 'MagnifyingGlassIcon',
            'box'                  => 'ArchiveBoxIcon',
            'plus'                 => 'PlusIcon',
            'close'                => 'XMarkIcon',
            'x'                    => 'XMarkIcon',
            'star'                 => 'StarIcon',
            'heart'                => 'HeartIcon',
            'mail'                 => 'EnvelopeIcon',
            'email'                => 'EnvelopeIcon',
            'phone'                => 'PhoneIcon',
            'camera'               => 'CameraIcon',
            'photo'                => 'PhotoIcon',
            'image'                => 'PhotoIcon',
            'calendar'             => 'CalendarIcon',
            'clock'                => 'ClockIcon',
            'cloud'                => 'CloudIcon',
            'folder'               => 'FolderIcon',
            'tag'                  => 'TagIcon',
            'bookmark'             => 'BookmarkIcon',
            'flag'                 => 'FlagIcon',
            'printer'              => 'PrinterIcon',
            'share'                => 'ShareIcon',
            'download'             => 'ArrowDownTrayIcon',
            'upload'               => 'ArrowUpTrayIcon',
            'play'                 => 'PlayIcon',
            'pause'                => 'PauseIcon',
            'stop'                 => 'StopIcon',
            'power'                => 'PowerIcon',
            'wifi'                 => 'WifiIcon',
            'signal'               => 'SignalIcon',
            'gift'                 => 'GiftIcon',
            'fire'                 => 'FireIcon',
            'bolt'                 => 'BoltIcon',
            'sun'                  => 'SunIcon',
            'moon'                 => 'MoonIcon',
            'map'                  => 'MapIcon',
            'map-pin'              => 'MapPinIcon',
            'location'             => 'MapPinIcon',
            'truck'                => 'TruckIcon',
            'server'               => 'ServerIcon',
            'code'                 => 'CodeBracketIcon',
            'terminal'             => 'CommandLineIcon',
            'wrench'               => 'WrenchIcon',
            'tools'                => 'WrenchScrewdriverIcon',
            'puzzle'               => 'PuzzlePieceIcon',
            'rocket'               => 'RocketLaunchIcon',
            'shield'               => 'ShieldCheckIcon',
            'lock'                 => 'LockClosedIcon',
            'unlock'               => 'LockOpenIcon',
            'eye'                  => 'EyeIcon',
            'film'                 => 'FilmIcon',
            'chart'                => 'ChartBarIcon',
            'clipboard'            => 'ClipboardIcon',
            'newspaper'            => 'NewspaperIcon',
            'ticket'               => 'TicketIcon',
            'banknotes'            => 'BanknotesIcon',
            'chevron-down'         => 'ChevronDownIcon',
            'chevron-up'           => 'ChevronUpIcon',
            'chevron-left'         => 'ChevronLeftIcon',
            'chevron-right'        => 'ChevronRightIcon',
            'bars'                 => 'Bars3Icon',
            'cube'                 => 'CubeIcon',
        ];
    }

    /**
     * Check if an icon exists
     */
    public static function has(string $name): bool
    {
        return self::get($name) !== null;
    }

    /**
     * Resolve legacy/alias names to canonical Heroicon names
     */
    public static function resolve(string $name): ?string
    {
        $aliases = self::getAllAliases();

        if (isset($aliases[$name])) {
            return $aliases[$name];
        }

        self::ensureInitialized();

        // Try appending 'Icon' suffix
        if (!str_ends_with($name, 'Icon')) {
            $withIcon = $name . 'Icon';
            if (isset(self::$iconNames[$withIcon])) {
                return $withIcon;
            }
        }

        return null;
    }

    /**
     * Initialize the icon name registry
     * Reads symbol IDs from the SVG sprite file
     */
    protected static function ensureInitialized(): void
    {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        // All 284 Heroicon names from the sprite file
        self::$iconNames = array_fill_keys([
            'AcademicCapIcon', 'AdjustmentsHorizontalIcon', 'AdjustmentsVerticalIcon',
            'ArchiveBoxArrowDownIcon', 'ArchiveBoxIcon', 'ArchiveBoxXMarkIcon',
            'ArrowDownCircleIcon', 'ArrowDownIcon', 'ArrowDownLeftIcon',
            'ArrowDownOnSquareIcon', 'ArrowDownOnSquareStackIcon', 'ArrowDownRightIcon',
            'ArrowDownTrayIcon', 'ArrowLeftCircleIcon', 'ArrowLeftEndOnRectangleIcon',
            'ArrowLeftIcon', 'ArrowLeftOnRectangleIcon', 'ArrowLeftStartOnRectangleIcon',
            'ArrowLongDownIcon', 'ArrowLongLeftIcon', 'ArrowLongRightIcon',
            'ArrowLongUpIcon', 'ArrowPathIcon', 'ArrowPathRoundedSquareIcon',
            'ArrowRightCircleIcon', 'ArrowRightEndOnRectangleIcon', 'ArrowRightIcon',
            'ArrowRightOnRectangleIcon', 'ArrowRightStartOnRectangleIcon',
            'ArrowTopRightOnSquareIcon', 'ArrowTrendingDownIcon', 'ArrowTrendingUpIcon',
            'ArrowUpCircleIcon', 'ArrowUpIcon', 'ArrowUpLeftIcon',
            'ArrowUpOnSquareIcon', 'ArrowUpOnSquareStackIcon', 'ArrowUpRightIcon',
            'ArrowUpTrayIcon', 'ArrowUturnDownIcon', 'ArrowUturnLeftIcon',
            'ArrowUturnRightIcon', 'ArrowUturnUpIcon', 'ArrowsPointingInIcon',
            'ArrowsPointingOutIcon', 'ArrowsRightLeftIcon', 'ArrowsUpDownIcon',
            'AtSymbolIcon', 'BackspaceIcon', 'BackwardIcon',
            'BanknotesIcon', 'Bars2Icon', 'Bars3BottomLeftIcon',
            'Bars3BottomRightIcon', 'Bars3CenterLeftIcon', 'Bars3Icon',
            'Bars4Icon', 'BarsArrowDownIcon', 'BarsArrowUpIcon',
            'Battery0Icon', 'Battery100Icon', 'Battery50Icon',
            'BeakerIcon', 'BellAlertIcon', 'BellIcon',
            'BellSlashIcon', 'BellSnoozeIcon', 'BoltIcon',
            'BoltSlashIcon', 'BookOpenIcon', 'BookmarkIcon',
            'BookmarkSlashIcon', 'BookmarkSquareIcon', 'BriefcaseIcon',
            'BugAntIcon', 'BuildingLibraryIcon', 'BuildingOffice2Icon',
            'BuildingOfficeIcon', 'BuildingStorefrontIcon', 'CakeIcon',
            'CalculatorIcon', 'CalendarDaysIcon', 'CalendarIcon',
            'CameraIcon', 'ChartBarIcon', 'ChartBarSquareIcon',
            'ChartPieIcon', 'ChatBubbleBottomCenterIcon', 'ChatBubbleBottomCenterTextIcon',
            'ChatBubbleLeftEllipsisIcon', 'ChatBubbleLeftIcon', 'ChatBubbleLeftRightIcon',
            'ChatBubbleOvalLeftEllipsisIcon', 'ChatBubbleOvalLeftIcon', 'CheckBadgeIcon',
            'CheckCircleIcon', 'CheckIcon', 'ChevronDoubleDownIcon',
            'ChevronDoubleLeftIcon', 'ChevronDoubleRightIcon', 'ChevronDoubleUpIcon',
            'ChevronDownIcon', 'ChevronLeftIcon', 'ChevronRightIcon',
            'ChevronUpDownIcon', 'ChevronUpIcon', 'CircleStackIcon',
            'ClipboardDocumentCheckIcon', 'ClipboardDocumentIcon', 'ClipboardDocumentListIcon',
            'ClipboardIcon', 'ClockIcon', 'CloudArrowDownIcon',
            'CloudArrowUpIcon', 'CloudIcon', 'CodeBracketIcon',
            'CodeBracketSquareIcon', 'Cog6ToothIcon', 'Cog8ToothIcon',
            'CogIcon', 'CommandLineIcon', 'ComputerDesktopIcon',
            'CpuChipIcon', 'CreditCardIcon', 'CubeIcon',
            'CubeTransparentIcon', 'CurrencyBangladeshiIcon', 'CurrencyDollarIcon',
            'CurrencyEuroIcon', 'CurrencyPoundIcon', 'CurrencyRupeeIcon',
            'CurrencyYenIcon', 'CursorArrowRaysIcon', 'CursorArrowRippleIcon',
            'DevicePhoneMobileIcon', 'DeviceTabletIcon', 'DocumentArrowDownIcon',
            'DocumentArrowUpIcon', 'DocumentChartBarIcon', 'DocumentCheckIcon',
            'DocumentDuplicateIcon', 'DocumentIcon', 'DocumentMagnifyingGlassIcon',
            'DocumentMinusIcon', 'DocumentPlusIcon', 'DocumentTextIcon',
            'EllipsisHorizontalCircleIcon', 'EllipsisHorizontalIcon', 'EllipsisVerticalIcon',
            'EnvelopeIcon', 'EnvelopeOpenIcon', 'ExclamationCircleIcon',
            'ExclamationTriangleIcon', 'EyeDropperIcon', 'EyeIcon',
            'EyeSlashIcon', 'FaceFrownIcon', 'FaceSmileIcon',
            'FilmIcon', 'FingerPrintIcon', 'FireIcon',
            'FlagIcon', 'FolderArrowDownIcon', 'FolderIcon',
            'FolderMinusIcon', 'FolderOpenIcon', 'FolderPlusIcon',
            'ForwardIcon', 'FunnelIcon', 'GifIcon',
            'GiftIcon', 'GiftTopIcon', 'GlobeAltIcon',
            'GlobeAmericasIcon', 'GlobeAsiaAustraliaIcon', 'GlobeEuropeAfricaIcon',
            'HandRaisedIcon', 'HandThumbDownIcon', 'HandThumbUpIcon',
            'HashtagIcon', 'HeartIcon', 'HomeIcon',
            'HomeModernIcon', 'IdentificationIcon', 'InboxArrowDownIcon',
            'InboxIcon', 'InboxStackIcon', 'InformationCircleIcon',
            'KeyIcon', 'LanguageIcon', 'LifebuoyIcon',
            'LightBulbIcon', 'LinkIcon', 'ListBulletIcon',
            'LockClosedIcon', 'LockOpenIcon', 'MagnifyingGlassCircleIcon',
            'MagnifyingGlassIcon', 'MagnifyingGlassMinusIcon', 'MagnifyingGlassPlusIcon',
            'MapIcon', 'MapPinIcon', 'MegaphoneIcon',
            'MicrophoneIcon', 'MinusCircleIcon', 'MinusIcon',
            'MoonIcon', 'MusicalNoteIcon', 'NewspaperIcon',
            'NoSymbolIcon', 'PaintBrushIcon', 'PaperAirplaneIcon',
            'PaperClipIcon', 'PauseCircleIcon', 'PauseIcon',
            'PencilIcon', 'PencilSquareIcon', 'PhoneArrowDownLeftIcon',
            'PhoneArrowUpRightIcon', 'PhoneIcon', 'PhoneXMarkIcon',
            'PhotoIcon', 'PlayCircleIcon', 'PlayIcon',
            'PlayPauseIcon', 'PlusCircleIcon', 'PlusIcon',
            'PowerIcon', 'PresentationChartBarIcon', 'PresentationChartLineIcon',
            'PrinterIcon', 'PuzzlePieceIcon', 'QrCodeIcon',
            'QuestionMarkCircleIcon', 'QueueListIcon', 'RadioIcon',
            'ReceiptPercentIcon', 'ReceiptRefundIcon', 'RectangleGroupIcon',
            'RectangleStackIcon', 'RocketLaunchIcon', 'RssIcon',
            'ScaleIcon', 'ScissorsIcon', 'ServerIcon',
            'ServerStackIcon', 'ShareIcon', 'ShieldCheckIcon',
            'ShieldExclamationIcon', 'ShoppingBagIcon', 'ShoppingCartIcon',
            'SignalIcon', 'SignalSlashIcon', 'SparklesIcon',
            'SpeakerWaveIcon', 'SpeakerXMarkIcon', 'Square2StackIcon',
            'Square3Stack3DIcon', 'Squares2x2Icon', 'SquaresPlusIcon',
            'StarIcon', 'StopCircleIcon', 'StopIcon',
            'SunIcon', 'SwatchIcon', 'TableCellsIcon',
            'TagIcon', 'TicketIcon', 'TrashIcon',
            'TrophyIcon', 'TruckIcon', 'TvIcon',
            'UserCircleIcon', 'UserGroupIcon', 'UserIcon',
            'UserMinusIcon', 'UserPlusIcon', 'UsersIcon',
            'VariableIcon', 'VideoCameraIcon', 'VideoCameraSlashIcon',
            'ViewColumnsIcon', 'ViewfinderCircleIcon', 'WalletIcon',
            'WifiIcon', 'WindowIcon', 'WrenchIcon',
            'WrenchScrewdriverIcon', 'XCircleIcon', 'XMarkIcon',
        ], true);
    }
}
