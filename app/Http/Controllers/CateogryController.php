<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;
class CateogryController extends Controller
{

    public function __construct(){
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

   public function index(){
       $categoria = Category::all();

        return response()->json([
            'categorias' => $categoria,
            'status' => 'success',
            'code' => 200
            ]);
   }

   public function show($id){


       $categoria = Category::find($id);
       if(is_object($categoria)){
           $data = array(
               'status' => 'success',
               'code' => 200,
               'category' => $categoria
           );
       }else{
        $data = array(
            'status' => 'error',
            'code' => 404,
            'Mensaje' => 'la categoria no existe'
        );
       }
       return response()->json($data, $data['code']);
   }

   public function store (Request $solicitud){

       //recoger datos
       $datos = $solicitud->input('json', null);

       $convertir_array = json_decode($datos, true);

       if(!empty($convertir_array)){
            //validar la categoria

            $validar = \Validator::make($convertir_array, [
                'name' => 'required'
            ]);

            if($validar->fails()){
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'mensaje' => 'no se ha guardado la categoria'
                );
            }else{
                    //guardar la cateogiria
                $categoria = new Category();
                $categoria->name = $convertir_array['name'];
                $categoria->save();
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'mensaje' => 'Se ha guardado la categoria',
                    'category' => $categoria
                );
            }
       }else{
        $data = array(
            'code' => 404,
            'status' => 'error',
            'mensaje' => 'Por favor envia la categoria'
        );
       }

     
       return response()->json($data, $data['code']);
   }

   public function update($id, Request $solicitud){

    $json = $solicitud->input('json', null);
    $convertir_array = json_decode($json, true);

    if(!empty($convertir_array)){
        //validar la categoria

        $validar = \Validator::make($convertir_array, [
            'name' => 'required'
        ]);

        if($validar->fails()){
            $data = array(
                'code' => 400,
                'status' => 'error',
                'mensaje' => 'no se ha guardado la categoria'
            );
        }else{
                //guardar la cateogiria
           unset($convertir_array['id']);
           unset($convertir_array['created_at']);
            $categoria= Category::where('id', $id)->update($convertir_array);
           
            $data = array(
                'code' => 200,
                'status' => 'success',
                'mensaje' => 'Se ha Actualizado la categoria',
                'category' => $convertir_array
            );
        }
   }else{
    $data = array(
        'code' => 404,
        'status' => 'error',
        'mensaje' => 'Por favor envia la categoria'
    );
   }

 
   return response()->json($data, $data['code']);

   }

}
