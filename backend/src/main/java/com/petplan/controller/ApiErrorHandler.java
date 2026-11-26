package com.petplan.controller;
import org.springframework.http.*;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.bind.MethodArgumentNotValidException;
import org.springframework.web.server.ResponseStatusException;
import org.springframework.http.converter.HttpMessageNotReadableException;
import org.springframework.dao.DataIntegrityViolationException;
import java.util.*;
@RestControllerAdvice
public class ApiErrorHandler {
    @ExceptionHandler(MethodArgumentNotValidException.class) ResponseEntity<?> invalid(MethodArgumentNotValidException e){
        Map<String,List<String>> errors=new LinkedHashMap<>();
        e.getBindingResult().getFieldErrors().forEach(f->errors.computeIfAbsent(f.getField(),k->new ArrayList<>()).add(f.getDefaultMessage()));
        return ResponseEntity.badRequest().body(Map.of("message","La información suministrada contiene errores. Verifique la información nuevamente.","errors",errors));
    }
    @ExceptionHandler(HttpMessageNotReadableException.class) ResponseEntity<?> malformed(){return ResponseEntity.badRequest().body(Map.of("message","Formato de datos inválido."));}
    @ExceptionHandler(ResponseStatusException.class) ResponseEntity<?> business(ResponseStatusException e){return ResponseEntity.status(e.getStatusCode()).body(Map.of("message",Objects.requireNonNullElse(e.getReason(),"Solicitud rechazada.")));}
    @ExceptionHandler(DataIntegrityViolationException.class) ResponseEntity<?> integrity(){return ResponseEntity.status(409).body(Map.of("message","No se pudo guardar por una restricción de datos."));}
}
