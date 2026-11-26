package com.petplan.security;

import com.petplan.service.CuentaService;
import jakarta.servlet.FilterChain;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.authority.SimpleGrantedAuthority;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.web.filter.OncePerRequestFilter;
import org.springframework.web.server.ResponseStatusException;
import java.io.IOException;
import java.util.List;

/** Convierte el token opaco de PetPlan en una identidad autenticada de Spring Security. */
public final class TokenSesionFilter extends OncePerRequestFilter {
    private final CuentaService cuentas;

    public TokenSesionFilter(CuentaService cuentas) {
        this.cuentas = cuentas;
    }

    @Override
    protected boolean shouldNotFilter(HttpServletRequest request) {
        String path = request.getRequestURI().substring(request.getContextPath().length());
        return ("GET".equals(request.getMethod()) && "/api/comprobacion".equals(path))
            || ("POST".equals(request.getMethod()) && List.of("/api/login", "/api/registro",
                "/api/recuperacion", "/api/reset").contains(path));
    }

    @Override
    protected void doFilterInternal(HttpServletRequest request, HttpServletResponse response,
                                    FilterChain chain) throws ServletException, IOException {
        String header = request.getHeader("Authorization");
        if (header == null) {
            chain.doFilter(request, response); // Spring Security decide si exige sesión.
            return;
        }
        if (!header.regionMatches(true, 0, "Bearer ", 0, 7)
                || !header.substring(7).matches("[0-9a-f]{64}")) {
            SecurityConfig.error(response, 401, "Sesión inválida o vencida.");
            return;
        }
        CuentaService.Actor actor;
        try {
            actor = cuentas.actor(header.substring(7));
        } catch (ResponseStatusException e) {
            int status = e.getStatusCode().value();
            if (status != 401 && status != 403) throw e;
            SecurityContextHolder.clearContext();
            SecurityConfig.error(response, status, status == 401
                ? "Sesión inválida o vencida." : "Acceso denegado.");
            return;
        }
        var authentication = UsernamePasswordAuthenticationToken.authenticated(actor, null,
            List.of(new SimpleGrantedAuthority("ROLE_" + actor.rol())));
        var context = SecurityContextHolder.createEmptyContext();
        context.setAuthentication(authentication);
        SecurityContextHolder.setContext(context);
        chain.doFilter(request, response);
    }
}
