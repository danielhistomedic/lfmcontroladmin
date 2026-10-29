<?php

/** Persistencia de acceso por dispositivo sobre ssf_login_token, sin alterar el esquema. */
class LoginModel extends Mysql
{
    const TABLA = 'login_token';
    const PREFIJO_TABLA = 'ssf_';

    /** Consulta parametrizada; propaga errores para no confundir fallo de base con credenciales inválidas. */
    private function statement(string $sql, array $values)
    {
        $stmt = $this->getConexion()->prepare($sql);
        if (!$stmt->execute($values)) { throw new RuntimeException('No se pudo consultar el acceso.'); }
        return $stmt;
    }

    /** Devuelve el usuario y su hash exclusivamente en servidor; nunca serializar este resultado al cliente. */
    public function findUser(string $username): array
    {
        $rows = $this->statement('SELECT id, usuario, pass, activo FROM ssf_usuarios WHERE usuario = :usuario LIMIT 2',
            ['usuario' => $username])->fetchAll(PDO::FETCH_ASSOC);
        return count($rows) === 1 ? $rows[0] : [];
    }

    /** Inserta hashes de token. expiry_date recibe una marca Unix para evitar desfases PHP/MySQL. */
    public function insertToken(string $username, string $random_password_hash, string $random_selector_hash, string $expiry_date): bool
    {
        if (!ctype_digit($expiry_date) || (int)$expiry_date <= time()) {
            throw new InvalidArgumentException('Vencimiento de acceso no válido.');
        }
        $this->statement('INSERT INTO ssf_login_token (usuario, password_hash, selector_hash, expiry_date, is_expired)
            VALUES (:usuario, :password_hash, :selector_hash, FROM_UNIXTIME(:expiry_date), 0)', [
            'usuario' => $username, 'password_hash' => $random_password_hash,
            'selector_hash' => $random_selector_hash, 'expiry_date' => (int)$expiry_date
        ]);
        return true;
    }

    /** Solo reconoce el nuevo selector hasheado, vigente y no revocado; omite tokens legacy. */
    public function findToken(string $selectorHash): array
    {
        $rows = $this->statement('SELECT id, usuario, password_hash FROM ssf_login_token
            WHERE selector_hash = :selector AND is_expired = 0 AND expiry_date > CURRENT_TIMESTAMP LIMIT 2',
            ['selector' => $selectorHash])->fetchAll(PDO::FETCH_ASSOC);
        return count($rows) === 1 ? $rows[0] : [];
    }

    /** Revoca únicamente el token del dispositivo que sale o desmarca Recordarme. */
    public function expireSelector(string $selectorHash): bool
    {
        $this->statement('UPDATE ssf_login_token SET is_expired = 1 WHERE selector_hash = :selector AND is_expired = 0',
            ['selector' => $selectorHash]);
        return true;
    }

    /** Conserva la operación existente de revocación por ID. */
    public function markAsExpired($tokenId): bool
    {
        $this->statement('UPDATE ssf_login_token SET is_expired = 1 WHERE id = :id', ['id' => (int)$tokenId]);
        return true;
    }
}
