# watermarker PHP client

This repository contains a PHP 8.2+ client library for watermarking images using the [watermarker](https://github.com/codeinchq/watermarker) service.

## Installation

The recommended way to install the library is through [Composer](http://getcomposer.org):

```bash
composer require codeinc/watermarker-client
```

## Usage

This client requires a running instance of the [watermarker](https://github.com/codeinchq/watermarker) service. The service can be run locally [using Docker](https://hub.docker.com/r/codeinchq/watermarker) or deployed to a server.

### Examples

#### A simple scenario to apply a watermark to an image and display the result:
```php
use CodeInc\WatermarkerClient\WatermarkerClient;
use CodeInc\WatermarkerClient\Exception;

$apiBaseUri = 'http://localhost:3000/';
$anImage = '/path/to/local/image.png';
$theWatermark = '/path/to/local/watermark.png';

try {
    $client = new WatermarkerClient($apiBaseUri);

    // apply the watermark
    $watermarkedImageStream = $client->apply(
        $client->createStreamFromFile($anImage),
        $client->createStreamFromFile($theWatermark),
    );
    
    // display the watermarked image
    header('Content-Type: image/png');
    echo (string)$watermarkedImageStream;
}
catch (Exception $e) {
    // handle exception
}
```

#### A mire complex scenario to apply a watermark to an image with options and save the result to a file:
```php
use CodeInc\WatermarkerClient\WatermarkerClient;
use CodeInc\WatermarkerClient\ConvertOptions;
use CodeInc\WatermarkerClient\Position;
use CodeInc\WatermarkerClient\Format;

$apiBaseUri = 'http://localhost:3000/';
$theImageStream = '/path/to/local/image.png';
$theWatermarkStream = '/path/to/local/watermark.png';
$theDestinationFile = '/path/to/local/destination.png';
$convertOption = new ConvertOptions(
    size: 50,
    position: Position::topRight,
    format: Format::jpg,
    quality: 80,
    blur: 3,
    opacity: 75
);

try {
    $streamFactory = Psr17FactoryDiscovery::findStreamFactory();
    $client = new WatermarkerClient($apiBaseUri);

    // apply the watermark
    $watermarkedImageStream = $client->apply(
        $client->createStreamFromFile($theImageStream),
        $client->createStreamFromFile($theWatermarkStream),
        $convertOption
    );
    
    // save the watermarked image
    $client->saveStreamToFile($watermarkedImageStream, $theDestinationFile);
}
catch (Exception $e) {
    // handle exception
}
```

## License

The library is published under the MIT license (see [`LICENSE`](LICENSE) file).