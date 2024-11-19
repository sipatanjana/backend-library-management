<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

abstract class Controller
{
    /**
     * Return response success semua fungsi.
     */
    public function succesFunction(String $message = null, $data = null, $response = 200): Response
    {
        $data_message = $message ? $message : 'Success';
        if ($data) {

            return Response([
                'message' => $data_message,
                'data' => $data,
            ], $response);
        } else {

            return Response([
                'message' => $data_message,
            ], $response);
        }
    }

    /**
     * Return response fail semua fungsi.
     */
    public function failedFunction(String $message = null, $data = null, $response = 400): Response
    {
        $data_message = $message ? $message : 'Something wrong!';
        if (!$data) {

            return Response([
                'message' => $data_message,
            ], $response);
        } else {

            return Response([
                'message' => $data_message,
                'data' => $data,
            ], $response);
        }
    }
}
