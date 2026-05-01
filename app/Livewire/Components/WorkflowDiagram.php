<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Laraflow\Contracts\WorkflowRegistryInterface;
use Laraflow\Data\WorkflowDefinition;
use Livewire\Component;

class WorkflowDiagram extends Component
{
    public string $workflowName;

    /** @var array<string> */
    public array $activePlaces = [];

    public string $accent = 'sky';

    /**
     * @param  array<string>  $activePlaces
     */
    public function mount(string $workflowName, array $activePlaces = [], string $accent = 'sky'): void
    {
        $this->workflowName = $workflowName;
        $this->activePlaces = $activePlaces;
        $this->accent = $accent;
    }

    public function render()
    {
        $registry = app(WorkflowRegistryInterface::class);
        $workflow = $registry->get($this->workflowName);
        $diagram = $this->buildFlowchart($workflow->definition, $this->activePlaces, $this->accent);

        return view('livewire.components.workflow-diagram', [
            'diagram' => $diagram,
        ]);
    }

    /**
     * @param  array<string>  $active
     */
    private function buildFlowchart(WorkflowDefinition $definition, array $active, string $accent): string
    {
        // Per-accent color set so each workflow gets its own visual identity.
        $palette = match ($accent) {
            'emerald' => [
                'active' => 'fill:#a7f3d0,stroke:#059669,stroke-width:3px,color:#064e3b,font-weight:700',
                'done'   => 'fill:#bbf7d0,stroke:#16a34a,stroke-width:2px,color:#14532d',
            ],
            'orange' => [
                'active' => 'fill:#fed7aa,stroke:#ea580c,stroke-width:3px,color:#7c2d12,font-weight:700',
                'done'   => 'fill:#fde68a,stroke:#d97706,stroke-width:2px,color:#78350f',
            ],
            default => [ // sky
                'active' => 'fill:#bae6fd,stroke:#0284c7,stroke-width:3px,color:#075985,font-weight:700',
                'done'   => 'fill:#e0e7ff,stroke:#4f46e5,stroke-width:2px,color:#312e81',
            ],
        };

        $lines = [];
        $lines[] = 'flowchart LR';

        foreach ($definition->places as $place) {
            $description = $place->metadata['description'] ?? null;
            $label = $description ? "{$place->name}\\n{$description}" : $place->name;
            $lines[] = "    {$place->name}([\"{$label}\"])";
        }

        foreach ($definition->transitions as $t) {
            $label = $t->name;
            if ($t->guard !== null) {
                $label .= " ({$t->guard})";
            }
            $quoted = '"' . str_replace('"', '#quot;', $label) . '"';

            foreach ($t->froms as $from) {
                foreach ($t->tos as $to) {
                    $lines[] = "    {$from} -->|{$quoted}| {$to}";
                }
            }
        }

        $lines[] = '';
        $lines[] = '    classDef base fill:#fafafa,stroke:#a1a1aa,stroke-width:1px,color:#27272a';
        $lines[] = "    classDef active {$palette['active']}";
        $lines[] = "    classDef done {$palette['done']}";
        $lines[] = '    classDef rejected fill:#fecdd3,stroke:#e11d48,stroke-width:3px,color:#9f1239,font-weight:700';

        $allPlaceNames = array_map(fn ($p) => $p->name, $definition->places);
        $lines[] = '    class ' . implode(',', $allPlaceNames) . ' base';

        // Terminal "negative" places we always paint rose.
        $negatives = ['cancelled', 'failed', 'refunded'];

        foreach ($active as $place) {
            $class = match (true) {
                in_array($place, $negatives, true) => 'rejected',
                str_ends_with($place, '_approved'), in_array($place, ['delivered', 'completed', 'captured', 'ready'], true) => 'done',
                default => 'active',
            };
            $lines[] = "    class {$place} {$class}";
        }

        return implode("\n", $lines) . "\n";
    }
}
