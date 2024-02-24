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

/**
 * watermarker convert options.
 *
 * @author Joan Fabrégat <joan@codeinc.co>
 * @package CodeInc\WatermarkerClient
 * @license MIT <https://opensource.org/licenses/MIT>
 */
final readonly class ConvertOptions
{
    /**
     * @see https://github.com/codeinchq/watermarker?tab=readme-ov-file#usage
     * @param int $size The size of the watermark in relation to the image in percentage. The value must be an integer. The default value is 75.
     * @param Position $position The position of the watermark. The default value is center.
     * @param Format $format The format of the output image. The default value is png.
     * @param int $quality The quality of the output image. The value must be an integer between 0 and 100. The default value is 100.
     * @param int|null $blur The blur radius of underlying image. The value must be an integer.
     * @param int|null $opacity The opacity of the watermark. The value must be an integer between 0 and 100.
     */
    public function __construct(
        public int $size = 75,
        public Position $position = Position::center,
        public Format $format = Format::png,
        public int $quality = 100,
        public ?int $blur = null,
        public ?int $opacity = null,
    ) {
    }
}