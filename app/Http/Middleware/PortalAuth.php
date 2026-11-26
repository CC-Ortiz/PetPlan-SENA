<?php
namespace App\Http\Middleware;
use App\Services\PetPlanApi;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
class PortalAuth {
    public function handle(Request $request, Closure $next, string ...$roles) {
        if (!$request->session()->has('petplan_token')) return redirect()->route('login');
        try { $usuario = app(PetPlanApi::class)->request('GET', '/me'); }
        catch (HttpException $e) {
            if ($e->getStatusCode() !== 401) throw $e;
            $request->session()->invalidate(); $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['petplan'=>'La sesión terminó. Inicia sesión nuevamente.']);
        }
        $request->session()->put('petplan_usuario', $usuario);
        abort_if($roles && !in_array($usuario['rol'], $roles, true), 403);
        return $next($request);
    }
}
