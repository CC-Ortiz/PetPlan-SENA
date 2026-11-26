package com.petplan.service;
import org.springframework.web.server.ResponseStatusException; import org.springframework.http.HttpStatus;
import java.util.*; import java.time.*;
public final class Reglas {
 private Reglas(){}
 public static ResponseStatusException invalido(String s){return new ResponseStatusException(HttpStatus.BAD_REQUEST,s);}
 public static ResponseStatusException prohibido(){return new ResponseStatusException(HttpStatus.FORBIDDEN,"Operación no autorizada");}
 public static ResponseStatusException noEncontrado(String s){return new ResponseStatusException(HttpStatus.NOT_FOUND,s);}
 public static ResponseStatusException conflicto(String s){return new ResponseStatusException(HttpStatus.CONFLICT,s);}
 public static String texto(Map<String,Object> d,String k,int max,boolean obligatorio){Object o=d.get(k); if(o==null||o.toString().isBlank()){if(obligatorio)throw invalido("Campo requerido: "+k);return null;} if(!(o instanceof String)||o.toString().length()>max)throw invalido("Campo inválido: "+k);return o.toString().strip();}
 public static long id(Object x){try{long v=Long.parseLong(String.valueOf(x));if(v<1||v>4294967295L)throw new NumberFormatException();return v;}catch(Exception e){throw invalido("Identificador inválido");}}
 public static LocalDate fecha(String x){try{return LocalDate.parse(x);}catch(Exception e){throw invalido("Fecha inválida");}}
 public static long n(Map<String,Object> row,String key){return ((Number)row.get(key)).longValue();}
 public static boolean si(Object v){return Boolean.TRUE.equals(v)||(v instanceof Number n&&n.intValue()!=0);}
}
