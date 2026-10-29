<?php

/** Entrada web: autenticación manual y recuperación persistente sin credenciales en el navegador. */
class Login extends Controllers
{
    private $session;
    const prefijo_msj_error = 'usuarios';

    public function __construct()
    {
        parent::__construct();
        $this->session = new Session;
    }

    /** Renderiza el formulario con CSRF de sesión o recupera un acceso persistente válido. */
    public function Login()
    {
        header('Cache-Control: no-store');
        header('Referrer-Policy: no-referrer');
        if ($this->session->getStatus()) {
            $this->session->redirect('inicio');
            return;
        }
        $data = [
            'meta_keywords' => 'bombas, valvulas, sellos',
            'meta_description' => 'Sistema de Gestión LFM CONTROL',
            'meta_author' => 'LFM CONTROL', 'title' => 'Sistema LFM CONTROL',
            'page_functions_js' => 'login.js', 'token' => $this->session->getCsrfToken()
        ];
        $this->views->getView($this, 'login', $data);
    }

    /** POST de formulario; conserva el contrato JSON respuesta/mensaje/mostrar_mensaje/tiempo. */
    public function validaAcceso()
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        try {
            if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
                header('Allow: POST');
                http_response_code(405);
                $response = getResponse('Método no permitido.');
            } elseif (!$this->session->validCsrfToken($_POST['token'] ?? null)) {
                http_response_code(403);
                $response = getResponse('El formulario venció. Recargue la página e intente nuevamente.');
            } else {
                $email = $_POST['email'] ?? null;
                $password = $_POST['pass'] ?? null;
                $remember = $_POST['recordarme'] ?? null;
                if (!is_string($email) || !is_string($password) || strlen(trim($email)) > 45
                    || trim($email) === '' || $password === '' || strlen($password) > 4096
                    || ($remember !== null && (!is_string($remember) || !in_array($remember, ['1', 'on', '0'], true)))) {
                    throw new UnexpectedValueException('Usuario o contraseña no válidos.');
                }
                // La contraseña no se recorta ni se transforma con filtros de HTML/SQL.
                $username = strtolower(trim($email));
                $hash = hash('sha256', $password);
                $user = $this->model->findUser($username);
                if (!$user || !hash_equals($user['pass'], $hash) || (int)$user['activo'] !== 1) {
                    throw new UnexpectedValueException('Usuario o contraseña no válidos.');
                }
                $profile = $this->session->loadLoginProfile($user);
                $rememberMe = in_array($remember, ['1', 'on'], true);
                // Una autenticación explícita reemplaza el token de este dispositivo, incluso sin marcar la casilla.
                $this->session->clearAuthCookie();
                if ($rememberMe) { $this->session->rememberLogin($user['usuario'], $user['pass']); }
                $this->session->establishLogin($profile, $rememberMe);
                $response = getResponse('Acceso Autorizado', 'ok', false);
            }
        } catch (UnexpectedValueException $error) {
            $response = getResponse($error->getMessage());
        } catch (Throwable $error) {
            getLoggerSystem()->error('No se pudo completar el inicio de sesión.');
            $response = getResponse('No se pudo iniciar sesión. Intente nuevamente.');
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}
