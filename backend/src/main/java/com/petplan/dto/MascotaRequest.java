package com.petplan.dto;
import jakarta.validation.constraints.*; import java.math.BigDecimal;
public record MascotaRequest(@NotBlank @Size(max=60) String nombre_mascota,@NotBlank @Size(max=60) String especie,@Min(0) @Max(255) Integer edad,@DecimalMin("0.01") @Digits(integer=3,fraction=2) BigDecimal peso){}
