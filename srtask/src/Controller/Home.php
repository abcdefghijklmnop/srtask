<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\JsonResponse;
use Pimple\Psr11\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \DateTime;
use services\SecretModel;
//use \Sujip\Guid\Guid as Guid;

final class Home
{
    private const API_NAME = 'Secret Server Coding Task';

    private const API_VERSION = '1.0';

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    private function isExpired(array $array)
    {
        $datetime = new DateTime();
        $datetime->modify($array['expiresAt']);
        $expiresAt = (int)$datetime->getTimestamp();

        $datetime = new DateTime();
        $datetime->modify($array['createdAt']);
        $createdAt = (int)$datetime->getTimestamp();

        if (($expiresAt > $createdAt) && ((int)$array['remainingViews'] > 0)) return false;
        return true;
    }

    public function getSecret(Request $request, Response $response, array $args): Response
    {
        //$message = $args;
        if (isset($args['hash'])) {
            $model = new SecretModel($this->container->get('db'));
            $isSuccess = $model->getSecretByHash($args['hash']);

            if (!$this->isExpired($model->hashRecord)) {
                $isSuccess = $model->decrementRemainingViews($args['hash']);
                $message = $model->hashRecord;
            } else
                $isSuccess = false;
        }
        if ($isSuccess) {
            return JsonResponse::withJson($response, (string) json_encode($message), 200);
        }

        $message = ["message" => "Secret not found"];
        return JsonResponse::withJson($response, (string) json_encode($message), 404);
    }

    public function postSecret(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $Guid = new \Sujip\Guid\Guid;
        $guid = $Guid->create();

        if ($guid) {
            $model = new SecretModel($this->container->get('db'));
            $isSuccess = $model->insertByHashName($guid, $data);
        }

        if ($isSuccess) {
            $message = [
                "hash" => $guid,
                "secretText" => $data['secret'],
                "createdAt" => $model->insertedTime,
                "expiresAt" => $data['expireAfter'],
                "remainingViews" => $data['expireAfterViews']
            ];

            return JsonResponse::withJson($response, (string) json_encode($message), 200);
        }

        $message = ["message" => "Invalid input"];
        return JsonResponse::withJson($response, (string) json_encode($message), 405);
    }
}
