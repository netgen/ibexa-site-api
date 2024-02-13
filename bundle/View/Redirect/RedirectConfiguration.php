<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaSiteApiBundle\View\Redirect;

final class RedirectConfiguration
{
    public function __construct(
        private readonly mixed $target,
        private readonly array $targetParameters,
        private readonly bool $permanent,
        private readonly bool $keepRequestMethod,
        private readonly bool $absolute,
    ) {}

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

    public function getTarget(): mixed
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
