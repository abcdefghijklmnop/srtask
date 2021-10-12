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

/**
 * Basic controller class
 */
final class Home
{
    private const API_NAME = 'Secret Server Coding Task with SLIM 4';

    private const API_VERSION = '1.0';

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Return a boolean based on the parameter to decide the data is expired
     * @param array $array
     * @return bool boolean
     */
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

    /**
     * Get the secret data with a hash(guid) GET parameter
     * @param Request request: object
     * @param Response response: object
     * @param array $args: an array that contain get parameters
     * @return Response response: object
     */
    public function getSecret(Request $request, Response $response, array $args): Response
    {
        if (isset($args['hash'])) {
            $model = new SecretModel($this->container->get('db'));
            $isSuccess = $model->getSecretByHash($args['hash']);

            if ($model->hashRecord && !$this->isExpired($model->hashRecord)) {
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

    /**
     * Get the posted data, generate a GUID and save it all to the database
     * @param Request request: object
     * @param Response response: object
     * @return Response response: object
     */
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

    /**
     * Invoke the properly response helper function based on type parameter
     * @param string $type: the string 
     * @param int $code: html status code
     * @param mixed $message: the response data
     * @param Response $response: the response object
     * @return Response: Response object
     */
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

    /**
     * Return an xml format string from an array
     * @param array: an array
     * @return mixed xml: an xml string or false
     */
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

    /**
     * Return an xml format string from an array
     * @param xml: a SimpleXMLElement
     * @return mixed: an associative array or boolean or null
     */
    private function xml2array($xml)
    {
        return json_decode(json_encode($xml), true);
    }
}
