<?php

declare(strict_types=1);

namespace CodeInc\WatermarkerClient;

enum Position: string
{
    case center = 'center';

    case top = 'top';
    case topLeft = 'top-left';
    case topRight = 'top-right';

    case left = 'left';

    case right = 'right';

    case bottom = 'bottom';
    case bottomLeft = 'bottom-left';
    case bottomRight = 'bottom-right';
}