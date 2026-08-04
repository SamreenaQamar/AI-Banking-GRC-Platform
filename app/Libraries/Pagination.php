<?php
/**
 * AI Banking GRC Platform - Pagination Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides enterprise pagination functionality:
 * - Bootstrap pagination
 * - Search pagination
 * - Sorting
 * - Filtering
 * - Page links
 * - Pagination styling
 */

declare(strict_types=1);

namespace App\Libraries;

class Pagination
{
    /**
     * @var int Total items
     */
    private int $total;

    /**
     * @var int Items per page
     */
    private int $perPage;

    /**
     * @var int Current page
     */
    private int $currentPage;

    /**
     * @var int Total pages
     */
    private int $totalPages;

    /**
     * @var int Offset
     */
    private int $offset;

    /**
     * @var string URL base
     */
    private string $url;

    /**
     * @var array Query parameters
     */
    private array $params = [];

    /**
     * @var int Number of links to show
     */
    private int $linksToShow = 5;

    /**
     * @var string Pagination style
     */
    private string $style = 'bootstrap';

    /**
     * Constructor
     * 
     * @param int $total
     * @param int $perPage
     * @param int $currentPage
     * @param string $url
     * @param array $params
     */
    public function __construct(int $total, int $perPage = 15, int $currentPage = 1, string $url = '', array $params = [])
    {
        $this->total = $total;
        $this->perPage = max(1, $perPage);
        $this->currentPage = max(1, $currentPage);
        $this->url = $url ?: $_SERVER['REQUEST_URI'];
        $this->params = $params;

        $this->totalPages = (int)ceil($this->total / $this->perPage);
        $this->currentPage = min($this->currentPage, $this->totalPages);
        $this->offset = ($this->currentPage - 1) * $this->perPage;
    }

    /**
     * Get paginated data
     * 
     * @param array $data
     * @return array
     */
    public function paginate(array $data): array
    {
        return array_slice($data, $this->offset, $this->perPage);
    }

    /**
     * Get offset
     * 
     * @return int
     */
    public function offset(): int
    {
        return $this->offset;
    }

    /**
     * Get limit
     * 
     * @return int
     */
    public function limit(): int
    {
        return $this->perPage;
    }

    /**
     * Get current page
     * 
     * @return int
     */
    public function currentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Get total pages
     * 
     * @return int
     */
    public function totalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * Get total items
     * 
     * @return int
     */
    public function total(): int
    {
        return $this->total;
    }

    /**
     * Get items per page
     * 
     * @return int
     */
    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * Check if has previous page
     * 
     * @return bool
     */
    public function hasPrevious(): bool
    {
        return $this->currentPage > 1;
    }

