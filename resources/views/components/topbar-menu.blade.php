@php
use App\Services\TopbarMenuService;
$topbarService = app(TopbarMenuService::class);

if (!$topbarService->shouldShow()) {
    return;
}

$menuItems = $topbarService->getGroupedMenuItems();
@endphp

<div id="polycms-topbar" class="polycms-topbar">
    <div class="polycms-topbar-container">
        {{-- Left Menu --}}
        <div class="polycms-topbar-left">
            @foreach($menuItems['left'] ?? [] as $item)
                @include('components.topbar-menu-item', ['item' => $item])
            @endforeach
        </div>

        {{-- Right Menu --}}
        <div class="polycms-topbar-right">
            @foreach($menuItems['right'] ?? [] as $item)
                @include('components.topbar-menu-item', ['item' => $item])
            @endforeach
        </div>
    </div>
</div>

<style>
    #polycms-topbar.polycms-topbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 999999;
        background: #1f2937;
        color: #fff;
        font-size: 13px;
        line-height: 32px;
        height: 32px;
        min-height: 32px;
        max-height: 32px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        overflow: visible;
    }
    
    .polycms-topbar-container {
        width: 100%;
        max-width: 100%;
        margin: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 16px;
        height: 32px;
        min-height: 32px;
        max-height: 32px;
        gap: 8px;
        box-sizing: border-box;
        overflow: visible;
    }
    
    .polycms-topbar-left,
    .polycms-topbar-right {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 0;
        flex-shrink: 0;
        flex-wrap: nowrap;
        overflow: visible;
        height: 32px;
    }

    .polycms-topbar-right {
        justify-content: flex-end;
    }
    
    .polycms-topbar a:not(.topbar-dropdown > a),
    .polycms-topbar button:not(.topbar-button) {
        color: #d1d5db;
        text-decoration: none;
        padding: 0 10px;
        display: contents;
        border-radius: 3px;
        transition: all 0.15s ease;
        background: transparent;
        border: none;
        cursor: pointer;
        font-size: 13px;
        font-weight: 400;
        white-space: nowrap;
        flex-shrink: 0;
        overflow: visible;
        box-sizing: border-box;
        vertical-align: middle;
    }

    /* Wrap icon and text in a container for proper spacing when using display: contents */
    .polycms-topbar-left a:not(.topbar-dropdown > a) > *,
    .polycms-topbar-left button:not(.topbar-button) > *,
    .polycms-topbar-right a:not(.topbar-dropdown > a) > *,
    .polycms-topbar-right button:not(.topbar-button) > * {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        line-height: 32px;
        height: 32px;
        min-height: 32px;
        max-height: 32px;
        vertical-align: middle;
    }

    /* For dropdown links, keep flex layout */
    .polycms-topbar .topbar-dropdown > a,
    .polycms-topbar .topbar-button {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        padding: 0 10px !important;
        gap: 6px !important;
        color: #d1d5db !important;
        text-decoration: none !important;
        border-radius: 3px !important;
        transition: all 0.15s ease !important;
        background: transparent !important;
        border: none !important;
        cursor: pointer !important;
        font-size: 13px !important;
        font-weight: 400 !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
        overflow: visible !important;
        box-sizing: border-box !important;
        vertical-align: middle !important;
        line-height: 32px !important;
        height: 32px !important;
        min-height: 32px !important;
        max-height: 32px !important;
    }
    
    .polycms-topbar a:hover,
    .polycms-topbar button:hover {
        color: #60a5fa;
        background: rgba(255, 255, 255, 0.1);
    }
    
    .polycms-topbar .topbar-highlight {
        background: #3b82f6 !important;
        color: #fff !important;
        font-weight: 500;
    }
    
    .polycms-topbar .topbar-highlight:hover {
        background: #2563eb !important;
        color: #fff !important;
    }
    
    .polycms-topbar .topbar-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }
    
    .polycms-topbar .topbar-icon svg {
        width: 16px;
        height: 16px;
        fill: currentColor;
    }
    
    .polycms-topbar .topbar-dropdown {
        position: relative;
    }
    
    .polycms-topbar .topbar-dropdown > a::after {
        content: '';
        display: inline-block;
        width: 0;
        height: 0;
        margin-left: 4px;
        margin-top: 2px;
        vertical-align: middle;
        border-top: 4px solid #d1d5db;
        border-right: 4px solid transparent;
        border-bottom: 0;
        border-left: 4px solid transparent;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }
    
    .polycms-topbar .topbar-dropdown:hover > a::after {
        border-top-color: #60a5fa;
    }
    
    .polycms-topbar .topbar-dropdown:hover .topbar-dropdown-content,
    .polycms-topbar .topbar-dropdown-content:hover {
        display: block;
        opacity: 1;
        visibility: visible;
    }
    
    .polycms-topbar .topbar-dropdown-content {
        display: none;
        opacity: 0;
        visibility: hidden;
        position: absolute;
        top: 100%;
        margin-top: 4px;
        background: #1f2937;
        min-width: 200px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        border-radius: 6px;
        padding: 6px 0;
        z-index: 100000;
        transition: opacity 0.15s ease, visibility 0.15s ease;
        border: 1px solid rgba(255, 255, 255, 0.08);
        /* Default: left items align to left edge */
        left: 0;
        right: auto;
    }

    /* Create invisible bridge between trigger and dropdown to prevent hiding on gap */
    .polycms-topbar .topbar-dropdown-content::before {
        content: '';
        position: absolute;
        bottom: 100%;
        left: 0;
        right: 0;
        height: 4px;
        background: transparent;
    }

    /* Dropdown positioning - left items align to left, right items align to right */
    .polycms-topbar-left .topbar-dropdown-content {
        left: 0;
        right: auto;
    }

    .polycms-topbar-right .topbar-dropdown-content {
        right: 0;
        left: auto;
    }
    
    .polycms-topbar .topbar-dropdown-content a,
    .polycms-topbar .topbar-dropdown-content button {
        display: flex;
        padding: 10px 16px;
        color: #d1d5db;
        white-space: nowrap;
        width: 100%;
        text-align: left;
        justify-content: flex-start;
        align-items: center;
        gap: 10px;
        height: auto;
        border-radius: 0;
        margin: 0;
        font-size: 13px;
        transition: all 0.15s ease;
    }
    
    .polycms-topbar .topbar-dropdown-content a:hover,
    .polycms-topbar .topbar-dropdown-content button:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #60a5fa;
    }
    
    .polycms-topbar .topbar-dropdown-content a:first-child,
    .polycms-topbar .topbar-dropdown-content button:first-child {
        border-radius: 6px 6px 0 0;
    }
    
    .polycms-topbar .topbar-dropdown-content a:last-child,
    .polycms-topbar .topbar-dropdown-content button:last-child {
        border-radius: 0 0 6px 6px;
    }
    
    /* Add margin to body when topbar is present */
    body:has(#polycms-topbar) {
        padding-top: 32px !important;
    }
    
    /* Fallback for browsers that don't support :has() */
    body.polycms-topbar-active {
        padding-top: 32px !important;
    }
    
    /* Hide any large POLYCMS text that might be showing */
    .polycms-topbar .polycms-logo-large {
        display: none !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .polycms-topbar-container {
            padding: 0 8px;
        }

        .polycms-topbar a,
        .polycms-topbar button {
            padding: 0 8px;
            font-size: 12px;
        }
    }
</style>

<script>
    // Add class to body when topbar is present (for browsers without :has() support)
    if (document.getElementById('polycms-topbar')) {
        document.body.classList.add('polycms-topbar-active');
    }
</script>

