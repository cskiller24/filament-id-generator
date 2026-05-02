<?php

namespace Cskiller\FilamentIdGenerator\Enums;

enum IdTextOverflowMode: string
{
    case Wrap = 'wrap';
    case Clip = 'clip';
}
