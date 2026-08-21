<?php

namespace App\Services;

use App\Models\AuditLog;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditLogger
{
    private const EXCLUDED_FIELDS = [
        'id', 'created_at', 'updated_at', 'deleted_at',
        'password', 'password_confirmation', 'remember_token',
        '_token', '_method', 'session_id', 'csrf_token',
        'api_token', 'access_token', 'refresh_token', 'private_key',
        'issued_document_snapshot',
    ];

    private const SENSITIVE_FRAGMENTS = [
        'password', 'passwd', 'secret', 'credential', 'private_key',
        'access_token', 'refresh_token', 'api_token', 'csrf', 'session',
        'remember_token',
    ];

    public function snapshot(Model $model): array
    {
        $values = [];

        foreach (array_keys($model->getAttributes()) as $field) {
            if ($this->isExcluded($field)) {
                continue;
            }

            $values[$field] = $this->normalize($model->getAttribute($field));
        }

        return $values;
    }

    public function created(Model $model, string $description): AuditLog
    {
        return $this->write('created', $model, $description, null, $this->snapshot($model));
    }

    public function updated(Model $model, array $before, string $description, string $action = 'updated'): ?AuditLog
    {
        $changedFields = array_keys($model->getChanges());
        $passwordChanged = in_array('password', $changedFields, true);
        $changedFields = array_values(array_filter($changedFields, fn (string $field) => !$this->isExcluded($field)));

        $afterSnapshot = $this->snapshot($model);
        $oldValues = [];
        $newValues = [];

        foreach ($changedFields as $field) {
            $old = $this->normalize(Arr::get($before, $field));
            $new = Arr::get($afterSnapshot, $field);

            if ($old !== $new) {
                $oldValues[$field] = $old;
                $newValues[$field] = $new;
            }
        }

        if ($passwordChanged) {
            $newValues['password_changed'] = true;
        }

        if ($oldValues === [] && $newValues === []) {
            return null;
        }

        return $this->write($action, $model, $description, $oldValues ?: null, $newValues ?: null);
    }

    public function deleted(Model $model, array $before, string $description): AuditLog
    {
        return $this->write('deleted', $model, $description, $this->sanitize($before), null);
    }

    public function event(string $action, Model $model, string $description, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        return $this->write($action, $model, $description, $oldValues ? $this->sanitize($oldValues) : null, $newValues ? $this->sanitize($newValues) : null);
    }

    public function sanitize(array $values): array
    {
        $clean = [];

        foreach ($values as $field => $value) {
            if ($this->isExcluded((string) $field)) {
                continue;
            }

            $clean[$field] = $this->normalize($value);
        }

        return $clean;
    }

    private function write(
        string $action,
        Model $model,
        string $description,
        ?array $oldValues,
        ?array $newValues,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $model::class,
            'model_id' => $model->getKey(),
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => Str::limit((string) request()?->userAgent(), 255, ''),
        ]);
    }

    private function isExcluded(string $field): bool
    {
        $normalized = Str::lower($field);

        if (in_array($normalized, self::EXCLUDED_FIELDS, true)) {
            return true;
        }

        return Str::contains($normalized, self::SENSITIVE_FRAGMENTS);
    }

    private function normalize(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \UnitEnum) {
            return $value->name;
        }

        if (is_array($value)) {
            return array_map(fn (mixed $item) => $this->normalize($item), $value);
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return $this->normalize($value->toArray());
        }

        return $value;
    }
}
