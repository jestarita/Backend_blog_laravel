<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;
class UserController extends Controller
{
    public function prueba(Request $solicitud)
    {
        return 'Metodo de prueba controlador usuario';
    }

    public function register(Request $request)
    {

        //recoger los datos
        $datos = $request->input('json', null);

        $decifrar_objeto = json_decode($datos); //objeto
        $decifrar = json_decode($datos, true); //array

        if (!empty($decifrar) && !empty($decifrar_objeto)) {
            //limpiar array
            $decifrar = array_map('trim', $decifrar);

            //validar datos
            $validar = \Validator::make($decifrar, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users',
                'password' => 'required'
            ]);



            if ($validar->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'mensaje' => 'El usuario no se ha creado',
                    'errores' => $validar->errors()
                );
            } else {
                //todo correcto

                //cifrar contraseña
                $pwd = hash('sha256', $decifrar_objeto->password);
                //crear usuario
                $usuario = new User();
                $usuario->name = $decifrar['name'];
                $usuario->surname = $decifrar['surname'];
                $usuario->email = $decifrar['email'];
                $usuario->password =  $pwd;
                $usuario->role =  'Role_user';
                $usuario->save();

                $data = array(
                    'status' => 'Success',
                    'code' => 200,
                    'mensaje' => 'El usuario  se ha creado sin problemas',
                    'User' => $usuario
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'mensaje' => 'los datos enviados no son correctos'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function login(Request $solicitud)
    {
        $Jwt = new \JwtAuth();
        $datos = $solicitud->input('json', null);

        $decifrar_objeto = json_decode($datos); //objeto
        $decifrar = json_decode($datos, true); //array

        $validar = \Validator::make($decifrar, [           
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validar->fails()) {
            $signup = array(
                'status' => 'error',
                'code' => 404,
                'mensaje' => 'El usuario no se ha podido identificar',
                'errores' => $validar->errors()
            );
        }else{
            $pwd = hash('sha256', $decifrar_objeto->password);
            $signup = $Jwt->signup($decifrar_objeto->email, $pwd);
            if(!empty($decifrar_objeto->getToken)){
                $signup = $Jwt->signup($decifrar_objeto->email, $pwd, true);
            }         
        }     
        
        return response()->json($signup, 200);
    }

    public function update(Request $solicitud){

        $token = $solicitud->header('Authorization');
        $JwtAuth = new \JwtAuth();
        $checkear_token = $JwtAuth->check_token($token);
         //recoger datos del post
         $datos = $solicitud->input('json', null);
         $decifrar = json_decode($datos, true); //array

        if ($checkear_token && !empty($decifrar)) {
            //sacar id usuario
           $user = $JwtAuth->check_token($token, true);

           //validar datos del post
   
           $validar = \Validator::make($decifrar, [
            'name' => 'required|alpha',
            'surname' => 'required|alpha',
            'email' => 'required|email|unique:users, '.$user->sub
            ]);

           //quitar campos que no se desea actualiza
            unset($decifrar['id']);
            unset($decifrar['role']);
            unset($decifrar['password']);
            unset($decifrar['created_at']);
            unset($decifrar['remember_token']);
           //Actualizar usuario en db
           $user_update = User::Where('id', $user->sub)->update($decifrar);   

           //return respuesta
           $data = array(
            'changes' => $decifrar,
            'status' => 'success',
            'code' => 200,
            'mensaje' => 'El usuario se ha Actualizado', 
            'usuario' => $user
        );
        }else{
            $data = array(
                'status' => 'error',
                'code' => 404,
                'mensaje' => 'El usuario no esta identificado'
            );
        }
        return response()->json($data, $data['code']);
    }


    public function upload(Request $request){

        //recoger imagen 
        $imagen = $request->file('file0');

        //Validar todo
        $validar = \Validator::make($request->all(), [
            'file0' => 'required|image',
          
        ]);
        //chequear imagen
        if(!$imagen || $validar->fails()){
          
            $data = array(
                'status' => 'error',
                'code' => 404,
                'mensaje' => 'Error al subir imagen',
                'error' => $validar->errors()
                
            );
        }else{
            $imagen_name = time().$imagen->getClientOriginalName();
            \Storage::disk('users')->put($imagen_name, \File::get($imagen));

            $data = array(
                'code'=> 200,
                'image' => $imagen_name,
                'status' => 'success'
            );
        
        }
        return response()->json($data, $data['code']);
    }

    public function get_avatar($imagen){
        $isset = \Storage::disk('users')->exists($imagen);
        if ($isset) {
            $file = \Storage::disk('users')->get($imagen);
            return new Response($file, 200);
        }else{
            $data = array(
                'status' => 'error',
                'code' => 404,
                'mensaje' => 'Esa imagen no existe'                
            );
            return response()->json($data, $data['code']);
        }       
    }

    public function details($id){
        $usuario = User::find($id);

        if(is_object($usuario)){
            $data = array(
                'status' => 'success',
                'code' => 200,
                'user' =>  $usuario                
            ); 
        }else{
            $data = array(
                'status' => 'error',
                'code' => 404,
                'mensaje' => 'Ese usuario no existe'                
            );
        }
        return response()->json($data, $data['code']);
    }
}
