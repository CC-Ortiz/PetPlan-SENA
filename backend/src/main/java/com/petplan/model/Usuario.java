package com.petplan.model;
import jakarta.persistence.*;
@Entity @Table(name="usuario") public class Usuario {
 @Id @Column(name="codigo_usuario",columnDefinition="INT UNSIGNED") private Long id;
 @Column(name="rol",length=30) private String rol;
 protected Usuario(){} public Long getId(){return id;} public String getRol(){return rol;}
}
