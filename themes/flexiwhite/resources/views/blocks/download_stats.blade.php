@php
    $uniqueId = uniqid('proj-stats-');

    // Display mode: both | downloads | activity
    $mode = $display_mode ?? 'both';

    // Download metrics
    $downloads = $total_downloads ?? 0;
    $formattedDownloads = $downloads >= 1000000
        ? number_format($downloads / 1000000, 1) . 'M'
        : ($downloads >= 1000
            ? number_format($downloads / 1000, 1) . 'K'
            : number_format($downloads));
    $todayDownloads = $today ?? 0;
    $last7Downloads = $last_7_days ?? 0;
    $last30Downloads = $last_30_days ?? 0;
    $chartData = $chart_data ?? [];

    // Active Usage / Telemetry metrics
    $activeCount = $total_active_instances ?? 0;
    $formattedActive = $activeCount >= 1000000
        ? number_format($activeCount / 1000000, 1) . 'M'
        : ($activeCount >= 1000
            ? number_format($activeCount / 1000, 1) . 'K'
            : number_format($activeCount));
    $todayActive = $active_today ?? 0;
    $last7Active = $active_7_days ?? 0;
    $last30Active = $active_30_days ?? 0;
    $chartActiveData = $chart_active_data ?? [];

    // General meta
    $version = $latest_version ?? null;
    $projectCode = $project_code ?? '';
    $projectName = $project_name ?? '';
    $releasedAt = $released_at ?? null;
    $chartLabels = $chart_labels ?? [];

    // Auto-adjust mode if one side has absolutely no data and no telemetry configured
    $hasDl = !empty($has_download_stats) || $downloads > 0 || $todayDownloads > 0;
    $hasAct = !empty($has_active_stats) || $activeCount > 0 || $todayActive > 0;
@endphp

