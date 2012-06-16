<?php include("..clases/claseGral.php"); ?>
<?php
	$cedula = $_POST["cedula"];
	$clave = $_POST["clave"];
	$modulo = $_POST["modulo"];
	$learning = $_POST["learning"];

	if($learning == 'docente'){
		//$sql = "select count(cedula) as cedula from admon where cedula=$cedula AND clave='$clave'";
		$objSistema = new Sistema();
		$existeDB = $objSistema->login($usuario, $clave, $learning);

		if($existeDB == true){
			?>
			<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" bordercolor="#DCEDED">
				<tr>
					<td colspan="2" bgcolor="#DCEDED">
						<div align="center">
							MATERIAL DE APOYO PARA LOS TUTORES
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<div align="center">
							<table width="40%" border="0" align="center" cellpadding="2" cellspacing="3">
								<tr>
									<td height="29">
										<script type="text/javascript" language="JavaScript1.2">
											<!--
												stm_bm(["tubtehr",400,"","imago/blank.gif",0,"","",0,0,0,0,0,1,0,0,"","",0],this);
												stm_bp("p0",[0,4,0,0,5,3,0,0,95,"",-2,"",-2,90,0,0,"#000000","transparent","",3,0,0,"#ffffff"]);
												stm_ai("p0i0",[6,1,"#cccccc","",0,0,0]);
												stm_aix("p0i1","p0i0",[]);
													stm_ai("p0i2",[0,"  Orientación","","",-1,-1,0,"","_self","","","","",0,0,0,"","",0,0,0,0,1,"#7aafaf",0,"#b3ccd3",0,"","",3,3,0,0,"#ffffff","#ffffff","#ffffff","#000000","8pt Verdana","8pt Verdana",0,0]);
												stm_bp("p1",[1,4,0,0,0,2,1,0,100,"progid:DXImageTransform.Microsoft.Iris(irisStyle=cross,motion=out,enabled=0,Duration=0.50)",-2,"",-2,60,0,0,"#000000","#ffffff","",3,1,1,"#73a8b7"]);
												stm_aix("p1i0","p0i2",[0,"Introducción ","","",-1,-1,0,"docs/orientacion/chap1_Introduction.htm","_blank","","","","",1,1,0,"","",0,0,0,0,1,"#ebf8ff",0,"#acd2dd",0,"","",3,3,0,0,"#ffffff","#ffffff","#333333","#333333","7pt Verdana","7pt Verdana"]);
												stm_aix("p1i1","p0i0",[6,1,"#73a8b7"]);
												stm_aix("p1i2","p1i0",[0,"Manage Academy","","",-1,-1,0,"docs/orientacion/chap2_Manage%20Academy.htm"]);
												stm_aix("p1i3","p1i1",[]);
												stm_aix("p1i4","p1i0",[0,"Aprender","","",-1,-1,0,"docs/orientacion/chap3_Learn.htm"]);
												stm_aix("p1i5","p1i1",[]);
												stm_aix("p1i6","p1i0",[0,"Enseñanza","","",-1,-1,0,"docs/orientacion/chap4_Teach.htm.htm"]);
												stm_aix("p1i7","p1i1",[]);
												stm_aix("p1i8","p1i0",[0,"Practicas","","",-1,-1,0,"docs/orientacion/chap5_BestPractices.htm"]);
												stm_aix("p1i9","p1i1",[]);
												stm_aix("p1i10","p1i0",[0,"Comunidad","","",-1,-1,0,"docs/orientacion/chap6_Community.htm"]);
												stm_aix("p1i11","p1i1",[]);
												stm_aix("p1i12","p1i0",[0,"Recursos","","",-1,-1,0,"docs/orientacion/chap7_Resources.htm"]);
												stm_ep();
												stm_aix("p0i3","p0i0",[]);
												stm_aix("p0i4","p0i0",[]);
												stm_aix("p0i5","p0i2",[0,"  Modulo 1"]);
												stm_bpx("p2","p1",[]);
												stm_aix("p2i0","p1i0",[0,"Cap 1 - Introducción a Networking","","",-1,-1,0,"docs/material/ccna1_cap1.pdf"]);
												stm_aix("p2i1","p1i1",[]);
												stm_aix("p2i2","p1i0",[0,"Cap 2 -Aspectos Basicos de Networking","","",-1,-1,0,"docs/material/ccna1_cap2.pdf"]);
												stm_aix("p2i3","p1i1",[]);
												stm_aix("p2i4","p1i0",[0,"Cap 3 - Medios de networking","","",-1,-1,0,"docs/material/ccna1_cap3.pdf"]);
												stm_aix("p2i5","p1i1",[]);
												stm_aix("p2i6","p1i0",[0,"Cap 4 - Prueba del cable","","",-1,-1,0,"docs/material/ccna1_cap4.pdf"]);
												stm_aix("p2i7","p1i1",[]);
												stm_aix("p2i8","p1i0",[0,"Cap 5 - Cableado de las LAN y las WAN","","",-1,-1,0,"docs/material/ccna1_cap5.pdf"]);
												stm_aix("p2i9","p1i1",[]);
												stm_aix("p2i10","p1i0",[0,"Cap 6 - Principios básicos de Ethernet","","",-1,-1,0,"docs/material/ccna1_cap6.pdf"]);
												stm_aix("p2i11","p1i1",[]);
												stm_aix("p2i12","p1i0",[0,"Cap 7 -Tecnologias de Ethernet","","",-1,-1,0,"docs/material/ccna1_cap7.pdf"]);
												stm_aix("p2i13","p1i1",[]);
												stm_aix("p2i14","p1i0",[0,"Cap 8a -Segmentación","","",-1,-1,0,"docs/material/segmentacion.pdf"]);
												stm_aix("p2i15","p1i1",[]);
												stm_aix("p2i14","p1i0",[0,"Cap 8b -Conmutación de Ethernet","","",-1,-1,0,"docs/material/ccna1_cap8.pdf"]);
												stm_aix("p2i15","p1i1",[]);
												stm_aix("p2i14","p1i0",[0,"Cap 8c -Switches","","",-1,-1,0,"docs/material/switches.pdf"]);
												stm_aix("p2i15","p1i1",[]);
												stm_aix("p2i16","p1i0",[0,"Cap 9 - Conjunto de protocolos TCP/IP y direccionamiento IP","","",-1,-1,0,"docs/material/ccna1_cap9.pdf"]);
												stm_aix("p2i17","p1i1",[]);
												stm_aix("p2i18","p1i0",[0,"Cap 10 - Principios básicos de enrutamiento y subredes","","",-1,-1,0,"docs/material/ccna1_cap10.pdf"]);
												stm_aix("p2i19","p1i1",[]);
												stm_aix("p2i20","p1i0",[0,"Cap 11a - Capa de Transporte ","","",-1,-1,0,"docs/material/ccna1_cap11a.pdf"]);
												stm_aix("p2i21","p1i1",[]);
												stm_aix("p2i22","p1i0",[0,"Cap 11b - Capa de Transporte y Aplicación","","",-1,-1,0,"docs/material/ccna1v3.1_mod11.pdf"]);
												stm_ep();
												stm_aix("p0i6","p0i0",[]);
												stm_aix("p0i7","p0i0",[]);
												stm_aix("p0i8","p0i2",[0,"    Modulo 2  "]);
												stm_aix("p0i9","p0i0",[]);
												stm_aix("p0i10","p0i0",[]);
												stm_aix("p0i11","p0i2",[0,"    Modulo 3  "]);
												stm_aix("p0i12","p0i0",[]);
												stm_aix("p0i13","p0i0",[]);
												stm_aix("p0i14","p0i2",[0,"    Modulo 4 "]);
												stm_aix("p0i15","p0i0",[]);
												stm_aix("p0i16","p0i0",[]);
												stm_ep();
												stm_em();
											//-->
										</script>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
			</table>

			<?php
		}
	}
	else{
		//$sql = "select * from inscrito where cedula=$cedula AND clave='$clave' AND  modulo=$modulo";
		$objSistema = new Sistema();
		$existeDB = $objSistema->login($usuario, $clave, $learning);

		if($existeDB == true){
			$url = "http://giret.ufps.edu.co/cisco/modulos/Exploration/EspanolExploration/contenido/Modulo$modulo/";
			?>
			<script>location.href="<?=$url?>";</script>
			<?php
		}
	}

?>