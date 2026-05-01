<?php

declare(strict_types=1);

namespace App\Services;

use App\Facades\Hook;

/**
 * Content Renderer - Converts block-based JSON content to HTML
 */
class ContentRenderer
{
    /**
     * Context data for rendering (e.g. product, post)
     */
    protected $context = [];

    /**
     * Set rendering context
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }
    /**
     * Render blocks array to HTML
     */
    public function render(array $blocks): string
    {
        // Handle Tiptap document wrapper
        if (isset($blocks['type']) && $blocks['type'] === 'doc' && isset($blocks['content'])) {
            $blocks = $blocks['content'];
        }

        // Apply filter before rendering
        $blocks = Hook::applyFilters('content.render.blocks', $blocks);

        $html = '';

        foreach ($blocks as $block) {
            $blockHtml = $this->renderBlock($block);
            $html .= $blockHtml;
        }

        // Apply filter after rendering
        return Hook::applyFilters('content.render.html', $html, $blocks);
    }

    /**
     * Render a single block to HTML
     */
    protected function renderBlock(array $block): string
    {
        $type = $block['type'] ?? 'unknown';
        $attrs = $block['attrs'] ?? [];

        // Nested landing blocks inside layout blocks are stored in shorthand form:
        // ['type' => 'heading', 'data' => [...]] instead of a full TipTap landingBlock node.
        if ($type !== 'landingBlock' && isset($block['data']) && is_array($block['data'])) {
            return $this->renderLandingBlock([
                'type' => $type,
                'data' => $block['data'],
            ]);
        }

        // Apply filter for custom block rendering
        $html = Hook::applyFilters("content.render.block.{$type}", null, $block);

        if ($html !== null) {
            return $html;
        }

        // Default block renderers
        return match ($type) {
            'heading' => $this->renderHeading($block),
            'paragraph' => $this->renderParagraph($block),
            'image' => $this->renderImage($attrs),
            'bulletList' => $this->renderBulletList($block),
            'orderedList' => $this->renderOrderedList($block),
            'listItem' => $this->renderListItem($block),
            'quote' => $this->renderQuote($attrs),
            'code' => $this->renderCode($attrs),
            'grid' => $this->renderGrid($block),
            'landingBlock' => $this->renderLandingBlock($attrs),
            'horizontalRule' => '<hr>',
            'hardBreak' => '<br>',
            default => $this->renderUnknown($block),
        };
    }

    protected function renderHeading(array $block): string
    {
        $attrs = $block['attrs'] ?? [];
        $level = $attrs['level'] ?? 2;
        $content = $this->renderContent($block['content'] ?? []);
        $attributes = $this->renderNodeAttributes($attrs);

        return "<h{$level}{$attributes}>" . $content . "</h{$level}>";
    }

    protected function renderParagraph(array $block): string
    {
        $attrs = $block['attrs'] ?? [];
        $content = $this->renderContent($block['content'] ?? []);

        return "<p{$this->renderNodeAttributes($attrs)}>" . $content . '</p>';
    }

    protected function renderContent(array $content): string
    {
        $html = '';
        foreach ($content as $item) {
            $type = $item['type'] ?? '';
            
            if ($type === 'text') {
                $text = htmlspecialchars($item['text'] ?? '', ENT_QUOTES, 'UTF-8');
                
                // Handle marks (bold, italic, etc.)
                if (isset($item['marks'])) {
                    foreach ($item['marks'] as $mark) {
                        $markType = $mark['type'] ?? '';
                        $markHtml = match($markType) {
                            'bold' => '<strong>' . $text . '</strong>',
                            'italic' => '<em>' . $text . '</em>',
                            'link' => '<a href="' . htmlspecialchars($mark['attrs']['href'] ?? '#', ENT_QUOTES, 'UTF-8') . '">' . $text . '</a>',
                            'strike' => '<s>' . $text . '</s>',
                            'underline' => '<u>' . $text . '</u>',
                            default => $text
                        };
                        $text = $markHtml;
                    }
                }
                $html .= $text;
            } elseif ($type === 'image') {
                $html .= $this->renderImage($item['attrs'] ?? []);
            } elseif ($type === 'hardBreak') {
                $html .= '<br>';
            }
        }
        return $html;
    }

    protected function renderImage(array $attrs): string
    {
        $mediaId = $attrs['media_id'] ?? null;
        $alt = $attrs['alt'] ?? '';
        $url = $attrs['src'] ?? $attrs['url'] ?? '';

        if (!$url && $mediaId) {
            // In a real implementation, you'd fetch the media URL from database
            $url = '/media/' . $mediaId;
        }

        if (!$url) {
            return '';
        }

        return '<img src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '">';
    }

