package com.petplan.repository;
import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.jdbc.support.GeneratedKeyHolder;
import org.springframework.stereotype.Repository;
import java.sql.Statement; import java.util.*;
import com.petplan.service.Reglas;
@Repository public class Datos {
 private final JdbcTemplate jdbc;
 public Datos(JdbcTemplate jdbc){this.jdbc=jdbc;}
 public List<Map<String,Object>> lista(String sql,Object... args){return jdbc.queryForList(sql,args);}
 public Map<String,Object> uno(String sql,Object... args){return lista(sql,args).stream().findFirst().orElseThrow(()->Reglas.noEncontrado("Registro no encontrado"));}
 public int cambiar(String sql,Object... args){return jdbc.update(sql,args);}
 public long insertar(String sql,Object... args){var key=new GeneratedKeyHolder();jdbc.update(c->{var ps=c.prepareStatement(sql,Statement.RETURN_GENERATED_KEYS);for(int i=0;i<args.length;i++)ps.setObject(i+1,args[i]);return ps;},key);return Objects.requireNonNull(key.getKey()).longValue();}
}
