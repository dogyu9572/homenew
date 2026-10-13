<?php

namespace App\Services;

use App\Models\IaPromptCommand;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class AnthropicIaPlanner
{
    /**
     * @param  list<array{depth1: string, depth2: string, depth3: string, style: string, access: string, description: string}>  $rows
     * @return list<array<string, mixed>>
     */
    public function plan(array $rows): array
    {
        $key = (string) config('services.anthropic.key');
        if ($key === '') {
            throw new RuntimeException('클로드 API 키가 없습니다.');
        }

        $commands = $this->commands();
        $items = [];
        foreach ($this->batches($rows) as $batch) {
            $items = array_merge($items, $this->request($commands, $batch));
        }

        return $items;
    }

    /**
     * @param  list<array{depth1: string, depth2: string, depth3: string, style: string, access: string, description: string}>  $rows
     * @return list<list<array{depth1: string, depth2: string, depth3: string, style: string, access: string, description: string}>>
     */
    private function batches(array $rows): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $groups[$row['depth1']."\n".$row['depth2']][] = $row;
        }

        $batches = [];
        $current = [];
        $count = 0;
        foreach ($groups as $group) {
            if ($count > 0 && $count + count($group) > 8) {
                $batches[] = $current;
                $current = [];
                $count = 0;
            }
            array_push($current, ...$group);
            $count += count($group);
        }
        if ($current !== []) {
            $batches[] = $current;
        }

        return $batches;
    }

    /**
     * @param  list<array{depth1: string, depth2: string, depth3: string, style: string, access: string, description: string}>  $rows
     * @return list<array<string, mixed>>
     */
    private function request(string $commands, array $rows): array
    {
        $key = (string) config('services.anthropic.key');
        try {
            $response = Http::withHeaders([
                'x-api-key' => $key,
                'anthropic-version' => '2023-06-01',
            ])->connectTimeout(20)
                ->timeout((int) config('services.anthropic.timeout', 480))
                ->post('https://api.anthropic.com/v1/messages', [
                'model' => (string) config('services.anthropic.model'),
                'max_tokens' => 16000,
                'system' => $this->systemPrompt($commands),
                'messages' => [
                    ['role' => 'user', 'content' => $this->sheetText($rows)],
                ],
            ]);
        } catch (ConnectionException) {
            throw new RuntimeException('클로드 응답이 제한 시간을 넘겼습니다.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('클로드 요청이 실패했습니다.');
        }

        $payload = $response->json();
        $parts = [];
        foreach ((array) data_get($payload, 'content', []) as $block) {
            if (is_array($block) && ($block['type'] ?? '') === 'text') {
                $parts[] = (string) ($block['text'] ?? '');
            }
        }
        $decoded = $this->decode(implode("\n", $parts));
        $items = $decoded['items'] ?? null;
        if (! is_array($items)) {
            throw new RuntimeException('클로드 결과 형식이 올바르지 않습니다.');
        }

        return $items;
    }

    private function commands(): string
    {
        if (! Schema::hasTable('ia_prompt_commands')) {
            return '';
        }

        return IaPromptCommand::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('body')
            ->filter(fn ($body) => trim((string) $body) !== '')
            ->implode("\n");
    }

    private function systemPrompt(string $commands): string
    {
        $commands = trim($commands);
        if ($commands === '') {
            throw new RuntimeException('사용 중인 IA 명령이 없습니다.');
        }

        return $commands;
    }

    /**
     * @param  list<array{depth1: string, depth2: string, depth3: string, style: string, access: string, description: string}>  $rows
     */
    private function sheetText(array $rows): string
    {
        $lines = [];
        foreach ($rows as $row) {
            $lines[] = implode(' | ', [
                $row['depth1'],
                $row['depth2'],
                $row['depth3'],
                $row['style'],
                $row['access'],
                $row['description'],
            ]);
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(string $text): array
    {
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $text) ?? $text;
        $json = $this->firstJsonObject($text);
        $decoded = $json === null ? null : json_decode($json, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('클로드 결과를 해석하지 못했습니다.');
        }
        if (! isset($decoded['items']) && array_is_list($decoded)) {
            return ['items' => $decoded];
        }

        return $decoded;
    }

    private function firstJsonObject(string $text): ?string
    {
        $start = strpos($text, '{');
        if ($start === false) {
            return null;
        }

        $depth = 0;
        $inString = false;
        $escape = false;
        $length = strlen($text);
        for ($index = $start; $index < $length; $index++) {
            $char = $text[$index];
            if ($inString) {
                if ($escape) {
                    $escape = false;
                    continue;
                }
                if ($char === '\\') {
                    $escape = true;
                    continue;
                }
                if ($char === '"') {
                    $inString = false;
                }
                continue;
            }
            if ($char === '"') {
                $inString = true;
                continue;
            }
            if ($char === '{') {
                $depth++;
            } elseif ($char === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($text, $start, $index - $start + 1);
                }
            }
        }

        return null;
    }
}
