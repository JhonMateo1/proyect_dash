<?php

require_once MODEL_PATH . '/database.php';

class UserModel
{
    private $jsonFile;

    public function __construct()
    {
        $this->jsonFile = DATA_PATH . '/users.json';

        if (!file_exists($this->jsonFile)) {
            file_put_contents($this->jsonFile, json_encode([]));
        }
    }

    public function create($data)
    {
        // --- GUARDAR EN JSON ---
        $users = json_decode(file_get_contents($this->jsonFile), true);
        $record = [];

        $rawPassword = (string)($data['password'] ?? '');
        $mustChangePassword = (bool)($data['must_change_password'] ?? false);

        $record['id'] = uniqid();
        $record['name'] = isset($data['full_name']) ? $data['full_name'] : ($data['name'] ?? '');
        $record['email'] = $data['email'] ?? '';
        $record['phone'] = $data['phone'] ?? '';
        $record['type'] = $data['type'] ?? '';
        $record['department'] = $data['department'] ?? '';
        $record['position'] = $data['position'] ?? '';
        $record['hired_at'] = $data['hired_at'] ?? '';
        $record['status'] = $data['status'] ?? '';
        $record['photo_url'] = $data['photo_url'] ?? null;
        if ($rawPassword !== '') {
            $record['password'] = password_hash($rawPassword, PASSWORD_DEFAULT);
            $record['must_change_password'] = $mustChangePassword;
        }
        $record['created_at'] = date('Y-m-d H:i:s');

        $users[] = $record;

        file_put_contents($this->jsonFile, json_encode($users, JSON_PRETTY_PRINT));

        // Intentar guardar en MySQL si la tabla existe, pero no romper si hay error
        try {
            $db = Database::connect();

            $sql = "INSERT INTO users (id, name, email, phone, type, department, position, hired_at, status, photo_url, created_at)
                    VALUES (:id, :name, :email, :phone, :type, :department, :position, :hired_at, :status, :photo_url, :created_at)";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id' => $record['id'],
                ':name' => $record['name'],
                ':email' => $record['email'],
                ':phone' => $record['phone'],
                ':type' => $record['type'],
                ':department' => $record['department'],
                ':position' => $record['position'],
                ':hired_at' => $record['hired_at'],
                ':status' => $record['status'],
                ':photo_url' => $record['photo_url'],
                ':created_at' => $record['created_at']
            ]);
        } catch (Exception $e) {
            // registrar error en auditoría o ignorar para no romper flujo
        }
    }

    public function findByEmail($email)
    {
        $users = json_decode(file_get_contents($this->jsonFile), true);

        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }

        return null;
    }

    public function updatePasswordByEmail($email, $newPassword)
    {
        $users = json_decode(file_get_contents($this->jsonFile), true);
        $updated = false;

        foreach ($users as &$user) {
            if (($user['email'] ?? null) === $email) {
                $user['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                $user['must_change_password'] = false;
                $updated = true;
                break;
            }
        }
        unset($user);

        if ($updated) {
            file_put_contents($this->jsonFile, json_encode($users, JSON_PRETTY_PRINT));
        }

        return $updated;
    }

    public function findById($id)
    {
        $users = json_decode(file_get_contents($this->jsonFile), true) ?? [];
        foreach ($users as $user) {
            if (($user['id'] ?? null) === $id) {
                return $user;
            }
        }
        return null;
    }

    public function update($id, $data)
    {
        $users = json_decode(file_get_contents($this->jsonFile), true) ?? [];
        $updated = false;
        $updatedUser = null;

        foreach ($users as &$user) {
            if (($user['id'] ?? null) === $id) {
                // Actualizar campos
                $user['name'] = isset($data['full_name']) ? $data['full_name'] : ($data['name'] ?? $user['name']);
                $user['email'] = $data['email'] ?? $user['email'];
                $user['phone'] = $data['phone'] ?? $user['phone'];
                $user['type'] = $data['type'] ?? $user['type'];
                $user['department'] = $data['department'] ?? $user['department'];
                $user['position'] = $data['position'] ?? $user['position'];
                $user['hired_at'] = $data['hired_at'] ?? $user['hired_at'];
                $user['status'] = $data['status'] ?? $user['status'];
                $updated = true;
                $updatedUser = $user;
                break;
            }
        }
        unset($user);

        if ($updated) {
            file_put_contents($this->jsonFile, json_encode($users, JSON_PRETTY_PRINT));

            // Intentar actualizar en MySQL
            try {
                $db = Database::connect();
                $sql = "UPDATE users SET name=:name, email=:email, phone=:phone, type=:type, department=:department, position=:position, hired_at=:hired_at, status=:status WHERE id=:id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':id' => $id,
                    ':name' => $updatedUser['name'] ?? '',
                    ':email' => $updatedUser['email'] ?? '',
                    ':phone' => $updatedUser['phone'] ?? '',
                    ':type' => $updatedUser['type'] ?? '',
                    ':department' => $updatedUser['department'] ?? '',
                    ':position' => $updatedUser['position'] ?? '',
                    ':hired_at' => $updatedUser['hired_at'] ?? '',
                    ':status' => $updatedUser['status'] ?? ''
                ]);
            } catch (Exception $e) {
                // ignorar errores de base de datos
            }
        }

        return $updated;
    }
}
