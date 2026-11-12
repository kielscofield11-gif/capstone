<?php

return [
    // Shared-host deployment helpers (see DEPLOY_INFINITYFREE.md). Hosts like
    // InfinityFree have no SSH, no Composer, and no cron, so schema changes are
    // applied through the temporary setup script and the scheduler is triggered
    // by an external cron service. Both entry points are disabled unless their
    // token is set, and tokens are read through config so `config:cache` does
    // not turn them into null values (env() returns null once the configuration
    // is cached).

    // Token guarded setup script: copy deploy/infinityfree-setup.php into
    // public/, then open /setup.php?token=<SETUP_TOKEN>. Delete the script
    // immediately after use.
    'setup_token' => env('SETUP_TOKEN'),

    // Token guarded scheduler trigger: GET /scheduler/run?token=<SCHEDULER_TOKEN>
    // runs `php artisan schedule:run`. Point an external cron service (for
    // example cron-job.org) at it every minute. Leave SCHEDULER_TOKEN empty to
    // keep the endpoint permanently disabled.
    'scheduler_token' => env('SCHEDULER_TOKEN'),
];
