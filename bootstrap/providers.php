<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\ThemeServiceProvider::class,
    Nuwave\Lighthouse\WhereConditions\WhereConditionsServiceProvider::class,
    App\Providers\MailServiceProvider::class,
    PragmaRX\Google2FALaravel\ServiceProvider::class,
];
