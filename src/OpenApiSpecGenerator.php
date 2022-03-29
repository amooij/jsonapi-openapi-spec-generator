<?php
declare(strict_types=1);

namespace PrinsFrank\JsonapiOpenapiSpecGenerator;

use GoldSpecDigital\ObjectOrientedOAS\OpenApi;
use Illuminate\Foundation\Application;
use LaravelJsonApi\Core\Server\Server;
use LaravelJsonApi\Core\Support\AppResolver;
use PrinsFrank\JsonapiOpenapiSpecGenerator\Components\ComponentsBuilder;
use PrinsFrank\JsonapiOpenapiSpecGenerator\ExternalDocs\ExternalDocsBuilder;
use PrinsFrank\JsonapiOpenapiSpecGenerator\Info\InfoBuilder;
use PrinsFrank\JsonapiOpenapiSpecGenerator\Paths\PathsBuilder;
use PrinsFrank\JsonapiOpenapiSpecGenerator\Security\SecurityBuilder;
use PrinsFrank\JsonapiOpenapiSpecGenerator\Servers\ServersBuilder;
use PrinsFrank\JsonapiOpenapiSpecGenerator\Tags\TagsBuilder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

class OpenApiSpecGenerator
{
    public function __construct(
        private Application $application,
        private TagsBuilder $tagsBuilder,
        private ExternalDocsBuilder $externalDocsBuilder,
        private InfoBuilder $infoBuilder,
        private ServersBuilder $serversBuilder,
        private SecurityBuilder $securityBuilder,
        private PathsBuilder $pathsBuilder,
        private ComponentsBuilder $componentsBuilder
    ) { }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function generate(string $serverName): OpenApi
    {
        $apiVersions = config('jsonapi.servers');
        if (is_array($apiVersions) === false) {
            throw new RuntimeException('No api versions configured for jsonapi.servers configuration key');
        }

        $apiVersionFQN = config('jsonapi.servers.' . $serverName);
        if ($apiVersionFQN === null || class_exists($apiVersionFQN) === false) {
            throw new RuntimeException('Invalid api server "' . $apiVersionFQN . '" for "' . $serverName . '"');
        }

        $appResolver = $this->application->get(AppResolver::class);
        $server = new $apiVersionFQN($appResolver, $serverName);
        if ($server instanceof Server === false) {
            throw new RuntimeException('Server is not an instance of "' . Server::class . '"');
        }

        return OpenApi::create()
            ->openapi(OpenApi::OPENAPI_3_0_2)
            ->tags(...$this->tagsBuilder->build())
            ->externalDocs($this->externalDocsBuilder->build())
            ->info($this->infoBuilder->build())
            ->servers(...$this->serversBuilder->build())
            ->security(...$this->securityBuilder->build())
            ->paths(...$this->pathsBuilder->build())
            ->components($this->componentsBuilder->build());
    }
}