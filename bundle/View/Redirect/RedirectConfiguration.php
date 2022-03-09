<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View\Redirect;

final class RedirectConfiguration
{
    private string $target;
    private array $targetParameters;
    private bool $permanent;
    private bool $keepRequestMethod;
    private bool $absolute;

    public function __construct(
        string $target,
        array $targetParameters,
        bool $permanent,
        bool $keepRequestMethod,
        bool $absolute
    ) {
        $this->target = $target;
        $this->targetParameters = $targetParameters;
        $this->permanent = $permanent;
        $this->keepRequestMethod = $keepRequestMethod;
        $this->absolute = $absolute;
    }

    public static function fromConfigurationArray(array $config): self
    {
        return new self(
            $config['target'],
            $config['target_parameters'],
            $config['permanent'],
            $config['keep_request_method'],
            $config['absolute'],
        );
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

    public function keepRequestMethod(): bool
    {
        return $this->keepRequestMethod;
    }

    public function isAbsolute(): bool
    {
        return $this->absolute;
    }
}
