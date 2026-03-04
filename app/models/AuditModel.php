<?php

require_once MODEL_PATH . '/database.php';

class AuditModel
{
    private $jsonFile;

    public function __construct()
    {
        $this->jsonFile = DATA_PATH . '/audit.json';

        if (!file_exists($this->jsonFile)) {
            file_put_contents($this->jsonFile, json_encode([]));
        }
    }

    public function log($event, $email, $details = '')
    {
        $data = json_decode(file_get_contents($this->jsonFile), true);

        $log = [
            'event' => $event,
            'email' => $email,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'created_at' => date('Y-m-d H:i:s'),
            'details' => $details
        ];

        // Guardar en JSON
        $data[] = $log;
        file_put_contents($this->jsonFile, json_encode($data, JSON_PRETTY_PRINT));

        // Guardar en MySQL
        $db = Database::connect();

        $sql = "INSERT INTO audit_logs 
                (event, email, ip, user_agent, created_at, details)
                VALUES (:event, :email, :ip, :user_agent, :created_at, :details)";

        $stmt = $db->prepare($sql);
        $stmt->execute($log);
    }
}
