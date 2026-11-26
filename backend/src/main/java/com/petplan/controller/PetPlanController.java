package com.petplan.controller;
import com.petplan.repository.Datos;
import org.springframework.web.bind.annotation.*;
import java.util.Map;
@RestController @RequestMapping("/api")
public class PetPlanController {
 private final Datos db;
 public PetPlanController(Datos db){this.db=db;}
 @GetMapping("/comprobacion") public Map<String,String> check(){db.uno("SELECT 1 AS conectado");return Map.of("estado","OK");}
}
