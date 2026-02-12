<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditObserver
{
    /**
     * Handle the "created" event.
     */
    public function created(Model $model): void
    {
        $this->logAction($model, 'created', null, $model->getAttributes());
    }

    /**
     * Handle the "updated" event.
     */
    public function updated(Model $model): void
    {
        $original = $model->getOriginal();
        $changes = $model->getChanges();

        // Remove timestamp fields from the diff
        unset($changes['updated_at'], $changes['created_at']);

        if (empty($changes)) {
            return;
        }

        // Build old values for only the changed fields
        $oldValues = [];
        foreach (array_keys($changes) as $key) {
            $oldValues[$key] = $original[$key] ?? null;
        }

        $this->logAction($model, 'updated', $oldValues, $changes);
    }

    /**
     * Handle the "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->logAction($model, 'deleted', $model->getOriginal(), null);
    }

    /**
     * Create an audit log entry.
     */
    protected function logAction(Model $model, string $action, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
        ]);
    }
}
