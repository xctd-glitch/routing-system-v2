<?php

declare(strict_types=1);

namespace SRP\Service;

use InvalidArgumentException;
use SRP\Storage\JsonFileStorage;

final class DashboardService
{
    /**
     * @var array{system_on: bool, is_active: bool, rule_type: string, mute_duration: int, unmute_duration: int}
     */
    private const DEFAULT_CONFIG = [
        'system_on' => false,
        'is_active' => false,
        'rule_type' => 'static_route',
        'mute_duration' => 120,
        'unmute_duration' => 120,
    ];

    /**
     * @var list<array{id: int, url: string, weight: int, priority: int, active: bool}>
     */
    private const DEFAULT_URLS = [
        [
            'id' => 1,
            'url' => 'https://example.com',
            'weight' => 1,
            'priority' => 1,
            'active' => true,
        ],
        [
            'id' => 2,
            'url' => 'https://backup.com',
            'weight' => 2,
            'priority' => 2,
            'active' => true,
        ],
    ];

    /**
     * @var list<array{code: string, name: string}>
     */
    private const DEFAULT_COUNTRIES = [
        [
            'code' => 'US',
            'name' => 'United States',
        ],
        [
            'code' => 'UK',
            'name' => 'United Kingdom',
        ],
        [
            'code' => 'DE',
            'name' => 'Germany',
        ],
    ];

    private const VALID_RULE_TYPES = ['static_route', 'random_route', 'mute_unmute'];

    /**
     * @var array<string, string>
     */
    private const COUNTRY_NAMES = [
        'AU' => 'Australia',
        'BR' => 'Brazil',
        'CA' => 'Canada',
        'DE' => 'Germany',
        'FR' => 'France',
        'GB' => 'United Kingdom',
        'ID' => 'Indonesia',
        'IN' => 'India',
        'JP' => 'Japan',
        'MY' => 'Malaysia',
        'NZ' => 'New Zealand',
        'PH' => 'Philippines',
        'SG' => 'Singapore',
        'TH' => 'Thailand',
        'UK' => 'United Kingdom',
        'US' => 'United States',
    ];

    private JsonFileStorage $storage;

    public function __construct(?JsonFileStorage $storage = null)
    {
        $defaultState = self::getDefaultState();
        $this->storage = $storage ?? new JsonFileStorage(
            dirname(__DIR__, 2) . '/storage/dashboard_state.json',
            $defaultState
        );
    }

    /**
     * @return array{
     *     config: array<string, mixed>,
     *     urls: list<array<string, mixed>>,
     *     countries: list<array<string, string>>
     * }
     */
    public function getState(): array
    {
        $state = $this->storage->read();

        $configData = $state['config'] ?? [];
        $urlsData = $state['urls'] ?? [];
        $countriesData = $state['countries'] ?? [];

        return [
            'config' => $this->normalizeConfig(is_array($configData) ? $configData : []),
            'urls' => $this->normalizeUrls($urlsData),
            'countries' => $this->normalizeCountries($countriesData),
        ];
    }

    public function updateConfig(
        bool $systemOn,
        bool $isActive,
        string $ruleType,
        int $muteDuration,
        int $unmuteDuration
    ): void {
        $normalizedRuleType = $this->sanitizeRuleType($ruleType);
        $muteDurationValue = $this->sanitizeDuration($muteDuration);
        $unmuteDurationValue = $this->sanitizeDuration($unmuteDuration);

        $state = $this->getState();
        $state['config'] = [
            'system_on' => $systemOn,
            'is_active' => $isActive,
            'rule_type' => $normalizedRuleType,
            'mute_duration' => $muteDurationValue,
            'unmute_duration' => $unmuteDurationValue,
        ];

        $this->storage->write($state);
    }

    public function addUrl(string $url, int $weight, int $priority): void
    {
        $validatedUrl = filter_var($url, FILTER_VALIDATE_URL);
        if ($validatedUrl === false) {
            throw new InvalidArgumentException('Please provide a valid URL.');
        }

        $weightValue = $this->sanitizeRange($weight, 1, 1000, 'weight');
        $priorityValue = $this->sanitizeRange($priority, 0, 1000, 'priority');

        $state = $this->getState();
        /** @var list<array{id: int, url: string, weight: int, priority: int, active: bool}> $existingUrls */
        $existingUrls = $state['urls'];
        $nextId = $this->nextUrlId($existingUrls);
        $existingUrls[] = [
            'id' => $nextId,
            'url' => $validatedUrl,
            'weight' => $weightValue,
            'priority' => $priorityValue,
            'active' => true,
        ];

        $state['urls'] = $this->orderUrls($existingUrls);
        $this->storage->write($state);
    }

