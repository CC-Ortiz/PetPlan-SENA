package com.petplan.controller;
import java.util.Map;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RestController;
@RestController
public class ComprobacionController {
    @GetMapping("/api/comprobacion")
    public Map<String, Object> comprobar() { return Map.of("estado", "ok", "etapa", "ST01.1"); }
}
