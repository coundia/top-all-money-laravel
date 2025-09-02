<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="Top All Money API",
 *   description="Documentation de l'API top all money",
 *   @OA\Contact(email="support@tonapp.com")
 * )
 *
 * @OA\Server(
 *   url="http://127.0.0.1:8000",
 *   description="Local"
 * )
 *
 * @OA\Server(
 *   url="https://topall.megastore.sn",
 *   description="Production"
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 *
 * @OA\Tag(
 *   name="Accounts",
 *   description="Endpoints de gestion des comptes"
 * )
 */
class ApiDocController {}