<div id="{{ $uniqueId }}" class="projecthub-dl-block" data-default-mode="{{ $mode }}">
    <!-- Header & KPIs Row -->
    <div class="projecthub-dl-header">
        <!-- Project Info & Primary Counters -->
        <div class="projecthub-dl-main-info">
            <div class="projecthub-dl-icon-group">
                @if($mode === 'activity')
                    <div class="projecthub-dl-icon projecthub-dl-icon--active" title="{{ __('Active Devices') }}">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @else
                    <div class="projecthub-dl-icon projecthub-dl-icon--dl" title="{{ __('Downloads') }}">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                    </div>
                @endif
            </div>

            <div class="projecthub-dl-titles">
                <!-- Dual / Single Count Row -->
                <div class="projecthub-dl-count-row">
                    @if($mode === 'both')
                        <div class="projecthub-metric-item" data-metric="downloads">
                            <span class="projecthub-dl-count text-blue-600 dark:text-blue-400">{{ $formattedDownloads }}</span>
                            <span class="projecthub-dl-count-label">{{ __('DOWNLOADS') }}</span>
                        </div>
                        <span class="projecthub-dl-divider">·</span>
                        <div class="projecthub-metric-item" data-metric="active">
                            <span class="projecthub-dl-count text-emerald-600 dark:text-emerald-400">{{ $formattedActive }}</span>
                            <span class="projecthub-dl-count-label">{{ __('ACTIVE DEVICES') }}</span>
                        </div>
                    @elseif($mode === 'activity')
                        <div class="projecthub-metric-item">
                            <span class="projecthub-dl-count text-emerald-600 dark:text-emerald-400">{{ $formattedActive }}</span>
                            <span class="projecthub-dl-count-label">{{ __('ACTIVE DEVICES') }}</span>
                        </div>
                    @else
                        <div class="projecthub-metric-item">
                            <span class="projecthub-dl-count">{{ $formattedDownloads }}</span>
                            <span class="projecthub-dl-count-label">{{ __('TOTAL DOWNLOADS') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Meta Row -->
                <div class="projecthub-dl-meta-row">
                    @if($projectCode)
                        <span class="projecthub-dl-badge-code">{{ $projectCode }}</span>
                    @endif
                    @if($version)
                        <span class="projecthub-dl-badge-version">v{{ $version }}</span>
                    @endif
                    @if($releasedAt)
                        <span class="projecthub-dl-date">{{ \Carbon\Carbon::parse($releasedAt)->format('M d, Y') }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Mini KPI Badges -->
        <div class="projecthub-dl-kpis">
            <div class="projecthub-dl-kpi-item">
                <span class="projecthub-dl-kpi-label">{{ __('TODAY') }}</span>
                <span class="projecthub-dl-kpi-val" id="kpi-today-{{ $uniqueId }}">
                    @if($mode === 'both')
                        <span class="kpi-both-val"><span class="kpi-dl-val">{{ number_format($todayDownloads) }}</span><span class="kpi-sep">/</span><span class="kpi-act-val text-emerald-600 dark:text-emerald-400">{{ number_format($todayActive) }}</span></span>
                    @elseif($mode === 'activity')
                        <span class="text-emerald-600 dark:text-emerald-400">{{ number_format($todayActive) }}</span>
                    @else
                        {{ number_format($todayDownloads) }}
                    @endif
                </span>
            </div>
            <div class="projecthub-dl-kpi-item">
                <span class="projecthub-dl-kpi-label">{{ __('7 DAYS') }}</span>
                <span class="projecthub-dl-kpi-val" id="kpi-7d-{{ $uniqueId }}">
                    @if($mode === 'both')
                        <span class="kpi-both-val"><span class="kpi-dl-val">{{ number_format($last7Downloads) }}</span><span class="kpi-sep">/</span><span class="kpi-act-val text-emerald-600 dark:text-emerald-400">{{ number_format($last7Active) }}</span></span>
                    @elseif($mode === 'activity')
                        <span class="text-emerald-600 dark:text-emerald-400">{{ number_format($last7Active) }}</span>
                    @else
                        {{ number_format($last7Downloads) }}
                    @endif
                </span>
            </div>
            <div class="projecthub-dl-kpi-item">
                <span class="projecthub-dl-kpi-label">{{ __('30 DAYS') }}</span>
                <span class="projecthub-dl-kpi-val" id="kpi-30d-{{ $uniqueId }}">
                    @if($mode === 'both')
                        <span class="kpi-both-val"><span class="kpi-dl-val">{{ number_format($last30Downloads) }}</span><span class="kpi-sep">/</span><span class="kpi-act-val text-emerald-600 dark:text-emerald-400">{{ number_format($last30Active) }}</span></span>
                    @elseif($mode === 'activity')
                        <span class="text-emerald-600 dark:text-emerald-400">{{ number_format($last30Active) }}</span>
                    @else
                        {{ number_format($last30Downloads) }}
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Trend Chart Section -->
    <div class="projecthub-dl-chart-section">
        <div class="projecthub-dl-chart-header">
            <div class="projecthub-dl-chart-title">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <span id="chart-title-{{ $uniqueId }}">
                    @if($mode === 'both')
                        {{ __('Download & Activity Trend (14 Days)') }}
                    @elseif($mode === 'activity')
                        {{ __('Active Usage Trend (14 Days)') }}
                    @else
                        {{ __('Download Activity Trend (14 Days)') }}
                    @endif
                </span>
            </div>

            <!-- Filter Pills (When in 'both' mode, allows toggling between All, Downloads, and Active) -->
            @if($mode === 'both')
                <div class="projecthub-dl-pills" id="pills-{{ $uniqueId }}">
                    <button type="button" class="projecthub-pill-btn is-active" data-filter="all">
                        <span class="pill-dot pill-dot--dual"></span>
                        <span>{{ __('All') }}</span>
                    </button>
                    <button type="button" class="projecthub-pill-btn" data-filter="downloads">
                        <span class="pill-dot pill-dot--blue"></span>
                        <span>{{ __('Downloads') }}</span>
                    </button>
                    <button type="button" class="projecthub-pill-btn" data-filter="activity">
                        <span class="pill-dot pill-dot--emerald"></span>
                        <span>{{ __('Active') }}</span>
                    </button>
                </div>
            @endif
        </div>

        <div class="projecthub-dl-canvas-box">
            <canvas id="canvas-{{ $uniqueId }}"></canvas>
        </div>
    </div>
</div>

<style>
.projecthub-dl-block {
    margin: 1.75rem 0;
    padding: 1.25rem 1.5rem;
    border-radius: var(--radius, 8px);
    border: 1px solid var(--color-border, var(--geist-accents-2, #eaeaea));
    background: var(--theme-surface-color, var(--geist-background, #ffffff));
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    font-family: var(--font-sans, inherit);
    color: var(--theme-body-color, var(--geist-foreground, #000000));
    transition: border-color 0.2s ease, background-color 0.2s ease;
    box-sizing: border-box;
}

html.dark .projecthub-dl-block,
.dark .projecthub-dl-block {
    background: var(--theme-surface-color, #182433) !important;
    border-color: var(--color-border, #3a4859) !important;
    color: var(--theme-body-color, #e2e8f0) !important;
    box-shadow: none;
}

.projecthub-dl-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--color-border, var(--geist-accents-2, #eaeaea));
}

html.dark .projecthub-dl-header,
.dark .projecthub-dl-header {
    border-bottom-color: var(--color-border, #3a4859) !important;
}

.projecthub-dl-main-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.projecthub-dl-icon-group {
    display: flex;
    align-items: center;
}

.projecthub-dl-icon {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background 0.2s, color 0.2s;
}

.projecthub-dl-icon--dl {
    background: var(--primary-light, rgba(37, 99, 235, 0.08));
    color: var(--primary, #2563eb);
}

html.dark .projecthub-dl-icon--dl,
.dark .projecthub-dl-icon--dl {
    background: rgba(59, 130, 246, 0.15) !important;
    color: var(--primary, #3b82f6) !important;
}

.projecthub-dl-icon--active {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
}

html.dark .projecthub-dl-icon--active,
.dark .projecthub-dl-icon--active {
    background: rgba(16, 185, 129, 0.2) !important;
    color: #34d399 !important;
}

.projecthub-dl-titles {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.projecthub-dl-count-row {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.projecthub-metric-item {
    display: inline-flex;
    align-items: baseline;
    gap: 0.4rem;
}

.projecthub-dl-divider {
    color: var(--geist-accents-3, #ccc);
    font-weight: bold;
    user-select: none;
}

html.dark .projecthub-dl-divider,
.dark .projecthub-dl-divider {
    color: #475569 !important;
}

.projecthub-dl-count {
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -0.02em;
    color: var(--theme-heading-color, var(--geist-foreground, #000000));
}

html.dark .projecthub-dl-count,
.dark .projecthub-dl-count {
    color: var(--theme-heading-color, #f8fafc) !important;
}

.projecthub-dl-count-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--theme-body-muted-color, var(--geist-accents-5, #666666));
}

html.dark .projecthub-dl-count-label,
.dark .projecthub-dl-count-label {
    color: var(--theme-body-muted-color, #94a3b8) !important;
}

.projecthub-dl-meta-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    flex-wrap: wrap;
}

.projecthub-dl-badge-code {
    font-family: var(--font-mono, monospace);
    font-size: 0.7rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 4px;
    background: var(--geist-accents-1, #fafafa);
    border: 1px solid var(--geist-accents-2, #eaeaea);
    color: var(--geist-foreground, #000000);
}

html.dark .projecthub-dl-badge-code,
.dark .projecthub-dl-badge-code {
    background: #111a24 !important;
    border-color: #3a4859 !important;
    color: #e2e8f0 !important;
}

.projecthub-dl-badge-version {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 4px;
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

html.dark .projecthub-dl-badge-version,
.dark .projecthub-dl-badge-version {
    background: rgba(16, 185, 129, 0.15) !important;
    color: #34d399 !important;
    border-color: rgba(16, 185, 129, 0.3) !important;
}

.projecthub-dl-date {
    color: var(--theme-body-muted-color, var(--geist-accents-5, #888888));
    font-size: 0.75rem;
}

html.dark .projecthub-dl-date,
.dark .projecthub-dl-date {
    color: #64748b !important;
}

/* KPIs */
.projecthub-dl-kpis {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.projecthub-dl-kpi-item {
    padding: 0.35rem 0.65rem;
    border-radius: 6px;
    background: var(--geist-accents-1, #fafafa);
    border: 1px solid var(--geist-accents-2, #eaeaea);
    text-align: center;
    min-width: 60px;
}

html.dark .projecthub-dl-kpi-item,
.dark .projecthub-dl-kpi-item {
    background: #111a24 !important;
    border-color: #3a4859 !important;
}

.projecthub-dl-kpi-label {
    display: block;
    font-size: 0.625rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--theme-body-muted-color, var(--geist-accents-5, #888888));
}

html.dark .projecthub-dl-kpi-label,
.dark .projecthub-dl-kpi-label {
    color: #64748b !important;
}

.projecthub-dl-kpi-val {
    display: block;
    font-size: 0.825rem;
    font-weight: 700;
    color: var(--theme-heading-color, var(--geist-foreground, #000000));
}

html.dark .projecthub-dl-kpi-val,
.dark .projecthub-dl-kpi-val {
    color: #f1f5f9 !important;
}

.kpi-both-val {
    display: inline-flex;
    align-items: center;
    gap: 2px;
}
.kpi-sep {
    opacity: 0.4;
    font-weight: normal;
    padding: 0 1px;
}

/* Chart Header & Pills */
.projecthub-dl-chart-section {
    margin-top: 0.875rem;
}

.projecthub-dl-chart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.65rem;
}

.projecthub-dl-chart-title {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--theme-body-muted-color, var(--geist-accents-5, #666666));
}

html.dark .projecthub-dl-chart-title,
.dark .projecthub-dl-chart-title {
    color: #94a3b8 !important;
}

.projecthub-dl-pills {
    display: flex;
    align-items: center;
    gap: 4px;
    background: var(--geist-accents-1, #f4f4f5);
    padding: 2px;
    border-radius: 6px;
    border: 1px solid var(--geist-accents-2, #e4e4e7);
}

html.dark .projecthub-dl-pills,
.dark .projecthub-dl-pills {
    background: #111a24 !important;
    border-color: #2b394a !important;
}

.projecthub-pill-btn {
    border: none;
    background: transparent;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--theme-body-muted-color, #71717a);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.15s ease;
}

html.dark .projecthub-pill-btn,
.dark .projecthub-pill-btn {
    color: #94a3b8 !important;
}

.projecthub-pill-btn:hover {
    color: var(--theme-heading-color, #000);
}

html.dark .projecthub-pill-btn:hover,
.dark .projecthub-pill-btn:hover {
    color: #fff !important;
}

.projecthub-pill-btn.is-active {
    background: var(--theme-surface-color, #ffffff);
    color: var(--theme-heading-color, #000000);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
}

html.dark .projecthub-pill-btn.is-active,
.dark .projecthub-pill-btn.is-active {
    background: #1e293b !important;
    color: #f8fafc !important;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
}

.pill-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
}
.pill-dot--blue {
    background: #2563eb;
}
.pill-dot--emerald {
    background: #10b981;
}
.pill-dot--dual {
    background: linear-gradient(135deg, #2563eb 50%, #10b981 50%);
}

.projecthub-dl-canvas-box {
    position: relative;
    width: 100%;
    height: 140px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const rootBlock = document.getElementById('{{ $uniqueId }}');
    if (!rootBlock) return;

    const canvas = document.getElementById('canvas-{{ $uniqueId }}');
    if (!canvas) return;

    const isDark = document.documentElement.classList.contains('dark')
        || document.body.classList.contains('dark')
        || document.documentElement.getAttribute('data-theme') === 'dark';

    const defaultMode = rootBlock.getAttribute('data-default-mode') || 'both';
    let currentFilter = defaultMode === 'both' ? 'all' : defaultMode;

    const labels = @json($chartLabels);
    const dlData = @json($chartData);
    const actData = @json($chartActiveData);

    const kpiData = {
        downloads: {
            today: '{{ number_format($todayDownloads) }}',
            last7: '{{ number_format($last7Downloads) }}',
            last30: '{{ number_format($last30Downloads) }}',
        },
        activity: {
            today: '{{ number_format($todayActive) }}',
            last7: '{{ number_format($last7Active) }}',
            last30: '{{ number_format($last30Active) }}',
        },
        both: {
            today: '<span class="kpi-both-val"><span class="kpi-dl-val">{{ number_format($todayDownloads) }}</span><span class="kpi-sep">/</span><span class="kpi-act-val text-emerald-600 dark:text-emerald-400">{{ number_format($todayActive) }}</span></span>',
            last7: '<span class="kpi-both-val"><span class="kpi-dl-val">{{ number_format($last7Downloads) }}</span><span class="kpi-sep">/</span><span class="kpi-act-val text-emerald-600 dark:text-emerald-400">{{ number_format($last7Active) }}</span></span>',
            last30: '<span class="kpi-both-val"><span class="kpi-dl-val">{{ number_format($last30Downloads) }}</span><span class="kpi-sep">/</span><span class="kpi-act-val text-emerald-600 dark:text-emerald-400">{{ number_format($last30Active) }}</span></span>',
        }
    };

    function updateKpis(filter) {
        const elToday = document.getElementById('kpi-today-{{ $uniqueId }}');
        const el7d = document.getElementById('kpi-7d-{{ $uniqueId }}');
        const el30d = document.getElementById('kpi-30d-{{ $uniqueId }}');
        if (!elToday || !el7d || !el30d) return;

        if (filter === 'downloads') {
            elToday.innerHTML = kpiData.downloads.today;
            el7d.innerHTML = kpiData.downloads.last7;
            el30d.innerHTML = kpiData.downloads.last30;
        } else if (filter === 'activity') {
            elToday.innerHTML = `<span class="text-emerald-600 dark:text-emerald-400">${kpiData.activity.today}</span>`;
            el7d.innerHTML = `<span class="text-emerald-600 dark:text-emerald-400">${kpiData.activity.last7}</span>`;
            el30d.innerHTML = `<span class="text-emerald-600 dark:text-emerald-400">${kpiData.activity.last30}</span>`;
        } else {
            elToday.innerHTML = kpiData.both.today;
            el7d.innerHTML = kpiData.both.last7;
            el30d.innerHTML = kpiData.both.last30;
        }
    }

    const ChartConstructor = window.Chart 
        || (window.__POLYCMS_SDK__ && window.__POLYCMS_SDK__.ChartJS && (window.__POLYCMS_SDK__.ChartJS.Chart || window.__POLYCMS_SDK__.ChartJS.default)) 
        || (window.__POLYCMS_SDK__ && window.__POLYCMS_SDK__.Chart);

    let chartInstance = null;

    if (typeof ChartConstructor !== 'undefined') {
        const ctx = canvas.getContext('2d');

        // Color palettes
        const dlColor = isDark ? '#3b82f6' : '#2563eb';
        const actColor = isDark ? '#34d399' : '#10b981';

        const dlGrad = ctx.createLinearGradient(0, 0, 0, 140);
        dlGrad.addColorStop(0, isDark ? 'rgba(59, 130, 246, 0.25)' : 'rgba(37, 99, 235, 0.16)');
        dlGrad.addColorStop(1, 'rgba(0, 0, 0, 0.00)');

        const actGrad = ctx.createLinearGradient(0, 0, 0, 140);
        actGrad.addColorStop(0, isDark ? 'rgba(52, 211, 153, 0.25)' : 'rgba(16, 185, 129, 0.16)');
        actGrad.addColorStop(1, 'rgba(0, 0, 0, 0.00)');

        function buildDatasets(filter) {
            const ds = [];

            if (filter === 'all' || filter === 'downloads') {
                ds.push({
                    label: '{{ __("Downloads") }}',
                    data: dlData,
                    borderColor: dlColor,
                    backgroundColor: dlGrad,
                    fill: filter === 'downloads' || filter === 'all',
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 2.5,
                    pointHoverRadius: 5,
                    pointBackgroundColor: dlColor,
                    pointBorderColor: isDark ? '#182433' : '#ffffff',
                    pointBorderWidth: 1.5,
                });
            }

            if (filter === 'all' || filter === 'activity') {
                ds.push({
                    label: '{{ __("Active Devices") }}',
                    data: actData,
                    borderColor: actColor,
                    backgroundColor: actGrad,
                    fill: filter === 'activity' || filter === 'all',
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 2.5,
                    pointHoverRadius: 5,
                    pointBackgroundColor: actColor,
                    pointBorderColor: isDark ? '#182433' : '#ffffff',
                    pointBorderWidth: 1.5,
                });
            }

            return ds;
        }

        chartInstance = new ChartConstructor(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: buildDatasets(currentFilter),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: {
                        display: currentFilter === 'all',
                        position: 'top',
                        align: 'end',
                        labels: {
                            boxWidth: 8,
                            boxHeight: 8,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            color: isDark ? '#94a3b8' : '#64748b',
                            font: { size: 10, weight: 600 },
                            padding: 10,
                        }
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#0f172a' : '#1e293b',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        padding: 8,
                        cornerRadius: 6,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: isDark ? '#64748b' : '#94a3b8',
                            font: { size: 10 },
                            maxTicksLimit: 7,
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)' },
                        ticks: {
                            color: isDark ? '#64748b' : '#94a3b8',
                            font: { size: 10 },
                            precision: 0,
                        }
                    }
                }
            }
        });

        // Pill click listeners
        const pillWrap = document.getElementById('pills-{{ $uniqueId }}');
        if (pillWrap) {
            pillWrap.querySelectorAll('.projecthub-pill-btn').forEach((btn) => {
                btn.addEventListener('click', () => {
                    pillWrap.querySelectorAll('.projecthub-pill-btn').forEach((b) => b.classList.remove('is-active'));
                    btn.classList.add('is-active');

                    const filter = btn.getAttribute('data-filter');
                    currentFilter = filter;
                    updateKpis(filter);

                    chartInstance.data.datasets = buildDatasets(filter);
                    chartInstance.options.plugins.legend.display = filter === 'all';
                    chartInstance.update();
                });
            });
        }
    } else {
        // Native High-DPI Canvas 2D Fallback
        function renderNativeCanvas() {
            const dpr = window.devicePixelRatio || 1;
            const width = canvas.parentElement.clientWidth || 300;
            const height = 140;

            canvas.width = width * dpr;
            canvas.height = height * dpr;
            canvas.style.width = width + 'px';
            canvas.style.height = height + 'px';

            const ctx = canvas.getContext('2d');
            ctx.scale(dpr, dpr);

            const padLeft = 28;
            const padRight = 12;
            const padTop = 10;
            const padBottom = 22;
            const chartW = Math.max(10, width - padLeft - padRight);
            const chartH = Math.max(10, height - padTop - padBottom);

            const allVals = [];
            if (currentFilter === 'all' || currentFilter === 'downloads') allVals.push(...dlData);
            if (currentFilter === 'all' || currentFilter === 'activity') allVals.push(...actData);
            const maxVal = Math.max(5, ...allVals);
            const count = labels.length;

            if (count < 2) return;

            // Clear
            ctx.clearRect(0, 0, width, height);

            // Draw grid
            const gridLines = 3;
            ctx.strokeStyle = isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.06)';
            ctx.fillStyle = isDark ? '#64748b' : '#94a3b8';
            ctx.font = '10px -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';

            for (let i = 0; i <= gridLines; i++) {
                const val = Math.round((maxVal / gridLines) * i);
                const y = padTop + chartH - (val / maxVal) * chartH;
                ctx.beginPath();
                ctx.moveTo(padLeft, y);
                ctx.lineTo(padLeft + chartW, y);
                ctx.stroke();
                ctx.fillText(val.toString(), padLeft - 6, y);
            }

            function drawSeries(seriesData, strokeColor, gradStart) {
                const points = seriesData.map((v, i) => {
                    const x = padLeft + (i / (count - 1)) * chartW;
                    const y = padTop + chartH - (v / maxVal) * chartH;
                    return { x, y, v };
                });

                // Fill gradient
                const grad = ctx.createLinearGradient(0, padTop, 0, padTop + chartH);
                grad.addColorStop(0, gradStart);
                grad.addColorStop(1, 'rgba(0, 0, 0, 0.00)');

                ctx.beginPath();
                ctx.moveTo(points[0].x, padTop + chartH);
                ctx.lineTo(points[0].x, points[0].y);
                for (let i = 1; i < count; i++) {
                    const prev = points[i - 1];
                    const curr = points[i];
                    const cx = (prev.x + curr.x) / 2;
                    ctx.bezierCurveTo(cx, prev.y, cx, curr.y, curr.x, curr.y);
                }
                ctx.lineTo(points[count - 1].x, padTop + chartH);
                ctx.closePath();
                ctx.fillStyle = grad;
                ctx.fill();

                // Stroke line
                ctx.beginPath();
                ctx.moveTo(points[0].x, points[0].y);
                for (let i = 1; i < count; i++) {
                    const prev = points[i - 1];
                    const curr = points[i];
                    const cx = (prev.x + curr.x) / 2;
                    ctx.bezierCurveTo(cx, prev.y, cx, curr.y, curr.x, curr.y);
                }
                ctx.strokeStyle = strokeColor;
                ctx.lineWidth = 2;
                ctx.stroke();

                // Points
                points.forEach((p) => {
                    ctx.beginPath();
                    ctx.arc(p.x, p.y, 2.5, 0, Math.PI * 2);
                    ctx.fillStyle = strokeColor;
                    ctx.fill();
                    ctx.lineWidth = 1.5;
                    ctx.strokeStyle = isDark ? '#182433' : '#ffffff';
                    ctx.stroke();
                });
            }

            if (currentFilter === 'all' || currentFilter === 'downloads') {
                drawSeries(dlData, isDark ? '#3b82f6' : '#2563eb', isDark ? 'rgba(59, 130, 246, 0.25)' : 'rgba(37, 99, 235, 0.16)');
            }
            if (currentFilter === 'all' || currentFilter === 'activity') {
                drawSeries(actData, isDark ? '#34d399' : '#10b981', isDark ? 'rgba(52, 211, 153, 0.25)' : 'rgba(16, 185, 129, 0.16)');
            }

            // Labels
            ctx.fillStyle = isDark ? '#64748b' : '#94a3b8';
            labels.forEach((lbl, idx) => {
                if (idx === 0 || idx === Math.floor(count / 2) || idx === count - 1) {
                    const x = padLeft + (idx / (count - 1)) * chartW;
                    ctx.textAlign = idx === 0 ? 'left' : (idx === count - 1 ? 'right' : 'center');
                    ctx.fillText(lbl, x, padTop + chartH + 12);
                }
            });
        }

        renderNativeCanvas();
        window.addEventListener('resize', renderNativeCanvas);

        const pillWrap = document.getElementById('pills-{{ $uniqueId }}');
        if (pillWrap) {
            pillWrap.querySelectorAll('.projecthub-pill-btn').forEach((btn) => {
                btn.addEventListener('click', () => {
                    pillWrap.querySelectorAll('.projecthub-pill-btn').forEach((b) => b.classList.remove('is-active'));
                    btn.classList.add('is-active');

                    const filter = btn.getAttribute('data-filter');
                    currentFilter = filter;
                    updateKpis(filter);
                    renderNativeCanvas();
                });
            });
        }
    }
});
</script>
