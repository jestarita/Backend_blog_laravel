<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthMiddleware
{
  
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');
        $JwtAuth = new \JwtAuth();
        $checkear_token = $JwtAuth->check_token($token);

        if($checkear_token){
            return $next($request);

        }else{
            $data = array(
                'status' => 'error',
                'code' => 404,
                'mensaje' => 'error al autenticarse'
            );
            return response()->json($data, $data['code']);
        }
      
    }
}
