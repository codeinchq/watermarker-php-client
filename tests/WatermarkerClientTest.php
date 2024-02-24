<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace CodeInc\WatermarkerClient\Tests;

use CodeInc\WatermarkerClient\ConvertOptions;
use CodeInc\WatermarkerClient\Exception;
use CodeInc\WatermarkerClient\Format;
use CodeInc\WatermarkerClient\Position;
use CodeInc\WatermarkerClient\WatermarkerClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * Class WatermarkerClientTest
 *
 * @see WatermarkerClient
 */
final class WatermarkerClientTest extends TestCase
{
    private const string DEFAULT_WATERMARKER_BASE_URL = 'http://localhost:3000';
    private const string TEST_IMG_PATH = __DIR__.'/assets/doc.png';
    private const string TEST_WATERMARK_PATH = __DIR__.'/assets/watermark.png';
    private const string TEST_RESULT_PATH = __DIR__.'/assets/watermarked.jpg';

    /**
     * @throws Exception
     */
    public function testWatermarkWithoutOptions(): void
    {
        $client = $this->getNewClient();
        $stream = $client->apply(
            $client->createStreamFromFile(self::TEST_IMG_PATH),
            $client->createStreamFromFile(self::TEST_WATERMARK_PATH)
        );
        $this->assertInstanceOf(StreamInterface::class, $stream, 'The returned value is not a stream');
        $imageContent = (string)$stream;
        $this->assertStringContainsString('PNG', $imageContent, 'The image is not a PNG');
    }

    /**
     * @throws Exception
     */
    public function testWatermarkWithOptions(): void
    {
        $this->assertIsWritable(dirname(self::TEST_RESULT_PATH), 'The result file is not writable');

        $client = $this->getNewClient();

       $stream = $client->apply(
            $client->createStreamFromFile(self::TEST_IMG_PATH),
            $client->createStreamFromFile(self::TEST_WATERMARK_PATH),
            new ConvertOptions(
                size: 50,
                position: Position::topLeft,
                format: Format::jpg,
                quality: 100,
                blur: 3,
                opacity: 30
            )
        );

        $this->assertInstanceOf(StreamInterface::class, $stream, 'The returned value is not a stream');

        $client->saveStreamToFile($stream, self::TEST_RESULT_PATH);
        $this->assertFileExists(self::TEST_RESULT_PATH, 'The result file does not exist');
        $this->assertStringContainsString('JFIF', file_get_contents(self::TEST_RESULT_PATH), 'The image is not a JPEG');

        unlink(self::TEST_RESULT_PATH);
    }

    private function getNewClient(): WatermarkerClient
    {
        $apiBaseUrl = self::DEFAULT_WATERMARKER_BASE_URL;
        if (defined('WATERMARKER_BASE_URL')) {
            $apiBaseUrl = constant('WATERMARKER_BASE_URL');
        }
        return new WatermarkerClient($apiBaseUrl);
    }
}