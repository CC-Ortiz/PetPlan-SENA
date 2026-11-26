package com.petplan.service;
import org.springframework.stereotype.Service;
import org.apache.pdfbox.pdmodel.*; import org.apache.pdfbox.pdmodel.font.*;
import java.io.*; import java.util.*;
@Service public class ReporteService {
 interface Estrategia {String titulo(); List<String> lineas(Map<String,Object> fila);}
 static class Historial implements Estrategia {public String titulo(){return "Historial clínico";}public List<String> lineas(Map<String,Object> f){return List.of(campo(f,"diagnostico","Diagnóstico"),campo(f,"observaciones","Observaciones"));}}
 static String campo(Map<String,Object> f,String k,String l){return l+": "+Objects.toString(f.get(k),"Sin registrar");}
 public byte[] generar(List<Map<String,Object>> filas) throws IOException {Estrategia e=new Historial();List<String> lineas=new ArrayList<>();lineas.add("PetPlan - "+e.titulo());lineas.add("");if(filas.isEmpty())lineas.add("Sin registros para los filtros seleccionados");for(var f:filas){lineas.add(campo(f,"nombre_mascota","Mascota")+" | "+campo(f,"fecha_hc","Fecha"));lineas.addAll(e.lineas(f));lineas.add("");}List<String> envueltas=new ArrayList<>();for(String l:lineas){l=l.replaceAll("[^\\x20-\\x7E\\xA0-\\xFF]"," ");if(l.isEmpty())envueltas.add("");while(!l.isEmpty()){int n=Math.min(78,l.length());envueltas.add(l.substring(0,n));l=l.substring(n);}}try(var doc=new PDDocument();var out=new ByteArrayOutputStream()){var font=new PDType1Font(Standard14Fonts.FontName.HELVETICA);for(int i=0;i<envueltas.size();i+=48){var page=new PDPage();doc.addPage(page);try(var cs=new PDPageContentStream(doc,page)){cs.beginText();cs.setFont(font,10);cs.setLeading(14);cs.newLineAtOffset(45,745);for(String l:envueltas.subList(i,Math.min(i+48,envueltas.size()))){cs.showText(l);cs.newLine();}cs.endText();}}doc.save(out);return out.toByteArray();}}
}
