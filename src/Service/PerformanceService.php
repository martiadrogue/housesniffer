<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class PerformanceService
{
    private LoggerInterface $logger;
    private Stopwatch $stopwatch;

    private const LABEL = 'performance';
    private const MEMORY_UNITS = ['B', 'KB', 'MB', 'GB', 'TB',];
    private const BASE = 1024;
    private const MS_IN_S = 1000;

    public function __construct(Stopwatch $stopwatch, LoggerInterface $logger)
    {
        $this->stopwatch = $stopwatch;
        $this->logger = $logger;
    }

    /**
     * Start the performance measurement
     *
     * @return void
     */
    public function start(): void
    {
        $this->stopwatch = new Stopwatch();
        $this->stopwatch->start(self::LABEL);
    }

    /**
     * Stop the performance measurement and return the output
     *
     * @return string The output in the format of duration|memory|lav
     */
    public function stop(): string
    {
        $stopwatchEvent = $this->stopwatch->stop(self::LABEL);

        $output = $this->formatDuration($stopwatchEvent->getDuration()) . '|';
        $output .= $this->formatMemoryUsage($stopwatchEvent->getMemory()) . '|LAV';
        $output .= number_format($this->getCpuUsage(), 3, '.', '');

        $this->logger->info("Performance: $output");

        return $output;
    }

    /**
     * Get the CPU usage as a percentage
     *
     * @return float The CPU usage
     */
    private function getCpuUsage(): float
    {
        $load = sys_getloadavg();
        $cpuCount = shell_exec('nproc');

        return $load[0] / $cpuCount;
    }

    /**
     * Format the memory usage in bytes to a human-readable unit
     *
     * @param integer $bytes The memory usage in bytes
     * @param integer $precision The number of decimal places to round to
     * @return string The formatted memory usage
     */
    private function formatMemoryUsage(int $bytes, int $precision = 2): string
    {
        $bytes = max($bytes, 0);
        $pow = min(floor(log($bytes) / log(self::BASE)), count(self::MEMORY_UNITS) - 1);
        $bytes /= pow(self::BASE, $pow);
        $bytesFormated = round($bytes, $precision);

        return $bytesFormated . self::MEMORY_UNITS[$pow];
    }

    /**
     * Format the duration in milliseconds to a human-readable unit
     *
     * @param integer $milliseconds The duration in milliseconds
     * @return string The formatted duration
     */
    private function formatDuration(int $milliseconds): string
    {
        return gmdate('H:i:s', intval(round($milliseconds / self::MS_IN_S)));
    }
}
