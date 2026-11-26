package com.petplan.controller;
import com.petplan.service.*; import com.petplan.dto.*;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*; import org.springframework.http.*; import jakarta.validation.Valid;
import java.util.*; import java.io.IOException;
import static com.petplan.service.CuentaService.*; import static com.petplan.service.Reglas.*;
@RestController @RequestMapping("/api")
public class PetPlanController {
 private final CuentaService cuentas;
 public PetPlanController(CuentaService c){cuentas=c;}
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
}
