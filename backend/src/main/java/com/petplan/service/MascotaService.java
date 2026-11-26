package com.petplan.service;
import com.petplan.dto.*; import com.petplan.repository.*; import com.petplan.model.*; import org.springframework.stereotype.Service; import org.springframework.transaction.annotation.Transactional; import java.util.*;
@Service public class MascotaService {
 private final MascotaRepository repo; private final UsuarioRepository usuarios; private final Datos db;
 public MascotaService(MascotaRepository r,UsuarioRepository u,Datos d){repo=r;usuarios=u;db=d;}
 @Transactional(readOnly=true) public List<MascotaResponse> listar(long u){return repo.listar(u).stream().map(MascotaResponse::from).toList();}
 @Transactional(readOnly=true) public MascotaResponse ver(long u,long id){return MascotaResponse.from(buscar(u,id));}
 @Transactional public MascotaResponse crear(long u,MascotaRequest d){return MascotaResponse.from(repo.saveAndFlush(new Mascota(usuarios.findById(u).orElseThrow(),d)));}
 @Transactional public MascotaResponse editar(long u,long id,MascotaRequest d){var m=buscar(u,id);m.actualizar(d);return MascotaResponse.from(m);}
 @Transactional public void eliminar(long u,long id){db.uno("SELECT codigo_usuario FROM usuario WHERE codigo_usuario=? FOR UPDATE",u);var m=buscar(u,id);if(!db.lista("SELECT codigo_historia_clinica FROM historia_clinica WHERE codigo_mascota=?",id).isEmpty()||!db.lista("SELECT codigo_cita FROM usuario_cita WHERE codigo_usuario=?",u).isEmpty())throw Reglas.conflicto("No se puede eliminar: existen historias clínicas o citas del propietario");repo.delete(m);repo.flush();}
 private Mascota buscar(long u,long id){return repo.findByIdAndUsuario_Id(id,u).orElseThrow(()->Reglas.noEncontrado("Mascota no encontrada"));}
}
