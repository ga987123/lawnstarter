<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Behat\Behat\Context\Context;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\Assert;

final class ApiContext implements Context
{
    private string $baseUrl;
    private ?int $responseStatusCode = null;
    private ?string $responseBody = null;

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->baseUrl = rtrim(
            getenv('ACCEPTANCE_BASE_URL') ?: (string) ($parameters['base_url'] ?? 'http://localhost:8080'),
            '/'
        );
    }

    /**
     * @When I send a GET request to :path
     */
    public function iSendAGetRequestTo(string $path): void
    {
        $url = $this->baseUrl . $path;
        $client = new Client([
            'http_errors' => false,
            'verify' => false,
        ]);

        try {
            $response = $client->get($url);
            $this->responseStatusCode = $response->getStatusCode();
            $this->responseBody = (string) $response->getBody();
        } catch (GuzzleException $e) {
            throw new \RuntimeException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @Then the response status code should be :code
     */
    public function theResponseStatusCodeShouldBe(string $code): void
    {
        $code = (int) $code;
        Assert::assertNotNull($this->responseStatusCode, 'No response received. Did you send a request?');
        Assert::assertSame($code, $this->responseStatusCode, sprintf(
            'Expected status code %d but got %d. Response body: %s',
            $code,
            $this->responseStatusCode,
            $this->responseBody ?? ''
        ));
    }

    /**
     * @Then the response body should contain JSON :path equal to :value
     */
    public function theResponseBodyShouldContainJsonEqualTo(string $path, string $value): void
    {
        Assert::assertNotNull($this->responseBody, 'No response body.');
        $data = json_decode($this->responseBody, true);
        Assert::assertIsArray($data, 'Response body is not valid JSON: ' . $this->responseBody);

        $actual = $this->getValueByPath($data, $path);
        $expected = $this->castValue($value);
        Assert::assertSame($expected, $actual, sprintf(
            'Expected JSON path "%s" to equal %s but got %s',
            $path,
            json_encode($expected),
            json_encode($actual)
        ));
    }

    /**
     * @Then the response body should contain JSON key :path
     */
    public function theResponseBodyShouldContainJsonKey(string $path): void
    {
        Assert::assertNotNull($this->responseBody, 'No response body.');
        $data = json_decode($this->responseBody, true);
        Assert::assertIsArray($data, 'Response body is not valid JSON: ' . $this->responseBody);

        $value = $this->getValueByPath($data, $path);
        Assert::assertNotSame(
            self::UNDEFINED,
            $value,
            sprintf('JSON path "%s" not found in response.', $path)
        );
    }

    private const UNDEFINED = '__UNDEFINED__';

    /**
     * @param array<string, mixed> $data
     * @return mixed
     */
    private function getValueByPath(array $data, string $path)
    {
        $keys = explode('.', $path);
        $current = $data;

        foreach ($keys as $key) {
            if (! is_array($current) || ! array_key_exists($key, $current)) {
                return self::UNDEFINED;
            }
            $current = $current[$key];
        }

        return $current;
    }

    private function castValue(string $value): mixed
    {
        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }
        if ($value === 'true') {
            return true;
        }
        if ($value === 'false') {
            return false;
        }
        return $value;
    }
}
