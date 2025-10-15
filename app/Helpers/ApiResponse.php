<?php
namespace App\Helpers;

class ApiResponse
{

//Réponse succèes

    public static function success($data = [], $code = 200, $message = null)
    {
        $response = [
            'data' => $data,
            'code' => $code,
            'success' => true,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response, $code);
    }

    //Réponse erreur
    public static function error($message = 'Une erreur est survenue', $code = 400, $errors = [], $error = null)
    {
        return response()->json([
            'errors' => $errors,
            'code' => $code,
            'success' => false,
            'message' => $message,
            'error' => $error
        ], $code);
    }

}
