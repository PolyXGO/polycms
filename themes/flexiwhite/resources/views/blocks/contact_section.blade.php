@php
    $formId = $attrs['form_id'] ?? '';
    $formHtml = '';
    if (!empty($formId)) {
        $widgetInstance = new \App\Models\WidgetInstance();
        $widgetInstance->id = 'landing-contact-form-' . uniqid();
        $widgetInstance->title = '';
        $widgetInstance->config = [
            'form_id' => (string) $formId
        ];
        $widget = new \App\Widgets\ContactFormWidget();
        $formHtml = $widget->render($widgetInstance);
    }
@endphp

<style>
    @media (max-width: 968px) {
        .contact-grid {
            grid-template-columns: 1fr !important;
            gap: 3rem !important;
            padding: 2rem 0 !important;
        }
    }
</style>

<section class="section-lg" style="background-color: #fff; min-height: 80vh; display: flex; align-items: center;">
    <div class="container">
        <div class="contact-grid" style="display: grid; grid-template-columns: 1fr 1.1fr; gap: 6rem; align-items: center; max-width: 1280px; margin: 0 auto; padding: 4rem 0;">
            <!-- Left Column: Details -->
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <h1 style="font-size: 2.8rem; font-weight: 800; line-height: 1.15; color: #111; letter-spacing: -0.03em; margin: 0;">
                    {{ $attrs['heading'] ?? "Let's talk about what your system needs next" }}
                </h1>
                
                <div style="display: flex; flex-direction: column; gap: 1.5rem; color: #555; font-size: 1.05rem; line-height: 1.6;">
                    @if(!empty($attrs['desc_1']))
                        <p style="margin: 0;">{{ $attrs['desc_1'] }}</p>
                    @endif
                    @if(!empty($attrs['desc_2']))
                        <p style="margin: 0;">{{ $attrs['desc_2'] }}</p>
                    @endif
                    @if(!empty($attrs['desc_3']))
                        <p style="margin: 0;">{{ $attrs['desc_3'] }}</p>
                    @endif
                    @if(!empty($attrs['desc_4']))
                        <p style="font-weight: 600; color: #111; margin: 0;">{{ $attrs['desc_4'] }}</p>
                    @endif
                </div>
                
                <div style="margin-top: 1rem;">
                    <!-- Envato Market Styled badge -->
                    <div style="display: inline-flex; align-items: center; gap: 0.4rem; background: #000; color: #fff; padding: 0.5rem 1rem; border-radius: 4px; font-weight: bold; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; user-select: none;">
                        <span style="color: #81b441; font-size: 1.2rem; line-height: 1;">🍃</span>
                        <span style="letter-spacing: -0.02em; font-size: 0.95rem; font-weight: 700; line-height: 1;">envato<span style="font-weight: 300; color: #ccc; margin-left: 2px;">market</span></span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Contact Form Box -->
            <div style="background: #fcfcfc; border: 1px solid #eaeaea; border-radius: 12px; padding: 3rem; box-shadow: 0 10px 30px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: center; min-height: 500px;">
                @if(!empty($formHtml))
                    {!! $formHtml !!}
                @else
                    <div style="text-align: center; color: #888; padding: 2rem;">
                        <p>Please select a contact form in block settings.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
