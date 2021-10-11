<?php

//declare(strict_types=1);

namespace App\Controller;

use App\Helper\JsonResponse;
use Pimple\Psr11\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \DateTime;
use services\models\Secret;
//use \Sujip\Guid\Guid as Guid;

require __DIR__ . "/../App/Services.php";

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
            $model = new Secret();
            $isSuccess = $model->getSecretByHash($args['hash']);
            // $db = $this->container->get('db');
            // $stmt = $db->prepare("SELECT * FROM secret WHERE hash = ? ");
            // $isSuccess = $stmt->execute([$args['hash']]);
            // $message = $stmt->fetch();
            if (!$this->isExpired($model->hashRecord)) {
                $isSuccess = $model->decrementRemainingViews($args['hash']);
                $message = $model->hashRecord;
                // $db = $this->container->get('db');
                // $stmt = $db->prepare("UPDATE secret
                //                       SET remainingViews = remainingViews - 1 
                //                       WHERE hash = ? ");
                // $isSuccess = $stmt->execute([$args['hash']]);
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
            $model = new Secret();
            $isSuccess = $model->insertByHashName($guid, $data);
            // $db = $this->container->get('db');
            // $stmt = $db->prepare("INSERT INTO secret(hash,secretText,createdAt,expiresAt,remainingViews)
            //                   VALUES('$guid',:secretText,:createdAt,:expiresAt,:remainingViews) ");
            // $stmt->bindParam(':secretText', $data['secret'], PDO::PARAM_STR);
            // $stmt->bindParam(':createdAt', $now, PDO::PARAM_STR);
            // $stmt->bindParam(':expiresAt', $data['expireAfter'], PDO::PARAM_STR);
            // $stmt->bindParam(':remainingViews', $data['expireAfterViews'], PDO::PARAM_STR);
            // $isSuccess = $stmt->execute();
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

    // public function getHelp(Request $request, Response $response): Response
    // {
    //     $message = [
    //         'api' => self::API_NAME,
    //         'version' => self::API_VERSION,
    //         'timestamp' => time(),
    //     ];

    //     return JsonResponse::withJson($response, (string) json_encode($message));
    // }

    // public function getStatus(Request $request, Response $response): Response
    // {
    //     $this->container->get('db');
    //     $status = [
    //         'status' => [
    //             'database' => 'OK',
    //         ],
    //         'api' => self::API_NAME,
    //         'version' => self::API_VERSION,
    //         'timestamp' => time(),
    //     ];

    //     return JsonResponse::withJson($response, (string) json_encode($status));
    // }
}
