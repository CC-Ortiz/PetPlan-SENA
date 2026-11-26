package com.petplan.controller;
import com.petplan.service.*; import com.petplan.dto.*;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*; import org.springframework.http.*; import jakarta.validation.Valid;
import java.util.*; import java.io.IOException;
import static com.petplan.service.CuentaService.*; import static com.petplan.service.Reglas.*;
@RestController @RequestMapping("/api")
public class PetPlanController {
 private final CuentaService cuentas;private final MascotaService mascotas;
 public PetPlanController(CuentaService c,MascotaService m){cuentas=c;mascotas=m;}
 private long u(CuentaService.Actor actor){actor.exige(USUARIO);return actor.id();}
 @GetMapping("/comprobacion") public Map<String,String> check(){return Map.of("estado","OK");}
 @PostMapping("/registro") @ResponseStatus(HttpStatus.CREATED) public Object registro(@RequestBody Map<String,Object>d){return cuentas.registro(d);}
 @PostMapping("/login") public Object login(@RequestBody Map<String,Object>d){return cuentas.login(d);}
 @GetMapping("/me") public Object me(@AuthenticationPrincipal CuentaService.Actor actor){return cuentas.perfil(actor.id());}
 @PostMapping("/logout") @ResponseStatus(HttpStatus.NO_CONTENT) public void logout(@RequestHeader("Authorization") String header){cuentas.logout(header.substring(7));}
 @PutMapping("/perfil") @ResponseStatus(HttpStatus.NO_CONTENT) public void perfil(@AuthenticationPrincipal CuentaService.Actor actor,@RequestBody Map<String,Object>d){cuentas.editar(actor,d);}
 @PutMapping("/password") @ResponseStatus(HttpStatus.NO_CONTENT) public void password(@AuthenticationPrincipal CuentaService.Actor actor,@RequestBody Map<String,Object>d){cuentas.cambiarPassword(actor,d);}
 @PostMapping("/recuperacion") @ResponseStatus(HttpStatus.NO_CONTENT) public void recuperar(@RequestBody Map<String,Object>d){cuentas.recuperar(d);}
 @PostMapping("/reset") @ResponseStatus(HttpStatus.NO_CONTENT) public void reset(@RequestBody Map<String,Object>d){cuentas.reset(d);}
 @GetMapping("/mascotas") public Object mascotas(@AuthenticationPrincipal CuentaService.Actor actor){return mascotas.listar(u(actor));}
 @GetMapping("/mascotas/{id}") public Object mascota(@AuthenticationPrincipal CuentaService.Actor actor,@PathVariable long id){return mascotas.ver(u(actor),id);}
 @PostMapping("/mascotas") @ResponseStatus(HttpStatus.CREATED) public Object crearMascota(@AuthenticationPrincipal CuentaService.Actor actor,@Valid @RequestBody MascotaRequest d){return mascotas.crear(u(actor),d);}
 @PutMapping("/mascotas/{id}") public Object editarMascota(@AuthenticationPrincipal CuentaService.Actor actor,@PathVariable long id,@Valid @RequestBody MascotaRequest d){return mascotas.editar(u(actor),id,d);}
 @DeleteMapping("/mascotas/{id}") @ResponseStatus(HttpStatus.NO_CONTENT) public void eliminar(@AuthenticationPrincipal CuentaService.Actor actor,@PathVariable long id){mascotas.eliminar(u(actor),id);}
}
