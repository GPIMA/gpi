<?php

use Illuminate\Support\Facades\Schedule;

// Supervision en continu : un relevé + évaluation des règles toutes les
// 5 minutes (nécessite `php artisan schedule:work` en local).
Schedule::command('parc:superviser')->everyFiveMinutes();
