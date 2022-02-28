<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View\Redirect;

final class RedirectConfiguration
{
    private string $target;
    private array $targetParameters;
    private bool $permanent;
    private bool $absolute;

    public function __construct(string $target, array $targetParameters, bool $permanent, bool $absolute)
    {
        $this->target = $target;
        $this->targetParameters = $targetParameters;
        $this->permanent = $permanent;
        $this->absolute = $absolute;
    }

    public static function fromConfigurationArray(array $config): self
    {
        $target = $config['target'];
        $targetParameters = $config['target_parameters'];
        $permanent = $config['permanent'];
        $absolute = $config['absolute'];

        return new self($target, $targetParameters, $permanent, $absolute);
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function getTargetParameters(): array
    {
        return $this->targetParameters;
    }

    public function isPermanent(): bool
    {
        return $this->permanent;
    }

    public function isAbsolute(): bool
    {
        return $this->absolute;
    }
}
