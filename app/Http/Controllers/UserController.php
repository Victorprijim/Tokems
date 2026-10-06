<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Token;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Devuelve la lista de los 10 primeros usuarios (sin la contraseña).
     */
    public function index()
    {
        try {
            // Obtenemos los 10 primeros usuarios ordenados por id
            $users = User::orderBy('id')->take(10)->get();

            return response()->json([
                'success' => true,
                'message' => 'Lista de usuarios obtenida correctamente',
                'data' => $users
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los usuarios: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Crea un usuario nuevo con la contraseña cifrada.
     */
    public function store(Request $request)
    {
        try {
            // Validamos los datos recibidos
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8'
            ]);

            // Creamos el usuario guardando la contraseña con Hash::make
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'data' => $user
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos no válidos',
                'data' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el usuario: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Alias de store() por si la ruta apunta a create().
     */
    public function create(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Inicia sesión: comprueba el usuario y la contraseña y genera un token nuevo.
     */
    public function login(Request $request)
    {
        try {
            // Validamos los datos recibidos
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:8'
            ]);

            // Buscamos el usuario por email
            $user = User::where('email', $request->email)->first();

            // Si no existe o la contraseña no coincide, devolvemos 401
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email o contraseña incorrectos',
                    'data' => null
                ], 401);
            }

            // Borramos el token anterior del usuario (si lo tiene)
            Token::where('user_id', $user->id)->delete();

            // Generamos un token nuevo
            $token = hash('sha256', Str::random(64));

            Token::create([
                'user_id' => $user->id,
                'token' => $token
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login correcto',
                'data' => $token
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos no válidos',
                'data' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar sesión: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Cambia el nombre del usuario al que pertenece el token.
     * El token se recibe en el body ('token') o en la cabecera Authorization: Bearer.
     */
    public function updateName(Request $request)
    {
        try {
            // Si no viene en el body, usamos el token de la cabecera Authorization
            if (!$request->filled('token') && $request->bearerToken()) {
                $request->merge(['token' => $request->bearerToken()]);
            }

            // Validamos los datos recibidos
            $request->validate([
                'token' => 'required|string',
                'name' => 'required|string|max:255'
            ]);

            // Buscamos el token en la base de datos
            $token = Token::where('token', $request->token)->first();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token no válido',
                    'data' => null
                ], 401);
            }

            // Obtenemos el usuario del token
            $user = $token->user;

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                    'data' => null
                ], 404);
            }

            // Actualizamos el nombre
            $user->name = $request->name;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Nombre actualizado correctamente',
                'data' => $user
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos no válidos',
                'data' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el nombre: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
