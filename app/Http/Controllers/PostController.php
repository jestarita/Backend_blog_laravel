<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\helpers\JwtAuth;
class PostController extends Controller
{
    public function __construct(){
        $this->middleware('api.auth', ['except' => 
        ['index',
         'show', 
         'getImage',
         'getPostByCategory',
         'getPostByUser']]);
    }

    public function index(){
        $post = Post::all()->load('category');


        return response()->json([
            'code'=> 200, 
            'status'=> 'success',
            'posts'=> $post
        ], 200);
    }

    public function show($id){
        $post = Post::find($id)->load('category')
                               ->load('user') ;
        if(is_object($post)){
            $data = array(
                'status' => 'success',
                'code' => 200,
                'post' => $post
            );
        }else{
            $data = array(
                'status' => 'error',
                'code' => 404,
                'mensaje'=> 'Esa publicacion no existe'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $solicitud){
        $datos = $solicitud->input('json', null);
       
        $json = json_decode($datos);
        $json_array = json_decode($datos, true);
   
        
        if(!empty($json_array)){
            $user = $this->get_identity($solicitud);
            $validador = \Validator::make($json_array,[
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required'
            ]);
            if($validador->fails()){
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'mensaje' => 'No se pudo guardar el post, faltan datos'
                );
            }else{
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $json->category_id;
                $post->title = $json->title;
                $post->content = $json->content;
                $post->image = $json->image;
                $post->save();
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'mensaje' => 'Post Creado Exitosamente',
                    'post' => $post
                );
            }         
        }else{
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'mensaje' => 'Envia bien los datos'
                );
        }
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $solicitud){

       $user = $this->get_identity($solicitud);

        $datos = $solicitud->input('json', null);
        $json_array = json_decode($datos, true);

        $data = array(
            'status' => 'error',
            'code' => 400,
            'mensaje' => 'Envia bien los datos'
        );

        if(!empty($json_array)){
            $validador = \Validator::make($json_array,[
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
            ]);

            if($validador->fails()){
                $data['errors'] = $validador->errors();
                return response()->json($data, $data['code']);
            }
    
            unset($json_array['id']);
            unset($json_array['user_id']);
            unset($json_array['created_at']);
            unset($json_array['user']);

            $post = Post::where('id', $id)
            ->where('user_id', $user->sub)
            ->first();

            if(!empty($post) && is_object($post)){

                $post->update($json_array);
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'mensaje' => 'Post Actualizado Exitosamente',
                    'post' => $post,
                    'changes' => $json_array
                );

            }          
        }       

        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $solicitud){
        $user = $this->get_identity($solicitud);


        $post = Post::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->first();

        if($post){
            $post->delete();

            $listado = Post::all()->load('category');
            $data = array(
                'status' => 'success',
                'code' => 200,
                'mensaje' => 'Post Eliminado Exitosamente',
                'post' => $post
            );
        }else{
            $data = array(
                'status' => 'error',
                'code' => 404,
                'mensaje' => 'Ese post no existe'
            );
        }

       
        return response()->json($data, $data['code']);
    }

    private function get_identity($solicitud){
        $jwtAuth = new JwtAuth();
        $token = $solicitud->header('Authorization', null);
        $user = $jwtAuth->check_token($token, true);

        return $user;
    }


    public function upload(Request $solicitud){
        $file = $solicitud->file('file0');
        $validador = \Validator::make($solicitud->all(), [
            'file0' => 'required|image'
        ]);

        if($validador->fails() || !$file){
            $data = array(
                'status' => 'error',
                'code' => 400,
                'mensaje' => 'Error al subir la imagen', 
                'errors' => $validador->errors()
            ); 
        }else{
            $nombre = time().$file->getClientOriginalName();
            \Storage::disk('images')->put($nombre, \File::get($file));
            $data = array(
                'status' => 'success',
                'code' => 200,
                'mensaje' => 'Imagen almacenada',
                'image' => $nombre
            );
        }
        return response()->json($data, $data['code']);
    }

    public function getImage($filename){

        $isset = \Storage::disk('images')->exists($filename);

        if($isset){
            $file = \Storage::disk('images')->get($filename);
            return new Response($file, 200);
        }else{
            $data = array(
                'status' => 'error',
                'code' => 404,
                'mensaje' => 'Imagen No Existe'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function getPostByCategory($id){
        $posts = Post::where('category_id', $id)->get();
        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }

    public function getPostByUser($id){
        $posts = Post::where('user_id', $id)->get();
        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }

}
