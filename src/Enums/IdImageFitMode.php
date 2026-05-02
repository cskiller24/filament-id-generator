<?php

namespace Cskiller\FilamentIdGenerator\Enums;

enum IdImageFitMode: string
{
    case Contain = 'contain';
    case Cover = 'cover';
    case Stretch = 'stretch';
}
