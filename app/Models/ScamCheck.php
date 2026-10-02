<?php

namespace App\Models;

use App\Services\ScamChecker;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * @property int $id
 * @property string $public_id
 * @property int|null $user_id
 * @property string $message
 * @property string $locale
 * @property string $status
 * @property array<string, mixed>|null $content
 * @property string|null $model
 * @property int|null $duration_ms
 * @property string|null $error
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'message', 'locale'])]
class ScamCheck extends Model
{
    use HasUlids;

    /**
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check the message (synchronously) and store the result.
     */
    public function check(ScamChecker $checker): void
    {
        $started = microtime(true);

        try {
            $result = $checker->check($this->message, $this->locale);

            $this->forceFill([
                'status' => Brief::READY,
                'content' => $result['content'],
                'model' => $result['model'],
                'error' => null,
            ]);
        } catch (Throwable $e) {
            report($e);

            $this->forceFill(['status' => Brief::FAILED, 'error' => $e->getMessage()]);
        }

        $this->forceFill(['duration_ms' => (int) ((microtime(true) - $started) * 1000)])->save();
    }
}
