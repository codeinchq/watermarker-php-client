<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace CodeInc\WatermarkerClient;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Message\MultipartStream\MultipartStreamBuilder;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class WatermarkerClient
 *
 * @package CodeInc\WatermarkerClient
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @link    https://github.com/codeinchq/watermarker-php-client
 * @link    https://github.com/codeinchq/watermarker
 * @license MIT <https://opensource.org/licenses/MIT>
 */
readonly class WatermarkerClient
{
    private ClientInterface $client;
    private StreamFactoryInterface $streamFactory;
    private RequestFactoryInterface $requestFactory;

    /**
     * WatermarkerClient constructor.
     *
     * @param string $baseUrl                              The base URL of the WATERMARKER API.
     * @param ClientInterface|null $client                 The HTTP client (optional, uses the PSR-18 discovery by
     *                                                     default).
     * @param StreamFactoryInterface|null $streamFactory   The stream factory (optional, uses the PSR-17 discovery by
     *                                                     default).
     * @param RequestFactoryInterface|null $requestFactory The request factory (optional, uses the PSR-17 discovery by
     *                                                     default).
     */
    public function __construct(
        private string $baseUrl,
        ClientInterface|null $client = null,
        StreamFactoryInterface|null $streamFactory = null,
        RequestFactoryInterface|null $requestFactory = null,
    ) {
        $this->client = $client ?? Psr18ClientDiscovery::find();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
    }

    /**
     * Applies a watermark to an image using the WATERMARKER API.
     *
     * @param StreamInterface|resource|string $imageStream     The PDF content.
     * @param StreamInterface|resource|string $watermarkStream The watermark content.
     * @param ConvertOptions $options                          The convert options.
     * @return StreamInterface
     * @throws Exception
     */
    public function apply(
        mixed $imageStream,
        mixed $watermarkStream,
        ConvertOptions $options = new ConvertOptions()
    ): StreamInterface {
        try {
            // building the multipart stream
            $multipartStreamBuilder = (new MultipartStreamBuilder($this->streamFactory))
                ->addResource('image', $imageStream)
                ->addResource('watermark', $watermarkStream)
                ->addResource('size', (string)$options->size)
                ->addResource('position', $options->position->value)
                ->addResource('format', $options->format->value)
                ->addResource('quality', (string)$options->quality);

            if ($options->blur !== null) {
                $multipartStreamBuilder->addResource('blur', (string)$options->blur);
            }
            if ($options->opacity !== null) {
                $multipartStreamBuilder->addResource('opacity', (string)$options->opacity);
            }

            // sending the request
            $response = $this->client->sendRequest(
                $this->requestFactory
                    ->createRequest("POST", $this->getEndpointUri('/apply'))
                    ->withHeader(
                        "Content-Type",
                        "multipart/form-data; boundary={$multipartStreamBuilder->getBoundary()}"
                    )
                    ->withBody($multipartStreamBuilder->build())
            );
        } catch (ClientExceptionInterface $e) {
            throw new Exception(
                message: "An error occurred while sending the request to the WATERMARKER API",
                code: Exception::ERROR_REQUEST,
                previous: $e
            );
        }

        // checking the response
        if ($response->getStatusCode() !== 200) {
            throw new Exception(
                message: "The WATERMARKER API returned an error {$response->getStatusCode()}",
                code: Exception::ERROR_RESPONSE,
                previous: new Exception((string)$response->getBody())
            );
        }

        // returning the response
        return $response->getBody();
    }

    /**
     * Opens a local file and creates a stream from it.
     *
     * @param string $path     The path to the file.
     * @param string $openMode The mode used to open the file.
     * @return StreamInterface
     * @throws Exception
     */
    public function createStreamFromFile(string $path, string $openMode = 'r'): StreamInterface
    {
        $f = fopen($path, $openMode);
        if ($f === false) {
            throw new Exception("The file '$path' could not be opened", Exception::ERROR_FILE_OPEN);
        }

        return $this->streamFactory->createStreamFromResource($f);
    }

    /**
     * Saves a stream to a local file.
     *
     * @param StreamInterface $stream
     * @param string $path     The path to the file.
     * @param string $openMode The mode used to open the file.
     * @throws Exception
     */
    public function saveStreamToFile(StreamInterface $stream, string $path, string $openMode = 'w'): void
    {
        $f = fopen($path, $openMode);
        if ($f === false) {
            throw new Exception("The file '$path' could not be opened", Exception::ERROR_FILE_OPEN);
        }

        if (stream_copy_to_stream($stream->detach(), $f) === false) {
            throw new Exception("The stream could not be copied to the file '$path'", Exception::ERROR_FILE_WRITE);
        }

        fclose($f);
    }

    /**
     * Returns an endpoint URI.
     *
     * @param string $endpoint
     * @return string
     */
    private function getEndpointUri(string $endpoint): string
    {
        $url = $this->baseUrl;
        if (str_ends_with($url, '/')) {
            $url = substr($url, 0, -1);
        }
        if (str_starts_with($endpoint, '/')) {
            $endpoint = substr($endpoint, 1);
        }

        return "$url/$endpoint";
    }

    /**
     * Health check to verify the service is running.
     *
     * @return bool Health check response, expected to be "ok".
     */
    public function checkServiceHealth(): bool
    {
        try {
            $response = $this->client->sendRequest(
                $this->requestFactory->createRequest(
                    "GET",
                    $this->getEndpointUri("/health")
                )
            );

            // The response status code should be 200
            if ($response->getStatusCode() !== 200) {
                return false;
            }

            // The response body should be {"status":"up"}
            $responseBody = json_decode((string)$response->getBody(), true);
            return isset($responseBody['status']) && $responseBody['status'] === 'up';
        } catch (ClientExceptionInterface) {
            return false;
        }
    }
}
