<?php

require_once MODEL_PATH . '/UserModel.php';
require_once MODEL_PATH . '/AuditModel.php';

class DashboardController
{
    private function processProfilePhotoUpload()
    {
        if (empty($_FILES['profile_photo']) || ($_FILES['profile_photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $photo = $_FILES['profile_photo'];

        if (($photo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $_SESSION['employee_error_message'] = 'No se pudo cargar la foto de perfil. Intente nuevamente.';
            return null;
        }

        if (($photo['size'] ?? 0) > 2 * 1024 * 1024) {
            $_SESSION['employee_error_message'] = 'La foto de perfil supera el tamaño máximo permitido (2 MB).';
            return null;
        }

        $mimeType = mime_content_type($photo['tmp_name']);
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if (!isset($allowedTypes[$mimeType])) {
            $_SESSION['employee_error_message'] = 'Formato de imagen no válido. Use JPG, PNG, WEBP o GIF.';
            return null;
        }

        $uploadDir = BASE_PATH . '/public/uploads/profiles';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $fileName = 'profile_' . uniqid('', true) . '.' . $allowedTypes[$mimeType];
        $destination = $uploadDir . '/' . $fileName;

        if (!move_uploaded_file($photo['tmp_name'], $destination)) {
            $_SESSION['employee_error_message'] = 'No se pudo guardar la imagen en el servidor.';
            return null;
        }

        return 'uploads/profiles/' . $fileName;
    }

    private function enforcePasswordUpdated()
    {
        $email = $_SESSION['user_id'] ?? null;
        if (!$email) {
            return;
        }

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!empty($user['must_change_password'])) {
            redirect(route('auth', 'changePasswordRequired'));
        }
    }

    private function generateTemporaryPassword()
    {
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';
        $special = '!@#$%^&*';

        $requiredChars = [
            $lower[random_int(0, strlen($lower) - 1)],
            $upper[random_int(0, strlen($upper) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $special[random_int(0, strlen($special) - 1)],
        ];

        $all = $lower . $upper . $digits . $special;
        for ($i = 0; $i < 4; $i++) {
            $requiredChars[] = $all[random_int(0, strlen($all) - 1)];
        }

        shuffle($requiredChars);

        return 'TechSkills' . implode('', $requiredChars);
    }

    public function index()
    {
        authRequired();
        $this->enforcePasswordUpdated();

        $email = $_SESSION['user_id'];

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);
        
        // obtener todos los empleados para listarlos en el dashboard
        $usersFile = DATA_PATH . '/users.json';
        $allUsersRaw = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) ?? [] : [];

        // leer filtros desde GET
        $search = trim($_GET['search'] ?? '');
        $typeFilter = trim($_GET['type'] ?? '');
        $statusFilter = trim($_GET['status'] ?? '');

        // aplicar filtros (si existen)
        $allUsers = [];
        foreach ($allUsersRaw as $u) {
            // búsqueda libre sobre nombre, email y puesto
            if (!empty($search)) {
                $needle = mb_strtolower($search);
                $found = false;
                foreach (['name', 'email', 'position'] as $field) {
                    if (!empty($u[$field]) && mb_strpos(mb_strtolower($u[$field]), $needle) !== false) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) continue;
            }

            // filtro por tipo
            if (!empty($typeFilter)) {
                $uType = trim($u['type'] ?? '');
                if ($uType !== $typeFilter) continue;
            }

            // filtro por estado
            if (!empty($statusFilter)) {
                $uStatus = trim($u['status'] ?? '');
                if ($uStatus !== $statusFilter) continue;
            }

            $allUsers[] = $u;
        }

        // 3. Cargar vista
        require VIEW_PATH . '/dashboard.php';
    }


    public function addEmployee()
    {
        authRequired();
        $this->enforcePasswordUpdated();
        require VIEW_PATH . '/add_employee.php';
    }

    public function saveEmployee()
    {
        authRequired();
        $this->enforcePasswordUpdated();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(route('dashboard', 'index'));
        }

        $payload = [];
        $payload['full_name'] = trim($_POST['full_name'] ?? '');
        $payload['email'] = trim($_POST['email'] ?? '');
        $payload['phone'] = trim($_POST['phone'] ?? '');
        $payload['type'] = trim($_POST['type'] ?? '');
        $payload['department'] = trim($_POST['department'] ?? '');
        $payload['position'] = trim($_POST['position'] ?? '');
        $payload['hired_at'] = trim($_POST['hired_at'] ?? '');
        $payload['status'] = trim($_POST['status'] ?? '');
        $payload['password'] = $this->generateTemporaryPassword();
        $payload['must_change_password'] = true;
        $payload['photo_url'] = $this->processProfilePhotoUpload();

        $userModel = new UserModel();
        $userModel->create($payload);

        $baseMessage = 'Empleado creado exitosamente. Contraseña temporal: ' . $payload['password'] . '. Guarde esta información.';
        if (!empty($_SESSION['employee_error_message'])) {
            $_SESSION['employee_temp_password_message'] = $baseMessage . ' (La foto no pudo guardarse: ' . $_SESSION['employee_error_message'] . ')';
            unset($_SESSION['employee_error_message']);
        } else {
            $_SESSION['employee_temp_password_message'] = $baseMessage;
        }

        redirect(route('dashboard', 'addEmployee'));
    }

    public function logout()
    {
        session_destroy();
        redirect(route('auth', 'login'));
    }

    public function audit()
    {
        authRequired();
        $this->enforcePasswordUpdated();

        $auditModel = new AuditModel();
        $logs = json_decode(file_get_contents(DATA_PATH . '/audit.json'), true);

        require VIEW_PATH . '/audit.php';
    }

    public function viewEmployee()
    {
        authRequired();
        $this->enforcePasswordUpdated();

        $id = trim($_GET['id'] ?? '');
        
        if (empty($id)) {
            redirect(route('dashboard', 'index'));
        }

        $usersFile = DATA_PATH . '/users.json';
        if (!file_exists($usersFile)) {
            redirect(route('dashboard', 'index'));
        }

        $allUsers = json_decode(file_get_contents($usersFile), true) ?? [];
        $user = null;

        foreach ($allUsers as $u) {
            if (!empty($u['id']) && trim($u['id']) === $id) {
                $user = $u;
                break;
            }
        }

        if (!$user) {
            redirect(route('dashboard', 'index'));
        }

        require VIEW_PATH . '/employee_detail.php';
    }

    public function editEmployee()
    {
        authRequired();
        $this->enforcePasswordUpdated();

        $id = trim($_GET['id'] ?? '');
        
        if (empty($id)) {
            redirect(route('dashboard', 'index'));
        }

        $userModel = new UserModel();
        $user = $userModel->findById($id);

        if (!$user) {
            redirect(route('dashboard', 'index'));
        }

        require VIEW_PATH . '/edit_employee.php';
    }

    public function saveEditEmployee()
    {
        authRequired();
        $this->enforcePasswordUpdated();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(route('dashboard', 'index'));
        }

        $id = trim($_POST['id'] ?? '');
        
        if (empty($id)) {
            redirect(route('dashboard', 'index'));
        }

        $payload = [];
        $payload['full_name'] = trim($_POST['full_name'] ?? '');
        $payload['email'] = trim($_POST['email'] ?? '');
        $payload['phone'] = trim($_POST['phone'] ?? '');
        $payload['type'] = trim($_POST['type'] ?? '');
        $payload['department'] = trim($_POST['department'] ?? '');
        $payload['position'] = trim($_POST['position'] ?? '');
        $payload['hired_at'] = trim($_POST['hired_at'] ?? '');
        $payload['status'] = trim($_POST['status'] ?? '');

        $userModel = new UserModel();
        $userModel->update($id, $payload);

        $_SESSION['employee_edit_message'] = 'Empleado actualizado exitosamente.';

        redirect(route('dashboard', 'index'));
    }
}
