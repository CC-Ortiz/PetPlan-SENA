<?php
namespace App\Http\Controllers;
use App\Services\PetPlanApi;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
class PortalController extends Controller {
    public function __construct(private PetPlanApi $api) {}
    private function get(string $path, array $filters=[]): array { return $this->api->request('GET',$path,$filters); }
    private function role(): string { return session('petplan_usuario.rol'); }
    private function datos(Request $r,array $fields): array { return $r->only($fields); }
    private function cuenta(Request $r): array { return $r->only('nombres','apellidos','email','numero_contacto','numero_documento','user','password','password_confirmation','num_licencia','codigo_veterinarias'); }
    private function ok(string $method,string $endpoint,array $data=[],string $route='dashboard') { $this->api->request($method,$endpoint,$data); return redirect()->route($route)->with('status','Operación completada.'); }
    public function dashboard() {
        $rol = $this->role();
        $estadisticas = []; $accesos = []; $avisos = []; $citas = null; $clinicas = null;
        // Solo se consultan módulos disponibles en esta etapa y autorizados al rol.
        $disponible = fn($ruta) => \Illuminate\Support\Facades\Route::has($ruta);
        $consultar = function ($endpoint, $titulo) use (&$avisos) {
            try { return $this->get($endpoint); }
            catch (HttpException $e) {
                if (!in_array($e->getStatusCode(), [403,404,409], true)) throw $e;
                $avisos[] = 'No se pudo consultar '.$titulo.'. Verifica los permisos y la asignación a una veterinaria.';
                return null;
            }
        };
        $stat = function ($titulo,$icono,$color,$ruta,$total) use (&$estadisticas) {
            $estadisticas[] = compact('titulo','icono','color','ruta','total');
        };
        $acceso = function ($titulo,$descripcion,$icono,$ruta) use (&$accesos,$disponible) {
            if ($disponible($ruta)) $accesos[] = compact('titulo','descripcion','icono','ruta');
        };
        if ($rol === 'USUARIO' && $disponible('mascotas.index')) {
            $mascotas = $consultar('/mascotas','las mascotas');
            $stat('Mascotas Registradas','🐶','green-bg','mascotas.index',$mascotas === null ? null : count($mascotas));
            $acceso('Registrar Mascota','Añade una mascota a tu perfil.','🐶','mascotas.create');
        }
        if (in_array($rol,['USUARIO','VETERINARIO','ADMINISTRADOR'],true) && $disponible('citas.index')) {
            $datos = $consultar('/citas','las citas');
            if ($datos !== null) {
                $fecha = fn($valor) => is_numeric($valor)
                    ? \Illuminate\Support\Carbon::createFromTimestampMs((int)$valor)
                    : \Illuminate\Support\Carbon::parse($valor);
                $citas = array_values(array_filter($datos,fn($c) => $fecha($c['fecha'])->isFuture()));
                usort($citas,fn($a,$b) => $fecha($a['fecha'])->getTimestamp() <=> $fecha($b['fecha'])->getTimestamp());
                foreach ($citas as &$cita) $cita['fecha_panel'] = $fecha($cita['fecha'])->format('d/m/Y - h:i A');
                unset($cita);
                $pendientes = count(array_filter($citas,fn($c) => !$c['confirmacion']));
                $confirmadas = count($citas)-$pendientes;
                $citas = array_slice($citas,0,5);
            }
            $stat('Próximas Citas Pendientes','📅','light-green-bg','citas.index',$pendientes ?? null);
            $stat('Próximas Citas Confirmadas','✅','outline-bg','citas.index',$confirmadas ?? null);
            if ($rol === 'USUARIO') $acceso('Agendar Cita','Programa una atención veterinaria.','📅','citas.create');
            else $acceso('Consultar Citas','Revisa la agenda de tu veterinaria.','📅','citas.index');
        }
        if ($rol === 'USUARIO' && $disponible('historial.index')) {
            $acceso('Historial Clínico','Consulta los registros de tus mascotas.','📋','historial.index');
        }
        if ($disponible('veterinarias.index')) {
            if ($rol === 'SUPERADMINISTRADOR') {
                $datos = $consultar('/veterinarias','las veterinarias');
                $clinicas = $datos === null ? null : array_values(array_filter($datos,fn($c) => $c['estado']==='Pendiente'));
                $stat('Veterinarias Registradas','🏥','green-bg','veterinarias.index',$datos === null ? null : count($datos));
                $stat('Por Aprobar','📋','light-green-bg','veterinarias.index',$clinicas === null ? null : count($clinicas));
                if ($clinicas !== null) $clinicas = array_slice($clinicas,0,5);
            }
            $acceso('Veterinarias','Consulta los establecimientos registrados.','🏥','veterinarias.index');
        }
        if (in_array($rol,['ADMINISTRADOR','SUPERADMINISTRADOR'],true) && $disponible('cuentas')) {
            $veterinarios = $consultar('/veterinarios','los veterinarios');
            $stat('Veterinarios','🩺','outline-bg','cuentas',$veterinarios === null ? null : count($veterinarios));
            $acceso('Gestión de Cuentas','Administra las cuentas autorizadas.','👥','cuentas');
        }
        $acceso('Mi Perfil','Consulta o actualiza tus datos personales.','👤','perfil');
        return view('portal.dashboard',compact('estadisticas','accesos','avisos','citas','clinicas'));
    }
    public function perfil() { return view('portal.perfil',['usuario'=>$this->get('/me')]); }
    public function editarPerfil(Request $r) { return $this->ok('PUT','/perfil',$r->only('nombres','apellidos','numero_contacto'),'perfil'); }
    public function password(Request $r) { $this->api->request('PUT','/password',$r->only('actual','password','password_confirmation')); $r->session()->invalidate(); $r->session()->regenerateToken(); return redirect()->route('login')->with('status','Contraseña actualizada.'); }
    public function mascotas() { return view('portal.mascotas',['mascotas'=>$this->get('/mascotas')]); }
    public function mascota($id) { return view('portal.mascota',['mascota'=>$this->get('/mascotas/'.$id),'editar'=>false]); }
    public function nuevaMascota() { return view('portal.mascota',['mascota'=>null,'editar'=>true]); }
    public function editarMascota($id) { return view('portal.mascota',['mascota'=>$this->get('/mascotas/'.$id),'editar'=>true]); }
    private function pet(Request $r): array { $d=$r->only('nombre_mascota','especie','edad','peso'); foreach (['edad','peso'] as $key) if (($d[$key]??null)==='') $d[$key]=null; return $d; }
    public function crearMascota(Request $r) { return $this->ok('POST','/mascotas',$this->pet($r),'mascotas.index'); }
    public function guardarMascota(Request $r,$id) { return $this->ok('PUT','/mascotas/'.$id,$this->pet($r),'mascotas.index'); }
    public function eliminarMascota($id) { return $this->ok('DELETE','/mascotas/'.$id,[],'mascotas.index'); }
    public function veterinarias() { return view('portal.veterinarias',['clinicas'=>$this->get('/veterinarias')]); }
    public function crearClinica(Request $r) { return $this->ok('POST','/veterinarias',$r->only('nit','nombre','contacto'),'veterinarias.index'); }
    public function estadoClinica(Request $r,$id) { return $this->ok('PUT','/veterinarias/'.$id.'/estado',$r->only('estado'),'veterinarias.index'); }
    public function cuentas() { $admin=$this->role()==='SUPERADMINISTRADOR'; return view('portal.cuentas',['veterinarios'=>$this->get('/veterinarios'),'administradores'=>$admin?$this->get('/administradores'):[],'clinicas'=>$admin?$this->get('/veterinarias'):[]]); }
    public function crearAdmin(Request $r) { $d=$this->api->request('POST','/administradores',$this->cuenta($r));return redirect()->route('cuentas')->with('status','Administrador creado: código '.$d['codigo_usuario'].'. Asigna la clínica en PETPLAN_ADMIN_CLINICAS.'); }
    public function crearVet(Request $r) { return $this->ok('POST','/veterinarios',$this->cuenta($r),'cuentas'); }
    public function editarVet(Request $r,$id) { return $this->ok('PUT','/veterinarios/'.$id,$r->only('nombres','apellidos','numero_contacto','num_licencia'),'cuentas'); }
    public function citas(Request $r) { return view('portal.citas',['citas'=>$this->get('/citas',$r->only('desde','hasta','estado','veterinaria')),'clinicas'=>$this->get('/veterinarias')]); }
    public function agendar() { return view('portal.agendar',['clinicas'=>$this->get('/veterinarias')]); }
    public function crearCita(Request $r) { return $this->ok('POST','/citas',$r->only('tipo_cita','descripcion','fecha','codigo_veterinarias'),'citas.index'); }
    public function reprogramar(Request $r,$id) { return $this->ok('PUT','/citas/'.$id,$r->only('fecha'),'citas.index'); }
    public function cancelar($id) { return $this->ok('DELETE','/citas/'.$id,[],'citas.index'); }
    public function aceptar($id) { return $this->ok('POST','/citas/'.$id.'/aceptar',[],'citas.index'); }
    public function historiaForm($id) { return view('portal.historia-form',['id'=>$id,'mascotas'=>$this->get('/citas/'.$id.'/mascotas')]); }
    public function historia(Request $r,$id) { return $this->ok('POST','/citas/'.$id.'/historia',$r->only('codigo_mascota','diagnostico','observaciones'),'citas.index'); }
    public function historial(Request $r) { $f=$r->only('mascota','desde','hasta','cita');$vet=$this->role()==='VETERINARIO';return view('portal.historial',['registros'=>$this->get('/historial',$f),'mascotas'=>$vet?$this->get('/citas/'.(int)$r->query('cita').'/mascotas'):$this->get('/mascotas'),'cita'=>$vet?$r->query('cita'):null]); }
    public function reporte(Request $r) { $pdf=$this->api->request('GET','/reportes/historial',$r->only('mascota','desde','hasta','cita'),true);return response($pdf,200,['Content-Type'=>'application/pdf','Content-Disposition'=>'attachment; filename="petplan-historial.pdf"','Cache-Control'=>'no-store']); }
}
