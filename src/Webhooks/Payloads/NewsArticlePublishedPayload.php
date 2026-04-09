<?php

namespace LanSoftware\LanCoreClient\Webhooks\Payloads;

use Illuminate\Http\Request;

readonly class NewsArticlePublishedPayload extends WebhookPayload
{
    public function __construct(
        public int $articleId,
        public string $title,
        public ?string $slug,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $id = $request->integer('article.id');

        abort_unless($id > 0, 422, 'Invalid payload.');

        return new static(
            articleId: $id,
            title: (string) $request->input('article.title', ''),
            slug: $request->input('article.slug'),
        );
    }
}
