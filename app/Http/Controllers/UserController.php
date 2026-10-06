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
    public function index()
    {
        try {
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

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8'
            ]);

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

    public function create(Request $request)
    {
        return $this->store($request);
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:8'
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email o contraseña incorrectos',
                    'data' => null
                ], 401);
            }

            Token::where('user_id', $user->id)->delete();

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

    public function updateName(Request $request)
    {
        try {
            if (!$request->filled('token') && $request->bearerToken()) {
                $request->merge(['token' => $request->bearerToken()]);
            }

            $request->validate([
                'token' => 'required|string',
                'name' => 'required|string|max:255'
            ]);

            $token = Token::where('token', $request->token)->first();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token no válido',
                    'data' => null
                ], 401);
            }

            $user = $token->user;

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                    'data' => null
                ], 404);
            }

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