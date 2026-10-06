<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(): User
    {
        return User::factory()->create([
            'username' => 'victor',
            'email' => 'victor@example.com',
            'password' => 'secret123',
        ]);
    }

    public function test_lista_usuarios_paginados_de_diez_en_diez(): void
    {
        User::factory(12)->create();

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonPath('per_page', 10)
            ->assertJsonCount(10, 'data');
    }

    public function test_crea_un_usuario(): void
    {
        $this->postJson('/api/users/create', [
            'username' => 'nuevo',
            'email' => 'nuevo@example.com',
            'password' => 'secret123',
        ])->assertCreated()->assertJsonPath('user.email', 'nuevo@example.com');

        $this->assertDatabaseHas('users', ['email' => 'nuevo@example.com']);
    }

    public function test_crear_con_datos_invalidos_devuelve_422(): void
    {
        $this->crearUsuario();

        $this->postJson('/api/users/create', [
            'username' => 'victor',
            'email' => 'victor@example.com',
            'password' => '123',
        ])->assertStatus(422)->assertJsonValidationErrors(['username', 'email', 'password']);
    }

    public function test_login_correcto(): void
    {
        $this->crearUsuario();

        $this->postJson('/api/users/login', [
            'email' => 'victor@example.com',
            'password' => 'secret123',
        ])->assertOk()->assertJsonPath('user.username', 'victor');
    }

    public function test_login_sin_campos_devuelve_422(): void
    {
        $this->postJson('/api/users/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_con_contrasena_incorrecta_devuelve_401(): void
    {
        $this->crearUsuario();

        $this->postJson('/api/users/login', [
            'email' => 'victor@example.com',
            'password' => 'incorrecta',
        ])->assertStatus(401)
            ->assertExactJson(['message' => 'Las credenciales introducidas no son correctas.']);
    }

    public function test_login_con_email_inexistente_devuelve_401(): void
    {
        $this->postJson('/api/users/login', [
            'email' => 'noexiste@example.com',
            'password' => 'secret123',
        ])->assertStatus(401);
    }

    public function test_actualiza_username_email_y_password(): void
    {
        $this->crearUsuario();

        $this->postJson('/api/users/update_username', [
            'email' => 'victor@example.com',
            'password' => 'secret123',
            'username' => 'victor2',
        ])->assertOk()->assertJsonPath('user.username', 'victor2');

        $this->postJson('/api/users/update_email', [
            'email' => 'victor@example.com',
            'password' => 'secret123',
            'new_email' => 'victor2@example.com',
        ])->assertOk()->assertJsonPath('user.email', 'victor2@example.com');

        $this->postJson('/api/users/update_password', [
            'email' => 'victor2@example.com',
            'password' => 'secret123',
            'new_password' => 'nueva1234',
        ])->assertOk();

        $this->postJson('/api/users/login', [
            'email' => 'victor2@example.com',
            'password' => 'nueva1234',
        ])->assertOk();
    }

    public function test_updates_y_delete_con_credenciales_incorrectas_devuelven_401(): void
    {
        $this->crearUsuario();
        $base = ['email' => 'victor@example.com', 'password' => 'incorrecta'];

        $this->postJson('/api/users/update_username', $base + ['username' => 'otro'])->assertStatus(401);
        $this->postJson('/api/users/update_email', $base + ['new_email' => 'otro@example.com'])->assertStatus(401);
        $this->postJson('/api/users/update_password', $base + ['new_password' => 'nueva1234'])->assertStatus(401);
        $this->postJson('/api/users/delete', $base)->assertStatus(401);

        $this->assertDatabaseHas('users', ['email' => 'victor@example.com', 'username' => 'victor']);
    }

    public function test_elimina_el_usuario(): void
    {
        $this->crearUsuario();

        $this->postJson('/api/users/delete', [
            'email' => 'victor@example.com',
            'password' => 'secret123',
        ])->assertOk();

        $this->assertDatabaseMissing('users', ['email' => 'victor@example.com']);
    }
}
