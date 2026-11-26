<?php
namespace App\Http\Controllers;
use App\Services\PetPlanApi;
use Illuminate\Http\Request;
class AuthController extends Controller {
    public function __construct(private PetPlanApi $api) {}
    public function registro(Request $r) {
        $this->api->request('POST','/registro',$r->only('nombres','apellidos','email','numero_contacto','numero_documento','user','password','password_confirmation'));
        return redirect()->route('login')->with('status','Cuenta registrada. Inicia sesión.');
    }
    public function login(Request $r) {
        $result=$this->api->request('POST','/login',$r->only('email','password'));
        $r->session()->regenerate(); $r->session()->put('petplan_token',$result['token']); $r->session()->put('petplan_usuario',$result['usuario']);
        return redirect()->route('dashboard');
    }
    public function logout(Request $r) {
        try { if ($r->session()->has('petplan_token')) $this->api->request('POST','/logout'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException | \Illuminate\Validation\ValidationException $e) { /* local logout still completes */ }
        finally { $r->session()->invalidate(); $r->session()->regenerateToken(); }
        return redirect()->route('login');
    }
    public function recuperar(Request $r) { $this->api->request('POST','/recuperacion',$r->only('email')); return back()->with('status','Si existe la cuenta, recibirás un correo.'); }
    public function reset(Request $r) { $this->api->request('POST','/reset',$r->only('email','token','password','password_confirmation')); return redirect()->route('login')->with('status','Contraseña actualizada.'); }
}
