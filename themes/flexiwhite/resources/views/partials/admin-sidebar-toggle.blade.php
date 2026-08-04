@php
    $showSidebar = $showSidebar ?? true;
    $settingKey = $settingKey ?? 'flexiwhite_products_show_sidebar';
@endphp

<div id="admin-sidebar-toggle-wrap" style="display: none; align-items: center;" class="admin-sidebar-toggle-wrapper">
    <button type="button" 
            id="admin-sidebar-toggle-btn" 
            class="admin-sidebar-toggle-btn {{ $showSidebar ? 'is-active' : '' }}" 
            onclick="toggleSidebarAdminSetting('{{ $settingKey }}')"
            title="{{ _l('Toggle Sidebar (Admin Option)') }}">
        <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
        </svg>
        <span id="admin-sidebar-toggle-label">{{ $showSidebar ? _l('Sidebar: ON') : _l('Sidebar: OFF') }}</span>
        <span class="toggle-switch"></span>
    </button>
</div>

<style>
    .admin-sidebar-toggle-wrapper {
        margin-left: auto;
    }
    .admin-sidebar-toggle-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        user-select: none;
    }
    .admin-sidebar-toggle-btn:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
        color: #0f172a;
    }
    html.dark .admin-sidebar-toggle-btn,
    .dark .admin-sidebar-toggle-btn {
        background: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
    }
    html.dark .admin-sidebar-toggle-btn:hover,
    .dark .admin-sidebar-toggle-btn:hover {
        background: #334155;
        color: #f8fafc;
    }
    .admin-sidebar-toggle-btn .toggle-switch {
        width: 28px;
        height: 16px;
        background: #cbd5e1;
        border-radius: 10px;
        position: relative;
        display: inline-block;
        transition: background 0.2s ease;
        flex-shrink: 0;
    }
    .admin-sidebar-toggle-btn.is-active .toggle-switch {
        background: #10b981;
    }
    .admin-sidebar-toggle-btn .toggle-switch::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 12px;
        height: 12px;
        background: #fff;
        border-radius: 50%;
        transition: transform 0.2s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }
    .admin-sidebar-toggle-btn.is-active .toggle-switch::after {
        transform: translateX(12px);
    }
    .admin-toast-notice {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: #0f172a;
        color: #fff;
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        z-index: 99999;
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
        pointer-events: none;
    }
    .admin-toast-notice.is-show {
        opacity: 1;
        transform: translateY(0);
    }
</style>

<script>
    function initAdminSidebarToggle() {
        const authToken = localStorage.getItem('auth_token');
        if (authToken) {
            const wrap = document.getElementById('admin-sidebar-toggle-wrap');
            if (wrap) {
                wrap.style.display = 'inline-flex';
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdminSidebarToggle);
    } else {
        initAdminSidebarToggle();
    }
    document.addEventListener('inertia:success', initAdminSidebarToggle);

    function toggleSidebarAdminSetting(settingKey) {
        const authToken = localStorage.getItem('auth_token');
        if (!authToken) {
            alert('{{ _l("Administrator session token not found. Please log in to Admin Panel.") }}');
            return;
        }

        const gridEl = document.querySelector('.grid-sidebar');
        const asideEl = document.querySelector('.grid-sidebar > aside');
        const btnEl = document.getElementById('admin-sidebar-toggle-btn');
        const labelEl = document.getElementById('admin-sidebar-toggle-label');

        if (!gridEl) return;

        // Check current state (whether aside is currently visible or hidden)
        const isCurrentlyVisible = asideEl 
            ? (asideEl.style.display !== 'none')
            : (!gridEl.classList.contains('no-sidebar'));

        const newStatus = isCurrentlyVisible ? 'hide' : 'show';
        const isNowShowing = (newStatus === 'show');

        // 1. Immediate UI Feedback
        if (asideEl) {
            asideEl.style.display = isNowShowing ? 'block' : 'none';
        }
        if (isNowShowing) {
            gridEl.classList.remove('no-sidebar');
            gridEl.style.gridTemplateColumns = '';
        } else {
            gridEl.classList.add('no-sidebar');
            gridEl.style.gridTemplateColumns = '1fr';
        }

        if (btnEl) {
            btnEl.classList.toggle('is-active', isNowShowing);
        }
        if (labelEl) {
            labelEl.textContent = isNowShowing ? '{{ _l("Sidebar: ON") }}' : '{{ _l("Sidebar: OFF") }}';
        }

        showAdminSidebarToast(isNowShowing ? '{{ _l("Sidebar enabled") }}' : '{{ _l("Sidebar disabled") }}');

        // 2. Persist to server via Setting API
        fetch('/api/v1/settings/group/theme_options', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + authToken,
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                key: settingKey,
                value: newStatus
            })
        }).then(res => res.json()).then(data => {
            if (data.success) {
                console.log('Sidebar setting updated:', settingKey, newStatus);
            }
        }).catch(err => {
            console.error('Failed to update sidebar setting via API:', err);
        });
    }

    function showAdminSidebarToast(msg) {
        let toast = document.getElementById('admin-sidebar-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'admin-sidebar-toast';
            toast.className = 'admin-toast-notice';
            document.body.appendChild(toast);
        }
        toast.textContent = msg;
        toast.classList.add('is-show');
        setTimeout(() => {
            toast.classList.remove('is-show');
        }, 2500);
    }
</script>