    /**
     * Check if has next page
     * 
     * @return bool
     */
    public function hasNext(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    /**
     * Get previous page URL
     * 
     * @return string|null
     */
    public function previousUrl(): ?string
    {
        if (!$this->hasPrevious()) {
            return null;
        }
        return $this->pageUrl($this->currentPage - 1);
    }

    /**
     * Get next page URL
     * 
     * @return string|null
     */
    public function nextUrl(): ?string
    {
        if (!$this->hasNext()) {
            return null;
        }
        return $this->pageUrl($this->currentPage + 1);
    }

    /**
     * Get page URL
     * 
     * @param int $page
     * @return string
     */
    public function pageUrl(int $page): string
    {
        $params = $this->params;
        $params['page'] = $page;

        $url = $this->url;
        $query = http_build_query($params);

        if (strpos($url, '?') !== false) {
            return $url . '&' . $query;
        }

        return $url . '?' . $query;
    }

    /**
     * Get page links
     * 
     * @return array
     */
    public function links(): array
    {
        $links = [];
        $half = (int)floor($this->linksToShow / 2);
        $start = max(1, $this->currentPage - $half);
        $end = min($this->totalPages, $this->currentPage + $half);

        if ($start > 1) {
            $links[] = ['page' => 1, 'label' => '1', 'current' => false];
            if ($start > 2) {
                $links[] = ['page' => null, 'label' => '...', 'current' => false];
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            $links[] = [
                'page' => $i,
                'label' => (string)$i,
                'current' => $i === $this->currentPage
            ];
        }

        if ($end < $this->totalPages) {
            if ($end < $this->totalPages - 1) {
                $links[] = ['page' => null, 'label' => '...', 'current' => false];
            }
            $links[] = ['page' => $this->totalPages, 'label' => (string)$this->totalPages, 'current' => false];
        }

        return $links;
    }

    /**
     * Render pagination HTML
     * 
     * @return string
     */
    public function render(): string
    {
        if ($this->totalPages <= 1) {
            return '';
        }

        $html = '<nav aria-label="Page navigation">';
        $html .= '<ul class="pagination">';

        // Previous
        $html .= $this->renderPrevious();

        // Pages
        foreach ($this->links() as $link) {
            if ($link['page'] === null) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            } elseif ($link['current']) {
                $html .= '<li class="page-item active"><span class="page-link">' . $link['label'] . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="' . $this->pageUrl($link['page']) . '">' . $link['label'] . '</a></li>';
            }
        }

        // Next
        $html .= $this->renderNext();

        $html .= '</ul>';
        $html .= '</nav>';

        return $html;
    }

    /**
     * Render previous button
     * 
     * @return string
     */
    private function renderPrevious(): string
    {
        if ($this->hasPrevious()) {
            return '<li class="page-item"><a class="page-link" href="' . $this->previousUrl() . '" aria-label="Previous">' .
                   '<span aria-hidden="true">&laquo;</span></a></li>';
        }

        return '<li class="page-item disabled"><span class="page-link" aria-label="Previous">' .
               '<span aria-hidden="true">&laquo;</span></span></li>';
    }

    /**
     * Render next button
     * 
     * @return string
     */
    private function renderNext(): string
    {
        if ($this->hasNext()) {
            return '<li class="page-item"><a class="page-link" href="' . $this->nextUrl() . '" aria-label="Next">' .
                   '<span aria-hidden="true">&raquo;</span></a></li>';
        }

        return '<li class="page-item disabled"><span class="page-link" aria-label="Next">' .
               '<span aria-hidden="true">&raquo;</span></span></li>';
    }

    /**
     * Get pagination info
     * 
     * @return string
     */
    public function info(): string
    {
        $start = $this->offset + 1;
        $end = min($this->offset + $this->perPage, $this->total);

        return "Showing {$start} to {$end} of {$this->total} entries";
    }

    /**
     * Set number of links to show
     * 
     * @param int $count
     * @return void
     */
    public function setLinksToShow(int $count): void
    {
        $this->linksToShow = max(3, $count);
    }

    /**
     * Set pagination style
     * 
     * @param string $style
     * @return void
     */
    public function setStyle(string $style): void
    {
        $this->style = $style;
    }

    /**
     * Set parameters
     * 
     * @param array $params
     * @return void
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * Add parameter
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function addParam(string $key, $value): void
    {
        $this->params[$key] = $value;
    }

    /**
     * Remove parameter
     * 
     * @param string $key
     * @return void
     */
    public function removeParam(string $key): void
    {
        unset($this->params[$key]);
    }

    /**
     * Get pagination as array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'per_page' => $this->perPage,
            'current_page' => $this->currentPage,
            'total_pages' => $this->totalPages,
            'offset' => $this->offset,
            'links' => $this->links(),
            'has_previous' => $this->hasPrevious(),
            'has_next' => $this->hasNext(),
            'previous_url' => $this->previousUrl(),
            'next_url' => $this->nextUrl()
        ];
    }

    /**
     * Create from request
     * 
     * @param int $total
     * @param int $defaultPerPage
     * @return self
     */
    public static function fromRequest(int $total, int $defaultPerPage = 15): self
    {
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : $defaultPerPage;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $params = $_GET;
        unset($params['page'], $params['per_page']);

        $url = strtok($_SERVER['REQUEST_URI'], '?');

        return new self($total, $perPage, $page, $url, $params);
    }
}