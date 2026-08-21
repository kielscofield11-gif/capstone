<?php

namespace App\Traits;

use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;

trait LogsAudit
{
    protected function auditSnapshot(Model $model): array
    {
        return app(AuditLogger::class)->snapshot($model);
    }

    protected function auditCreated(Model $model, string $description): void
    {
        app(AuditLogger::class)->created($model, $description);
    }

    protected function auditUpdated(Model $model, array $before, string $description, string $action = 'updated'): void
    {
        app(AuditLogger::class)->updated($model, $before, $description, $action);
    }

    protected function auditDeleted(Model $model, array $before, string $description): void
    {
        app(AuditLogger::class)->deleted($model, $before, $description);
    }

    protected function auditEvent(string $action, Model $model, string $description, ?array $oldValues = null, ?array $newValues = null): void
    {
        app(AuditLogger::class)->event($action, $model, $description, $oldValues, $newValues);
    }
}
