<?php

namespace Cskiller\FilamentIdGenerator\Enums;

enum IdGenerationStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}
