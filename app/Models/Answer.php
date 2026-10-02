<?php

namespace App\Models;

use App\Services\AnswerGenerator;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

/**
 * A public answer page for one question. The same question (per language) is
 * answered once and shared, so every question asked makes the site more useful.
 *
 * @property int $id
 * @property string $public_id
 * @property string $question
 * @property string $question_hash
 * @property string $locale
 * @property string $status
 * @property array<string, mixed>|null $content
 * @property string|null $model
 * @property int|null $duration_ms
 * @property int $asked_count
 * @property string|null $error
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['question', 'question_hash', 'locale'])]
class Answer extends Model
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
            'asked_count' => 'integer',
        ];
    }

    /**
     * Find the page for a question, or create it. Matching ignores case,
     * spacing and trailing punctuation.
     */
    public static function forQuestion(string $question, string $locale): self
    {
        $question = Str::squish($question);
        $hash = sha1(preg_replace('/[\s?؟.!]+$/u', '', Str::lower($question)));

        $answer = self::createOrFirst(
            ['locale' => $locale, 'question_hash' => $hash],
            ['question' => $question],
        );

        if (! $answer->wasRecentlyCreated) {
            // Through the query builder, so updated_at (used to detect stuck runs) is not touched.
            self::whereKey($answer->getKey())->toBase()->increment('asked_count');
        }

        return $answer;
    }

    /**
     * Ready answers whose question is safe to list publicly (sitemap, data page), most asked first.
     * Filtered in PHP: JSON boolean comparisons differ between SQLite and Postgres.
     *
     * @return Collection<int, self>
     */
    public static function published(): Collection
    {
        return self::where('status', Brief::READY)
            ->orderByDesc('asked_count')
            ->latest('updated_at')
            ->limit(5000)
            ->get()
            ->filter(fn (self $answer) => ($answer->content['publishable'] ?? false) === true)
            ->values();
    }

    /**
     * The cleaned question shown publicly once answered.
     */
    public function publicQuestion(): string
    {
        return $this->content['public_question'] ?? $this->question;
    }

    /**
     * Generate the answer (synchronously) and store the result.
     */
    public function generate(AnswerGenerator $generator): void
    {
        $started = microtime(true);

        try {
            $result = $generator->generate($this->question, $this->locale);

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
