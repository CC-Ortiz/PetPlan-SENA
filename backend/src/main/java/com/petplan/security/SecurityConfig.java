package com.petplan.security;

import jakarta.servlet.DispatcherType;
import jakarta.servlet.http.HttpServletResponse;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.http.HttpMethod;
import org.springframework.security.config.annotation.web.builders.HttpSecurity;
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity;
import org.springframework.security.config.http.SessionCreationPolicy;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.security.web.SecurityFilterChain;
import java.io.IOException;
import com.petplan.repository.Datos;
import com.petplan.service.CuentaService;
import org.springframework.security.authentication.AuthenticationManager;
import org.springframework.security.authentication.ProviderManager;
import org.springframework.security.authentication.dao.DaoAuthenticationProvider;
import org.springframework.security.core.userdetails.User;
import org.springframework.security.core.userdetails.UserDetailsService;
import org.springframework.security.core.userdetails.UsernameNotFoundException;
import org.springframework.security.web.authentication.UsernamePasswordAuthenticationFilter;
import java.util.List;
import java.util.Locale;

@Configuration
@EnableWebSecurity
public class SecurityConfig {
    @Bean
    BCryptPasswordEncoder passwordEncoder() { return new BCryptPasswordEncoder(); }

    static void error(HttpServletResponse response, int status, String message) throws IOException {
        response.setStatus(status);
        response.setContentType("application/json;charset=UTF-8");
        response.setHeader("Cache-Control", "no-store");
        if (status == 401) response.setHeader("WWW-Authenticate", "Bearer");
        response.getWriter().write("{\"message\":\"" + message + "\"}");
    }

    @Bean
    UserDetailsService usuarios(Datos db) {
        return email -> {
            var rows = db.lista("SELECT email,password,UPPER(rol) rol FROM usuario WHERE email=?",
                email.strip().toLowerCase(Locale.ROOT));
            if (rows.isEmpty()) throw new UsernameNotFoundException("Usuario no encontrado");
            var row = rows.getFirst();
            String rol = row.get("rol").toString();
            if (!List.of("USUARIO", "VETERINARIO", "ADMINISTRADOR", "SUPERADMINISTRADOR").contains(rol))
                throw new UsernameNotFoundException("Rol no válido");
            return User.withUsername(row.get("email").toString())
                .password(row.get("password").toString()).roles(rol).build();
        };
    }

    @Bean
    AuthenticationManager authenticationManager(UserDetailsService usuarios, BCryptPasswordEncoder encoder) {
        var provider = new DaoAuthenticationProvider(usuarios);
        provider.setPasswordEncoder(encoder);
        return new ProviderManager(provider);
    }
    @Bean
    SecurityFilterChain api(HttpSecurity http, CuentaService cuentas) throws Exception {
        // API local, sin cookies de autenticación. Laravel conserva CSRF en sus formularios.
        return http.sessionManagement(s -> s.sessionCreationPolicy(SessionCreationPolicy.STATELESS))
            .csrf(c -> c.ignoringRequestMatchers("/api/**"))
            .httpBasic(b -> b.disable())
            .formLogin(f -> f.disable())
            .logout(l -> l.disable())
            .requestCache(c -> c.disable())
            .exceptionHandling(e -> e
                .authenticationEntryPoint((req, res, ex) -> error(res, 401, "Sesión inválida o vencida."))
                .accessDeniedHandler((req, res, ex) -> error(res, 403, "Acceso denegado.")))
            .authorizeHttpRequests(a -> a
                .dispatcherTypeMatchers(DispatcherType.ERROR).permitAll()
                .requestMatchers(HttpMethod.GET, "/api/comprobacion").permitAll()
                .requestMatchers(HttpMethod.POST, "/api/login", "/api/registro", "/api/recuperacion", "/api/reset").permitAll()
                .requestMatchers(HttpMethod.GET, "/api/me").authenticated()
                .requestMatchers(HttpMethod.POST, "/api/logout").authenticated()
                .requestMatchers(HttpMethod.PUT, "/api/perfil", "/api/password").authenticated()
                .requestMatchers("/api/mascotas", "/api/mascotas/**").hasRole("USUARIO")
                .requestMatchers(HttpMethod.GET, "/api/veterinarias").authenticated()
                .requestMatchers("/api/veterinarias", "/api/veterinarias/**", "/api/administradores", "/api/administradores/**").hasRole("SUPERADMINISTRADOR")
                .requestMatchers("/api/veterinarios", "/api/veterinarios/**").hasAnyRole("ADMINISTRADOR", "SUPERADMINISTRADOR")
                .requestMatchers(HttpMethod.POST, "/api/citas/{id}/aceptar").hasRole("VETERINARIO")
                .requestMatchers(HttpMethod.GET, "/api/citas", "/api/citas/{id}").hasAnyRole("USUARIO", "VETERINARIO", "ADMINISTRADOR")
                .requestMatchers(HttpMethod.POST, "/api/citas").hasRole("USUARIO")
                .requestMatchers(HttpMethod.PUT, "/api/citas/{id}").hasRole("USUARIO")
                .requestMatchers(HttpMethod.DELETE, "/api/citas/{id}").hasRole("USUARIO")
                .anyRequest().denyAll())
            .addFilterBefore(new TokenSesionFilter(cuentas), UsernamePasswordAuthenticationFilter.class)
            .build();
    }
}
