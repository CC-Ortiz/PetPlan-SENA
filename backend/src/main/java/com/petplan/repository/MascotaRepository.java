package com.petplan.repository;
import com.petplan.model.Mascota;
import java.util.*;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
public interface MascotaRepository extends JpaRepository<Mascota,Long>{
 @Query("select m from Mascota m where m.usuario.id=:usuario order by m.id desc") List<Mascota> listar(@Param("usuario") Long usuario);
 Optional<Mascota> findByIdAndUsuario_Id(Long id,Long usuario);
}
