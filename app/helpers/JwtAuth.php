<?php

namespace App\helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth{
    public $key;

    public function __construct(){
        $this->key = 'esto_es_una_clave_super_secreta-99887766';
    }
    public function signup($email, $password, $recibir_token = null){

    //comprobar si existen credenciales
        $user = User::where([
            'email' => $email,
            'password' => $password
        ])->first();

      
    //Comprobar si son correctos
    $signup = false;
    if (is_object($user)) {
        $signup = true;
    }

    //Generar el token con los datos identificados
    if($signup){
        $token = array(
            'sub' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'surname' => $user->surname,
            'description' => $user->description,
            'image' => $user->image,
            'iat' => time(),
            'ext' => time()+ (7 * 24 * 60 * 60)
        );
     
        $jwt = JWT::encode($token, $this->key, 'HS256');
        $decodificacion = JWT::decode($jwt, $this->key, ['HS256']);
        //devolver los datos decodificados o token en funcion de parametros        

        if (is_null($recibir_token)) {
            return $datos = $jwt;
        }else{
            return $datos = $decodificacion;
        }

    }else{
        $datos = array(
            'status' => 'error',
            'mensaje' => 'ha fallado el login'
        );
    }  

        return $datos;

    }

    public function check_token($jwt, $getidentity = false){

        $auth = false;
        try {
            if($jwt != ""){
                $jwt = str_replace('"','',$jwt);
                $decodificacion = JWT::decode($jwt, $this->key, ['HS256']);
            }else{
                $auth = false; 
            }             
        } catch (\UnexpectedValueExcepcion $e) {
           
            $auth = false;
        }catch(\DomainException $dm){
            $auth = false;
        }
        
        if(!empty($decodificacion) && is_object($decodificacion) && isset($decodificacion->sub)){
            $auth = true;
        }else{
            $auth = false;
        }

        if ($getidentity) {
            return $decodificacion;
        }
        return $auth;
    }
   

}