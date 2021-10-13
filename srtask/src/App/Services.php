<?php

declare(strict_types=1);

namespace services;

use \DateTimeInterface;
use \DateTime;
use \DateTimeZone;
use \PDO;

/**
 * Database model
 */
final class SecretModel
{
    private PDO $db;
    public $insertedTime = null;
    public $hashRecord = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Set the current time
     * @return
     */
    public function setInsertedTime() {
        $this->insertedTime = new DateTime();
        //$this->insertedTime = gmdate('Y-m-d\TH:i:s\Z', $this->insertedTime->format('U'));
        $this->insertedTime = $this->dateTo8601Zulu($this->insertedTime);
        //$this->insertedTime = $this->insertedTime->format(DateTime::W3C);
        return $this->insertedTime;
    }

    /**
     * Insert a record to the database with a given GUID and a generated time
     * @var string $this->insertedTime
     * @param string $guid
     * @param array $data
     * @return bool
     */
    public function insertByHashName($guid, array $data): bool
    {
        // $this->insertedTime = new DateTime();
        // //$this->insertedTime = gmdate('Y-m-d\TH:i:s\Z', $this->insertedTime->format('U'));
        // $this->insertedTime = $this->dateTo8601Zulu($this->insertedTime);
        // //$this->insertedTime = $this->insertedTime->format(DateTime::W3C);

        $stmt = $this->db->prepare("INSERT INTO secret(hash,secretText,createdAt,expiresAt,remainingViews)
                              VALUES('$guid',:secretText,:createdAt,:expiresAt,:remainingViews) ");
        $stmt->bindParam(':secretText', $data['secret'], PDO::PARAM_STR);
        $stmt->bindParam(':createdAt', $this->insertedTime, PDO::PARAM_STR);
        $stmt->bindParam(':expiresAt', $data['expireAfter'], PDO::PARAM_STR);
        $stmt->bindParam(':remainingViews', $data['expireAfterViews'], PDO::PARAM_STR);
        return $stmt->execute();
    }

    /**
     * Change the DateTime format with Zulu time zone designator
     * @param DateTime $date
     * @return string (cloned) $date
     */
    private function dateTo8601Zulu(DateTime $date): string
    {
        return (clone $date)
            ->setTimezone(new DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s\Z'); //e,O,P,T;
    }

    /**
     * Get the secret data by hash param
     * @param string $hash
     * @return bool $isSuccess
     */
    public function getSecretByHash($hash)
    {
        $stmt = $this->db->prepare("SELECT * FROM secret WHERE hash = ? ");
        $isSuccess = $stmt->execute([$hash]);

        if ($isSuccess) {
            $this->hashRecord = $stmt->fetch();
        }

        return $isSuccess;
    }

    /**
     * Get the secret data is exists by hash param
     * @param string $hash
     * @return bool
     */
    public function isExistsSecretByHash($hash)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM secret WHERE hash = ? ");
        $isSuccess = $stmt->execute([$hash]);

        if ($isSuccess) {
            return ((int)$stmt->fetchColumn(0)) > 0;
        }

        return $isSuccess;
    }

    /**
     * Delete the secret data by hash param
     * @param string $hash
     * @return bool
     */
    public function deleteSecretByHash($hash)
    {
        $stmt = $this->db->prepare("DELETE FROM secret WHERE hash = ? ");
        return $stmt->execute([$hash]);
    }

    /**
     * Decrement the viewable number by 1 with specific hash
     * @param string $hash
     * @return bool
     */
    public function decrementRemainingViews($hash)
    {
        $stmt = $this->db->prepare("UPDATE secret
                              SET remainingViews = remainingViews - 1 
                              WHERE hash = ? ");
        return $stmt->execute([$hash]);
    }
}
