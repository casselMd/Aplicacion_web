<?php 
class AuthModel extends Mysql{
    public function guardarTokenRecuperacion($empleado_id, $token)
        {
            $sql = "UPDATE empleado SET token_recuperacion = ?, token_expira = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?";
            $data = [$token, $empleado_id];
            return $this->update($sql, $data);
        }

        public function getEmpleadoByToken($token)
        {
            $sql = "SELECT * FROM empleado WHERE token_recuperacion = ? AND token_expira > NOW()";
            return $this->select($sql, [$token]);
        }

        public function actualizarPassword($id, $newPassword)
        {
            $sql = "UPDATE empleado SET password = ?, token_recuperacion = NULL, token_expira = NULL WHERE id = ?";
            return $this->update($sql, [$newPassword, $id]);
        }
        public function getEmpleadoByCorreo($correo)
        {
            $sql = "SELECT * FROM empleado WHERE email = ?";
            $data = $this->select($sql, [$correo]);
            return !empty($data) ? $data : null;
        }
}