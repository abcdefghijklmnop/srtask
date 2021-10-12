<?php

declare(strict_types=1);

namespace App\Helper;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;

final class XmlResponse
{
    public static function withXml(
        Response $response,
        string $data,
        int $status = StatusCodeInterface::STATUS_OK
    ): Response {
        $response->getBody()->write($data);

        return $response
            ->withHeader('Content-Type', 'application/xml')
            ->withStatus($status);
        //->withHeader('Content-Type', 'text/xml')
    }
}
