<?php

/** Sesión del portal y acceso persistente por dispositivo, sin credenciales en cookies. */
class Session
{
    private $current_date;
    private $cookie_expiration_time;
    private static $rememberAttempted = false;

    public function __construct() { $this->init(); }

    public static function sessionName() { return encode(date('YmdHis')); }

    /** Inicia una sola vez; la sesión normal termina al cerrar el navegador. */
    public function init()
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            session_set_cookie_params([
                'lifetime' => 0, 'path' => '/', 'secure' => $this->isSecure(),
                'httponly' => true, 'samesite' => 'Lax'
            ]);
            if (!session_start()) { throw new RuntimeException('No se pudo iniciar la sesión.'); }
        }
        $this->current_date = date('Y-m-d H:i:s');
        $this->cookie_expiration_time = time() + 30 * 86400;
        // Retirar credenciales legacy también de navegadores con una sesión ya iniciada.
        foreach (['usuario', 'password'] as $key) {
            if (isset($_COOKIE[PREFIJO_SESSION . $key])) {
                $this->setCookie($key, '', time() - 3600);
                unset($_COOKIE[PREFIJO_SESSION . $key]);
            }
        }
    }

    /** HTTPS real o URL canónica HTTPS; no confiar en cabeceras proxy arbitrarias. */
    private function isSecure(): bool
    {
        return (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
            || (int)($_SERVER['SERVER_PORT'] ?? 0) === 443
            || strtolower((string)parse_url(base_url(), PHP_URL_SCHEME)) === 'https';
    }

    public function add($key, $value) { $_SESSION[PREFIJO_SESSION . $key] = $value; }

    /** Conserva 0 y false; admite los mismos niveles que el contrato existente. */
    public function get($key, $key2 = '', $key3 = '', $key4 = '')
    {
        $value = $_SESSION[PREFIJO_SESSION . $key] ?? null;
        foreach ([$key2, $key3, $key4] as $part) {
            if ($part !== '') { $value = is_array($value) ? ($value[$part] ?? null) : null; }
        }
        return $value;
    }

    public function getAll() { return $_SESSION; }
    public function remove($key) { unset($_SESSION[PREFIJO_SESSION . $key]); }
    public function redirect($page) { header('Location: ' . base_url() . '/' . $page); }

    /** Cookie host-only: HttpOnly, SameSite y Secure cuando corresponde. */
    public function setCookie($key, $value, $time)
    {
        if (!setcookie(PREFIJO_SESSION . $key, $value, [
            'expires' => (int)$time, 'path' => '/', 'secure' => $this->isSecure(),
            'httponly' => true, 'samesite' => 'Lax'
        ])) { throw new RuntimeException('No se pudo guardar la cookie de acceso.'); }
    }

    /** Token CSRF aleatorio y separado por acción, vinculado a esta sesión. */
    public function getCsrfToken(string $action = 'login'): string
    {
        $key = 'csrf_' . $action;
        if (!is_string($this->get($key)) || strlen($this->get($key)) !== 64) {
            $this->add($key, bin2hex(random_bytes(32)));
        }
        return $this->get($key);
    }

    public function validCsrfToken($token, string $action = 'login'): bool
    {
        $expected = $this->get('csrf_' . $action);
        return is_string($token) && is_string($expected) && strlen($expected) === 64
            && hash_equals($expected, $token);
    }

    /** Obtiene un contexto único activo; no selecciona una empresa arbitrariamente. */
    public function loadLoginProfile(array $user): array
    {
        if ((int)($user['activo'] ?? 0) !== 1 || (int)($user['id'] ?? 0) < 1) {
            throw new UnexpectedValueException('Usuario o contraseña no válidos.');
        }
        $companies = new EmpresasModel;
        $companies->setUsuarioId((int)$user['id']);
        $assignments = array_values(array_filter($companies->getEmpresasUsuarios($companies),
            static function ($row) { return (int)($row['activo'] ?? 0) === 1; }));
        if (count($assignments) !== 1) {
            throw new UnexpectedValueException('Debe tener una única empresa y sucursal activas. Contacte al administrador.');
        }
        $assignment = $assignments[0];
        $users = new UsuariosModel;
        $users->setId((int)$user['id']);
        $users->setEmpresaId((int)$assignment['empresa_id']);
        $users->setSucursalId((int)$assignment['sucursal_id']);
        $profile = $users->selectUsuarioLogin($users);
        if (!$profile || (int)($profile['activo'] ?? 0) !== 1 || empty($profile['email'])
            || empty($profile['rol_id'])) {
            throw new UnexpectedValueException('El perfil, rol o sucursal no están disponibles. Contacte al administrador.');
        }
        return $profile;
    }

    /** Regenera el ID y elimina datos de otra identidad antes de establecer el perfil. */
    public function establishLogin(array $profile, bool $remember): void
    {
        if (!session_regenerate_id(true)) { throw new RuntimeException('No se pudo renovar la sesión.'); }
        foreach (array_keys($_SESSION) as $key) {
            if (str_starts_with((string)$key, PREFIJO_SESSION)) { unset($_SESSION[$key]); }
        }
        foreach (['usuario', 'theme', 'email', 'empresa_id', 'sucursal_id', 'rol_id', 'rol', 'ccveusuario'] as $key) {
            $this->add($key, $profile[$key] ?? null);
        }
        $this->add('usuario_id', (int)$profile['id']);
        $this->add('nombre', trim(implode(' ', [$profile['nombre'], $profile['paterno'] ?? '', $profile['materno'] ?? ''])));
        $this->add('nombre_solo', $profile['nombre']);
        $this->add('recordarme', $remember ? 1 : 0);
        $this->add('remember_selector', $remember ? $this->cookieParts()[0] : null);
    }

    /** Cookie nueva: selector de 128 bits y secreto de 256 bits, ambos aleatorios. */
    private function cookieParts(): array
    {
        $cookie = $_COOKIE[PREFIJO_SESSION . 'remember'] ?? '';
        return is_string($cookie) && preg_match('/\A[a-f0-9]{32}:[a-f0-9]{64}\z/D', $cookie)
            ? explode(':', $cookie, 2) : ['', ''];
    }

    /** Crea persistencia por 30 días; almacena solo hashes del token en la base. */
    public function rememberLogin(string $username, string $passwordHash): void
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $model = new LoginModel;
        // Vincular al hash vigente invalida el token si cambia la contraseña.
        if (!$model->insertToken($username, hash_hmac('sha256', $validator, $passwordHash),
            hash('sha256', $selector), (string)$this->cookie_expiration_time)) {
            throw new RuntimeException('No se pudo activar Recordarme. Intente nuevamente.');
        }
        try {
            $this->setCookie('remember', $selector . ':' . $validator, $this->cookie_expiration_time);
            $_COOKIE[PREFIJO_SESSION . 'remember'] = $selector . ':' . $validator;
        } catch (Throwable $error) {
            $model->expireSelector(hash('sha256', $selector));
            throw $error;
        }
    }

    /** Revoca solo este dispositivo y retira cookies antiguas de credenciales. */
    public function clearAuthCookie()
    {
        [$selector] = $this->cookieParts();
        $selector = $selector ?: $this->get('remember_selector');
        try {
            if (is_string($selector) && preg_match('/\A[a-f0-9]{32}\z/D', $selector)) {
                (new LoginModel)->expireSelector(hash('sha256', $selector));
            }
        } finally {
            foreach (['remember', 'usuario', 'password'] as $key) {
                $this->setCookie($key, '', time() - 3600);
                unset($_COOKIE[PREFIJO_SESSION . $key]);
            }
            $this->remove('remember_selector');
        }
    }

    /** Sesión actual o recuperación persistente; $inicio=true solo consulta la sesión. */
    public function getStatus($inicio = false): bool
    {
        if ((int)$this->get('usuario_id') > 0 && !empty($this->get('email'))) { return true; }
        if ($inicio || self::$rememberAttempted) { return false; }
        self::$rememberAttempted = true;
        [$selector, $validator] = $this->cookieParts();
        if ($selector !== '') { return $this->reconect($selector, $validator); }
        // Las cookies de credenciales del esquema anterior ya no autentican.
        if (isset($_COOKIE[PREFIJO_SESSION . 'remember']) || isset($_COOKIE[PREFIJO_SESSION . 'usuario'])
            || isset($_COOKIE[PREFIJO_SESSION . 'password'])) {
            $this->clearAuthCookie();
        }
        return false;
    }

    /** Recupera con token vigente, usuario activo y contraseña todavía válida. */
    public function reconect(string $selector, string $validator): bool
    {
        if (!preg_match('/\A[a-f0-9]{32}\z/D', $selector) || !preg_match('/\A[a-f0-9]{64}\z/D', $validator)) {
            return false;
        }
        try {
            $model = new LoginModel;
            $token = $model->findToken(hash('sha256', $selector));
            $user = $token ? $model->findUser($token['usuario']) : [];
            if (!$user || (int)$user['activo'] !== 1 || !hash_equals($token['password_hash'],
                hash_hmac('sha256', $validator, $user['pass']))) {
                // No revocar una fila por un secreto erróneo: evitar denegación de otro dispositivo.
                $this->forgetInvalidCookie();
                return false;
            }
            $profile = $this->loadLoginProfile($user);
            $this->establishLogin($profile, true);
            return true;
        } catch (UnexpectedValueException $error) {
            $this->forgetInvalidCookie();
        } catch (Throwable $error) {
            // No registrar tokens, contraseñas ni parámetros SQL. Permitir reintento tras un fallo transitorio.
            getLoggerSystem()->error('No se pudo recuperar la sesión persistente.');
        }
        return false;
    }

    private function forgetInvalidCookie(): void
    {
        $this->setCookie('remember', '', time() - 3600);
        unset($_COOKIE[PREFIJO_SESSION . 'remember']);
    }

    /** Cierra sesión y elimina también la cookie PHP, incluso si falla la revocación remota. */
    public function close()
    {
        try { $this->clearAuthCookie(); }
        catch (Throwable $error) { getLoggerSystem()->error('No se pudo revocar el token persistente al salir.'); }
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            $options = session_get_cookie_params();
            unset($options['lifetime']);
            $options['expires'] = time() - 3600;
            setcookie(session_name(), '', $options);
            session_destroy();
        }
        $this->redirect('login');
    }

    public function getCurrentDate() { return $this->current_date; }
    public function setCurrentDate($value): self { $this->current_date = $value; return $this; }
    public function getCookieExpirationTime() { return $this->cookie_expiration_time; }
    public function setCookieExpirationTime($value): self { $this->cookie_expiration_time = $value; return $this; }
}
