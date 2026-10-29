<?php

/** Cierre explícito mediante POST protegido, sin efectos al visitar un enlace GET. */
class Logout
{
    private $session;
    public function __construct() { $this->session = new Session; }

    public function Logout()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            echo 'Para cerrar sesión utilice la opción Salir del menú.';
            return;
        }
        if (!$this->session->validCsrfToken($_POST['token'] ?? null, 'logout')) {
            http_response_code(403);
            echo 'No se pudo validar la solicitud. Recargue la página e intente nuevamente.';
            return;
        }
        $this->session->close();
    }
}
