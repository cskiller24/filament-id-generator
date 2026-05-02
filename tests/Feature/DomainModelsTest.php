<?php

namespace Cskiller\FilamentIdGenerator\Tests\Feature;

use Cskiller\FilamentIdGenerator\Enums\IdTemplateSideType;
use Cskiller\FilamentIdGenerator\Models\IdGenerationBatch;
use Cskiller\FilamentIdGenerator\Models\IdTemplate;
use Cskiller\FilamentIdGenerator\Models\IdTemplateField;
use Cskiller\FilamentIdGenerator\Models\IdTemplateSide;
use Cskiller\FilamentIdGenerator\Tests\Fixtures\TestIdSourceRecord;
use Cskiller\FilamentIdGenerator\Tests\TestCase;

class DomainModelsTest extends TestCase
{
    public function test_template_has_sides_and_batches_relationships(): void
    {
        $template = IdTemplate::factory()->create();
        IdTemplateSide::factory()->for($template, 'template')->create();
        IdGenerationBatch::factory()->for($template, 'template')->create();

        $this->assertCount(1, $template->sides);
        $this->assertCount(1, $template->batches);
    }

    public function test_side_casts_enum_and_has_ordered_fields(): void
    {
        $side = IdTemplateSide::factory()->front()->create();
        IdTemplateField::factory()->for($side, 'side')->create(['z_index' => 2]);
        IdTemplateField::factory()->for($side, 'side')->create(['z_index' => 1]);

        $this->assertSame(IdTemplateSideType::Front, $side->side);
        $this->assertSame([1, 2], $side->fields->pluck('z_index')->all());
    }

    public function test_batch_initiated_by_uses_configured_model(): void
    {
        $operator = TestIdSourceRecord::factory()->create();
        $batch = IdGenerationBatch::factory()->create(['initiated_by' => $operator->id]);

        $this->assertInstanceOf(TestIdSourceRecord::class, $batch->initiatedBy);
        $this->assertSame($operator->id, $batch->initiatedBy->id);
    }
}
