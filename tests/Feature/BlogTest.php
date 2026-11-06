<?php

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

function blogPost(string $slug, array $tags = [], array $attributes = []): Post
{
    $post = Post::forceCreate($attributes + [
        'title' => $slug,
        'content' => 'Body of '.$slug,
        'slug' => $slug,
        'status' => 'published',
        'user_id' => User::factory()->create()->id,
    ]);

    $post->tags()->attach(collect($tags)->map(fn ($name) => Tag::firstOrCreate(
        ['slug' => $name],
        ['name_ar' => $name, 'name_en' => $name],
    )->id));

    return $post;
}

function slugsIn($response, string $section): array
{
    return $response->viewData('sections')[$section]->pluck('slug')->all();
}

test('similar posts share a tag, most shared first, and never include drafts or the post itself', function () {
    $post = blogPost('main', ['phones', 'guides']);
    blogPost('one-tag', ['phones']);
    blogPost('two-tags', ['phones', 'guides']);
    blogPost('other-topic', ['shoes']);
    blogPost('no-tags');
    blogPost('draft', ['phones', 'guides'], ['status' => 'archived']);

    $response = $this->get(route('front.posts.show', $post))->assertOk()
        ->assertSee(__('app.similar_posts'));

    expect(slugsIn($response, 'similar'))->toBe(['two-tags', 'one-tag']);
});

test('similar posts can come from a shared title word alone', function () {
    $post = blogPost('a', [], ['title' => 'Picking a Smart Watch']);
    blogPost('b', [], ['title' => 'Caring for your watch']);
    blogPost('c', [], ['title' => 'Winter coats']);

    $response = $this->get(route('front.posts.show', $post))->assertOk();

    expect(slugsIn($response, 'similar'))->toBe(['b']);
});

test('arabic title words match with or without the definite article', function () {
    $post = blogPost('a', [], ['title' => 'مميزات الهاتف الجديد']);
    blogPost('b', [], ['title' => 'صيانة هاتف قديم']);
    blogPost('c', [], ['title' => 'أنواع الأحذية']);

    $response = $this->get(route('front.posts.show', $post))->assertOk();

    expect(slugsIn($response, 'similar'))->toBe(['b']);
});

test('a shared arabic title word links the two posts in both directions', function () {
    $phone = blogPost('phone', [], ['title' => 'مميزات الهاتف الذكي']);
    $watch = blogPost('watch', [], ['title' => 'عيوب ساعة ذكية']);

    expect(slugsIn($this->get(route('front.posts.show', $phone)), 'similar'))->toBe(['watch'])
        ->and(slugsIn($this->get(route('front.posts.show', $watch)), 'similar'))->toBe(['phone']);
});

test('common title words do not make posts similar', function () {
    $post = blogPost('a', [], ['title' => 'كيف تختار الهاتف المناسب']);
    blogPost('b', [], ['title' => 'كيف تختار الحذاء المناسب']);

    $this->get(route('front.posts.show', $post))->assertOk()
        ->assertDontSee('data-post-list="similar"', false);
});

test('similar posts rank tags above a title word, and both together first', function () {
    $post = blogPost('a', ['phones'], ['title' => 'Phone cameras']);
    blogPost('word-only', [], ['title' => 'Cameras explained', 'created_at' => now()->addDay()]);
    blogPost('tag-only', ['phones'], ['title' => 'Battery life']);
    blogPost('both', ['phones'], ['title' => 'Cameras compared', 'created_at' => now()->subDay()]);

    $response = $this->get(route('front.posts.show', $post))->assertOk();

    expect(slugsIn($response, 'similar'))->toBe(['both', 'tag-only', 'word-only']);
});

test('a title with no useful words still finds posts by tag', function () {
    $post = blogPost('a', ['phones'], ['title' => 'كيف']);
    blogPost('b', ['phones'], ['title' => 'Battery life']);

    $response = $this->get(route('front.posts.show', $post))->assertOk();

    expect(slugsIn($response, 'similar'))->toBe(['b']);
});

test('a post without tags has no similar list unless a title word matches', function () {
    $post = blogPost('lonely');
    blogPost('other', ['phones']);

    $this->get(route('front.posts.show', $post))->assertOk()
        ->assertDontSee('data-post-list="similar"', false);
});

test('a read counts once per visitor and leaves the post untouched', function () {
    $post = blogPost('read-me', [], ['updated_at' => now()->subWeek()]);
    $updatedAt = $post->fresh()->updated_at;

    $this->get(route('front.posts.show', $post))->assertOk();
    $this->get(route('front.posts.show', $post))->assertOk();

    expect($post->fresh()->views_count)->toBe(1)
        ->and($post->fresh()->updated_at->equalTo($updatedAt))->toBeTrue();

    $this->flushSession();
    $this->get(route('front.posts.show', $post))->assertOk();

    expect($post->fresh()->views_count)->toBe(2);
});

test('previewing a draft is not counted as a read', function () {
    $post = blogPost('draft', [], ['status' => 'archived']);
    $staff = User::factory()->create();
    Illuminate\Support\Facades\Gate::define('access-dashboard', fn () => true);

    $this->actingAs($staff)->get(route('front.posts.show', $post))->assertOk();

    expect($post->fresh()->views_count)->toBe(0);
});

test('most read lists read posts by count and leaves out unread ones and drafts', function () {
    blogPost('few', [], ['views_count' => 2]);
    blogPost('many', [], ['views_count' => 50]);
    blogPost('unread');
    blogPost('draft', [], ['views_count' => 99, 'status' => 'archived']);

    $response = $this->get(route('front.posts.index'))->assertOk()
        ->assertSee(__('app.most_read_posts'))
        ->assertSee(__('app.reads_count', ['count' => 50]));

    expect(slugsIn($response, 'mostRead'))->toBe(['many', 'few']);
});

test('the lists on an article leave out that article and do not repeat each other', function () {
    $post = blogPost('main', ['phones'], ['views_count' => 100]);
    blogPost('similar', ['phones'], ['views_count' => 90]);
    blogPost('popular', [], ['views_count' => 80]);
    blogPost('recent');

    $response = $this->get(route('front.posts.show', $post))->assertOk();

    expect(slugsIn($response, 'similar'))->toBe(['similar'])
        ->and(slugsIn($response, 'mostRead'))->toBe(['popular'])
        ->and(slugsIn($response, 'latest'))->toBe(['recent']);
});

test('the blog page shows similar posts only after an article has been read', function () {
    $post = blogPost('main', ['phones']);
    blogPost('related', ['phones']);

    $this->get(route('front.posts.index'))->assertOk()
        ->assertDontSee('data-post-list="similar"', false)
        ->assertSee('data-post-list="latest"', false);

    $this->get(route('front.posts.show', $post))->assertOk();

    $response = $this->get(route('front.posts.index'))->assertOk()
        ->assertSee(__('app.similar_to_last_read'));

    expect(slugsIn($response, 'similar'))->toBe(['related']);
});

test('the blog page copes with a last read post that was unpublished', function () {
    $post = blogPost('main', ['phones']);
    blogPost('related', ['phones']);

    $this->get(route('front.posts.show', $post))->assertOk();
    $post->update(['status' => 'archived']);

    $this->get(route('front.posts.index'))->assertOk()
        ->assertDontSee('data-post-list="similar"', false);
});
