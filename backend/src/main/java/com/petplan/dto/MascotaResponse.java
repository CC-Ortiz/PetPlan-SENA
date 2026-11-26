package com.petplan.dto;
import com.petplan.model.Mascota; import java.math.BigDecimal;
public record MascotaResponse(Long codigo_mascota,String nombre_mascota,String especie,Integer edad,BigDecimal peso){public static MascotaResponse from(Mascota m){return new MascotaResponse(m.getId(),m.getNombre(),m.getEspecie(),m.getEdad(),m.getPeso());}}
