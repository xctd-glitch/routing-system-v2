<?php

declare(strict_types=1);

namespace SRP\Storage;

use JsonException;
use RuntimeException;

final class JsonFileStorage
{
    private string $filePath;

    /**
     * @var array<mixed>
     */
    private array $defaultData;

    /**
     * @param array<mixed> $defaultData
     */
    public function __construct(string $filePath, array $defaultData = [])
    {
        $this->filePath = $filePath;
        $this->defaultData = $defaultData;
    }

    /**
     * @return array<mixed>
     */
    public function read(): array
    {
        if (!file_exists($this->filePath)) {
            $this->write($this->defaultData);

            return $this->defaultData;
        }

        $contents = file_get_contents($this->filePath);
        if ($contents === false) {
            throw new RuntimeException('Unable to read the data store.');
        }

        $trimmed = trim($contents);
        if ($trimmed === '') {
            $this->write($this->defaultData);

            return $this->defaultData;
        }

        try {
            $data = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to decode the data store.', 0, $exception);
        }

        if (!is_array($data)) {
            throw new RuntimeException('Invalid data structure in the data store.');
        }

        return $data;
    }

    /**
     * @param array<mixed> $data
     */
    public function write(array $data): void
    {
        try {
            $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to encode data for storage.', 0, $exception);
        }

        $directory = dirname($this->filePath);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new RuntimeException('Unable to create the data directory.');
            }
        }

        $handle = fopen($this->filePath, 'c+');
        if ($handle === false) {
            throw new RuntimeException('Unable to open the data store for writing.');
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new RuntimeException('Unable to lock the data store for writing.');
            }

            if (!ftruncate($handle, 0) || fseek($handle, 0) !== 0) {
                throw new RuntimeException('Unable to prepare the data store for writing.');
            }

            $bytesWritten = fwrite($handle, $encoded);
            if ($bytesWritten === false || $bytesWritten < strlen($encoded)) {
                throw new RuntimeException('Unable to write data to the store.');
            }

            fflush($handle);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
