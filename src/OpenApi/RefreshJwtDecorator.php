<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model;

final class RefreshJwtDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private readonly OpenApiFactoryInterface $decorated
    ) {
    }

    /**
     * @param mixed[] $context
     * @return OpenApi
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $schemas = $openApi->getComponents()->getSchemas();

        $schemas['RefreshToken'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'refresh_token' => [
                    'type' => 'string',
                    'example' => 'JSON_WEB_TOKEN',
                ],
            ],
        ]);

        $pathItem = new Model\PathItem(
            ref: 'JWT Token Refresh',
            post: new Model\Operation(
                operationId: 'refreshTokenItem',
                tags: ['Tokens Refresh'],
                responses: [
                    '200' => [
                        'description' => 'Refresh expired Token and get new Refresh Token',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Tokens',
                                ],
                            ],
                        ],
                    ],
                ],
                summary: 'Refresh JWT tokens to Authenticate',
                requestBody: new Model\RequestBody(
                    description: 'The updated token and refresh token',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/RefreshToken',
                            ],
                        ],
                    ]),
                ),
                security: [],
            ),
        );
        $openApi->getPaths()->addPath('/refresh_token', $pathItem);

        return $openApi;
    }
}
