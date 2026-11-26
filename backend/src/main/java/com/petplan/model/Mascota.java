package com.petplan.model;
import jakarta.persistence.*; import com.petplan.dto.MascotaRequest; import java.math.BigDecimal;
@Entity @Table(name="mascota") public class Mascota {
 @Id @GeneratedValue(strategy=GenerationType.IDENTITY) @Column(name="codigo_mascota",columnDefinition="INT UNSIGNED") private Long id;
 @Column(name="nombre_mascota",length=60,nullable=false) private String nombre;
 @Column(length=60) private String especie;
 @Column(columnDefinition="TINYINT UNSIGNED") private Integer edad;
 @Column(precision=5,scale=2) private BigDecimal peso;
 @ManyToOne(fetch=FetchType.LAZY,optional=false) @JoinColumn(name="codigo_usuario",columnDefinition="INT UNSIGNED") private Usuario usuario;
 protected Mascota(){} public Mascota(Usuario u,MascotaRequest d){usuario=u;actualizar(d);}
 public void actualizar(MascotaRequest d){nombre=d.nombre_mascota().strip();especie=d.especie().strip();edad=d.edad();peso=d.peso();}
 public Long getId(){return id;} public String getNombre(){return nombre;} public String getEspecie(){return especie;} public Integer getEdad(){return edad;} public BigDecimal getPeso(){return peso;}
}
