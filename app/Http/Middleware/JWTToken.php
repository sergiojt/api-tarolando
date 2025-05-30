<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Support\Facades\Auth;

class JWTToken
{
    /**
     * Verifica o token de autenticação d app mobile
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
 
        if(!$request->hasHeader('Authorization')) {
            return response()->json([
                'message' => 'Authorization Header não encontrado.'
            ], 401);
        }

        if($request->header('Authorization') == null) {
            return response()->json([
                'message' => 'Token não encontrado.'
            ], 401);
        }

        // Separando a String do token
        list($token) = sscanf( $request->header('Authorization'), 'Bearer %s');

        try {
            $decoded = JWT::decode($token, new Key(env('APP_KEY'), 'HS256'));

            $email = $decoded->email;
            $id = $decoded->id;
            $name = $decoded->name;

            $user = User::find($id);
      
            if (!$user) {
                return response()->json(['message' => 'Usuário não encontrado.'], 401);
            }

            // Autentica o usuário
            Auth::login($user);

        }catch (SignatureInvalidException $e){
            // Signature verification failed'
            return response()->json([
                'message' => 'Não foi possível validar o Token de Acesso.'
            ], 401);
        }catch (ExpiredException $e){
            // Expired token
            return response()->json([
                'message' => 'Token de Acesso expirado.'
            ], 401);
        }
        catch(\UnexpectedValueException $e) {
            return response()->json([
                'message' => 'Token de Acesso inválido.'
            ], 401);
        } 
        catch(\Exception $e) {
            return response()->json([
                'message' => 'Token de Acesso inválid.'
            ], 401);
        }

        $request->merge([
            'user_email'        => $email,
            'user_id'           => $id,
            'user_name'         => $name,
            'user_token'        => $token
        ]);

        return $next($request)->header('Access-Control-Allow-Origin', "*")
        ->header('Access-Control-Allow-Methods', "PUT, POST, DELETE, GET, OPTIONS")
        ->header('Access-Control-Allow-Headers', "Accept, Authorization, Content-Type");        
    }
}
