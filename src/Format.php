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
 * watermarker convert output format.
 *
 * @author Joan Fabrégat <joan@codeinc.co>
 * @package CodeInc\WatermarkerClient
 * @license MIT <https://opensource.org/licenses/MIT>
 */
enum Format: string
{
    case jpg = 'jpg';
    case png = 'png';
    case gif = 'gif';
}