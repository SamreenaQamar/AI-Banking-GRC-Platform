<?php
/**
 * AI Banking GRC Platform - Excel Exporter Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides enterprise Excel export functionality:
 * - Export arrays to Excel
 * - Export reports
 * - Export users
 * - Export risks
 * - Export compliance data
 * - Export audit data
 * - Export dashboard data
 * - CSV support
 * - Multiple sheet support
 */

declare(strict_types=1);

namespace App\Libraries;

use App\Libraries\Logger;

class ExcelExporter
{
    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var array Export data
     */
    private array $data = [];

    /**
     * @var array Headers
     */
    private array $headers = [];

    /**
     * @var string Filename
     */
    private string $filename = 'export';

    /**
     * @var string Format (xlsx, csv)
     */
    private string $format = 'xlsx';

    /**
     * @var array Sheet data for multi-sheet exports
     */
    private array $sheets = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = new Logger();
    }

    /**
     * Set data for export
     * 
     * @param array $data
     * @param array $headers
     * @return self
     */
    public function setData(array $data, array $headers = []): self
    {
        $this->data = $data;
        $this->headers = $headers;
        return $this;
    }

    /**
     * Set filename
     * 
     * @param string $filename
     * @return self
     */
    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Set format
     * 
     * @param string $format
     * @return self
     */
    public function setFormat(string $format): self
    {
        $this->format = in_array($format, ['xlsx', 'csv']) ? $format : 'xlsx';
        return $this;
    }

    /**
     * Add sheet for multi-sheet export
     * 
     * @param string $name
     * @param array $data
     * @param array $headers
     * @return self
     */
    public function addSheet(string $name, array $data, array $headers = []): self
    {
        $this->sheets[$name] = [
            'data' => $data,
            'headers' => $headers
        ];
        return $this;
    }

    /**
     * Export users
     * 
     * @param array $users
     * @return string
     */
    public function exportUsers(array $users): string
    {
        $headers = [
            'ID', 'Username', 'Email', 'Full Name', 'Role', 'Status', 
            'Department', 'Last Login', 'Created At'
        ];

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'full_name' => $user->full_name ?? $user->username,
                'role' => $user->role_name ?? 'N/A',
                'status' => $user->status,
                'department' => $user->department_name ?? 'N/A',
                'last_login' => $user->last_login ?? 'Never',
                'created_at' => $user->created_at
            ];
        }

        $this->setData($data, $headers);
        return $this->export();
    }

    /**
     * Export risks
     * 
     * @param array $risks
     * @return string
     */
    public function exportRisks(array $risks): string
    {
        $headers = [
            'Risk Code', 'Title', 'Category', 'Level', 'Score', 
            'Status', 'Owner', 'Likelihood', 'Impact', 'Created At'
        ];

        $data = [];
        foreach ($risks as $risk) {
            $data[] = [
                'risk_code' => $risk->risk_code,
                'title' => $risk->title,
                'category' => $risk->category_name ?? 'N/A',
                'level' => $risk->risk_level ?? 'N/A',
                'score' => $risk->inherent_risk_score ?? 0,
                'status' => $risk->status,
                'owner' => $risk->owner_name ?? 'Unassigned',
                'likelihood' => $risk->inherent_likelihood ?? 0,
                'impact' => $risk->inherent_impact ?? 0,
                'created_at' => $risk->created_at
            ];
        }

        $this->setData($data, $headers);
        return $this->export();
    }

    /**
     * Export compliance data
     * 
     * @param array $compliance
     * @return string
     */
    public function exportCompliance(array $compliance): string
    {
        $headers = [
            'Task ID', 'Title', 'Category', 'Framework', 'Priority', 
            'Status', 'Due Date', 'Assigned To', 'Score', 'Created At'
        ];

        $data = [];
        foreach ($compliance as $task) {
            $data[] = [
                'id' => $task->id,
                'title' => $task->title,
                'category' => $task->category_name ?? 'N/A',
                'framework' => $task->framework_name ?? 'N/A',
                'priority' => $task->priority,
                'status' => $task->status,
                'due_date' => $task->due_date,
                'assigned_to' => $task->assigned_to_name ?? 'Unassigned',
                'score' => $task->compliance_score ?? 0,
                'created_at' => $task->created_at
            ];
        }

        $this->setData($data, $headers);
        return $this->export();
    }

    /**
     * Export audit data
     * 
     * @param array $audits
     * @return string
     */
    public function exportAudits(array $audits): string
    {
        $headers = [
            'Audit ID', 'Title', 'Type', 'Department', 'Status', 
            'Start Date', 'End Date', 'Lead Auditor', 'Budget', 'Created At'
        ];

        $data = [];
        foreach ($audits as $audit) {
            $data[] = [
                'id' => $audit->id,
                'title' => $audit->title,
                'type' => $audit->audit_type,
                'department' => $audit->department_name ?? 'N/A',
                'status' => $audit->status,
                'start_date' => $audit->start_date,
                'end_date' => $audit->end_date,
                'lead_auditor' => $audit->lead_auditor_name ?? 'Unassigned',
                'budget' => $audit->estimated_budget ?? 0,
                'created_at' => $audit->created_at
            ];
        }

        $this->setData($data, $headers);
        return $this->export();
    }

    /**
     * Export dashboard data
     * 
     * @param array $stats
     * @return string
     */
    public function exportDashboard(array $stats): string
    {
        $headers = ['Metric', 'Value', 'Change', 'Status'];
        $data = [];

        foreach ($stats as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $data[] = [
                'metric' => ucfirst(str_replace('_', ' ', $key)),
                'value' => $value,
                'change' => '0%',
                'status' => 'Stable'
            ];
        }

        $this->setData($data, $headers);
        return $this->export();
    }

    /**
     * Main export method
     * 
     * @return string
     */
    public function export(): string
    {
        try {
            if ($this->format === 'csv') {
                return $this->exportCSV();
            }

            return $this->exportXLSX();

        } catch (\Exception $e) {
            $this->logger->error('Excel export error: ' . $e->getMessage());
            throw new \RuntimeException('Failed to export: ' . $e->getMessage());
        }
    }

    /**
     * Export to CSV
     * 
     * @return string
     */
    private function exportCSV(): string
    {
        $output = fopen('php://temp', 'r+');

        // Add BOM for UTF-8
        fwrite($output, "\xEF\xBB\xBF");

        // Write headers
        $headers = $this->headers ?: $this->getHeadersFromData($this->data);
        if (!empty($headers)) {
            fputcsv($output, $headers);
        }

        // Write data
        foreach ($this->data as $row) {
            $rowData = [];
            if (is_object($row)) {
                $row = (array)$row;
            }
            foreach ($headers as $header) {
                $key = strtolower(str_replace(' ', '_', $header));
                $rowData[] = $row[$key] ?? '';
            }
            fputcsv($output, $rowData);
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
    }

    /**
     * Export to XLSX (Simple XML-based implementation)
     * 
     * @return string
     */
    private function exportXLSX(): string
    {
        // Simple XML-based XLSX generation
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Workbook></Workbook>');
        $xml->addAttribute('xmlns', 'urn:schemas-microsoft-com:office:spreadsheet');
        $xml->addAttribute('xmlns:o', 'urn:schemas-microsoft-com:office:office');
        $xml->addAttribute('xmlns:x', 'urn:schemas-microsoft-com:office:excel');
        $xml->addAttribute('xmlns:ss', 'urn:schemas-microsoft-com:office:spreadsheet');

        $worksheet = $xml->addChild('Worksheet');
        $worksheet->addAttribute('ss:Name', 'Sheet1');

        $table = $worksheet->addChild('Table');

        // Headers
        $headers = $this->headers ?: $this->getHeadersFromData($this->data);
        if (!empty($headers)) {
            $row = $table->addChild('Row');
            foreach ($headers as $header) {
                $cell = $row->addChild('Cell');
                $data = $cell->addChild('Data', htmlspecialchars($header));
                $data->addAttribute('ss:Type', 'String');
            }
        }

        // Data
        foreach ($this->data as $row) {
            $xmlRow = $table->addChild('Row');
            if (is_object($row)) {
                $row = (array)$row;
            }
            foreach ($headers as $header) {
                $key = strtolower(str_replace(' ', '_', $header));
                $value = $row[$key] ?? '';
                $cell = $xmlRow->addChild('Cell');
                $data = $cell->addChild('Data', htmlspecialchars((string)$value));
                $data->addAttribute('ss:Type', 'String');
            }
        }

        return $xml->asXML();
    }

    /**
     * Get headers from data
     * 
     * @param array $data
     * @return array
     */
    private function getHeadersFromData(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        $firstRow = (array)$data[0];
        return array_keys($firstRow);
    }

    /**
     * Download exported file
     * 
     * @param string $content
     * @return void
     */
    public function download(string $content): void
    {
        $extension = $this->format === 'csv' ? 'csv' : 'xlsx';
        $contentType = $this->format === 'csv' 
            ? 'text/csv' 
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        $filename = $this->filename . '.' . $extension;

        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }

    /**
     * Save to file
     * 
     * @param string $content
     * @param string $path
     * @return bool
     */
    public function save(string $content, string $path): bool
    {
        return file_put_contents($path, $content) !== false;
    }

    /**
     * Export and download
     * 
     * @return void
     */
    public function exportAndDownload(): void
    {
        $content = $this->export();
        $this->download($content);
    }

    /**
     * Export multi-sheet
     * 
     * @return string
     */
    public function exportMultiSheet(): string
    {
        if ($this->format === 'csv') {
            // CSV doesn't support multiple sheets, export first sheet
            $firstSheet = reset($this->sheets);
            $this->data = $firstSheet['data'] ?? [];
            $this->headers = $firstSheet['headers'] ?? [];
            return $this->exportCSV();
        }

        // Simple XML-based multi-sheet XLSX
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Workbook></Workbook>');
        $xml->addAttribute('xmlns', 'urn:schemas-microsoft-com:office:spreadsheet');
        $xml->addAttribute('xmlns:o', 'urn:schemas-microsoft-com:office:office');
        $xml->addAttribute('xmlns:x', 'urn:schemas-microsoft-com:office:excel');
        $xml->addAttribute('xmlns:ss', 'urn:schemas-microsoft-com:office:spreadsheet');

        foreach ($this->sheets as $name => $sheet) {
            $worksheet = $xml->addChild('Worksheet');
            $worksheet->addAttribute('ss:Name', $name);

            $table = $worksheet->addChild('Table');

            // Headers
            $headers = $sheet['headers'] ?: $this->getHeadersFromData($sheet['data']);
            if (!empty($headers)) {
                $row = $table->addChild('Row');
                foreach ($headers as $header) {
                    $cell = $row->addChild('Cell');
                    $data = $cell->addChild('Data', htmlspecialchars($header));
                    $data->addAttribute('ss:Type', 'String');
                }
            }

            // Data
            foreach ($sheet['data'] as $row) {
                $xmlRow = $table->addChild('Row');
                if (is_object($row)) {
                    $row = (array)$row;
                }
                foreach ($headers as $header) {
                    $key = strtolower(str_replace(' ', '_', $header));
                    $value = $row[$key] ?? '';
                    $cell = $xmlRow->addChild('Cell');
                    $data = $cell->addChild('Data', htmlspecialchars((string)$value));
                    $data->addAttribute('ss:Type', 'String');
                }
            }
        }

        return $xml->asXML();
    }
}