    /**
     * @param list<string> $countryCodes
     */
    public function updateCountries(array $countryCodes): void
    {
        if ($countryCodes === []) {
            throw new InvalidArgumentException('Please provide at least one country code.');
        }

        $normalizedCodes = [];
        foreach ($countryCodes as $code) {
            $normalizedCode = strtoupper(trim($code));
            if ($normalizedCode === '') {
                continue;
            }

            if (!preg_match('/^[A-Z]{2}$/', $normalizedCode)) {
                throw new InvalidArgumentException(sprintf('Invalid country code: %s', $code));
            }

            $normalizedCodes[$normalizedCode] = true;
        }

        if ($normalizedCodes === []) {
            throw new InvalidArgumentException('Please provide at least one valid country code.');
        }

        $state = $this->getState();
        $state['countries'] = [];

        foreach (array_keys($normalizedCodes) as $normalizedCode) {
            $state['countries'][] = [
                'code' => $normalizedCode,
                'name' => $this->resolveCountryName($normalizedCode, ''),
            ];
        }

        $this->storage->write($state);
    }

    /**
     * @return array{
     *     config: array<string, mixed>,
     *     urls: list<array<string, mixed>>,
     *     countries: list<array<string, string>>
     * }
     */
    public static function getDefaultState(): array
    {
        return [
            'config' => self::DEFAULT_CONFIG,
            'urls' => self::DEFAULT_URLS,
            'countries' => self::DEFAULT_COUNTRIES,
        ];
    }

    /**
     * @return list<string>
     */
    public static function getValidRuleTypes(): array
    {
        return self::VALID_RULE_TYPES;
    }

    /**
     * @param array<int|string, mixed> $config
     * @return array{
     *     system_on: bool,
     *     is_active: bool,
     *     rule_type: string,
     *     mute_duration: int,
     *     unmute_duration: int
     * }
     */
    private function normalizeConfig(array $config): array
    {
        $merged = array_merge(self::DEFAULT_CONFIG, $config);

        $systemOnSource = $merged['system_on'] ?? self::DEFAULT_CONFIG['system_on'];
        $systemOn = is_bool($systemOnSource) ? $systemOnSource : (bool) $systemOnSource;

        $isActiveSource = $merged['is_active'] ?? self::DEFAULT_CONFIG['is_active'];
        $isActive = is_bool($isActiveSource) ? $isActiveSource : (bool) $isActiveSource;

        $ruleTypeSource = $merged['rule_type'] ?? self::DEFAULT_CONFIG['rule_type'];
        $ruleTypeValue = is_string($ruleTypeSource) ? $ruleTypeSource : self::DEFAULT_CONFIG['rule_type'];

        $muteDurationSource = $merged['mute_duration'] ?? self::DEFAULT_CONFIG['mute_duration'];
        $muteDurationValue = is_int($muteDurationSource) ? $muteDurationSource : self::DEFAULT_CONFIG['mute_duration'];

        $unmuteDurationSource = $merged['unmute_duration'] ?? self::DEFAULT_CONFIG['unmute_duration'];
        $unmuteDurationValue = is_int($unmuteDurationSource) ? $unmuteDurationSource : self::DEFAULT_CONFIG['unmute_duration'];

        return [
            'system_on' => $systemOn,
            'is_active' => $isActive,
            'rule_type' => $this->sanitizeRuleType($ruleTypeValue),
            'mute_duration' => $this->sanitizeDuration($muteDurationValue),
            'unmute_duration' => $this->sanitizeDuration($unmuteDurationValue),
        ];
    }

    /**
     * @param mixed $urls
     * @return list<array{id: int, url: string, weight: int, priority: int, active: bool}>
     */
    private function normalizeUrls($urls): array
    {
        if (!is_array($urls)) {
            return self::DEFAULT_URLS;
        }

        /** @var list<array{id: int, url: string, weight: int, priority: int, active: bool}> $normalized */
        $normalized = [];
        foreach ($urls as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            /** @var array<string, mixed> $candidate */
            $candidate = $entry;
            $sanitized = $this->sanitizeUrlEntry($candidate);
            if ($sanitized !== null) {
                $normalized[] = $sanitized;
            }
        }

        if ($normalized === []) {
            return self::DEFAULT_URLS;
        }

        return $this->orderUrls($normalized);
    }

