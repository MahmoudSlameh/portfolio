<?php

namespace App\Ai;

use App\Support\Templates\TemplateDefinition;

/**
 * What the owner asked for, turned into the user message for the TemplateDesigner agent.
 */
final readonly class TemplateBrief
{
    /**
     * @param  array<string, mixed>|null  $startSpec  The spec of a studio template to start from
     * @param  TemplateDefinition|null  $startTemplate  A built-in template to take inspiration from
     */
    public function __construct(
        public string $name,
        public string $prompt,
        public ?array $startSpec = null,
        public ?TemplateDefinition $startTemplate = null,
        public int $images = 0,
        public bool $refine = false,
    ) {}

    public function toPrompt(): string
    {
        $parts = [$this->refine
            ? "Revise the portfolio template \"{$this->name}\": apply the owner's requested changes to its current spec and keep everything else as it is."
            : "Design a portfolio template named \"{$this->name}\"."];
        $prompt = trim($this->prompt);

        $parts[] = $prompt !== ''
            ? ($this->refine ? 'The requested changes' : "The owner's request").":\n<request>\n{$prompt}\n</request>"
            : 'The owner gave no written request; follow the reference images.';

        if ($this->images > 0) {
            $parts[] = $this->images === 1
                ? 'One reference image is attached. Take its palette, typography, density and layout ideas; never copy its text or logos.'
                : "{$this->images} reference images are attached. Combine their palette, typography, density and layout ideas; never copy their text or logos.";
        }

        if ($this->startSpec !== null) {
            $json = json_encode($this->startSpec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $parts[] = ($this->refine ? 'The current spec' : 'Start from this existing spec and change what the request asks for; keep the rest').":\n<start-spec>\n{$json}\n</start-spec>";
        } elseif ($this->startTemplate !== null) {
            $parts[] = "Take inspiration from the built-in template \"{$this->startTemplate->label}\": {$this->startTemplate->description}";
        }

        $parts[] = 'Return the complete spec in "spec" and one or two sentences about the design in "summary".';

        return implode("\n\n", $parts);
    }
}
