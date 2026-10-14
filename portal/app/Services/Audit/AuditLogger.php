<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Records important admin actions (CLAUDE.md §17). Secrets are never stored:
 * keys that look like passwords/tokens are masked before saving.
 */
class AuditLogger
{
    private const MASKED = ['password', 'remember_token', 'token', 'secret', 'otp'];

    public function record(string $action, ?Model $subject = null, ?string $description = null, array $old = [], array $new = []): AuditLog
    {
        $request = request();

        return AuditLog::create([
            'admin_user_id' => auth('admin')->id(),
            'action' => $action,
            'auditable_type' => $subject?->getMorphClass(),
            'auditable_id' => $subject?->getKey(),
            'description' => $description ? Str::limit($description, 250) : null,
            'old_values' => $old ? $this->mask($old) : null,
            'new_values' => $new ? $this->mask($new) : null,
            'ip' => $request?->ip(),
            'user_agent' => $request ? Str::limit((string) $request->userAgent(), 250, '') : null,
        ]);
    }

    private function mask(array $values): array
    {
        foreach ($values as $key => $value) {
            if (Str::contains(strtolower((string) $key), self::MASKED)) {
                $values[$key] = '••••';
            }
        }

        return $values;
    }
}
