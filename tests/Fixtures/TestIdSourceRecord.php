<?php

namespace Cskiller\FilamentIdGenerator\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestIdSourceRecord extends Model
{
    use HasFactory;

    protected $table = 'test_id_source_records';

    protected $fillable = [
        'name',
        'email',
        'avatar_path',
    ];

    protected static function newFactory(): TestIdSourceRecordFactory
    {
        return TestIdSourceRecordFactory::new();
    }
}
