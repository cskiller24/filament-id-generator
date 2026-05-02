{{-- @deprecated Superseded by editor view --}}
<x-filament-panels::page>
    <div
        x-data="idTemplateEditor({
            layouts: @js($this->layouts),
            sides: @js($this->sides),
            sampleValues: @js($this->sampleValues),
            adapterFields: @js($this->adapterFields),
            wireModel: @entangle('layouts').live,
            activeSide: @entangle('activeSide').live,
        })"
        class="space-y-6"
    >
        <div
            x-show="sides.length === 0"
            class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900"
        >
            Upload at least a front source image/PDF on the template edit page, save it, then return here to edit layout.
        </div>
    </div>
</x-filament-panels::page>
