<?php

namespace App\Support;

/**
 * 레거시 ProjectAttachFileList.FileName 등에 여러 파일명이 묶여 있을 때 분리.
 * 파이프(|), CR/LF 기준 + HTML 엔티티 디코딩.
 * 일부 레거시 행은 "파일.pdf, 다른파일.docx"처럼 콤마+공백도 파일 구분자로 사용한다.
 */
final class LegacyAttachmentFileNames
{
    /**
     * @return list<string> 비어 있지 않은 파일명 목록
     */
    public static function split(string $raw): array
    {
        $decoded = html_entity_decode(trim($raw), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $pieces = preg_split('/\s*\|\s*|\r\n|\n|\r/', $decoded) ?: [];
        $out = [];
        foreach ($pieces as $piece) {
            $out = array_merge($out, self::splitCommaSeparatedFileNames($piece));
        }

        return array_values(array_filter(
            array_map(static fn (string $s): string => trim($s), $out),
            static fn (string $s): bool => $s !== ''
        ));
    }

    /**
     * Windows7,10.pdf 같은 파일명 내부 콤마는 유지하고,
     * "a.pdf, b.docx"처럼 콤마 뒤에 새 파일명이 이어지는 케이스만 분리한다.
     *
     * @return list<string>
     */
    private static function splitCommaSeparatedFileNames(string $raw): array
    {
        $extensions = 'pdf|docx?|xlsx?|xlsb|pptx?|hwp|hwpx|zip|rar|jpg|jpeg|png|gif|txt';
        $normalized = preg_replace(
            '/(\.(?:'.$extensions.')),\s+(?=[^,|]+\.(?:'.$extensions.')(?:$|[,|]))/iu',
            '$1|',
            $raw
        ) ?? $raw;

        return preg_split('/\|/', $normalized) ?: [$raw];
    }
}
