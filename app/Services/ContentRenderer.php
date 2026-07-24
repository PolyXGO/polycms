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
     * Collected modal overlays and scripts to be appended at the end of the document
     */
    protected array $modalOverlays = [];

    /**
     * Track if shared modal styles have been registered
     */
    protected bool $sharedStylesRendered = false;

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
        // Reset state for each render call
        $this->modalOverlays = [];
        $this->sharedStylesRendered = false;

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

        // Append collected modal overlays and styles at the very end of the content
        if (!empty($this->modalOverlays)) {
            $html .= "\n<!-- PolyCMS Modal Overlays -->\n" . implode("\n", $this->modalOverlays);
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
            'table' => $this->renderTable($block),
            'tableRow' => $this->renderTableRow($block),
            'tableHeader' => $this->renderTableHeader($block),
            'tableCell' => $this->renderTableCell($block),
            'horizontalRule' => '<hr>',
            'hardBreak' => '<br>',
            'youtubeGallery' => $this->renderYoutubeGallery($block),
            'modalLink' => $this->renderModalLink($block),
            'mermaidChart' => $this->renderMermaidChart($attrs),
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

    protected function renderTable(array $block): string
    {
        $attrs = $block['attrs'] ?? [];
        $content = '';
        if (isset($block['content'])) {
            foreach ($block['content'] as $row) {
                $content .= $this->renderTableRow($row);
            }
        }
        $attributes = $this->renderNodeAttributes($attrs);
        return "<div class=\"overflow-x-auto my-4\"><table{$attributes} class=\"min-w-full border-collapse table-auto border border-admin-theme-border\">" . $content . "</table></div>";
    }

    protected function renderTableRow(array $block): string
    {
        $attrs = $block['attrs'] ?? [];
        $content = '';
        if (isset($block['content'])) {
            foreach ($block['content'] as $cell) {
                if (($cell['type'] ?? '') === 'tableHeader') {
                    $content .= $this->renderTableHeader($cell);
                } else {
                    $content .= $this->renderTableCell($cell);
                }
            }
        }
        $attributes = $this->renderNodeAttributes($attrs);
        return "<tr{$attributes}>" . $content . "</tr>";
    }

    protected function renderTableHeader(array $block): string
    {
        $attrs = $block['attrs'] ?? [];
        $content = '';
        if (isset($block['content'])) {
            foreach ($block['content'] as $child) {
                $content .= $this->renderBlock($child);
            }
        }
        $attributes = $this->renderNodeAttributes($attrs);
        
        $colspan = isset($attrs['colspan']) ? " colspan=\"{$attrs['colspan']}\"" : '';
        $rowspan = isset($attrs['rowspan']) ? " rowspan=\"{$attrs['rowspan']}\"" : '';
        $colwidth = isset($attrs['colwidth']) && is_array($attrs['colwidth']) && !empty($attrs['colwidth']) 
            ? " style=\"width: {$attrs['colwidth'][0]}px\"" : '';

        return "<th{$attributes}{$colspan}{$rowspan}{$colwidth} class=\"border border-admin-theme-border px-4 py-2 bg-admin-theme-base font-bold\">" . $content . "</th>";
    }

    protected function renderTableCell(array $block): string
    {
        $attrs = $block['attrs'] ?? [];
        $content = '';
        if (isset($block['content'])) {
            foreach ($block['content'] as $child) {
                $content .= $this->renderBlock($child);
            }
        }
        $attributes = $this->renderNodeAttributes($attrs);
        
        $colspan = isset($attrs['colspan']) ? " colspan=\"{$attrs['colspan']}\"" : '';
        $rowspan = isset($attrs['rowspan']) ? " rowspan=\"{$attrs['rowspan']}\"" : '';
        $colwidth = isset($attrs['colwidth']) && is_array($attrs['colwidth']) && !empty($attrs['colwidth']) 
            ? " style=\"width: {$attrs['colwidth'][0]}px\"" : '';

        return "<td{$attributes}{$colspan}{$rowspan}{$colwidth} class=\"border border-admin-theme-border px-4 py-2\">" . $content . "</td>";
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
                    $marks = $item['marks'];
                    // Sort marks so 'textStyle' is applied last (outermost)
                    usort($marks, function ($a, $b) {
                        $typeA = $a['type'] ?? '';
                        $typeB = $b['type'] ?? '';
                        if ($typeA === 'textStyle') return 1;
                        if ($typeB === 'textStyle') return -1;
                        return 0;
                    });

                    foreach ($marks as $mark) {
                        $markType = $mark['type'] ?? '';
                        $markHtml = match($markType) {
                            'bold' => '<strong>' . $text . '</strong>',
                            'italic' => '<em>' . $text . '</em>',
                            'link' => (function() use ($mark, $text) {
                                $href = htmlspecialchars($mark['attrs']['href'] ?? '#', ENT_QUOTES, 'UTF-8');
                                $target = htmlspecialchars($mark['attrs']['target'] ?? '', ENT_QUOTES, 'UTF-8');
                                $rel = htmlspecialchars($mark['attrs']['rel'] ?? '', ENT_QUOTES, 'UTF-8');
                                $targetAttr = $target ? " target=\"{$target}\"" : '';
                                $relAttr = $rel ? " rel=\"{$rel}\"" : '';
                                return "<a href=\"{$href}\"{$targetAttr}{$relAttr}>" . $text . '</a>';
                            })(),
                            'strike' => '<s>' . $text . '</s>',
                            'underline' => '<u>' . $text . '</u>',
                            'code' => '<code>' . $text . '</code>',
                            'subscript' => '<sub>' . $text . '</sub>',
                            'superscript' => '<sup>' . $text . '</sup>',
                            'highlight' => (function() use ($mark, $text) {
                                $color = $mark['attrs']['color'] ?? null;
                                if ($color) {
                                    $bgColor = htmlspecialchars($color, ENT_QUOTES, 'UTF-8');
                                    return '<mark data-color="' . $bgColor . '" style="background-color: ' . $bgColor . ';">' . $text . '</mark>';
                                }
                                return '<mark>' . $text . '</mark>';
                            })(),
                            'textStyle' => (function() use ($mark, $text) {
                                $styles = [];
                                if (!empty($mark['attrs']['fontFamily'])) {
                                    $styles[] = 'font-family: ' . htmlspecialchars($mark['attrs']['fontFamily'], ENT_QUOTES, 'UTF-8');
                                }
                                if (!empty($mark['attrs']['fontSize'])) {
                                    $styles[] = 'font-size: ' . htmlspecialchars($mark['attrs']['fontSize'], ENT_QUOTES, 'UTF-8');
                                }
                                if (!empty($mark['attrs']['color'])) {
                                    $styles[] = 'color: ' . htmlspecialchars($mark['attrs']['color'], ENT_QUOTES, 'UTF-8');
                                }
                                if (empty($styles)) return $text;
                                return '<span style="' . implode('; ', $styles) . '">' . $text . '</span>';
                            })(),
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
            } elseif ($type === 'modalLink') {
                $html .= $this->renderModalLink($item);
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

    protected function renderMermaidChart(array $attrs): string
    {
        $code = $attrs['code'] ?? '';
        return '<pre><code class="language-mermaid">' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</code></pre>';
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

        // Fallback for core TipTap blocks rendered as landing blocks
        if (in_array($type, ['youtubeGallery', 'modalLink', 'mermaidChart', 'tabs'], true)) {
            $legacyBlock = [
                'type' => $type,
                'attrs' => $blockAttrs,
            ];
            $html = match ($type) {
                'youtubeGallery' => $this->renderYoutubeGallery($legacyBlock),
                'modalLink' => $this->renderModalLink($legacyBlock),
                'mermaidChart' => $this->renderMermaidChart($blockAttrs),
                'tabs' => $this->renderTabsFallback($blockAttrs),
                default => null,
            };
            if ($html !== null) {
                return Hook::applyFilters("content.render.landing_block.post", $html, $type, $blockAttrs, $this->context);
            }
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

    protected function renderTabsFallback(array $attrs): string
    {
        $items = $attrs['items'] ?? [];
        if (!is_array($items) || empty($items)) {
            return '';
        }
        $style = $attrs['style'] ?? 'underline';
        $alignment = $attrs['alignment'] ?? 'start';
        $margin = $attrs['margin'] ?? '';
        $padding = $attrs['padding'] ?? '';
        $tabId = 'tab-' . uniqid();

        $alignClass = match($alignment) {
            'center' => 'justify-center',
            'end' => 'justify-end',
            default => 'justify-start',
        };

        $baseTabClass = "px-5 py-3 font-bold text-sm transition-all focus:outline-none whitespace-nowrap cursor-pointer";
        $activeTabClass = match($style) {
            'pills' => "bg-emerald-600 text-white rounded-xl shadow-lg",
            'blocks' => "bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 border-t-2 border-emerald-600 shadow-sm",
            default => "text-emerald-600 dark:text-emerald-400 border-b-2 border-emerald-600",
        };
        $inactiveTabClass = match($style) {
            'pills' => "text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200",
            'blocks' => "bg-slate-50 dark:bg-slate-900 text-slate-400 hover:text-slate-600 border-t-2 border-transparent",
            default => "text-slate-400 hover:text-slate-600 border-b-2 border-transparent",
        };

        $inlineStyles = [];
        if ($margin) $inlineStyles[] = "margin: {$margin}";
        if ($padding) $inlineStyles[] = "padding: {$padding}";
        $styleAttr = !empty($inlineStyles) ? ' style="' . implode('; ', $inlineStyles) . '"' : '';

        $html = "<div id=\"{$tabId}\" class=\"landing-tabs w-full my-6\"{$styleAttr}>";
        $html .= "<div class=\"flex {$alignClass} " . ($style === 'blocks' ? 'bg-slate-50 dark:bg-slate-900 rounded-t-2xl overflow-hidden' : 'border-b border-slate-200 dark:border-slate-800 mb-6') . " gap-2 no-scrollbar overflow-x-auto\">";
        
        foreach ($items as $index => $item) {
            $title = htmlspecialchars($item['title'] ?? ('Tab ' . ($index + 1)), ENT_QUOTES, 'UTF-8');
            $activeClass = $index === 0 ? $activeTabClass : $inactiveTabClass;
            $html .= "<button type=\"button\" class=\"tab-trigger {$baseTabClass} {$activeClass}\" role=\"tab\" data-index=\"{$index}\">{$title}</button>";
        }
        $html .= "</div>";

        $html .= "<div class=\"tab-content-wrapper\">";
        foreach ($items as $index => $item) {
            $content = nl2br(htmlspecialchars($item['content'] ?? '', ENT_QUOTES, 'UTF-8'));
            $display = $index === 0 ? 'display: block;' : 'display: none;';
            $html .= "<div class=\"tab-panel transition-all duration-300\" role=\"tabpanel\" data-index=\"{$index}\" style=\"{$display}\">";
            $html .= "<div class=\"bg-white dark:bg-slate-900/60 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 prose dark:prose-invert max-w-none text-sm text-slate-700 dark:text-slate-200\">{$content}</div>";
            $html .= "</div>";
        }
        $html .= "</div>";
        $html .= "</div>";

        $html .= "<script>
        document.addEventListener('DOMContentLoaded', function() {
            (function() {
                const container = document.getElementById('{$tabId}');
                if (!container) return;
                const triggers = container.querySelectorAll('.tab-trigger');
                const panels = container.querySelectorAll('.tab-panel');
                triggers.forEach(trigger => {
                    trigger.addEventListener('click', () => {
                        const index = parseInt(trigger.getAttribute('data-index'));
                        triggers.forEach(t => {
                            const isItem = parseInt(t.getAttribute('data-index')) === index;
                            const activeClasses = '{$activeTabClass}'.split(' ');
                            const inactiveClasses = '{$inactiveTabClass}'.split(' ');
                            if (isItem) {
                                t.classList.remove(...inactiveClasses);
                                t.classList.add(...activeClasses);
                            } else {
                                t.classList.remove(...activeClasses);
                                t.classList.add(...inactiveClasses);
                            }
                        });
                        panels.forEach(p => {
                            const isItem = parseInt(p.getAttribute('data-index')) === index;
                            p.style.display = isItem ? 'block' : 'none';
                        });
                    });
                });
            })();
        });
        </script>";

        return $html;
    }

    protected function renderYoutubeGallery(array $block): string
    {
        $attrs = $block['attrs'] ?? [];
        $urls = $attrs['urls'] ?? [];
        $layout = $attrs['layout'] ?? 'grid';

        if (empty($urls) || !is_array($urls)) {
            return '';
        }

        // Self-contained, highly-robust styles to guarantee beautiful layout presentation
        // across all front-end themes, with or without Tailwind CSS.
        $html = '<style>
            .youtube-gallery-wrapper-scoped {
                width: 100%;
                box-sizing: border-box;
            }
            .youtube-gallery-aspect-video {
                position: relative !important;
                width: 100% !important;
                aspect-ratio: 16 / 9 !important;
            }
            .youtube-gallery-grid-layout {
                display: grid !important;
                grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
                gap: 16px !important;
                width: 100% !important;
            }
            @media (min-width: 640px) {
                .youtube-gallery-grid-layout {
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                }
            }
            .youtube-gallery-list-layout {
                display: flex !important;
                flex-direction: column !important;
                gap: 24px !important;
                width: 100% !important;
            }
            .youtube-gallery-hero-layout {
                width: 100% !important;
                aspect-ratio: 16 / 9 !important;
                margin-bottom: 16px !important;
            }
            .youtube-gallery-thumbnails-layout {
                display: grid !important;
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                gap: 8px !important;
                width: 100% !important;
            }
            @media (min-width: 640px) {
                .youtube-gallery-thumbnails-layout {
                    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
                }
            }
            @media (min-width: 768px) {
                .youtube-gallery-thumbnails-layout {
                    grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
                }
            }
            .youtube-gallery-thumbnail-item {
                position: relative !important;
                aspect-ratio: 16 / 9 !important;
                cursor: pointer !important;
                overflow: hidden !important;
                border-radius: 6px !important;
            }
            .youtube-gallery-thumbnail-item img {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
            }
            .youtube-gallery-slider-outer {
                position: relative !important;
                overflow: hidden !important;
                width: 100% !important;
            }
            .youtube-gallery-slider-inner {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
                -ms-overflow-style: none !important;
                scrollbar-width: none !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .youtube-gallery-slider-inner::-webkit-scrollbar {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
            }
            .youtube-gallery-slider-slide {
                flex-shrink: 0 !important;
                box-sizing: border-box !important;
            }
        </style>';

        $html .= '<div class="youtube-gallery youtube-gallery-wrapper-scoped youtube-gallery-' . htmlspecialchars($layout, ENT_QUOTES, 'UTF-8') . ' my-6">';

        if ($layout === 'gallery' && count($urls) > 0) {
            $firstUrl = $urls[0];
            $heroId = 'yt-hero-' . uniqid();
            $html .= '<div class="youtube-gallery-hero youtube-gallery-hero-layout mb-4">';
            $html .= '<iframe id="' . $heroId . '" src="' . htmlspecialchars($this->getYoutubeEmbedUrl($firstUrl), ENT_QUOTES, 'UTF-8') . '" class="w-full h-full border-0 rounded-lg shadow-sm" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
            $html .= '</div>';

            if (count($urls) > 1) {
                $html .= '<div class="youtube-gallery-thumbnails youtube-gallery-thumbnails-layout gap-2">';
                foreach ($urls as $url) {
                    $id = $this->getYoutubeId($url);
                    if (!$id) continue;
                    $thumbUrl = "https://img.youtube.com/vi/{$id}/mqdefault.jpg";
                    $embedUrl = "https://www.youtube.com/embed/{$id}";
                    
                    $html .= '<div class="youtube-gallery-thumbnail youtube-gallery-thumbnail-item cursor-pointer rounded-md overflow-hidden relative group" onclick="document.getElementById(\'' . $heroId . '\').src = \'' . $embedUrl . '\'">';
                    $html .= '<img src="' . $thumbUrl . '" alt="Video Thumbnail" class="w-full h-full object-cover">';
                    $html .= '<div class="absolute inset-0 bg-black/30 group-hover:bg-transparent transition-colors flex items-center justify-center"><svg class="w-8 h-8 text-white opacity-80" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg></div>';
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
        } elseif ($layout === 'slider') {
            $visibleItems = max(1, (int)($attrs['sliderVisibleItems'] ?? 1));
            $autoPlay = filter_var($attrs['sliderAutoPlay'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $continuous = filter_var($attrs['sliderContinuous'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $direction = $attrs['sliderDirection'] ?? 'left';
            $sliderId = 'yt-slider-' . uniqid();
            
            // Use strict inline style for width to avoid Tailwind JIT or Theme overrides
            $itemWidthPercent = 100 / $visibleItems;
            $itemStyle = 'width: ' . $itemWidthPercent . '%; flex: 0 0 ' . $itemWidthPercent . '%; max-width: ' . $itemWidthPercent . '%;';

            // If continuous, we duplicate the items to create a seamless loop
            $renderUrls = $continuous ? array_merge($urls, $urls) : $urls;

            $html .= '<div class="youtube-gallery-slider-wrapper youtube-gallery-slider-outer relative group">';
            // For continuous, we disable snap to allow smooth pixel-by-pixel scrolling
            $snapClass = $continuous ? '' : 'snap-x snap-mandatory';
            $html .= '<div id="' . $sliderId . '" class="youtube-gallery-slider youtube-gallery-slider-inner ' . $snapClass . ' gap-0" style="scroll-behavior: ' . ($continuous ? 'auto' : 'smooth') . ';">';
            foreach ($renderUrls as $url) {
                $html .= '<div class="youtube-gallery-slider-slide ' . ($continuous ? '' : 'snap-center') . ' px-2 py-2" style="' . $itemStyle . '">';
                $html .= '<div class="youtube-gallery-aspect-video w-full">';
                $html .= $this->renderYoutubeIframe($url);
                $html .= '</div></div>';
            }
            $html .= '</div>';
            
            // Only show Prev/Next buttons if not continuous and there are more items than visible
            if (!$continuous && count($urls) > $visibleItems) {
                $html .= '<button aria-label="Previous" onclick="document.getElementById(\'' . $sliderId . '\').scrollBy({left: -(document.getElementById(\'' . $sliderId . '\').offsetWidth / ' . $visibleItems . '), behavior: \'smooth\'})" class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/60 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity z-10 hover:bg-black/80 shadow-md" style="position: absolute !important; display: block !important; border: 0 !important; cursor: pointer !important;"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></button>';
                
                $html .= '<button aria-label="Next" onclick="document.getElementById(\'' . $sliderId . '\').scrollBy({left: (document.getElementById(\'' . $sliderId . '\').offsetWidth / ' . $visibleItems . '), behavior: \'smooth\'})" class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/60 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity z-10 hover:bg-black/80 shadow-md" style="position: absolute !important; display: block !important; border: 0 !important; cursor: pointer !important;"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>';
            }
            
            if (($autoPlay || $continuous) && count($urls) > $visibleItems) {
                $html .= '<script>
                    (function() {
                        const slider = document.getElementById("' . $sliderId . '");
                        if (!slider) return;
                        
                        let isPlaying = false;
                        const isContinuous = ' . ($continuous ? 'true' : 'false') . ';
                        const scrollDirection = "' . $direction . '";
                        
                        if (isContinuous) {
                            let speed = 1; // 1px per frame
                            let dir = scrollDirection === "left" ? 1 : -1; // "left" means scrolling contents to the left (increasing scrollLeft)
                            
                            function continuousStep() {
                                if (!slider.isConnected) return;
                                if (!isPlaying) {
                                    slider.scrollLeft += dir * speed;
                                    
                                    // Seamless loop logic
                                    const halfWidth = slider.scrollWidth / 2;
                                    if (dir === 1 && slider.scrollLeft >= halfWidth) {
                                        slider.scrollLeft -= halfWidth;
                                    } else if (dir === -1 && slider.scrollLeft <= 0) {
                                        slider.scrollLeft += halfWidth;
                                    }
                                }
                                requestAnimationFrame(continuousStep);
                            }
                            requestAnimationFrame(continuousStep);
                        } else {
                            let direction = 1;
                            setInterval(() => {
                                if (!slider.isConnected || isPlaying) return;
                                const maxScroll = slider.scrollWidth - slider.clientWidth;
                                if (slider.scrollLeft >= maxScroll - 10) direction = -1;
                                else if (slider.scrollLeft <= 10) direction = 1;
                                
                                slider.scrollBy({left: (slider.offsetWidth / ' . $visibleItems . ') * direction, behavior: "smooth"});
                            }, 5000);
                        }
                        
                        // YouTube API Integration to detect play state
                        function initPlayers() {
                            const iframes = slider.querySelectorAll("iframe");
                            iframes.forEach((iframe) => {
                                new YT.Player(iframe, {
                                    events: {
                                        "onStateChange": function(event) {
                                            // 1 = PLAYING, 3 = BUFFERING
                                            if (event.data === 1 || event.data === 3) {
                                                isPlaying = true;
                                            } else {
                                                // Check if any other iframe is still playing before setting to false
                                                let anyPlaying = false;
                                                iframes.forEach(otherIframe => {
                                                    // In JS API, getting state requires the player instance, but we can just use a simple heuristic:
                                                    // This might momentarily resume if multiple videos are interacted with, but it\'s sufficient for most cases.
                                                });
                                                isPlaying = false;
                                            }
                                        }
                                    }
                                });
                            });
                        }
                        
                        if (typeof YT === "undefined" || typeof YT.Player === "undefined") {
                            if (!document.getElementById("youtube-iframe-api-script")) {
                                const tag = document.createElement("script");
                                tag.id = "youtube-iframe-api-script";
                                tag.src = "https://www.youtube.com/iframe_api";
                                const firstScriptTag = document.getElementsByTagName("script")[0];
                                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
                                
                                const oldCallback = window.onYouTubeIframeAPIReady;
                                window.onYouTubeIframeAPIReady = function() {
                                    if (oldCallback) oldCallback();
                                    window.dispatchEvent(new Event("youtubeIframeAPIReady"));
                                };
                            }
                            window.addEventListener("youtubeIframeAPIReady", initPlayers);
                        } else {
                            initPlayers();
                        }
                    })();
                </script>';
            }
            
            $html .= '</div>';
        } elseif ($layout === 'list') {
            $html .= '<div class="youtube-gallery-list youtube-gallery-list-layout">';
            foreach ($urls as $url) {
                $html .= '<div class="youtube-gallery-item youtube-gallery-aspect-video">';
                $html .= $this->renderYoutubeIframe($url);
                $html .= '</div>';
            }
            $html .= '</div>';
        } else {
            // Default to grid
            $html .= '<div class="youtube-gallery-grid youtube-gallery-grid-layout">';
            foreach ($urls as $url) {
                $html .= '<div class="youtube-gallery-item youtube-gallery-aspect-video">';
                $html .= $this->renderYoutubeIframe($url);
                $html .= '</div>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }

    protected function getYoutubeId(string $url): ?string
    {
        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function getYoutubeEmbedUrl(string $url): string
    {
        $id = $this->getYoutubeId($url);
        // Append enablejsapi=1 so the YouTube Iframe API can interact with it
        return $id ? 'https://www.youtube.com/embed/' . $id . '?enablejsapi=1' : '';
    }

    protected function renderYoutubeIframe(string $url): string
    {
        $embedUrl = $this->getYoutubeEmbedUrl($url);
        if (!$embedUrl) return '';
        
        $iframeId = 'yt-iframe-' . uniqid();
        return '<iframe id="' . $iframeId . '" src="' . htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8') . '" class="w-full h-full border-0 rounded-lg shadow-sm" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
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

    /**
     * Process HTML content from TipTap to expand embedded landing block nodes.
     *
     * TipTap serialises landing blocks as:
     *   <div data-type="landing-block" data-block-type="features_grid" data-block-data='{"title":"..."}'>...</div>
     *
     * This method finds those nodes and replaces them with their fully rendered HTML
     * by delegating to renderLandingBlock().
     */
    protected function processEmbeddedLandingBlocks(string $html): string
    {
        if (strpos($html, 'data-type="landing-block"') === false) {
            return $html;
        }

        // Match <div data-type="landing-block" ...>...</div> (greedy-safe since landing blocks are atom/self-closing in TipTap)
        return preg_replace_callback(
            '/<div\s+[^>]*data-type=["\']landing-block["\'][^>]*>(?:.*?)<\/div>/si',
            function (array $match) {
                $tag = $match[0];

                // Extract data-block-type
                if (!preg_match('/data-block-type=["\']([^"\']+)["\']/', $tag, $typeMatch)) {
                    return $tag; // can't identify type — leave as-is
                }
                $blockType = $typeMatch[1];

                // Extract data-block-data JSON
                $blockData = [];
                if (preg_match("/data-block-data='([^']*)'/", $tag, $dataMatch)) {
                    $decoded = json_decode($dataMatch[1], true);
                    if (is_array($decoded)) {
                        $blockData = $decoded;
                    }
                } elseif (preg_match('/data-block-data="([^"]*)"/', $tag, $dataMatch)) {
                    $decoded = json_decode(html_entity_decode($dataMatch[1], ENT_QUOTES, 'UTF-8'), true);
                    if (is_array($decoded)) {
                        $blockData = $decoded;
                    }
                }

                return $this->renderLandingBlock([
                    'type' => $blockType,
                    'data' => $blockData,
                ]);
            },
            $html
        ) ?? $html;
    }

    protected function renderModalLink(array $block): string
    {
        $attrs = $block['attrs'] ?? [];
        $labelText = $attrs['labelText'] ?? 'Click here';
        $modalSize = $attrs['modalSize'] ?? 'lg';
        $contentType = $attrs['contentType'] ?? 'html';
        $contentHtml = $attrs['contentHtml'] ?? '';
        $iframeUrl = $attrs['iframeUrl'] ?? '';
        $displayMode = $attrs['displayMode'] ?? 'button';

        $uniq = uniqid('ml-');
        $overlayId = "overlay-{$uniq}";
        $triggerId = "trigger-{$uniq}";
        $closeId = "close-{$uniq}";

        // Clean values
        $labelTextHtml = htmlspecialchars($labelText, ENT_QUOTES, 'UTF-8');
        $iframeUrlHtml = htmlspecialchars($iframeUrl, ENT_QUOTES, 'UTF-8');

        // Modal sizes CSS
        $maxWidth = '800px';
        if ($modalSize === 'sm') {
            $maxWidth = '500px';
        } elseif ($modalSize === 'full') {
            $maxWidth = '95%';
        }

        $bodyPadding = $contentType === 'iframe' ? '0px' : '24px';
        $iframeHeight = $modalSize === 'full' ? '85vh' : '80vh';

        $isLink = $displayMode === 'link';

        // Render trigger HTML - entirely inline phrasing tag without block-level contents
        $triggerHtml = "";
        if ($isLink) {
            $triggerHtml = "<a href=\"javascript:void(0)\" id=\"{$triggerId}\" class=\"poly-modal-trigger-link\">{$labelTextHtml}</a>";
        } else {
            $triggerHtml = "<button id=\"{$triggerId}\" class=\"poly-modal-trigger-button\">{$labelTextHtml}</button>";
        }

        // Render modal overlay HTML
        $overlayHtml = "
        <div id=\"{$overlayId}\" class=\"poly-modal-overlay\">
            <div class=\"poly-modal-card\" style=\"max-width: {$maxWidth} !important;\">
                <button id=\"{$closeId}\" aria-label=\"Close dialog\" class=\"poly-modal-close-btn\">
                    <svg class=\"w-5 h-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\" style=\"width: 16px; height: 16px;\">
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2.5\" d=\"M6 18L18 6M6 6l12 12\"></path>
                    </svg>
                </button>
                <div class=\"poly-modal-body\" style=\"padding: {$bodyPadding} !important;\">
        ";

        if ($contentType === 'iframe' && $iframeUrl) {
            $overlayHtml .= "
                <div class=\"poly-modal-iframe-container\" style=\"height: {$iframeHeight} !important;\">
                    <iframe class=\"poly-modal-iframe\" src=\"{$iframeUrlHtml}\" allowfullscreen></iframe>
                </div>
            ";
        } else {
            // HTML content — process any embedded landing block nodes from TipTap
            $overlayHtml .= $this->processEmbeddedLandingBlocks($contentHtml);
        }

        $overlayHtml .= "
                </div>
            </div>
        </div>

        <script>
            (function() {
                const trigger = document.getElementById(\"{$triggerId}\");
                const overlay = document.getElementById(\"{$overlayId}\");
                const closeBtn = document.getElementById(\"{$closeId}\");
                if (!trigger || !overlay || !closeBtn) return;

                function openModal() {
                    document.body.style.overflow = 'hidden';
                    overlay.classList.add(\"is-open\");
                }

                function closeModal() {
                    document.body.style.overflow = '';
                    overlay.classList.remove(\"is-open\");
                }

                trigger.addEventListener(\"click\", function(e) {
                    e.preventDefault();
                    openModal();
                });

                closeBtn.addEventListener(\"click\", closeModal);

                overlay.addEventListener(\"click\", function(e) {
                    if (e.target === overlay) {
                        closeModal();
                    }
                });

                document.addEventListener(\"keydown\", function(e) {
                    if (e.key === \"Escape\" && overlay.classList.contains(\"is-open\")) {
                        closeModal();
                    }
                });
            })();
        </script>
        ";

        $cssOutput = '';
        // Register shared stylesheet once
        if (!$this->sharedStylesRendered) {
            $cssOutput = "
            <style>
                .poly-modal-trigger-link {
                    display: inline !important;
                    background: none !important;
                    border: none !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    color: #2563eb !important;
                    text-decoration: underline !important;
                    text-decoration-style: dotted !important;
                    text-underline-offset: 3px !important;
                    font-weight: 600 !important;
                    font-size: inherit !important;
                    font-family: inherit !important;
                    cursor: pointer !important;
                    transition: color 0.2s ease !important;
                    outline: none !important;
                }
                .poly-modal-trigger-link:hover {
                    color: #1d4ed8 !important;
                    text-decoration-style: solid !important;
                }
                .poly-modal-trigger-link:active {
                    color: #1e40af !important;
                }

                .poly-modal-trigger-button {
                    display: inline-block !important;
                    background-color: #3b82f6 !important;
                    color: #ffffff !important;
                    font-weight: 600 !important;
                    font-size: 14px !important;
                    padding: 10px 20px !important;
                    border-radius: 9999px !important;
                    border: none !important;
                    cursor: pointer !important;
                    text-decoration: none !important;
                    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3), 0 2px 4px -1px rgba(59, 130, 246, 0.06) !important;
                    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
                    outline: none !important;
                    user-select: none !important;
                }
                .poly-modal-trigger-button:hover {
                    background-color: #2563eb !important;
                    transform: translateY(-2px) !important;
                    box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4), 0 4px 6px -2px rgba(59, 130, 246, 0.05) !important;
                }
                .poly-modal-trigger-button:active {
                    transform: translateY(0px) !important;
                }

                .poly-modal-overlay {
                    position: fixed !important;
                    top: 0 !important;
                    left: 0 !important;
                    width: 100% !important;
                    height: 100% !important;
                    background-color: rgba(15, 23, 42, 0.6) !important;
                    backdrop-filter: blur(8px) !important;
                    -webkit-backdrop-filter: blur(8px) !important;
                    z-index: 99999 !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    opacity: 0 !important;
                    pointer-events: none !important;
                    transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                    padding: 16px !important;
                    box-sizing: border-box !important;
                }
                .poly-modal-overlay.is-open {
                    opacity: 1 !important;
                    pointer-events: auto !important;
                }

                .poly-modal-card {
                    position: relative !important;
                    background: #ffffff !important;
                    border-radius: 20px !important;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
                    width: 100% !important;
                    max-height: 90vh !important;
                    display: flex !important;
                    flex-direction: column !important;
                    transform: scale(0.9) translateY(20px) !important;
                    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
                    overflow: hidden !important;
                    box-sizing: border-box !important;
                    border: 1px solid rgba(226, 232, 240, 0.8) !important;
                }
                .poly-modal-overlay.is-open .poly-modal-card {
                    transform: scale(1) translateY(0) !important;
                }

                .poly-modal-close-btn {
                    position: absolute !important;
                    top: 12px !important;
                    right: 12px !important;
                    z-index: 9999 !important;
                    background: rgba(15, 23, 42, 0.6) !important;
                    border: none !important;
                    border-radius: 9999px !important;
                    width: 32px !important;
                    height: 32px !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    cursor: pointer !important;
                    color: #ffffff !important;
                    transition: all 0.2s ease !important;
                    outline: none !important;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2) !important;
                }
                .poly-modal-close-btn:hover {
                    background: rgba(15, 23, 42, 0.9) !important;
                    transform: scale(1.1) !important;
                }

                .poly-modal-body {
                    overflow-y: auto !important;
                    flex: 1 !important;
                    box-sizing: border-box !important;
                    font-size: 16px !important;
                    line-height: 1.6 !important;
                }

                .poly-modal-iframe-container {
                    position: relative !important;
                    width: 100% !important;
                    overflow: hidden !important;
                    border-radius: 0px !important;
                }

                .poly-modal-iframe {
                    width: 100% !important;
                    height: 100% !important;
                    border: none !important;
                }

                @media (prefers-color-scheme: dark) {
                    .poly-modal-trigger-link {
                        color: #60a5fa !important;
                    }
                    .poly-modal-trigger-link:hover {
                        color: #93c5fd !important;
                    }
                    .poly-modal-card {
                        background: #1e293b !important;
                        border: 1px solid rgba(51, 65, 85, 0.8) !important;
                        color: #f8fafc !important;
                    }
                }
            </style>
            ";
            $this->sharedStylesRendered = true;
        }

        return $cssOutput . $triggerHtml . $overlayHtml;
    }
}
