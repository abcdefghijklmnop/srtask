<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\JsonResponse;
use App\Helper\XmlResponse;
use Pimple\Psr11\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \DateTime;
use services\SecretModel;
use \Sujip\Guid\Guid as Guid;

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

        if (($expiresAt > $createdAt) && ((int)$array['remainingViews'] > 0)) {
            return false;
        }
        return true;
    }

    public function getSecret(Request $request, Response $response, array $args): Response
    {
        if (isset($args['hash'])) {
            $model = new SecretModel($this->container->get('db'));
            $isSuccess = $model->getSecretByHash($args['hash']);

            if (!$this->isExpired($model->hashRecord)) {
                $isSuccess = $model->decrementRemainingViews($args['hash']);
                $message = $model->hashRecord;
            } else {
                $isSuccess = false;
            }
        }

        if ($isSuccess) {
            return $this->sendApi($request->getHeaderLine('Content-Type'), 200, $message, $response);
        }

        $message = ["message" => "Secret not found"];
        return $this->sendApi($request->getHeaderLine('Content-Type'), 404, $message, $response);
    }

    public function postSecret(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        if ($request->getHeaderLine('Content-Type') === 'application/xml') {
            $data = $this->xml2array($data);
        }

        $Guid = new Guid;
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

            return $this->sendApi($request->getHeaderLine('Content-Type'), 200, $message, $response);
        }

        $message = ["message" => "Invalid input"];
        return $this->sendApi($request->getHeaderLine('Content-Type'), 405, $message, $response);
    }

    private function sendApi(string $type, int $code, mixed $message, Response $response): Response
    {
        switch ($type) {
        case 'application/json':
            return JsonResponse::withJson($response, (string) json_encode($message), $code);
                break;
        case 'application/xml':
            return XmlResponse::withXml($response, $this->array2xml($message), $code);
                break;
        default:
            $response->getBody()->write("Type is not matched");
            return $response
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(500);
                break;
        }
    }

    private function array2xml(array $array)
    {
        //This function create a xml object with element root.
        $xml = new \SimpleXMLElement('<root></root>');
        array_walk_recursive(
            $array,
            function ($val, $key) use (&$xml) {
                $xml->addChild((string)$key, (string)$val);
            }
        );
        return $xml->asXML();
    }

    private function xml2array($xml)
    {
        return json_decode(json_encode($xml), true);
    }
}
