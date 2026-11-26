<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
class PetPlanApi {
    public function request(string $method, string $path, array $data = [], bool $pdf = false): mixed {
        $client = Http::baseUrl(rtrim(config('services.petplan.url'), '/').'/api')
            ->accept($pdf ? 'application/pdf' : 'application/json')->timeout(15);
        $public = ($method === 'GET' && $path === '/comprobacion')
            || ($method === 'POST' && in_array($path, ['/login', '/registro', '/recuperacion', '/reset'], true));
        if (!$public && session('petplan_token')) {
            $client = $client->withToken((string) session('petplan_token'));
        }
        try {
            $response = $client->send($method, $path, $method === 'GET' ? ['query'=>$data] : ['json'=>$data]);
        } catch (ConnectionException $e) { throw new HttpException(503, 'Spring Boot no está disponible. Comprueba el puerto 8080.'); }
        if ($response->failed()) {
            $message = $response->json('message') ?: 'Solicitud rechazada por Spring Boot.';
            if (!$public && $response->status() === 401) {
                session()->invalidate();
                session()->regenerateToken();
                throw new HttpException(401, 'La sesión terminó. Inicia sesión nuevamente.');
            }
            if ($method !== 'GET' && in_array($response->status(), [400, 401, 409, 422])) {
                $errors = $response->json('errors') ?: ['petplan'=>$message];
                throw ValidationException::withMessages($errors);
            }
            throw new HttpException($response->status(), $message);
        }
        return $pdf ? $response->body() : ($response->json() ?? []);
    }
}
