{{--
    Sub-theme asset injection (backward-compatible combined partial).
    
    For optimal loading, use the split partials instead:
    - @includeIf('system.partials.theme-styles')  → in <head> for CSS
    - @includeIf('system.partials.theme-scripts') → before </body> for JS
    
    This combined partial outputs both CSS + JS at point of inclusion.
    When placed in <body> (legacy), CSS will load late but still work.
--}}
@includeIf('system.partials.theme-styles')
@includeIf('system.partials.theme-scripts')
