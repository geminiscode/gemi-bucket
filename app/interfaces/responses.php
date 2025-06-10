<?php



/*===============================================================================================*/
/* RESPONSES ------------------------------------------------------------------------------------*/
/*===============================================================================================*/



class Response
{



    /**
     * Devuelve una respuesta estandarizada del sistema
     *
     * @param bool $success Indica si la operación fue exitosa
     * @param string $message Mensaje descriptivo
     * @param mixed|null $data Datos adicionales (opcional)
     * @param string|null $detail Detalle técnico (opcional)
     * @param int|null $code Código de estado/error (opcional)
     * @param int|null $status Estado lógico de la respuesta (0:error, 1:éxito, 2:advertencia)
     * @param array|null $options Opciones disponibles (para decisiones futuras)
     * @param mixed|null $selected Opción seleccionada (si se recibe del cliente)
     * @return array Formato estandarizado de respuesta
     *
     * [
     *     'success' => true|false,
     *     'message' => string,
     *     'data' => mixed|null,
     *     'detail' => string|null,
     *     'code' => int|null,
     *     'status' => 0|1|2,      // 0: error, 1: éxito, 2: advertencia / decisión requerida
     *     'options' => array|null, // Opciones del backend para el usuario
     *     'selected' => mixed|null // Decisión tomada por el usuario (si aplica)
     * ]
     */
    public static function response(bool $success, string $message, $data = null, ?string $detail = null, ?int $code = null, ?int $status = null, ?array $options = null, $selected = null): array
    {
        $response = [
            'success' => $success,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($detail !== null) {
            $response['detail'] = $detail;
        }

        if ($code !== null) {
            $response['code'] = $code;
        }

        if ($status !== null) {
            $response['status'] = $status;
        }

        if ($options !== null) {
            $response['options'] = $options;
        }

        if ($selected !== null) {
            $response['selected'] = $selected;
        }

        return $response;
    }

    /**
     * Devuelve una respuesta de éxito
     *
     * @param string $message Mensaje descriptivo
     * @param mixed|null $data Datos adicionales (opcional)
     * @return array
     */
    public static function success(string $message = 'Operación exitosa', $data = null): array
    {
        return self::response(true, $message, $data, null, null, 1);
    }

    /**
     * Devuelve una respuesta de error
     *
     * @param string $message Mensaje general del error
     * @param string|null $detail Detalle técnico (opcional)
     * @param int|null $code Código de estado/error (opcional)
     * @return array
     */
    public static function error(string $message = 'Ocurrió un error', ?string $detail = null, ?int $code = null): array
    {
        return self::response(false, $message, null, $detail, $code, 0);
    }

    /**
     * Respuesta de advertencia (requiere acción del usuario)
     */
    public static function warning(string $message, array $options, $data = null, ?int $code = null): array
    {
        return self::response(false, $message, $data, 'Advertencia', $code, 2, $options);
    }


    
}