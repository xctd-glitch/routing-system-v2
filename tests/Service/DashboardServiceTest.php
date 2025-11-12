<?php

declare(strict_types=1);

namespace SRP\Tests\Service;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SRP\Service\DashboardService;
use SRP\Storage\JsonFileStorage;

final class DashboardServiceTest extends TestCase
{
    private string $dataFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataFile = sys_get_temp_dir() . '/routing-system-' . bin2hex(random_bytes(4)) . '.json';
        if (file_exists($this->dataFile)) {
            unlink($this->dataFile);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->dataFile)) {
            unlink($this->dataFile);
        }

        parent::tearDown();
    }

    public function testGetStateReturnsDefaultWhenStoreIsMissing(): void
    {
        $service = $this->createService();
        $state = $service->getState();
        $default = DashboardService::getDefaultState();

        self::assertSame($default['config'], $state['config']);
        self::assertSame($default['urls'], $state['urls']);
        self::assertSame($default['countries'], $state['countries']);
    }

    public function testUpdateConfigPersistsValues(): void
    {
        $service = $this->createService();
        $service->updateConfig(true, false, 'random_route', 30, 60);

        $state = $service->getState();

        self::assertTrue($state['config']['system_on']);
        self::assertFalse($state['config']['is_active']);
        self::assertSame('random_route', $state['config']['rule_type']);
        self::assertSame(30, $state['config']['mute_duration']);
        self::assertSame(60, $state['config']['unmute_duration']);
    }

    public function testAddUrlAppendsNewEntry(): void
    {
        $service = $this->createService();
        $service->addUrl('https://alpha.example', 3, 0);
        $service->addUrl('https://beta.example', 2, 5);

        $urls = $service->getState()['urls'];

        self::assertSame('https://alpha.example', $urls[0]['url']);
        self::assertSame(0, $urls[0]['priority']);
        self::assertSame('https://beta.example', $urls[array_key_last($urls)]['url']);
    }

    public function testUpdateCountriesRejectsInvalidCodes(): void
    {
        $service = $this->createService();

        $this->expectException(InvalidArgumentException::class);
        $service->updateCountries(['US', 'INVALID']);
    }

    public function testUpdateCountriesStoresNormalizedCodes(): void
    {
        $service = $this->createService();
        $service->updateCountries(['us', 'de', 'uk']);

        $countries = $service->getState()['countries'];
        $codes = array_column($countries, 'code');

        self::assertSame(['US', 'DE', 'UK'], $codes);
    }

    private function createService(): DashboardService
    {
        $storage = new JsonFileStorage($this->dataFile, DashboardService::getDefaultState());

        return new DashboardService($storage);
    }
}
