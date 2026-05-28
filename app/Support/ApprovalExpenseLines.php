<?php

namespace App\Support;

/**
 * 지출결의서 등 expense_lines 입력 정규화
 * (폼 name은 0~(LINE_COUNT-1) 인덱스, 과거 데이터는 1~5 또는 0~4 길이 배열일 수 있음)
 */
final class ApprovalExpenseLines
{
    /** 지출내역 입력 행 수 */
    public const LINE_COUNT = 3;

    /**
     * @param  array<int|string, mixed>  $lines
     * @return array<int, array<string, mixed>>
     */
    public static function normalize(array $lines): array
    {
        $max = self::LINE_COUNT;
        $hasZero = array_key_exists(0, $lines) || array_key_exists('0', $lines);

        if ($hasZero) {
            $normalized = [];
            for ($idx = 0; $idx < $max; $idx++) {
                $row = $lines[$idx] ?? $lines[(string) $idx] ?? [];
                $normalized[] = is_array($row) ? $row : [];
            }

            return $normalized;
        }

        if (array_key_exists(1, $lines) || array_key_exists('1', $lines)) {
            $normalized = [];
            for ($idx = 1; $idx <= $max; $idx++) {
                $row = $lines[$idx] ?? $lines[(string) $idx] ?? [];
                $normalized[] = is_array($row) ? $row : [];
            }

            return $normalized;
        }

        $values = array_values($lines);
        while (count($values) < $max) {
            $values[] = [];
        }

        return array_slice(array_map(
            fn ($row) => is_array($row) ? $row : [],
            $values
        ), 0, $max);
    }
}
