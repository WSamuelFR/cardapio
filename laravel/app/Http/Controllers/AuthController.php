<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['E-mail ou senha incorretos.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Este usuário foi desativado. Entre em contato com o administrador.',
            ], 403);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login realizado com sucesso.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'document' => $user->document,
                'role' => $user->role,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Sessão encerrada com sucesso.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['authenticated' => false, 'user' => null]);
        }

        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'document' => $user->document,
                'role' => $user->role,
            ],
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Usuário não autenticado.'], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:30',
            'document' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
        ]);

        $cleanName = trim(preg_replace('/\s+/', ' ', preg_replace('/[^\p{L}\s]/u', '', $validated['name'])));
        if (empty($cleanName)) {
            return response()->json(['message' => 'Nome completo inválido. Use apenas letras e espaços.'], 422);
        }

        if (!empty($validated['document'])) {
            $digits = preg_replace('/\D/', '', $validated['document']);
            if (strlen($digits) > 0 && strlen($digits) !== 11) {
                return response()->json(['message' => 'O CPF deve conter exatamente 11 dígitos numéricos.'], 422);
            }
            if (strlen($digits) === 11 && !$this->isValidCpf($validated['document'])) {
                return response()->json(['message' => 'O CPF informado é inválido. Verifique os números digitados.'], 422);
            }
        }

        $user->name = $cleanName;
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->document = $validated['document'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Seus dados foram atualizados com sucesso!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'document' => $user->document,
                'role' => $user->role,
            ],
        ]);
    }

    private function isValidCpf(?string $cpf): bool
    {
        if (empty($cpf)) {
            return true;
        }

        $digits = preg_replace('/\D/', '', $cpf);
        if (strlen($digits) !== 11) {
            return false;
        }

        if (preg_match('/^(\d)\1{10}$/', $digits)) {
            return false;
        }

        // 1º Dígito Verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $digits[$i] * (10 - $i);
        }
        $rest = ($sum * 10) % 11;
        $d1 = ($rest === 10 || $rest === 11) ? 0 : $rest;
        if ($d1 !== (int) $digits[9]) {
            return false;
        }

        // 2º Dígito Verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $digits[$i] * (11 - $i);
        }
        $rest = ($sum * 10) % 11;
        $d2 = ($rest === 10 || $rest === 11) ? 0 : $rest;
        return $d2 === (int) $digits[10];
    }
}

