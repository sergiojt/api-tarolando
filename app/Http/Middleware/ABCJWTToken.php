<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;

class ABCJWTToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
 
        if(!$request->hasHeader('Authorization')) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Header não encontrado.'
            ], 401);
        }

        if($request->header('Authorization') == null) {
            return response()->json([
                'success' => false,
                'message' => 'Token não encontrado.'
            ], 401);
        }
  
        // Separando a String do token
        list($token) = sscanf( $request->header('Authorization'), 'Bearer %s');

        try {
            $decoded = JWT::decode($token, new Key(env('APP_KEY'), 'HS256'));

            $email = $decoded->email;
        }catch (SignatureInvalidException $e){
            // Signature verification failed'
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível validar o Token de Acesso.'
            ], 401);
        }catch (ExpiredException $e){
            // Expired token
            return response()->json([
                'success' => false,
                'message' => 'Token de Acesso expirado.'
            ], 401);
        }
        catch(\UnexpectedValueException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token de Acesso inválido.'
            ], 401);
        } 
        catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token de Acesso inválid.'
            ], 401);
        }

        $request->merge([
            'user_email' => $email,
        ]);

        return $next($request)->header('Access-Control-Allow-Origin', "*")
        ->header('Access-Control-Allow-Methods', "PUT, POST, DELETE, GET, OPTIONS")
        ->header('Access-Control-Allow-Headers', "Accept, Authorization, Content-Type");        
    }
}
