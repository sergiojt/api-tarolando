<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ABCController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required',
                'password' => 'required',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $response = Http::withOptions([
                'verify' => false,
            ])->get(
                "https://chatbot.gruposinos.com.br:10443/ScapSOA/service/check/signature/email/actual/login/{$request->email}/password/{$request->password}",
                [
                    'vehicle' => 10,
                ]
            );

            if ($response->successful()) {
                $data = $response->json();

                if ($data["codigo"] == 0 && $data["mensagem"] == "assinatura vigente") {
                    return response()->json([
                        'success' => true,
                        'message' => 'Login realizado com sucesso',
                        'token'    => self::generateToken($request),
                    ], 200);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Assinatura expirada ou inexistente',
                ], 401);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erro ao autenticar',
                'error'   => $response->json() ?? $response->body(),
            ], $response->status());
        } catch (\Throwable $e) {
            // Erro interno (timeout, DNS, exception, etc)
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao comunicar com o serviço de autenticação',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function refreshToken(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        }

        $token = $request->token;

        try {
            // 1. Decodifica o token
            $decoded = JWT::decode($token, new Key(env('APP_KEY'), 'HS256'));
            $email   = $decoded->email ?? null;
            $password   = $decoded->password ?? null;

            if (!$email && !$password) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido',
                ], 401);
            }

            $response = Http::withOptions([
                'verify' => false,
            ])->get(
                "https://chatbot.gruposinos.com.br:10443/ScapSOA/service/check/signature/email/actual/login/{$email}/password/{$password}",
                [
                    'vehicle' => 10,
                ]
            );

            if ($response->successful()) {
                $data = $response->json();

                if ($data["codigo"] == 0 && $data["mensagem"] == "assinatura vigente") {
                    return response()->json([
                        'success' => true,
                        'message' => 'Login realizado com sucesso',
                        'token'    => self::generateToken($decoded),
                    ], 200);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Assinatura expirada ou inexistente',
                ], 401);
            }
        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expirado',
            ], 401);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido',
                'error'   => $e->getMessage(),
            ], 401);
        }
    }

    public function check(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Assinatura validada com sucesso',
            'email'    => $request->user_email,
        ], 200);
    }

    public function generateToken($data)
    {
        $payload = [
            'email'    => $data->email,
            'password' => $data->password,
            'iat'      => time(),
            'exp'      => time() + (12 * 60 * 60),
        ];

        return JWT::encode($payload, env('APP_KEY'), 'HS256');
    }
}
