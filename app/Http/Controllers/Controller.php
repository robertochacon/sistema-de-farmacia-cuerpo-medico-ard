<?php

namespace App\Http\Controllers;

/**
 * @OA\OpenApi(
 *     security={{ "bearerAuth": {} }}
 * ),
 * @OA\Info(
 *     title="Api default",
 *     version="1.0.0"
 * ),
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

abstract class Controller
{
    //
}
