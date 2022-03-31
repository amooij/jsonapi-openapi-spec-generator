<?php
declare(strict_types=1);

namespace PrinsFrank\JsonapiOpenapiSpecGenerator\Attributes\Server;

use PrinsFrank\JsonapiOpenapiSpecGenerator\Attributes\OpenApiAttribute;

interface OpenApiServerAttribute extends OpenApiAttribute
{
    public function description(): string;

    public function objectId(): string;
}