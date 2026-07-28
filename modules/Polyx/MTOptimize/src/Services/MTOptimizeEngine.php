<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Services;

use Illuminate\Http\Request;
use Modules\Polyx\MTOptimize\Support\MetaPayloadNormalizer;
use Modules\Polyx\MTOptimize\Support\MTOptimizeHooks;

class MTOptimizeEngine
{
    public function __construct(
        protected SEOContextBuilder $contextBuilder,
        protected RuleEngine $ruleEngine,
        protected MetaResolver $metaResolver,
        protected SocialResolver $socialResolver,
        protected SchemaResolver $schemaResolver,
        protected LinkResolver $linkResolver,
        protected MetaPayloadNormalizer $normalizer,
        protected SEODocumentStore $documentStore,
    ) {}

    /**
     * @return array{context: array<string, mixed>, payload: array<string, mixed>, from_cache: bool}
     */
    public function resolve(?Request $request = null): array
    {
        $request ??= request();

        $context = $this->contextBuilder->build($request);
        $context = MTOptimizeHooks::applyFilters('mtoptimize/context/build', $context, $request);
        $context = MTOptimizeHooks::applyFilters('mtoptimize/context/before_resolve', $context, $request);

        $cachedPayload = $this->documentStore->get($context);
        if (is_array($cachedPayload)) {
            $payload = MTOptimizeHooks::applyFilters('mtoptimize/meta/resolve', $cachedPayload, $context, ['cached' => true]);
            $payload = $this->normalizer->normalize($payload, $context);

            MTOptimizeHooks::doAction('mtoptimize/context/after_resolve', $context, $payload, true);

            return [
                'context' => $context,
                'payload' => $payload,
                'from_cache' => true,
            ];
        }

        $defaults = $this->ruleEngine->resolve($context);

        if (!(bool) ($defaults['enabled'] ?? true)) {
            $payload = [];
            MTOptimizeHooks::doAction('mtoptimize/context/after_resolve', $context, $payload, false);

            return [
                'context' => $context,
                'payload' => $payload,
                'from_cache' => false,
            ];
        }

        $meta = $this->metaResolver->resolve($context, $defaults);
        $social = $this->socialResolver->resolve($context, $defaults, $meta);

        $links = $this->linkResolver->resolve($context, array_merge($meta, ['robots' => $meta['robots'] ?? null]), $defaults);
        $schema = $this->schemaResolver->resolve($context, $meta, $social, $defaults);

        $payload = array_merge($meta, [
            'og' => $social['og'] ?? [],
            'twitter' => $social['twitter'] ?? [],
            'links' => $links,
            'schema' => $schema,
        ]);

        $payload = MTOptimizeHooks::applyFilters('mtoptimize/meta/resolve', $payload, $context, $defaults);
        $payload = $this->normalizer->normalize($payload, $context);

        $this->documentStore->put($context, $payload);

        MTOptimizeHooks::doAction('mtoptimize/context/after_resolve', $context, $payload, false);

        return [
            'context' => $context,
            'payload' => $payload,
            'from_cache' => false,
        ];
    }

    public function resolveCanonicalFromCurrentRequest(?string $fallback = null): ?string
    {
        $result = $this->resolve(request());

        $canonical = $result['payload']['canonical'] ?? null;
        if ($canonical === null || trim((string) $canonical) === '') {
            return $fallback;
        }

        return (string) $canonical;
    }
}
