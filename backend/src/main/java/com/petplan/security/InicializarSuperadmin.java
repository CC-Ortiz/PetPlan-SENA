package com.petplan.security;
import com.petplan.repository.Datos; import com.petplan.service.CuentaService;
import org.springframework.boot.ApplicationRunner; import org.springframework.context.annotation.*; import org.springframework.core.env.Environment;
import java.util.Map;
@Configuration public class InicializarSuperadmin {
 @Bean ApplicationRunner superadmin(Datos db,CuentaService c,Environment env){return args->{String email=env.getProperty("PETPLAN_SUPER_EMAIL");if(email==null||email.isBlank()||!db.lista("SELECT codigo_usuario FROM usuario WHERE UPPER(rol)='SUPERADMINISTRADOR'").isEmpty())return;String p=env.getRequiredProperty("PETPLAN_SUPER_PASSWORD");c.crear(Map.of("email",email,"password",p,"password_confirmation",p,"nombres",env.getRequiredProperty("PETPLAN_SUPER_NOMBRES"),"apellidos",env.getRequiredProperty("PETPLAN_SUPER_APELLIDOS"),"numero_contacto",env.getRequiredProperty("PETPLAN_SUPER_CONTACTO"),"numero_documento",env.getRequiredProperty("PETPLAN_SUPER_DOCUMENTO"),"user",env.getRequiredProperty("PETPLAN_SUPER_USER")),CuentaService.SUPER);};}
}