    protected function renderBulletList(array $block): string
    {
        $html = '<ul>';
        foreach ($block['content'] ?? [] as $item) {
            $html .= $this->renderBlock($item);
        }
        $html .= '</ul>';
        return $html;
    }

    protected function renderOrderedList(array $block): string
    {
        $html = '<ol>';
        foreach ($block['content'] ?? [] as $item) {
            $html .= $this->renderBlock($item);
        }
        $html .= '</ol>';
        return $html;
    }

    protected function renderListItem(array $block): string
    {
        $html = '<li>';
        foreach ($block['content'] ?? [] as $item) {
            $html .= $this->renderBlock($item);
        }
        $html .= '</li>';
        return $html;
    }

    protected function renderQuote(array $attrs): string
    {
        $text = $attrs['text'] ?? '';
        $citation = $attrs['citation'] ?? '';

        $html = '<blockquote>';
        $html .= '<p>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</p>';
        if ($citation) {
            $html .= '<cite>' . htmlspecialchars($citation, ENT_QUOTES, 'UTF-8') . '</cite>';
        }
        $html .= '</blockquote>';

        return $html;
    }

    protected function renderCode(array $attrs): string
    {
        $code = $attrs['code'] ?? '';
        $language = $attrs['language'] ?? '';

        return '<pre><code' . ($language ? ' class="language-' . htmlspecialchars($language, ENT_QUOTES, 'UTF-8') . '"' : '') . '>' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</code></pre>';
    }

    protected function renderLandingBlock(array $attrs): string
    {
        $type = $attrs['type'] ?? 'unknown';
        // Support both 'data' (new schema) and 'attrs' (legacy/direct TipTap schema)
        $blockAttrs = $attrs['data'] ?? $attrs['attrs'] ?? [];

        // Apply filter for specific landing block rendering
        $html = Hook::applyFilters("content.render.landing_block.{$type}", null, $blockAttrs, $this->context, $this);

        if ($html !== null) {
            return Hook::applyFilters("content.render.landing_block.post", $html, $type, $blockAttrs, $this->context);
        }

        return "<!-- Missing renderer for landing block type: {$type} -->";
    }

    protected function renderGrid(array $block): string
    {
        $attrs = $block['attrs'] ?? [];
        $columns = $attrs['columns'] ?? 2;
        $nestedBlocks = $attrs['blocks'] ?? [];
        
        // Group blocks by column (round-robin distribution)
        $columnsData = [];
        for ($i = 0; $i < $columns; $i++) {
            $columnsData[] = [];
        }
        
        foreach ($nestedBlocks as $index => $nestedBlock) {
            $colIndex = $index % $columns;
            $columnsData[$colIndex][] = $nestedBlock;
        }
        
        // Render each column
        $html = "<div class=\"grid grid-cols-{$columns} gap-4\">";
        foreach ($columnsData as $colBlocks) {
            $html .= '<div class="grid-column">';
            foreach ($colBlocks as $colBlock) {
                $html .= $this->renderBlock($colBlock);
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        
        return $html;
    }

    protected function renderUnknown(array $block): string
    {
        // Log or handle unknown block types
        return '<!-- Unknown block type: ' . htmlspecialchars($block['type'] ?? 'unknown', ENT_QUOTES, 'UTF-8') . ' -->';
    }

    protected function renderNodeAttributes(array $attrs): string
    {
        $htmlAttributes = [];
        $styles = [];

        if (!empty($attrs['style']) && is_string($attrs['style'])) {
            $styles[] = trim($attrs['style']);
        }

        $textAlign = $attrs['textAlign'] ?? null;
        if (is_string($textAlign) && in_array($textAlign, ['left', 'center', 'right', 'justify'], true)) {
            $styles[] = sprintf('text-align: %s', $textAlign);
        }

        if ($styles !== []) {
            $htmlAttributes['style'] = implode('; ', array_filter($styles));
        }

        foreach (['id', 'class'] as $attribute) {
            $value = $attrs[$attribute] ?? null;
            if (!is_string($value) || trim($value) === '') {
                continue;
            }

            $htmlAttributes[$attribute] = $value;
        }

        if ($htmlAttributes === []) {
            return '';
        }

        $pairs = [];
        foreach ($htmlAttributes as $attribute => $value) {
            $pairs[] = sprintf(
                '%s="%s"',
                $attribute,
                htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            );
        }

        return ' ' . implode(' ', $pairs);
    }
}
