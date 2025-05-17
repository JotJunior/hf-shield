<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Swagger\Annotation as SA;
use Psr\Container\ContainerInterface;

#[SA\Info(
    version: '1.0',
    description: 'HF-Shield Base Admin',
    title: 'HF-Shield',
    license: new SA\License(name: 'Private license')
)]
#[SA\SecurityScheme(
    securityScheme: 'shieldBearerAuth',
    type: 'http',
    in: 'header',
    scheme: 'bearer'
)]
abstract class AbstractController
{
    protected const DESCRIPTION_APPLICATION_ERROR = 'Application error';

    protected const DESCRIPTION_BAD_REQUEST = 'Bad Request';

    protected const DESCRIPTION_FORBIDDEN_ACCESS = 'Forbidden access';

    protected const DESCRIPTION_PAGE_NUMBER = 'Page number for pagination';

    protected const DESCRIPTION_PAGE_RESULTS_FIELDS = 'Fields to include in the response';

    protected const DESCRIPTION_PAGE_RESULTS_PER_PAGE = 'Number of results per page';

    protected const DESCRIPTION_PAGE_RESULTS_SORT = 'Sort results by a specific fields';

    protected const DESCRIPTION_UNAUTHORIZED_ACCESS = 'Unauthorized access';

    protected const DESCRIPTION_RESPONSE_ERROR_RESULT = 'Response result property';

    protected const DESCRIPTION_RESPONSE_ERROR_MESSAGE = 'Friendly error message';

    protected const DESCRIPTION_RESPONSE_ERROR_JSON = 'Response Json content';

    protected const QUERY_PAGE_NUMBER = '_page';

    protected const QUERY_RESULTS_PER_PAGE = '_per_page';

    protected const QUERY_RESULT_FIELDS = '_fields';

    protected const QUERY_SORT = '_sort';

    protected const DESCRIPTION_PARAMETER_ID = 'Unique record identifier';

    protected const EXAMPLE_PARAMETER_ID = '9fb949e3-2ecf-4d21-8130-a009509da939';

    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;
}
