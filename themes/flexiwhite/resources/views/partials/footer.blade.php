<footer class="footer" style="border-top: 1px solid var(--geist-accents-2); padding: 4rem 0 3rem 0; font-size: 0.875rem; background: var(--geist-background);">
    <style>
        .footer-widgets {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }
        @media (min-width: 768px) {
            .footer-widgets {
                grid-template-columns: 1fr 1fr 1fr 1fr 1.5fr;
            }
            .footer-widget-col-5 {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
            }
        }
        .footer-widgets .widget {
            padding: 0 !important;
            border: none !important;
            background: transparent !important;
            margin-bottom: 0 !important;
        }
        .footer-widgets .widget-title {
            font-size: 0.8125rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.08em !important;
            color: var(--geist-foreground) !important;
            margin-bottom: 1.25rem !important;
        }
        .footer-widgets .widget-content {
            color: var(--geist-accents-5);
            line-height: 1.6;
        }
        .footer-widgets .widget-content a {
            transition: color 0.2s;
            text-decoration: none;
            color: var(--geist-accents-5);
        }
        .footer-widgets .widget-content a:hover {
            color: var(--geist-foreground) !important;
            text-decoration: underline;
        }
        .admin-quick-edit-btn {
            position: absolute;
            top: -10px;
            right: -10px;
            z-index: 100;
            display: none;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: #4f46e5;
            color: #ffffff !important;
            border-radius: 50%;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.4), 0 2px 4px -1px rgba(79, 70, 229, 0.2);
            opacity: 0;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
        }
        body.polycms-topbar-active .admin-quick-edit-btn {
            display: flex;
        }
        .footer-widgets-container:hover .admin-quick-edit-btn,
        .footer-bottom-left:hover .admin-quick-edit-btn,
        .footer-social-wrapper:hover .admin-quick-edit-btn,
        .footer-bottom-widgets-container:hover .admin-quick-edit-btn {
            opacity: 1;
            transform: scale(1.1);
        }
        .admin-quick-edit-btn:hover {
            background: #4338ca;
            transform: scale(1.2) !important;
        }
        .footer-bottom-widgets {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 2rem;
            border-top: 1px solid var(--geist-accents-2);
            padding-top: 2rem;
            margin-top: 3rem;
        }
        .footer-bottom-widgets .widget {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .footer-bottom-widgets .widget-footer-menu ul {
            display: flex;
            gap: 1.25rem;
            flex-wrap: wrap;
            margin-bottom: 0.5rem;
        }
        .footer-bottom-widgets .widget-footer-menu ul li {
            line-height: 1 !important;
        }
        .footer-bottom-widgets .widget-footer-menu ul li a {
            font-weight: 600;
            color: var(--geist-foreground) !important;
        }
        .footer-bottom-widgets .widget-footer-menu ul li a:hover {
            color: var(--geist-link) !important;
        }
        .footer-bottom-widgets .widget-html-block {
            flex: 1 1 650px;
        }
        .footer-bottom-widgets .widget-social-links {
            margin-left: auto;
        }
    </style>

    <div class="container">
        {{-- Row 1: Footer Widgets Grid --}}
        @php
            $hasFooterWidgets = false;
            for ($i = 1; $i <= 4; $i++) {
                if (trim(app('widget')->renderArea("footer_col_{$i}")) !== '') {
                    $hasFooterWidgets = true;
                }
            }
            $col5Html = trim(app('widget')->renderArea('footer_col_5'));
            $col5Type = theme_get_option('flexiwhite_footer_col5_type', 'stats');
            $showCol5 = ($col5Html !== '' || $col5Type !== 'none');
        @endphp

        @if($hasFooterWidgets || $showCol5)
            <div class="footer-widgets-container" style="position: relative;">
                        <a href="/admin/widgets" target="_blank" class="admin-quick-edit-btn" title="{{ _l('Edit Widgets') }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </a>
                <div class="footer-widgets" @if(!$showCol5) style="grid-template-columns: repeat(4, 1fr) !important;" @endif>
                    <div class="footer-widget-col footer-widget-col-1">
                        @include('partials.widget-area', ['key' => 'footer_col_1'])
                    </div>
                    <div class="footer-widget-col footer-widget-col-2">
                        @include('partials.widget-area', ['key' => 'footer_col_2'])
                    </div>
                    <div class="footer-widget-col footer-widget-col-3">
                        @include('partials.widget-area', ['key' => 'footer_col_3'])
                    </div>
                    <div class="footer-widget-col footer-widget-col-4">
                        @include('partials.widget-area', ['key' => 'footer_col_4'])
                    </div>
                    @if($showCol5)
                        <div class="footer-widget-col footer-widget-col-5">
                            @if($col5Html !== '')
                                {!! $col5Html !!}
                            @elseif($col5Type === 'stats')
                                <div class="footer-brand-stats" style="display: flex; flex-direction: column; gap: 1.5rem; width: 100%;">
                                    <div class="footer-logo" style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.35rem; font-weight: 800; letter-spacing: -0.03em; color: var(--geist-foreground);">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #10b981; transform: rotate(-10deg);"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 3.5 2 5.5a7 7 0 0 1-10 12.5z"/></svg>
                                        {!! theme_get_option('flexiwhite_footer_logo_text', 'poly<span>cms</span>') !!}
                                    </div>
                                    <div class="footer-stats-boxes" style="display: flex; gap: 2rem;">
                                        <div class="stat-box">
                                            <div style="font-size: 1.25rem; font-weight: 800; color: var(--geist-foreground); line-height: 1.2;">{{ theme_get_option('flexiwhite_footer_stat1_value', '1,250,000+') }}</div>
                                            <div style="font-size: 0.75rem; color: var(--geist-accents-4); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem;">{{ theme_get_option('flexiwhite_footer_stat1_label', 'Downloads') }}</div>
                                        </div>
                                        <div class="stat-box">
                                            <div style="font-size: 1.25rem; font-weight: 800; color: var(--geist-foreground); line-height: 1.2;">{{ theme_get_option('flexiwhite_footer_stat2_value', '78,000+') }}</div>
                                            <div style="font-size: 0.75rem; color: var(--geist-accents-4); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem;">{{ theme_get_option('flexiwhite_footer_stat2_label', 'Active Sites') }}</div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($col5Type === 'newsletter')
                                @php
                                    $newsletterForm = \App\Models\ContactForm::where('slug', 'newsletter-signup')->first();
                                    $newsletterWidgetInstance = null;
                                    if ($newsletterForm) {
                                        $newsletterWidgetInstance = new \App\Models\WidgetInstance([
                                            'widget_type' => 'contact_form',
                                            'title' => 'Subscribe',
                                            'config' => ['form_id' => $newsletterForm->id]
                                        ]);
                                    }
                                @endphp
                                @if($newsletterWidgetInstance)
                                    {!! app('widget')->render($newsletterWidgetInstance) !!}
                                @else
                                    <div class="footer-newsletter" style="display: flex; flex-direction: column; gap: 1rem; width: 100%;">
                                        <h3 class="widget-title" style="margin: 0 !important; font-size: 0.8125rem !important; font-weight: 700 !important; text-transform: uppercase !important; letter-spacing: 0.08em !important; color: var(--geist-foreground);">Subscribe</h3>
                                        <p style="margin: 0; font-size: 0.875rem; color: var(--geist-accents-5); line-height: 1.5; text-align: left;">Get the latest updates and resources directly in your inbox.</p>
                                        <form action="#" method="POST" style="display: flex; gap: 0.5rem; width: 100%;" onsubmit="event.preventDefault(); alert('Subscribed!');">
                                            <input type="email" placeholder="Your email address" required style="flex: 1; min-width: 0; padding: 0.625rem 0.875rem; border: 1px solid var(--geist-accents-2); border-radius: var(--radius); font-size: 0.875rem; background: var(--geist-background); color: var(--geist-foreground); outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--geist-foreground)'" onblur="this.style.borderColor='var(--geist-accents-2)'" />
                                            <button type="submit" style="padding: 0.625rem 1rem; border: 1px solid var(--geist-foreground); border-radius: var(--radius); background: var(--geist-foreground); color: var(--geist-background); font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">Subscribe</button>
                                        </form>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if(theme_widget_area_has_content('footer_bottom'))
            <div class="footer-bottom-widgets-container" style="position: relative; width: 100%;">
                <a href="/admin/widgets" target="_blank" class="admin-quick-edit-btn" title="{{ _l('Edit Footer Bottom') }}" style="top: 10px; right: 10px;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                </a>
                <div class="footer-bottom-widgets">
                    {!! app('widget')->renderArea('footer_bottom') !!}
                </div>
            </div>
        @else
            {{-- Separator Line --}}
            <div style="border-top: 1px solid var(--geist-accents-2); margin: 3rem 0 2rem 0;"></div>

            {{-- Row 2: Bottom Copyright, Disclaimer & Social Icons --}}
            <div class="footer-bottom" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 2rem;">
                <div class="footer-bottom-left" style="position: relative; display: flex; flex-direction: column; gap: 0.5rem; max-width: 650px;">
                            <a href="/admin/menus" target="_blank" class="admin-quick-edit-btn" title="{{ _l('Edit Menu') }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </a>
                    @php
                        $envatoUrl = get_option('social_envato') ?: 'https://codecanyon.net/user/polyxgo/portfolio';
                        $supportUrl = 'https://headrandom.com/y34yNm5v';
                        $facebookUrl = get_option('social_facebook') ?: 'https://www.facebook.com/polyxgoltd';
                        $youtubeUrl = get_option('social_youtube') ?: 'https://headrandom.com/GlU63s5D';
                    @endphp
                    <div class="footer-bottom-links" style="display: flex; gap: 1.25rem; flex-wrap: wrap; margin-bottom: 0.5rem;">
                        @php
                            $bottomMenu = \App\Models\Menu::where('location', 'footer_bottom')->first();
                            $bottomMenuItems = $bottomMenu ? $bottomMenu->items()->where('parent_id', null)->where('active', true)->orderBy('order')->get() : [];
                        @endphp
                        @if(count($bottomMenuItems) > 0)
                            @foreach($bottomMenuItems as $menuItem)
                                <a href="{{ $menuItem->url }}" target="{{ $menuItem->target }}" style="color: var(--geist-foreground); font-weight: 600; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--geist-link)'" onmouseout="this.style.color='var(--geist-foreground)'">
                                    {{ $menuItem->title }}
                                </a>
                            @endforeach
                        @else
                            <a href="{{ $envatoUrl }}" target="_blank" style="color: var(--geist-foreground); font-weight: 600; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--geist-link)'" onmouseout="this.style.color='var(--geist-foreground)'">Envato Portfolio</a>
                            <a href="{{ $supportUrl }}" target="_blank" style="color: var(--geist-foreground); font-weight: 600; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--geist-link)'" onmouseout="this.style.color='var(--geist-foreground)'">Support Center</a>
                            <a href="{{ $facebookUrl }}" target="_blank" style="color: var(--geist-foreground); font-weight: 600; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--geist-link)'" onmouseout="this.style.color='var(--geist-foreground)'">Facebook Page</a>
                            <a href="{{ $youtubeUrl }}" target="_blank" style="color: var(--geist-foreground); font-weight: 600; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--geist-link)'" onmouseout="this.style.color='var(--geist-foreground)'">YouTube Channel</a>
                        @endif
                    </div>
                    <div class="footer-disclaimer" style="color: var(--geist-accents-4); font-size: 0.75rem;">
                        {!! _l('Powered by <a href="https://polycms.org" rel="nofollow" target="_blank">PolyCMS</a> - Modern Open-Source CMS built with Laravel.') !!}
                    </div>
                    <div class="footer-copyright" style="color: var(--geist-accents-4); font-size: 0.75rem;">
                        &copy; {{ date('Y') }} {{ _l('PolyXGO. All rights reserved.') }}
                    </div>
                </div>

                {{-- Bottom Social Icons --}}
                <div class="footer-social-wrapper" style="position: relative;">
                            <a href="/admin/settings" target="_blank" class="admin-quick-edit-btn" title="{{ _l('Edit Settings') }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </a>
                    @php
                        $socialWidgetInstance = new \App\Models\WidgetInstance([
                            'widget_type' => 'social_links',
                            'title' => '',
                            'config' => ['layout' => 'horizontal_icons']
                        ]);
                    @endphp
                    {!! app('widget')->render($socialWidgetInstance) !!}
                </div>
            </div>
        @endif
        </div>
    </div>
</footer>
