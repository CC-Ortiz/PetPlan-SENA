package com.petplan.service;
import org.springframework.security.authentication.AuthenticationManager;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.AuthenticationException;
import com.petplan.repository.Datos; import org.springframework.stereotype.Service; import org.springframework.transaction.annotation.Transactional; import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder; import org.springframework.web.server.ResponseStatusException; import org.springframework.http.HttpStatus; import org.springframework.mail.javamail.JavaMailSender; import org.springframework.mail.SimpleMailMessage; import org.springframework.beans.factory.annotation.Value; import java.util.*; import java.util.concurrent.ConcurrentHashMap; import java.nio.charset.StandardCharsets; import java.security.*; import java.time.*; import java.net.URLEncoder; import static com.petplan.service.Reglas.*;
@Service public class CuentaService {
 public static final String USUARIO="USUARIO", VET="VETERINARIO", ADMIN="ADMINISTRADOR", SUPER="SUPERADMINISTRADOR";
 private final AuthenticationManager autenticacion; private final Datos db; private final BCryptPasswordEncoder encoder; private final JavaMailSender mail; private final String url,from;
 private record Sesion(long id,Instant vence){} private final Map<String,Sesion> sesiones=new ConcurrentHashMap<>(), recuperaciones=new ConcurrentHashMap<>();
 public CuentaService(Datos db,AuthenticationManager autenticacion,BCryptPasswordEncoder encoder,JavaMailSender mail,@Value("${petplan.web-url}") String url,@Value("${petplan.mail-from}") String from){this.db=db;this.autenticacion=autenticacion;this.encoder=encoder;this.mail=mail;this.url=url;this.from=from;}
 public record Actor(long id,String rol){public void exige(String... roles){if(!List.of(roles).contains(rol))throw prohibido();}}
 public Map<String,Object> perfil(long id){return db.uno("SELECT codigo_usuario,nombres,apellidos,email,numero_contacto,numero_documento,`user`,UPPER(rol) rol FROM usuario WHERE codigo_usuario=?",id);}
 private void limpiar(Map<String,Sesion> m){m.entrySet().removeIf(e->!e.getValue().vence().isAfter(Instant.now()));}
 public Actor actor(String token){limpiar(sesiones);Sesion s=token==null?null:sesiones.get(hash(token));if(s==null)throw new ResponseStatusException(HttpStatus.UNAUTHORIZED,"Sesión inválida o vencida");Map<String,Object> r;
  try { r=perfil(s.id()); } catch(ResponseStatusException e) {
   if(e.getStatusCode().value()!=404) throw e;
   sesiones.remove(hash(token)); throw new ResponseStatusException(HttpStatus.UNAUTHORIZED,"Sesión inválida o vencida");
  }var a=new Actor(s.id(),r.get("rol").toString());if(!List.of(USUARIO,VET,ADMIN,SUPER).contains(a.rol()))throw prohibido();return a;}
 @Transactional public Map<String,Object> registro(Map<String,Object>d){return perfil(crear(d,USUARIO));}
 public long crear(Map<String,Object>d,String rol){String email=texto(d,"email",120,true).toLowerCase(Locale.ROOT);if(!email.matches("[^\\s@]+@[^\\s@]+\\.[^\\s@]+"))throw invalido("Correo inválido");return db.insertar("INSERT INTO usuario(nombres,apellidos,email,numero_contacto,numero_documento,`user`,password,rol) VALUES(?,?,?,?,?,?,?,?)",texto(d,"nombres",80,true),texto(d,"apellidos",80,true),email,texto(d,"numero_contacto",20,true),texto(d,"numero_documento",20,true),texto(d,"user",50,true),encoder.encode(password(d)),rol);}
 public Map<String,Object> login(Map<String,Object>d){
  String email=texto(d,"email",120,true).toLowerCase(Locale.ROOT);
  String pass=String.valueOf(d.getOrDefault("password",""));
  if(pass.getBytes(StandardCharsets.UTF_8).length>72)throw new ResponseStatusException(HttpStatus.UNAUTHORIZED,"Credenciales incorrectas");
  try { autenticacion.authenticate(UsernamePasswordAuthenticationToken.unauthenticated(email,pass)); }
  catch(AuthenticationException e){throw new ResponseStatusException(HttpStatus.UNAUTHORIZED,"Credenciales incorrectas");}
  long id=n(db.uno("SELECT codigo_usuario FROM usuario WHERE email=?",email),"codigo_usuario");
  var usuario=perfil(id);
  String t=token();limpiar(sesiones);sesiones.put(hash(t),new Sesion(id,Instant.now().plus(Duration.ofHours(2))));
  return Map.of("token",t,"usuario",usuario);
 }
 public void logout(String t){if(t!=null)sesiones.remove(hash(t));}
 @Transactional public void editar(Actor a,Map<String,Object>d){String nom=texto(d,"nombres",80,true),ape=texto(d,"apellidos",80,true),tel=texto(d,"numero_contacto",20,true);db.cambiar("UPDATE usuario SET nombres=?,apellidos=?,numero_contacto=? WHERE codigo_usuario=?",nom,ape,tel,a.id());if(a.rol().equals(VET))db.cambiar("UPDATE veterinario SET nombres_vet=?,apellidos_vet=?,num_contacto_vet=? WHERE codigo_usuario=?",nom,ape,tel,a.id());}
 @Transactional public void cambiarPassword(Actor a,Map<String,Object>d){var old=db.uno("SELECT password FROM usuario WHERE codigo_usuario=?",a.id());if(!encoder.matches(String.valueOf(d.get("actual")),old.get("password").toString()))throw invalido("Contraseña actual incorrecta");guardarPassword(a.id(),password(d));}
 public void recuperar(Map<String,Object>d){String email=texto(d,"email",120,true);var rows=db.lista("SELECT codigo_usuario FROM usuario WHERE email=?",email);if(rows.isEmpty())return;long id=n(rows.getFirst(),"codigo_usuario");String t=token();limpiar(recuperaciones);recuperaciones.entrySet().removeIf(e->e.getValue().id()==id);var msg=new SimpleMailMessage();msg.setFrom(from);msg.setTo(email);msg.setSubject("PetPlan - Recuperar contraseña");msg.setText(url+"/pass-reset/"+t+"?email="+URLEncoder.encode(email,StandardCharsets.UTF_8));mail.send(msg);recuperaciones.put(hash(t),new Sesion(id,Instant.now().plus(Duration.ofMinutes(30))));}
 @Transactional public synchronized void reset(Map<String,Object>d){String t=texto(d,"token",64,true),email=texto(d,"email",120,true);limpiar(recuperaciones);Sesion s=recuperaciones.get(hash(t));if(s==null||!perfil(s.id()).get("email").toString().equalsIgnoreCase(email))throw invalido("Enlace inválido o vencido");guardarPassword(s.id(),password(d));}
 private void guardarPassword(long id,String p){db.cambiar("UPDATE usuario SET password=? WHERE codigo_usuario=?",encoder.encode(p),id);sesiones.entrySet().removeIf(e->e.getValue().id()==id);recuperaciones.entrySet().removeIf(e->e.getValue().id()==id);}
 private String password(Map<String,Object>d){Object v=d.get("password");if(!(v instanceof String p)||p.length()<8||p.getBytes(StandardCharsets.UTF_8).length>72||!p.equals(d.get("password_confirmation")))throw invalido("Contraseña inválida o confirmación diferente (mínimo 8 caracteres, máximo 72 bytes)");return p;}
 private static String token(){byte[] b=new byte[32];new SecureRandom().nextBytes(b);return HexFormat.of().formatHex(b);}
 private static String hash(String t){try{return HexFormat.of().formatHex(MessageDigest.getInstance("SHA-256").digest(t.getBytes(StandardCharsets.UTF_8)));}catch(NoSuchAlgorithmException e){throw new IllegalStateException(e);}}
}
