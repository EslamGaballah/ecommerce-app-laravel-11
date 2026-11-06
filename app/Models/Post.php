<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    private const SIMILAR_TAG_WEIGHT = 2;

    /** Words any article title may use, which say nothing about its topic. */
    private const COMMON_TITLE_WORDS = [
        'كيف', 'هل', 'ماذا', 'لماذا', 'متى', 'أين', 'الى', 'إلى', 'على', 'عن', 'عند', 'مع', 'من', 'في', 'عبر',
        'هذا', 'هذه', 'ذلك', 'تلك', 'التي', 'الذي', 'بعد', 'قبل', 'كل', 'أكثر', 'أفضل', 'بين', 'دون', 'فعلا',
        'تختار', 'اختيار', 'تحتاج', 'مناسب', 'مناسبة', 'دليل', 'دليلك', 'نصائح', 'طرق',
        'the', 'and', 'for', 'with', 'from', 'your', 'you', 'how', 'what', 'why', 'when', 'are', 'that', 'this',
        'best', 'guide', 'tips', 'choose',
    ];

    protected $fillable = [
        'title',
        'content',
        'status',
        'slug',
        'user_id',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /** Only posts somebody has actually read. */
    public function scopeMostRead(Builder $query): Builder
    {
        return $query->where('views_count', '>', 0)->orderByDesc('views_count')->orderByDesc('id');
    }

    public function scopeNewestFirst(Builder $query): Builder
    {
        return $query->latest()->orderByDesc('id');
    }

    /**
     * Posts sharing a tag or a title word with the given one, closest first.
     * A shared tag counts for more than a shared word, since an editor chose it.
     */
    public function scopeSimilarTo(Builder $query, self $post): Builder
    {
        $tagIds = $post->tags()->pluck('tags.id');
        $words = $post->titleWords();

        $likes = array_map(fn ($word) => "%{$word}%", $words);
        $matches = array_fill(0, count($words), 'posts.title LIKE ?');

        return $query->select('posts.*')
            ->whereKeyNot($post->id)
            ->where(function (Builder $similar) use ($tagIds, $matches, $likes) {
                $similar->whereHas('tags', fn (Builder $tags) => $tags->whereIn('tags.id', $tagIds));

                if ($matches) {
                    $similar->orWhereRaw('('.implode(' OR ', $matches).')', $likes);
                }
            })
            ->withCount(['tags as shared_tags_count' => fn (Builder $tags) => $tags->whereIn('tags.id', $tagIds)])
            ->selectRaw(
                ($matches ? implode(' + ', array_map(fn ($match) => "({$match})", $matches)) : '0').' as shared_title_words',
                $likes,
            )
            ->orderByRaw('shared_tags_count * '.self::SIMILAR_TAG_WEIGHT.' + shared_title_words DESC')
            ->newestFirst();
    }

    /**
     * The words of the title worth comparing: no common words, no short ones,
     * and no Arabic "ال" or final "ة", so that "الهاتف" and "هاتف", or "ذكية" and
     * "الذكي", count as the same word whichever post is being looked at.
     *
     * @return array<int, string>
     */
    public function titleWords(): array
    {
        preg_match_all('/[\p{L}\p{N}]+/u', mb_strtolower((string) $this->title), $found);

        return collect($found[0])
            ->map(fn ($word) => preg_replace('/^(ال|وال|بال|فال|كال|لل)(?=\p{L}{3})/u', '', $word))
            ->map(fn ($word) => preg_replace('/(?<=\p{L}{3})ة$/u', '', $word))
            ->filter(fn ($word) => mb_strlen($word) >= 3 && ! in_array($word, self::COMMON_TITLE_WORDS, true))
            ->unique()
            ->values()
            ->all();
    }

    // using accessors
    public function getExcerptAttribute()
    {
        // show 150 char in blpg.index
        return Str::limit(strip_tags($this->content), 150);
    }

    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function images() 
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function tags()
    {
        // return $this->belongsToMany(Tag::class);

        return $this->morphToMany(Tag::class, 'taggable');

    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }
}
