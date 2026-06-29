<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'key_type', 'identifier', 'token', 'memo', 'last_used_at'])]
#[Hidden(['token'])]
class ApiKey extends Model
{
    public const TYPE_APPLICATION = 'application';
    public const TYPE_CLIENT = 'client';

    /**
     * Public prefixes that make a key's type recognisable at a glance.
     */
    private const PREFIXES = [
        self::TYPE_APPLICATION => 'yuno_app',
        self::TYPE_CLIENT => 'yuno_cli',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
        ];
    }

    /**
     * The user this key belongs to.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new key of the given type for a user. Returns the persisted
     * model and the one-time plaintext secret (never stored, only its hash).
     *
     * @return array{0: self, 1: string}
     */
    public static function generate(User $user, string $type, ?string $memo = null): array
    {
        $prefix = self::PREFIXES[$type] ?? 'yuno';
        $identifier = Str::lower(Str::random(16));
        $secret = Str::random(32);
        $plaintext = "{$prefix}_{$identifier}.{$secret}";

        $key = static::create([
            'user_id' => $user->id,
            'key_type' => $type,
            'identifier' => $identifier,
            'token' => hash('sha256', $secret),
            'memo' => $memo,
        ]);

        return [$key, $plaintext];
    }
}
