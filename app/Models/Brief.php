<?php

namespace App\Models;

use App\Services\BriefGenerator;
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
 * @property string $country
 * @property string $profession
 * @property int $experience_years
 * @property string $family
 * @property string $locale
 * @property string $status
 * @property array<string, mixed>|null $content
 * @property string|null $model
 * @property int|null $duration_ms
 * @property string|null $error
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'country', 'profession', 'experience_years', 'family', 'locale'])]
class Brief extends Model
{
    use HasUlids;

    public const string PENDING = 'pending';

    public const string GENERATING = 'generating';

    public const string READY = 'ready';

    public const string FAILED = 'failed';

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
            'experience_years' => 'integer',
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
     * Generate the brief content (synchronously) and store the result.
     */
    public function generate(BriefGenerator $generator): void
    {
        $started = microtime(true);

        try {
            $result = $generator->generate($this->only(['country', 'profession', 'experience_years', 'family', 'locale']));

            $this->forceFill([
                'status' => self::READY,
                'content' => $result['content'],
                'model' => $result['model'],
                'error' => null,
            ]);
        } catch (Throwable $e) {
            report($e);

            $this->forceFill(['status' => self::FAILED, 'error' => $e->getMessage()]);
        }

        $this->forceFill(['duration_ms' => (int) ((microtime(true) - $started) * 1000)])->save();
    }
}
