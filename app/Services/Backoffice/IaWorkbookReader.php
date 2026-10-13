<?php

namespace App\Services\Backoffice;

use RuntimeException;
use ZipArchive;

class IaWorkbookReader
{
    /**
     * @return list<array{depth1: string, depth2: string, depth3: string, style: string, access: string, description: string}>
     */
    public function rows(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('IA 엑셀을 열 수 없습니다.');
        }

        $shared = $zip->getFromName('xl/sharedStrings.xml');
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (! is_string($sheet) || $sheet === '') {
            throw new RuntimeException('IA 엑셀에 시트가 없습니다.');
        }

        $strings = $this->sharedStrings(is_string($shared) ? $shared : '');
        $xml = $this->withoutNamespaces($sheet);
        $document = $this->loadXml($xml);
        if ($document === false || ! isset($document->sheetData->row)) {
            throw new RuntimeException('IA 엑셀 시트를 읽지 못했습니다.');
        }

        $current1 = '';
        $current2 = '';
        $rows = [];
        foreach ($document->sheetData->row as $row) {
            $cells = $this->cells($row, $strings);
            $depth1 = trim($cells['D'] ?? '');
            $depth2 = trim($cells['E'] ?? '');
            $depth3 = trim($cells['F'] ?? '');
            $style = trim($cells['G'] ?? '');
            $access = trim($cells['H'] ?? '');
            $description = trim($cells['J'] ?? '');

            if ($depth1 === '1depth' || ($depth1 === '' && $depth2 === '' && $depth3 === '' && $description === '')) {
                continue;
            }

            if ($depth1 !== '') {
                $current1 = $depth1;
                $current2 = '';
            }
            if ($depth2 !== '') {
                $current2 = $depth2;
            }

            if ($style === '' && $description === '') {
                continue;
            }

            $rows[] = [
                'depth1' => $current1,
                'depth2' => $depth2 !== '' ? $depth2 : $current2,
                'depth3' => $depth3,
                'style' => $style,
                'access' => $access,
                'description' => $description,
            ];
        }

        return $rows;
    }

    /**
     * @return list<string>
     */
    private function sharedStrings(string $xml): array
    {
        if ($xml === '') {
            return [];
        }

        $document = $this->loadXml($this->withoutNamespaces($xml));
        if ($document === false) {
            return [];
        }

        $strings = [];
        foreach ($document->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;
                continue;
            }

            $text = '';
            foreach ($item->r as $run) {
                $text .= (string) $run->t;
            }
            $strings[] = $text;
        }

        return $strings;
    }

    /**
     * @param  list<string>  $strings
     * @return array<string, string>
     */
    private function cells(object $row, array $strings): array
    {
        $cells = [];
        foreach ($row->c as $cell) {
            $column = preg_replace('/\d+/', '', (string) $cell['r']) ?? '';
            $type = (string) $cell['t'];
            $value = (string) $cell->v;
            if ($type === 's') {
                $value = $strings[(int) $value] ?? '';
            } elseif ($type === 'inlineStr') {
                $value = (string) $cell->is->t;
            }
            $cells[$column] = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $cells;
    }

    private function withoutNamespaces(string $xml): string
    {
        $xml = preg_replace('/\sxmlns(:\w+)?="[^"]*"/', '', $xml) ?? $xml;
        $xml = preg_replace('/<(\/?)(\w+):/', '<$1', $xml) ?? $xml;

        return preg_replace('/\s[\w.]+:[\w.-]+="[^"]*"/', '', $xml) ?? $xml;
    }

    private function loadXml(string $xml): \SimpleXMLElement|false
    {
        $previous = libxml_use_internal_errors(true);
        $document = simplexml_load_string($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $document;
    }
}