    /**
     * @param mixed $countries
     * @return list<array{code: string, name: string}>
     */
    private function normalizeCountries($countries): array
    {
        if (!is_array($countries)) {
            return self::DEFAULT_COUNTRIES;
        }

        $normalized = [];
        foreach ($countries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            /** @var array<string, mixed> $candidate */
            $candidate = $entry;

            $codeValue = $candidate['code'] ?? '';
            $code = strtoupper(is_string($codeValue) ? $codeValue : '');
            if (!preg_match('/^[A-Z]{2}$/', $code)) {
                continue;
            }

            $nameValue = $candidate['name'] ?? '';
            $name = trim(is_string($nameValue) ? $nameValue : '');
            $normalized[] = [
                'code' => $code,
                'name' => $this->resolveCountryName($code, $name),
            ];
        }

        if ($normalized === []) {
            return self::DEFAULT_COUNTRIES;
        }

        return $normalized;
    }

    private function sanitizeRuleType(string $ruleType): string
    {
        if (!in_array($ruleType, self::VALID_RULE_TYPES, true)) {
            throw new InvalidArgumentException('Please choose a valid routing rule.');
        }

        return $ruleType;
    }

    private function sanitizeDuration(int $duration): int
    {
        if ($duration < 0 || $duration > 86400) {
            throw new InvalidArgumentException('Durations must be between 0 and 86400 seconds.');
        }

        return $duration;
    }

    private function sanitizeRange(int $value, int $min, int $max, string $field): int
    {
        if ($value < $min || $value > $max) {
            throw new InvalidArgumentException(sprintf('The %s must be between %d and %d.', $field, $min, $max));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $entry
     * @return array{id: int, url: string, weight: int, priority: int, active: bool}|null
     */
    private function sanitizeUrlEntry(array $entry): ?array
    {
        $idValue = $entry['id'] ?? null;
        if (!is_int($idValue) || $idValue <= 0) {
            return null;
        }

        $urlValue = $entry['url'] ?? null;
        if (!is_string($urlValue)) {
            return null;
        }

        $validatedUrl = filter_var($urlValue, FILTER_VALIDATE_URL);
        if ($validatedUrl === false) {
            return null;
        }

        $weightValue = $entry['weight'] ?? 1;
        $weight = is_int($weightValue) ? $weightValue : 1;

        $priorityValue = $entry['priority'] ?? 0;
        $priority = is_int($priorityValue) ? $priorityValue : 0;

        $activeValue = $entry['active'] ?? true;
        $isActive = is_bool($activeValue) ? $activeValue : (bool) $activeValue;

        return [
            'id' => $idValue,
            'url' => $validatedUrl,
            'weight' => $this->sanitizeRange($weight, 1, 1000, 'weight'),
            'priority' => $this->sanitizeRange($priority, 0, 1000, 'priority'),
            'active' => $isActive,
        ];
    }

    /**
     * @param list<array{id: int, url: string, weight: int, priority: int, active: bool}> $urls
     * @return list<array{id: int, url: string, weight: int, priority: int, active: bool}>
     */
    private function orderUrls(array $urls): array
    {
        usort(
            $urls,
            static function (array $first, array $second): int {
                if ($first['priority'] === $second['priority']) {
                    return $first['id'] <=> $second['id'];
                }

                return $first['priority'] <=> $second['priority'];
            }
        );

        return $urls;
    }

    /**
     * @param list<array{id: int, url: string, weight: int, priority: int, active: bool}> $urls
     */
    private function nextUrlId(array $urls): int
    {
        $max = 0;
        foreach ($urls as $url) {
            $id = $url['id'];
            if ($id > $max) {
                $max = $id;
            }
        }

        return $max + 1;
    }

    private function resolveCountryName(string $code, string $currentName): string
    {
        if ($currentName !== '') {
            return $currentName;
        }

        if (isset(self::COUNTRY_NAMES[$code])) {
            return self::COUNTRY_NAMES[$code];
        }

        if ($code === 'UK') {
            return 'United Kingdom';
        }

        return $code;
    }
}
