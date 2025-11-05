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
     * Render blocks array to HTML
     */
    public function render(array $blocks): string
    {
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

        // Apply filter for custom block rendering
        $html = Hook::applyFilters("content.render.block.{$type}", null, $block);

        if ($html !== null) {
            return $html;
        }

        // Default block renderers
        return match ($type) {
            'heading' => $this->renderHeading($attrs),
            'paragraph' => $this->renderParagraph($attrs),
            'image' => $this->renderImage($attrs),
            'list' => $this->renderList($attrs),
            'quote' => $this->renderQuote($attrs),
            'code' => $this->renderCode($attrs),
            'grid' => $this->renderGrid($block),
            default => $this->renderUnknown($block),
        };
    }

    protected function renderHeading(array $attrs): string
    {
        $level = $attrs['level'] ?? 2;
        $text = $attrs['text'] ?? '';
        return "<h{$level}>" . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . "</h{$level}>";
    }

    protected function renderParagraph(array $attrs): string
    {
        $text = $attrs['text'] ?? '';
        return '<p>' . nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8')) . '</p>';
    }

    protected function renderImage(array $attrs): string
    {
        $mediaId = $attrs['media_id'] ?? null;
        $alt = $attrs['alt'] ?? '';
        $url = $attrs['url'] ?? '';

        if (!$url && $mediaId) {
            // In a real implementation, you'd fetch the media URL from database
            $url = '/media/' . $mediaId;
        }

        return '<img src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '">';
    }

    protected function renderList(array $attrs): string
    {
        $items = $attrs['items'] ?? [];
        $ordered = $attrs['ordered'] ?? false;

        $tag = $ordered ? 'ol' : 'ul';
        $html = "<{$tag}>";

        foreach ($items as $item) {
            $html .= '<li>' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '</li>';
        }

        $html .= "</{$tag}>";
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
}
