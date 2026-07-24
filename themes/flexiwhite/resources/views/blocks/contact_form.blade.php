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

<div class="landing-contact-form-wrapper w-full">
    @if(!empty($formHtml))
        {!! $formHtml !!}
    @else
        <div class="text-center text-slate-400 dark:text-slate-500 p-8">
            <p>Please select a contact form in block settings.</p>
        </div>
    @endif
</div>
