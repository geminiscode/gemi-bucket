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
     * @return array Formato estandarizado de respuesta
     *
     * [
     *     'success' => true|false,
     *     'message' => string,
     *     'data' => mixed|null,
     *     'detail' => string|null,
     *     'code' => int|null
     * ]
     */
    public static function response(bool $success, string $message, $data = null, ?string $detail = null, ?int $code = null): array
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
        return self::response(true, $message, $data);
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
        return self::response(false, $message, null, $detail, $code);
    }


    
}