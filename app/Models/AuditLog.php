<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_log';

    protected $primaryKey = 'log_id';

    const UPDATED_AT = null;

    protected $fillable = [
        'action_type',
        'table_name',
        'record_id',
        'description',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    public static function write(string $action, string $table, ?int $recordId, ?string $description = null): self
    {
        return static::create([
            'action_type' => $action,
            'table_name'  => $table,
            'record_id'   => $recordId,
            'description' => $description,
        ]);
    }
}
