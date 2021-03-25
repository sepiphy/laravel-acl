<?php

namespace Sepiphy\Laravel\Acl\Contracts;

interface RoleInterface
{
    /**
     * @return int|string
     */
    public function getKey();

    public function getCode(): string;

    public function getName(): string;

    public function getDescription(): ?string;
}