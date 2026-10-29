<?php

namespace Laravolt\Camunda\Tests;

/**
 * Base class for tests that talk to a real Camunda REST API.
 *
 * Every subclass must also carry #[Group('integration')] (PHPUnit does not inherit
 * class-level attributes). When CAMUNDA_URL is empty the test is skipped before the
 * application is booted, so `composer test` / `--exclude-group integration` and a plain
 * `vendor/bin/phpunit` both stay green without a Camunda server.
 */
abstract class IntegrationTestCase extends TestCase
{
    protected function setUp(): void
    {
        if (! static::camundaConfigured()) {
            $this->markTestSkipped('CAMUNDA_URL is empty; integration test needs a running Camunda REST API.');
        }

        parent::setUp();
    }

    public static function camundaConfigured(): bool
    {
        $url = $_ENV['CAMUNDA_URL'] ?? $_SERVER['CAMUNDA_URL'] ?? getenv('CAMUNDA_URL');

        return is_string($url) && trim($url) !== '';
    }
}
