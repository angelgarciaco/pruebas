<?php
/* **************************************************
	VERIFICAMOS QUE EXISTA EL FICHERO Y TOME EL URI CORRECTO
************************************************** */
if (file_exists("conexion/conexion.php"))
	include('conexion/conexion.php');
else
	include('../conexion/conexion.php'); 
/* *********************************************** */

class General
{
	private $cnn;
	public $rs;
	private $numregis;
	public $sql;

	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function __construct()
	{
		try{ 
			$this->cnn= @mysql_connect($_SESSION["servidor"], $_SESSION["root"],$_SESSION["claveBD"]); 

			if ($this->cnn === false){ 
				throw new Exception("No se puede conectar al servidor");
			}
			mysql_select_db($_SESSION["basededatos"],$this->cnn); 
		}
		catch(Exception $e){
			echo "Error!: " . $e->getMessage() . '	'."\n"; exit;
		}	
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function ejecutaQUERY()
	{
		$this->rs = mysql_query($this->sql);
		$this->numregis=@mysql_num_rows($this->rs);
		return $this->rs;
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function insertaQUERY()
	{
		$this->rs = mysql_query($this->sql,$this->cnn);			
		return mysql_insert_id($this->cnn);//Retorno el ultimo id insertado
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function retornaNumRows()
	{
		return $this->numregis;
	}
				
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function retornaSQL()
	{
		return $this->sql;
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function __destruct()
	{
		$this->sql="";
		unset($this->cnn);
	}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Usuario extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTAMOS LOS DATOS PESONALES DE UN DOCENTE ESPECIFICO
		function consulta_InfPersonal_especifico($ip_id)
		{
			$this->sql="SELECT * FROM inf_personal where ip_id=$ip_id";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function consulta_InfPersonal()
		{
			$this->sql="SELECT * FROM inf_personal";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTAMOS LOS DATOS PESONALES DE UN DOCENTE ESPECIFICO
		function consulta_InfPersonal_especifico_ID($ip_codigo)
		{
			$this->sql = "SELECT ip_id FROM inf_personal where ip_codigo=$ip_codigo";
			$this->rs = parent::ejecutaQUERY();

			$msg = false;
			while($row = mysql_fetch_array($this->rs))
			{
				$msg = $row['ip_id'];
			}
			return $msg;
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTAMOS TODOS LOS DATOS DEL DOCENTE
		function consulta_Todos_Usuarios()
		{
			$this->sql="SELECT * FROM usuario ";
			$this->sql.="inner join inf_personal ";
			$this->sql.="where inf_personal.ip_id = usuario.ip_id";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTAMOS EL NOMBRE DE USUARIO EMPLEADO POR EL DOCENTE
		function consulta_Usuario_NombreUsuario($u_id)
		{
			$this->sql="SELECT u_usuario FROM usuario ";
			$this->sql.="where u_id = $u_id";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ACTUALIZAMOS LA CLAVE DEL DOCENTE
		function actualiza_Usuario_clave($u_id, $clave)
		{
			//Encriptamos la clave
			$clave = md5($clave);

			$this->sql="update usuario set u_clave='$clave' where u_id=$u_id";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ACTUALIZAMOS LA CLAVE DEL DOCENTE
		function atualiza_Usuario_clave_olvidada($u_usuario, $u_clave, $ip_cedula, $ip_codigo)		
		{
			//Encriptamos la clave
			$u_clave = md5($u_clave);

			$this->sql="update usuario set u_clave='$u_clave' ";
			$this->sql.="where u_usuario='$u_usuario' and ip_id in (select ip_id from inf_personal where ip_cedula='$ip_cedula' and ip_codigo='$ip_codigo')";
			echo $this->sql;
			if(parent::ejecutaQUERY())
				return true;
			else
				return false;
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ACTUALIZAMOS EL STATUS DE ACCESO DEL DOCENTE
		function actualiza_Usuario_status($u_id)
		{
			//Consultamos el Status actual
			$this->sql="SELECT u_activo FROM usuario ";
			$this->sql.="where u_id=$u_id";
			$this->rs = parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$status = $row['u_activo'];
			
			//Se invierte el valor del Status
			if($status)
				$status=0;
			else
				$status=1;

			//Actualizamos el Status
			$this->sql="update usuario set u_activo=$status where u_id=$u_id";
			parent::ejecutaQUERY();
			return true;
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//REGISTRAMOS EL DOCENTE
		function registra_Usuario($u_usuario, $u_clave, $ip_cedula, $ip_codigo, $ip_apellido, $ip_nombre, $ip_tel_fijo, $ip_tel_celular, $ip_email)
		{
			//Encriptamos la clave
			$u_clave = md5($u_clave);

			$this->sql="insert into inf_personal(ip_cedula, ip_codigo, ip_apellido, ip_nombre, ip_tel_fijo, ip_tel_celular, ip_email, ip_fcha_reg) ";
			$this->sql.="values('$ip_cedula', '$ip_codigo', '$ip_apellido', '$ip_nombre', '$ip_tel_fijo', '$ip_tel_celular', '$ip_email', curdate())";
//			echo ' | '.$this->sql;
			$ip_id = parent::insertaQUERY(); //Obtenemos el ID del Docente que se acaba de registrar
			
			$tu_id = $this->consulta_TipoUsuario('usuario'); //Consulta el tipo de usuario
			$u_activo = 0; //Indica el estado del usuario

			$this->sql="insert into usuario(u_usuario, u_clave, u_fcha_reg, ip_id, tu_id, u_activo) ";
			$this->sql.="values('$u_usuario', '$u_clave', curdate(), $ip_id, $tu_id, $u_activo)";
			$this->rs = parent::ejecutaQUERY();
//			echo ' | '.$this->sql;
			return true;
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTAMOS EL TIPO DE USUARIO QUE ES EL DOCENTE
		function consulta_TipoUsuario($tu_descripcion)
		{
			$this->sql="SELECT tu_id FROM tipo_usuario where tu_descripcion='$tu_descripcion'";
			$this->rs=parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$campo=$row[0];
			return $campo;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTAMOS LA CANTIDAD DE DOCENTES EXISTENTES DE ACUERDO AL TIPO DE VINCULACION
		function consulta_count_TipoVinculacion($tv_id)
		{
			$this->sql="SELECT count(*) FROM inf_personal where tv_id=$tv_id";
			$this->rs=parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$campo=$row[0];
			return $campo;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTAMOS LA CANTIDAD DE DOCENTES EXISTENTES VINCULADOS A REDES ACADEMICAS
		function consulta_count_RedAcademica()
		{
			$contador=0;
			
			$this->sql="SELECT * FROM ip_red where red_id in (select red_id from red where tr_id in ";
			$this->sql.="(select tr_id from tipo_red where tr_descripcion='académica')) ";
			$this->sql.="GROUP BY ip_id";
			$this->rs=parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$contador++;
			return $contador;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTAMOS LA CANTIDAD DE DOCENTES EXISTENTES VINCULADOS A REDES SOCIALES
		function consulta_count_RedSocial()
		{
			$contador=0;
			
			$this->sql="SELECT * FROM ip_red where red_id in (select red_id from red where tr_id in ";
			$this->sql.="(select tr_id from tipo_red where tr_descripcion='social')) ";
			$this->sql.="GROUP BY ip_id";
			$this->rs=parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$contador++;
			return $contador;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTAMOS LA CANTIDAD DE REDES ACADEMICAS QUE HAY
		function consulta_count_RedesAcaddemicas()
		{
			$contador=0;
			
			$this->sql="SELECT red_id, count(red_id) as contador FROM ip_red where red_id in (select red_id from red where tr_id in ";
			$this->sql.="(select tr_id from tipo_red where tr_descripcion='academica')) ";
			$this->sql.="GROUP BY red_id";
			//echo $this->sql;			
			return parent::ejecutaQUERY();
 		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTAMOS LA CANTIDAD DE DOCENTES EXISTENTES VINCULADOS A GRUPOS DE INVESTIGACION
		function consulta_count_DocentesEnGrupoInvestigacion()
		{
			$contador=0;
			
			$this->sql="SELECT ip_id FROM ip_gi ";
			$this->sql.="GROUP BY ip_id";
			$this->rs=parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$contador++;
			return $contador;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function actualiza_InfPersonal($id, $cedula, $codigo, $apellido, $nombre, $fijo, $celular, $email)
		{
			$this->sql="update inf_personal set ip_cedula='$cedula', ip_codigo='$codigo', ip_apellido='$apellido', ip_nombre='$nombre', ip_tel_fijo='$fijo', ip_tel_celular='$celular', ip_email='$email' where ip_id=$id";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function actualiza_InfPersonal_TipoVinculacion($id, $tv, $lugar)
		{
			$objTipoVinculacion = new Tipo_Vinculacion();
			$tv = $objTipoVinculacion->consulta_TipoVinculacion_ID($tv);  //Retorna el ID del Tipo de Vinculación respectivo según la descripcion
			$this->sql="update inf_personal set tv_id='$tv', tv_lugar='$lugar' where ip_id=$id";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function consulta_Campo_InfPersonal($id, $campo)
		{
			$this->sql="SELECT $campo FROM inf_personal where ip_id=$id";
			$this->rs=parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$campo=$row[0];
			return $campo;
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Tipo_Vinculacion extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function consulta_TipoVinculacion()
		{
			$this->sql="SELECT * FROM tipo_vinculacion";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function actualiza_TipoVinculacion($id, $descripcion)
		{
			$this->sql="update tipo_vinculacion set tv_descripcion='$descripcion' where tv_id=$id";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function consulta_Campo_TipoVinculacion($id, $campo)
		{
			$this->sql="SELECT $campo FROM tipo_vinculacion where tv_id=$id";
			$this->rs=parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$campo=$row[0];
			return $campo;
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function consulta_TipoVinculacion_ID($valor)
		{
			$this->sql="SELECT tv_id FROM tipo_vinculacion where tv_descripcion like '$valor'";
			$this->rs=parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$valor=$row[0];
			return $valor;
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Nivel_Formacion extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function consulta_NivelFormacion()
		{
			$this->sql="SELECT * FROM nivel_formacion";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function actualiza_NivelFormacion($id, $descripcion, $sigla)
		{
			$this->sql="update nivel_formacion set nf_descripcion='$descripcion', nf_sigla='$sigla' where nf_id=$id";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function consulta_Campo_NivelFormacion($id, $campo)
		{
			$this->sql="SELECT $campo FROM nivel_formacion where nf_id=$id";
			$this->rs=parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$campo=$row[0];
			return $campo;
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function consulta_NivelFormacion_ID($valor)
		{
			$this->sql="SELECT nf_id FROM nivel_formacion where nf_sigla like '$valor'";
			$this->rs=parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$valor=$row[0];
			return $valor;
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class IP_NF extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function consulta_IPNF()
		{
			$this->sql="SELECT * FROM ip_nf";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA SI EL USUARIO TIENE UN DETERMINADO NIVEL DE FORMACION
		function consulta_IPNF_determinado($ip_id, $nf_id)
		{
			$this->sql="SELECT count(*) FROM ip_nf where ip_id=$ip_id and nf_id=$nf_id";
			$this->rs=parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$campo=$row[0];
			return $campo;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ELIMINA EL REGISTRO DE UN DETERMINADO NIVEL DE FORMACION DE UN DETERMINADO USUARIO
		function elimina_IPNF_determinado($ip_id, $nf_id)
		{
			$this->sql="delete from ip_nf where ip_id=$ip_id and nf_id=$nf_id";
			$this->rs=parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//AGREGA EL REGISTRO DE UN DETERMINADO NIVEL DE FORMACION DE UN DETERMINADO USUARIO
		function agrega_IPNF_determinado($ip_id, $nf_id)
		{
			$this->sql="insert into ip_nf(ip_id, nf_id) values($ip_id, $nf_id)";
			$this->rs=parent::ejecutaQUERY();
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Grupo_Curso extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function consulta_GrupoCurso()
		{
			$this->sql="SELECT * FROM grupo_curso";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LA DESCRIPCION DE UN CURSO DETERMINADO
		function consulta_GrupoCurso_determinado_descripcion($gc_id)
		{
			$this->sql="SELECT gc_descripcion FROM grupo_curso where gc_id=$gc_id";
			$this->rs=parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$campo=$row[0];
			return $campo;
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Tipo_Curso extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function consulta_TipoCurso()
		{
			$this->sql="SELECT * FROM tipo_curso";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LA DESCRIPCION DE UN CURSO DETERMINADO
		function consulta_TipoCurso_determinado_descripcion($tc_id)
		{
			$this->sql="SELECT tc_descripcion FROM tipo_curso where tc_id=$tc_id";
			$this->rs=parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$campo=$row[0];
			return $campo;
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Curso extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function consulta_Curso()
		{
			$this->sql="SELECT * FROM curso";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA TODA LA INFORMACIÓN DE LA TABLA ip_cac DE UN USUARIO DETERMINADO
		function consulta_Curso_determinado($ip_id)
		{
			$this->sql="SELECT * FROM curso where ip_id=$ip_id";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LA INFORMACIÓN DE UN USUARIO DETERMINADO DE ACUERDO A UN SOLO CURSO
		function consulta_Curso_determinado_UnCurso($cac_codigo, $cac_año, $cac_semestre, $ip_id, $gc_id)
		{
			$this->sql="SELECT * FROM curso ";
			$this->sql.="where cac_codigo=$cac_codigo and cac_año=$cac_año and cac_semestre=$cac_semestre and ip_id=$ip_id and gc_id=$gc_id";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LA DESCRIPCION DEL TIPO DE CURSO DETERMINADO
		function consulta_Curso_TIPOCURSO_determinado_descripcion($cac_codigo)
		{
			$this->sql="SELECT tc_id FROM curso where cac_codigo=$cac_codigo";
			$this->rs=parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$tc_id=$row[0];
			
			$objTipoCurso = new Tipo_Curso();
			return ( $objTipoCurso->consulta_TipoCurso_determinado_descripcion($tc_id) );
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LA DESCRIPCION DEL GRUPO DE UN CURSO DETERMINADO
		function consulta_Curso_GRUPOCURSO_determinado_descripcion($gc_id)
		{
			$objGrupoCurso = new Grupo_Curso();
			return ( $objGrupoCurso->consulta_GrupoCurso_determinado_descripcion($gc_id) );
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//AGREGA UN NUEVO CURSO
		function agrega_Curso($cac_codigo, $cac_año, $cac_semestre, $ip_id, $gc_id, $cac_nombre, $cac_creditos, $tc_id)
		{
			$this->sql="insert into curso(cac_codigo, cac_año, cac_semestre, ip_id, gc_id, cac_nombre, cac_creditos, tc_id) ";
			$this->sql.="values($cac_codigo, $cac_año, $cac_semestre, $ip_id, $gc_id, '$cac_nombre', $cac_creditos, $tc_id)";
			//echo $this->sql;
			parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ACTUALIZA TODOS LOS DATOS DE UN CURSO DETERMINADO
		function actualiza_Curso($ip_id, $cac_codigo, $db_cac_codigo, $cac_nombre, $db_cac_nombre, $cac_creditos, $db_cac_creditos, $gc_id, $db_gc_id, $cac_año, $db_cac_año, $cac_semestre, $db_cac_semestre, $tc_id, $db_tc_id)
		{
			$objCurso = new Curso;

			//---------------------------------------------
			//Actualizamos todos los datos de acuerdo con todos los datos suministrados
			//actualizando en primer lugar el codigo del curso [Nótese la condición $db_cac_codigo]
			$this->sql="UPDATE curso SET cac_codigo=$cac_codigo ";
			$this->sql.="WHERE cac_codigo=$db_cac_codigo and cac_año=$db_cac_año and cac_semestre=$db_cac_semestre and ip_id=$ip_id and gc_id=$db_gc_id";
			//echo htmlentities($this->sql).'<br><br>';
			parent::ejecutaQUERY();

			//actualizando en segundo lugar el resto de datos suministrados [Nótese la condición $cac_codigo]
			$this->sql="UPDATE curso SET cac_año=$cac_año, cac_semestre=$cac_semestre, gc_id=$gc_id, cac_nombre='$cac_nombre', cac_creditos=$cac_creditos, tc_id=$tc_id ";
			$this->sql.="WHERE cac_codigo=$cac_codigo and cac_año=$db_cac_año and cac_semestre=$db_cac_semestre and ip_id=$ip_id and gc_id=$db_gc_id";
			//echo htmlentities($this->sql).'<br><br>';
			parent::ejecutaQUERY();
			//---------------------------------------------
						
			//Esta actualización es la original, sin embargo,
			//por alguna razón no tomaba los cambios cocmpletamente cuando se cambiaban todos los datos incluyendo el codigo del curso
			/*
			$this->sql="UPDATE curso SET cac_codigo=$cac_codigo, cac_año=$cac_año, cac_semestre=$cac_semestre, gc_id=$gc_id, cac_nombre='$cac_nombre', cac_creditos=$cac_creditos, tc_id=$tc_id ";
			$this->sql.="WHERE cac_codigo=$db_cac_codigo and cac_año=$db_cac_año and cac_semestre=$db_cac_semestre and ip_id=$ip_id and gc_id=$db_gc_id";
			echo htmlentities($this->sql).'<br><br>';
			parent::ejecutaQUERY();
			*/

		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Grupo_Investigacion extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function consulta_GrupoInvestigacion()
		{
			$this->sql="SELECT * FROM nivel_formacion";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS GRUPOS DE INVESTIGACION
		function consulta_GrupoInvestigacion_especifico($id)
		{
			$this->sql="SELECT * FROM nivel_formacion WHERE gi_id=$id";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA EL NOMBRE DE UN GRUPO DE INVESTIGACION
		function consulta_GrupoInvestigacion_nombre($id)
		{
			$this->sql="SELECT gi_nombre FROM grupo_investigacion where gi_id=$id";
			$this->rs=parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$campo=$row[0];
			return $campo;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LA SIGLA DE UN GRUPO DE INVESTIGACION
		function consulta_GrupoInvestigacion_sigla($id)
		{
			$this->sql="SELECT gi_sigla FROM grupo_investigacion where gi_id=$id";
			$this->rs=parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$campo=$row[0];
			return $campo;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//AGREGAR UN GRUPO DE INVESTIGACION
		function agregar_GrupoInvestigacion($ip_id, $gi_nombre, $gi_año_vinculacion, $gi_cargo, $gi_linea, $gi_sigla, $gi_horas_docencia, $gi_horas_investigacion, $gi_horas_extension)
		{
			$this->sql="insert into grupo_investigacion(gi_nombre,gi_sigla) values('$gi_nombre','$gi_sigla')";
			$gi_id = parent::insertaQUERY();

			$objIPGI = new IP_GI();
			$objIPGI->agregar_IPGI($ip_id, $gi_id, $gi_año_vinculacion, $gi_cargo, $gi_linea, $gi_horas_docencia, $gi_horas_investigacion, $gi_horas_extension);
			
//			return $gi_id;
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ACTUALIZA LOS DATOS DE UN GRUPO DE INVESTIGACION
		function actualiza_GrupoInvestigacion_determinado($gi_id, $gi_nombre, $gi_sigla)
		{
			$this->sql="UPDATE grupo_investigacion SET gi_nombre='$gi_nombre', gi_sigla='$gi_sigla' where gi_id=$gi_id";
			return parent::ejecutaQUERY();
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class IP_GI extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA DE LOS DATOS DE LOS GRUPOS DE INVESTIGACIÓN DE UN DOCENTE DETERMINADO
		function consulta_IPGI_determinado($ip_id,$gi_id)
		{
			$this->sql="SELECT * FROM ip_gi where ip_id=$ip_id and gi_id=$gi_id";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA DE LOS DATOS DE LOS GRUPOS DE INVESTIGACIÓN DE UN DOCENTE DETERMINADO
		function consulta_IPGI($ip_id)
		{
			$this->sql="SELECT * FROM ip_gi where ip_id=$ip_id";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA DE LOS DATOS DE LOS GRUPOS DE INVESTIGACIÓN DE UN DOCENTE DETERMINADO
		function consulta_IPGI_count($ip_id)
		{
			$this->sql="SELECT count(*) FROM ip_gi where ip_id=$ip_id";
			$this->rs=parent::ejecutaQUERY();
			while($row = mysql_fetch_array($this->rs))
				$campo=$row[0];
			return $campo;
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//AGREGA LOS DATOS DE UN GRUPO DE INVESTIGACION
		function agregar_IPGI($ip_id, $gi_id, $gi_año_vinculacion, $gi_cargo, $gi_linea, $gi_horas_docencia, $gi_horas_investigacion, $gi_horas_extension)
		{
			$this->sql="insert into ip_gi values($ip_id, $gi_id, $gi_año_vinculacion, '$gi_cargo', '$gi_linea', $gi_horas_docencia, $gi_horas_investigacion, $gi_horas_extension)";
			parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ACTUALIZA LOS DATOS DE UN GRUPO DE INVESTIGACION
		function actualiza_IPGI_determinado($ip_id, $gi_nombre, $gi_año_vinculacion, $gi_cargo, $gi_linea, $gi_id, $gi_sigla, $gi_horas_docencia, $gi_horas_investigacion, $gi_horas_extension)
		{
			//Actualizamos en ip_gi
			$this->sql="UPDATE ip_gi SET gi_año_vinculacion=$gi_año_vinculacion, gi_cargo='$gi_cargo', gi_linea_investigacion='$gi_linea', gi_horas_docencia=$gi_horas_docencia, gi_horas_investigacion=$gi_horas_investigacion, gi_horas_extension=$gi_horas_extension where ip_id=$ip_id and gi_id=$gi_id";
			parent::ejecutaQUERY();
			
			//actualizamos en Grupo_Investigacion
			$objGrupoInvestigacion = new Grupo_Investigacion();
			$objGrupoInvestigacion->actualiza_GrupoInvestigacion_determinado($gi_id, $gi_nombre, $gi_sigla);
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Proyecto extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE UN PROYECTO ESPECIFICO
		function consulta_Proyecto_especifico($id)
		{
			$this->sql="SELECT * FROM proyecto ";
			$this->sql.="inner join tipo_proyecto on (tipo_proyecto.tp_id = proyecto.tp_id) ";
			$this->sql.="inner join financiamiento on (financiamiento.finan_id = proyecto.finan_id) ";
			$this->sql.="WHERE proy_id=$id";
			
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS PROYECTOS DE UN DOCENTE
		function consulta_Proyecto_ALL($ip_id, $descripcion)
		{
/*ORIGINAL
			$this->sql="select * from proyecto ";
			$this->sql.="inner join tipo_proyecto on (tipo_proyecto.tp_id = proyecto.tp_id) ";
			$this->sql.="where proy_id in (select proy_id from ip_proy where ip_id = $ip_id) and tipo_proyecto.tp_descripcion='$descripcion'";
*/
			$this->sql="select * from proyecto ";
			$this->sql.="inner join tipo_proyecto on (tipo_proyecto.tp_id = proyecto.tp_id) ";
			$this->sql.="where proy_id in (select proy_id from ip_proy where ip_id = $ip_id) ";
			$this->sql.="and tipo_proyecto.tp_descripcion='$descripcion'";

			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS PROYECTOS DE UN DOCENTE EN LOS ULTIMOS 3 AÑOS
		function consulta_Proyecto($ip_id, $descripcion)
		{
/*ORIGINAL
			$this->sql="select * from proyecto ";
			$this->sql.="inner join tipo_proyecto on (tipo_proyecto.tp_id = proyecto.tp_id) ";
			$this->sql.="where proy_id in (select proy_id from ip_proy where ip_id = $ip_id) and tipo_proyecto.tp_descripcion='$descripcion'";
*/
			$this->sql="select * from proyecto ";
			$this->sql.="inner join tipo_proyecto on (tipo_proyecto.tp_id = proyecto.tp_id) ";
			$this->sql.="where proy_id in (select proy_id from ip_proy where ip_id = $ip_id) ";
			$this->sql.="and tipo_proyecto.tp_descripcion='$descripcion' ";
			$this->sql.="and proyecto.proy_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE())";

			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//AGREGA LOS DATOS DE UN PROYECTO
		function agrega_Proyecto($ip_id, $proy_lugar, $proy_año, $proy_titulo, $proy_autor, $proy_nom_empresa, $tp_descripcion, $finan_id)
		{
			$objTipoProyecto = new Tipo_proyecto();
			$tp_id = $objTipoProyecto->consulta_TipoProyecto_especifico_id($tp_descripcion); //Averiguamos el id del tipo de proyecto
			
			//Agregamos los datos del proyecto
			$this->sql="insert into proyecto(proy_lugar, proy_año, proy_titulo, proy_autor, proy_nom_empresa, tp_id, finan_id) ";
			$this->sql.="values('$proy_lugar', $proy_año, '$proy_titulo', '$proy_autor', '$proy_nom_empresa', $tp_id, $finan_id)";
			$proy_id = parent::insertaQUERY();

			//Relacionamos los datos del proyecto al docente
			$objIPPROY = new IP_PROY();
			$objIPPROY->agregar_IPPROY($ip_id, $proy_id);
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ACTUALIZA LOS DATOS DE UN PROYECTO
		function actualiza_Proyecto($ip_id, $proy_id, $proy_lugar, $proy_año, $proy_titulo, $proy_autor, $proy_nom_empresa, $tp_id, $finan_id)
		{
			//Actualiza los datos del proyecto
			$this->sql="update proyecto set proy_lugar='$proy_lugar', proy_año=$proy_año, proy_titulo='$proy_titulo', proy_autor='$proy_autor', proy_nom_empresa='$proy_nom_empresa', tp_id=$tp_id, finan_id=$finan_id ";
			$this->sql.="where proy_id=$proy_id";
			
			parent::ejecutaQUERY();
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Tipo_Proyecto extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function consulta_TipoProyecto()
		{
			$this->sql="SELECT * FROM tipo_proyecto ";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE UN TIPO DE PROYECTO ESPECIFICO
		function consulta_TipoProyecto_especifico($id)
		{
			$this->sql="SELECT * FROM tipo_proyecto WHERE tp_id=$id";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA EL ID DE UN TIPO DE PROYECTO ESPECIFICO
		function consulta_TipoProyecto_especifico_id($tp_descripcion)
		{
			$this->sql="SELECT tp_id FROM tipo_proyecto WHERE tp_descripcion='$tp_descripcion'";
			$this->rs=parent::ejecutaQUERY();
			
			$msg = "";
			while($row = mysql_fetch_array($this->rs))
			{ $msg=$row['tp_id']; }

			return $msg;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LA DESCRIPCION DE UN TIPO DE PROYECTO ESPECIFICO
		function consulta_TipoProyecto_especifico_tp_descripcion($id)
		{
			$this->sql="SELECT tp_descripcion FROM tipo_proyecto WHERE tp_id=$id";
			$this->rs=parent::ejecutaQUERY();
			
			$msg = "";
			while($row = mysql_fetch_array($this->rs))
			{ $msg=$row['tp_desc']; }

			return $msg;
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Autor_Proyecto extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS DAUTORES DE UN PROYECTO ESPECIFICO
		function consulta_AutorProyecto_especifico($id)
		{
			$this->sql="SELECT * FROM autor_proyecto WHERE proy_id=$id";
			$this->rs=parent::ejecutaQUERY();
			
			$msg = "";
			while($row = mysql_fetch_array($this->rs))
			{
				$msg.=$row['aproy_apellido']." ".$row['aproy_nombre']."<br><br>";
			}
			return $msg;
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS AUTORES DE UN PROYECTO ESPECIFICO PARA EL FORMULARIO DE ACTUALIZACION (UPDATE)
		function consulta_AutorProyecto_especifico_ParaUpdate($id)
		{
			$this->sql="SELECT * FROM autor_proyecto WHERE proy_id=$id";
			$this->rs=parent::ejecutaQUERY();
			
			$cntdr = 1; //Contador para identificar el nùmero de autores relacionados
			$campo = "";
			while($row = mysql_fetch_array($this->rs))
			{
//				$msg.=$row['aproy_apellido']." ".$row['aproy_nombre']."<br><br>";

				$campo .= "<tr>";
				$campo .= "<td class='td100 tdSmall tdCapitalize'>Apellido</td>";
				$campo .= "<td class='td100 tdSmall tdCapitalize'><input type='hidden' name='aproy_id_".$cntdr."' id='aproy_id_".$cntdr."' value='".$row['aproy_id']."'><input type='text' name='aproy_apellido_".$cntdr."' id='aproy_apellido_".$cntdr."' value='".$row['aproy_apellido']."'></td>";
				$campo .= "<td class='td100 tdSmall tdCapitalize'>Nombre</td>";
				$campo .= "<td class='td100 tdSmall tdCapitalize'><input type='text' name='aproy_nombre_".$cntdr."' id='aproy_nombre_".$cntdr++."' value='".$row['aproy_nombre']."'></td>";
				$campo .= "</tr>";
			}
			return $campo;
//			echo $campo;
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//AGREGA LOS DATOS DEL AUTOR DEL PROYECTO
		function agregar_AutorProyecto_especifico($aproy_nombre, $aproy_apellido, $proy_id)
		{
			$this->sql =  "insert into autor_proyecto(aproy_nombre, aproy_apellido, proy_id) ";
			$this->sql .= "values('$aproy_nombre', '$aproy_apellido', $proy_id)";
			parent::ejecutaQUERY();
		}
}		


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Financiamiento extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE FINANCIAMIENTO
		function consulta_Financiamiento()
		{
			$this->sql="SELECT * FROM financiamiento";
			$this->rs=parent::ejecutaQUERY();
			
			$msg = "";
			while($row = mysql_fetch_array($this->rs))
			{
				$msg.="<option value='".$row['finan_id']."'>".strtoupper($row['finan_descripcion'])."</option>";
			}
			return $msg;
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE FINANCIAMIENTO
		function consulta_Financiamiento_options()
		{
			$this->sql="SELECT * FROM financiamiento";
			$this->rs=parent::ejecutaQUERY();
			
			$msg = "";
			while($row = mysql_fetch_array($this->rs))
			{
				$msg.="<option value='".$row['finan_id']."'>".strtoupper($row['finan_descripcion'])."</option>";
			}
			return $msg;
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LA DESCRIPCION DE UN FINANCIAMIENTO ESPECIFICO
		function consulta_Financiamiento_especifico_descripcion($finan_id)
		{
			$this->sql="SELECT finan_descripcion FROM financiamiento WHERE finan_id=$finan_id";
			$this->rs=parent::ejecutaQUERY();
			
			$msg = "";
			while($row = mysql_fetch_array($this->rs))
			{
				$msg=$row['finan_descripcion'];
			}
			return $msg;
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE UN FINANCIAMIENTO ESPECIFICO
		function consulta_Financiamiento_options_selected($id)
		{
			$this->sql="SELECT * FROM financiamiento";
			$this->rs=parent::ejecutaQUERY();
			
			$msg = "";
			while($row = mysql_fetch_array($this->rs))
			{
				if($row['finan_id'] == $id)
					$selected = "selected='selected'";
				else
					$selected="";
					
				$msg.="<option value='".$row['finan_id']."' ".$selected.">".strtoupper($row['finan_descripcion'])."</option>";
			}
			return $msg;
		}
}		


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class IP_PROY extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LA RELACION DE PROYECTOS DE UN DOCENTE DETERMINADO
		function consulta_IPPROY_determinado($ip_id,$tp_descripcion)
		{
			$this->sql="select * from ip_proy ";
			$this->sql.="where ip_id=$ip_id and proy_id in ";
			$this->sql.="( ";
			$this->sql.="	select proy_id from proyecto where tp_id in ";
			$this->sql.="	( ";
			$this->sql.="		select tp_id from tipo_proyecto ";
			$this->sql.="		where tp_descripcion = '$tp_descripcion' ";
			$this->sql.="	) ";
			$this->sql.=")";

			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//AGREGA LA RELACION DE LA PARTICIPACION DEL DOCENTE EN EL PROYECTO
		function agregar_IPPROY($ip_id, $proy_id)
		{
			$this->sql =  "insert into ip_proy(ip_id, proy_id) ";
			$this->sql .= "values($ip_id, $proy_id)";
			
			//Se realiza la inserción del nuevo registro y se obtiene el id del ultimo registro realizado
			parent::ejecutaQUERY();
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Tipo_Evento extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA TODAS LAS DESCRIPCIONES DE LOS TIPOS DE EVENTOS
		function consulta_TipoEvento()
		{
			$this->sql="select * from tipo_evento ";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LA DESCRIPCION DE UN TIPO DE EVENTO DETERMINADO
		function consulta_TipoEvento_determinado($te_id)
		{
			$this->sql="select * from tipo_evento ";
			$this->sql.="where te_id=$te_id";

			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LA DESCRIPCION DE UN TIPO DE EVENTO DETERMINADO
		function consulta_TipoEvento_determinado_descripcion($te_id)
		{
			$this->sql="select te_descripcion from tipo_evento ";
			$this->sql.="where te_id=$te_id";
			
			$this->rs=parent::ejecutaQUERY();

			$msg = "";
			while($row = mysql_fetch_array($this->rs))
				$msg = $row['te_descripcion'];
				
			return $msg;
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Ponencia extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA TODOS LOS DATOS DE TODAS LAS PONENCIAS
		function consulta_Ponencia_ALL($ip_id)
		{
			$this->sql="select * from ponencia ";
			$this->sql.="where ip_id=$ip_id";

			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA TODOS LOS DATOS DE TODAS LAS PONENCIAS EN LOS ULTIMOS 3 AÑOS
		function consulta_Ponencia($ip_id)
		{
			$this->sql="select * from ponencia ";
			$this->sql.="where ip_id=$ip_id ";
			$this->sql.="and ponencia.pp_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE())";

			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE UNA PONENCIA DETERMINADA
		function consulta_Ponencia_determinado($ip_id, $pp_id)
		{
			$this->sql="select * from ponencia ";
			$this->sql.="where ip_id=$ip_id and pp_id=$pp_id";

			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//AGREGA LOS DATOS DE UNA PONENCIA
		function agregar_Ponencia($ip_id, $año, $titulo, $nombre_evento, $lugar, $te_id)
		{
			$this->sql =  "insert into ponencia(ip_id, pp_año, pp_titulo, pp_nombre_evento, pp_lugar, te_id) ";
			$this->sql .= "values($ip_id, $año, '$titulo', '$nombre_evento', '$lugar', $te_id)";

			parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ACTUALIZA LOS DATOS DE UN GRUPO DE INVESTIGACION
		function actualiza_Ponencia($pp_id, $pp_año, $pp_titulo, $pp_nombre_evento, $pp_lugar, $te_id)
		{
			$this->sql="UPDATE ponencia SET pp_año=$pp_año, pp_titulo='$pp_titulo', pp_nombre_evento='$pp_nombre_evento', pp_lugar='$pp_lugar', te_id=$te_id where pp_id=$pp_id";
			
			parent::ejecutaQUERY();
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Capacitacion_Permanente extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA TODAS LAS CAPACITACIONES
		function consulta_CapacitacionPermanente()
		{
			$this->sql="select * from capacitacion";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE TODAS LAS CAPACITACIONES
		function consulta_CapacitacionPermanente_descripcion($capac_id)
		{
			$this->sql="select capac_descripcion from capacitacion ";
			$this->sql.="where capac_id=$capac_id";

			$this->rs=parent::ejecutaQUERY();

			$msg = "";
			while($row = mysql_fetch_array($this->rs))
				$msg = $row['capac_descripcion'];
				
			return $msg;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//AGREGA UNA CAPACITACION
		function agrega_CapacitacionPermanente($ip_id, $capac_año, $capac_entidad_capacitante, $capac_lugar, $capac_descripcion)
		{
			$this->sql="insert into capacitacion(capac_descripcion) values('$capac_descripcion')";
			$capac_id = parent::insertaQUERY();

			$this->sql="insert into ip_capac(ip_id, capac_id, capac_año, capac_entidad_capacitante, capac_lugar) ";
			$this->sql.="values($ip_id, $capac_id, $capac_año, '$capac_entidad_capacitante', '$capac_lugar')";
			parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ACTUALIZA UNA CAPACITACION
		function actualiza_CapacitacionPermanente($ip_id, $capac_id, $capac_año, $capac_entidad_capacitante, $capac_lugar, $capac_descripcion)
		{
			$this->sql="UPDATE capacitacion SET capac_descripcion='$capac_descripcion' where capac_id=$capac_id";
			parent::ejecutaQUERY();
			
			$this->sql="UPDATE ip_capac SET capac_año=$capac_año, capac_entidad_capacitante='$capac_entidad_capacitante', capac_lugar='$capac_lugar' ";
			$this->sql.="where ip_id=$ip_id and capac_id=$capac_id";
			parent::ejecutaQUERY();
		}

}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class IP_CAPAC extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE TODAS LAS CAPACITACIONES
		function consulta_IPCAPAC_all()
		{
			$this->sql="select * from IP_CAPAC";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LAS CAPACITACIONES DEL DOCENTE
		function consulta_IPCAPAC($ip_id)
		{
			$this->sql="select * from ip_capac ";
			$this->sql.="where ip_id=$ip_id";

			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE UNA CAPACITACION DETERMINADA
		function consulta_IPCAPAC_determinado($ip_id, $capac_id)
		{
			$this->sql="select * from ip_capac ";
			$this->sql.="where ip_id=$ip_id and capac_id=$capac_id";
			
			return parent::ejecutaQUERY();
		}
		
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class IP_CONVENIO extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE TODOS LOS CONVENIOS
		function consulta_IPCONVENIO_all()
		{
			$this->sql="select * from ip_convenio";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DEL CONVENIO EN EL QUE ESTA VINCULADO EL DOCENTE
		function consulta_IPCONVENIO_determinado($ip_id, $pc_id)
		{
			$this->sql="select * from ip_convenio ";
			$this->sql.="where ip_id=$ip_id and pc_id=$pc_id";

			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS CONVENIOS EN LOS QUE ESTA VINCULADO EL DOCENTE
		function consulta_IPCONVENIO($ip_id)
		{
			$this->sql="select * from ip_convenio ";
			$this->sql.="where ip_id=$ip_id";

			return parent::ejecutaQUERY();
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Convenio extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS CONVENIOS
		function consulta_Convenio_all()
		{
			$this->sql="select * from convenio";
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE UN CONVENIO
		function consulta_Convenio_determinado($pc_id)
		{
			$this->sql="select * from convenio ";
			$this->sql.="where pc_id=$pc_id";

			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//AGREGA UN CONVENIO
		function agrega_Convenio($ip_id, $pc_año, $pc_entidad, $pc_descripcion)
		{
			$this->sql="insert into convenio(pc_año, pc_entidad, pc_descripcion) values($pc_año, '$pc_entidad', '$pc_descripcion')";
			$pc_id = parent::insertaQUERY();

			$this->sql="insert into ip_convenio(ip_id, pc_id) ";
			$this->sql.="values($ip_id, $pc_id)";
			parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ACTUALIZA UN CONVENIO
		function actualiza_Convenio($pc_id, $pc_año, $pc_entidad, $pc_descripcion)
		{
			$this->sql="UPDATE convenio SET pc_año=$pc_año, pc_entidad='$pc_entidad', pc_descripcion='$pc_descripcion' ";
			$this->sql.="where pc_id=$pc_id";
			parent::ejecutaQUERY();
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Red extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LAS REDES DEL DOCENTE
		function consulta_Red($ip_id)
		{
			$this->sql="select * from red ";
			$this->sql.="where red_id in (select red_id from ip_red where ip_id=$ip_id)";

			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LAS REDES
		function consulta_Red_determinado($red_id)
		{
			$this->sql="select * from red ";
			$this->sql.="where red_id=$red_id";

			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//VERIFICA LA EXISTENCIA DE ESTA RED
		function consulta_Red_determinado_nombre($red_id)
		{
			$this->sql="select red_nombre from red ";
			$this->sql.="where red_id=$red_id";

			$this->rs=parent::ejecutaQUERY();
			
			if($row = mysql_fetch_array($this->rs))
				$msg=$row['red_nombre'];
			else $msg=false;
			
			return $msg;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//VERIFICA LA EXISTENCIA DE ESTA RED
		function consulta_Red_existencia($red_nombre)
		{
			$this->sql="select red_id from red ";
			$this->sql.="where red_nombre like '$red_nombre'";

			$this->rs=parent::ejecutaQUERY();
			
			if($row = mysql_fetch_array($this->rs))
				$msg=$row['red_id'];
			else $msg=false;
			
			return $msg;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//AGREGA UNA RED
		function agrega_Red($ip_id, $red_nombre, $red_url, $tr_id)
		{
			$red_id = $this->consulta_Red_existencia($red_nombre);
			if(!$red_id)
			{
				$this->sql="insert into red(red_nombre, red_url, tr_id) values('$red_nombre', '$red_url', $tr_id)";
				$red_id = parent::insertaQUERY();
			}

			$this->sql="insert into ip_red(ip_id, red_id) ";
			$this->sql.="values($ip_id, $red_id)";
			parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ACTUALIZA UNA RED
		function actualiza_Red($ip_id, $red_id, $red_nombre, $red_url, $tr_id)
		{
			$this->sql="UPDATE red SET red_nombre='$red_nombre', red_url='$red_url', tr_id=$tr_id ";
			$this->sql.="where red_id=$red_id";

			parent::ejecutaQUERY();
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Tipo_Red extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS TIPO DE REDES
		function consulta_TipoRed()
		{
			$this->sql="select * from tipo_red";

			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS TIPO DE REDES
		function consulta_TipoRed_options()
		{
			$this->sql="select * from tipo_red";
			$this->rs=parent::ejecutaQUERY();
			
			$msg = "";
			while($row = mysql_fetch_array($this->rs))
				$msg.="<option value='".$row['tr_id']."'>".strtoupper($row['tr_descripcion'])."</option>";

			return $msg;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS TIPO DE REDES
		function consulta_TipoRed_options_selected($tr_id)
		{
			$this->sql="select * from tipo_red";
			$this->rs=parent::ejecutaQUERY();
			
			$msg = "";
			while($row = mysql_fetch_array($this->rs))
			{
				if($tr_id==$row['tr_id'])
					$selected="selected='selected'";
				else $selected="";
				
				$msg.="<option value='".$row['tr_id']."' $selected>".strtoupper($row['tr_descripcion'])."</option>";
			}

			return $msg;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LA DESCRIPCION DE UN TIPO DE RED ESPECIFICO
		function consulta_TipoRed_determinado_descripcion($tr_id)
		{
			$this->sql="select tr_descripcion from tipo_red ";
			$this->sql.="where tr_id=$tr_id";

			$this->rs=parent::ejecutaQUERY();
			
			$msg = "";
			while($row = mysql_fetch_array($this->rs))
				$msg=$row['tr_descripcion'];

			return $msg;
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Material extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE UN MATERIALES PROPORCIONADOS POR UN DOCENTE
		function consulta_Material($ip_id)
		{
			$this->sql="select * from material ";
			$this->sql.="where ip_id=$ip_id ";
			$this->sql.="order by mat_fcha desc";

			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE UN MATERIAL ESPECIFICO
		function consulta_Material_determinado($mat_id)
		{
			$this->sql="select * from material ";
			$this->sql.="where mat_id=$mat_id";

			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE UN MATERIAL ESPECIFICO
		function consulta_Material_determinado_to_download($mat_id)
		{
			$this->sql="select file_name, file_type, file_size, file_content from material ";
			$this->sql.="where mat_id=$mat_id";

			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//AGREGA UN MATERIAL
		function agrega_Material($mat_nombre, $mat_url, $mat_copia, $fileName, $ip_id, $tm_id)
		{
			echo 'Estamos procesando el archivo. Esto puede tardar unos segundos...<br><br>';
	
			$this->sql =	"insert into material (mat_nombre, mat_url, mat_copia, mat_fcha, file_name, ip_id, tm_id ) ".
							"values ('$mat_nombre', '$mat_url', '$mat_copia', now(), '$fileName', $ip_id, $tm_id)";
			//echo $this->sql;
			parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ACTUALIZA UN MATERIAL
		function actualiza_Material($ip_id, $mat_id, $mat_nombre, $mat_url, $mat_copia, $tm_id)
		{
			$this->sql="UPDATE material SET mat_nombre='$mat_nombre', mat_url='$mat_url', mat_copia='$mat_copia', tm_id=$tm_id ";
			$this->sql.="where mat_id=$mat_id";

			parent::ejecutaQUERY();
		}
		
		
		function subir_Material()
		{
			//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
			//				SECCION DE SUBIDA DE LA IMAGEN
			//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
			
			//formulario de upload por jorge luis martinez
			//http://miscodigos.jlmnetwork.com/
			
			// & MODIFICADO POR MI: << MIGUEL ANGEL GARCIA >>
			
			$extension = explode(".",$archivo_name);
			$num = count($extension)-1;
			
			if($extension[$num]=='swf' || $extension[$num]=='SWF')
			{
				$nombre_file = $archivo_name;
				$proverbio = $nombre_file;
			
				if($archivo_size < 3000000)
				{
			/*
				 if(!copy($archivo, "images-usuarios/".$archivo_name))
				 { echo "Error al Copiar el Archivo"; }
			*/
			
				 if(!copy($archivo, $archivo_name))
				 { $mensaje = "Error al Copiar el Archivo"; $disponibilidad=0; }
			
				 else
				 {
				  rename($archivo_name,$proverbio);
				  echo "Archivo subido con Exito<br><br>El archivo subido es: ".$archivo_name;
				  RegistrarSubida($archivo_name,$_POST['desc'],$_POST['dir']);
				  $only_name = explode(".swf",$archivo_name);
				  redireccionar('visor.php?u=admin&var='.$only_name[0]);
				 }
				}
			
				else
				{ $mensaje = "El archivo supera los 3 MBytes"; $disponibilidad=0; }
			
			/*
				  $extension = explode("recID",$proverbio);
				  echo '<br>extension[0]: '.$extension[0];
				  echo '<br>extension[1]: '.$extension[1];	//id_usuario
				  echo '<br>extension[2]: '.$extension[2];	//nombre del archivo
				  echo '<br>extension[3]: '.$extension[3];
				  echo '<br>extension[4]: '.$extension[4];
				  echo '<br>proverbio: '.$proverbio;
			*/
			
			}
			
			else
				echo 'El formato de archivo no es V&aacute;lido. Verifique que sea .SWF';
			
			//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
		
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Tipo_Material extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS TIPOS DE MATERIAL
		function consulta_TipoMaterial()
		{
			$this->sql="select * from tipo_material";

			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS TIPOS DE MATERIAL
		function consulta_TipoMaterial_options()
		{
			$this->sql="select * from tipo_material";
			$this->rs=parent::ejecutaQUERY();
			
			$msg = "";
			while($row = mysql_fetch_array($this->rs))
				$msg.="<option value='".$row['tm_id']."'>".strtoupper($row['tm_descripcion'])."</option>";

			return $msg;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS TIPOS DE MATERIAL
		function consulta_TipoMaterial_options_selected($tm_id)
		{
			$this->sql="select * from tipo_material";
			$this->rs=parent::ejecutaQUERY();
			
			$msg = "";
			while($row = mysql_fetch_array($this->rs))
			{
				if($tm_id==$row['tm_id'])
					$selected="selected='selected'";
				else $selected="";
				
				$msg.="<option value='".$row['tm_id']."' $selected>".strtoupper($row['tm_descripcion'])."</option>";
			}

			return $msg;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LA DESCRIPCION DE UN TIPO DE MATERIAL ESPECIFICO
		function consulta_TipoMaterial_determinado_descripcion($tm_id)
		{
			$this->sql="select tm_descripcion from tipo_material ";
			$this->sql.="where tm_id=$tm_id";

			$this->rs=parent::ejecutaQUERY();
			
			$msg = "";
			while($row = mysql_fetch_array($this->rs))
				$msg=$row['tm_descripcion'];

			return $msg;
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Titulo extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS TITULOS DEL DOCENTE
		function consulta_Titulo($ip_id)
		{
			$this->sql="select * from ip_tit ";
			$this->sql.="where ip_id=".$ip_id;

			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS TITULOS
		function consulta_Titulo_determinado($ip_id, $tit_id)
		{
			$this->sql="select * from ip_tit ";
			$this->sql.="where ip_id=$ip_id and tit_id=$tit_id";

			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS TITULOS
		function consulta_Titulo_determinado_nombre($tit_id)
		{
			$this->sql="select tit_nombre from titulo ";
			$this->sql.="where tit_id=$tit_id";

			$this->rs=parent::ejecutaQUERY();
			
			$msg = "";
			while($row = mysql_fetch_array($this->rs))
				$msg=$row['tit_nombre'];

			return $msg;
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//VERIFICA LA EXISTENCIA DE ESTE TITULO
		function consulta_Titulo_existencia($tit_nombre)
		{
			$this->sql="select tit_id from titulo ";
			$this->sql.="where tit_nombre like '$tit_nombre'";

			$this->rs=parent::ejecutaQUERY();
			
			if($row = mysql_fetch_array($this->rs))
				$msg=$row['tit_id'];
			else $msg=false;
			
			return $msg;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//AGREGA UN TITULO
		function agrega_Titulo($ip_id, $tit_nombre, $tit_universidad, $tit_año, $tit_ciudad, $tit_pais)
		{
			//VERIFICAMOS SI EXISTE EL TITULO EN LA BASE DE DATOS
			if(!$tit_id = $this->consulta_Titulo_existencia($tit_nombre))
			{
				$this->sql="insert into titulo(tit_nombre) values('$tit_nombre')";	// SI NO EXISTE EL TITULO PROCEDEMOS A AGREGARLO A LA BASE DE DATOS
				$tit_id = parent::insertaQUERY();
			}
			
			//PROCEDEMOS A INSERTAR LOS DATOS
			$this->sql="insert into ip_tit(ip_id, tit_id, tit_universidad, tit_año, tit_ciudad, tit_pais) ";
			$this->sql.="values($ip_id, $tit_id, '$tit_universidad', $tit_año, '$tit_ciudad', '$tit_pais')";
			parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ACTUALIZA UN TITULO
		function actualiza_Titulo($ip_id, $tit_id, $tit_nombre, $tit_universidad, $tit_año, $tit_ciudad, $tit_pais)
		{
			$tit_id_Aux = $tit_id; // Variable auxiliar que contiene el valor del id original del titulo
			
			//VERIFICAMOS SI EXISTE EL TITULO EN LA BASE DE DATOS
			if(!$tit_id = $this->consulta_Titulo_existencia($tit_nombre))
			{
				$this->sql="insert into titulo(tit_nombre) values('$tit_nombre')";	// SI NO EXISTE EL TITULO PROCEDEMOS A AGREGARLO A LA BASE DE DATOS
				$tit_id = parent::insertaQUERY();
			}
			
			//PROCEDEMOS A ACTUALIZAR LOS DATOS
			$this->sql="UPDATE ip_tit SET tit_id=$tit_id, tit_universidad='$tit_universidad', tit_año=$tit_año, tit_ciudad='$tit_ciudad', tit_pais='$tit_pais' ";
			$this->sql.="where ip_id=$ip_id and tit_id=$tit_id_Aux";

			parent::ejecutaQUERY();
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Tipo_Publicacion extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS TIPOS DE MATERIAL
		function consulta_TipoPublicacion()
		{
			$this->sql="select * from tipo_publicacion";

			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS TIPOS DE MATERIAL
		function consulta_TipoPublicacion_options()
		{
			$this->sql="select * from tipo_publicacion";
			$this->rs=parent::ejecutaQUERY();
			
			$msg = "";
			while($row = mysql_fetch_array($this->rs))
				$msg.="<option value='".$row['tpub_id']."'>".strtoupper($row['tpub_descripcion'])."</option>";

			return $msg;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LOS TIPOS DE MATERIAL
		function consulta_TipoPublicacion_options_selected($tpub_id)
		{
			$this->sql="select * from tipo_publicacion";
			$this->rs=parent::ejecutaQUERY();
			
			$msg = "";
			while($row = mysql_fetch_array($this->rs))
			{
				if($tpub_id==$row['tpub_id'])
					$selected="selected='selected'";
				else $selected="";
				
				$msg.="<option value='".$row['tpub_id']."' $selected>".strtoupper($row['tpub_descripcion'])."</option>";
			}

			return $msg;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LA DESCRIPCION DE UN TIPO DE MATERIAL ESPECIFICO
		function consulta_TipoPublicacion_determinado_descripcion($tpub_id)
		{
			$this->sql="select tpub_descripcion from tipo_publicacion ";
			$this->sql.="where tpub_id=$tpub_id";

			$this->rs=parent::ejecutaQUERY();
			
			$msg = "";
			while($row = mysql_fetch_array($this->rs))
				$msg=$row['tpub_descripcion'];

			return $msg;
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Publicacion extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LAS PUBLICACIONES DEL DOCENTE
		function consulta_Publicacion_ALL($ip_id)
		{
			$this->sql="select * from publicacion ";
			$this->sql.="where ip_id=".$ip_id;

			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE LAS PUBLICACIONES DEL DOCENTE EN LOS ULTIMOS 3 AÑOS
		function consulta_Publicacion($ip_id)
		{
			$this->sql="select * from publicacion ";
			$this->sql.="where ip_id=".$ip_id." ";
			$this->sql.="and publicacion.pub_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE())";

			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//CONSULTA LOS DATOS DE UNA PUBLICACION ESPECIFICA
		function consulta_Publicacion_determinado($pub_id)
		{
			$this->sql="select * from publicacion ";
			$this->sql.="where pub_id=$pub_id";

			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//AGREGA LOS DATOS DE UNA PUBLICACION ESPECIFICA
		function agrega_Publicacion($ip_id, $pub_año, $pub_titulo, $pub_revista, $pub_lugar_publica, $pub_autor, $tpub_id)
		{
			$this->sql="INSERT INTO publicacion(ip_id, pub_año, pub_titulo, pub_revista, pub_lugar_publica, pub_autor, tpub_id) ";
			$this->sql.="VALUES($ip_id, $pub_año, '$pub_titulo', '$pub_revista', '$pub_lugar_publica', '$pub_autor', $tpub_id)";
			//echo $this->sql.'<br><br>';
			parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ACTUALIZA LOS DATOS DE UNA PUBLICACION ESPECIFICA
		function actualiza_Publicacion($ip_id, $pub_id, $pub_año, $pub_titulo, $pub_revista, $pub_lugar_publica, $pub_autor, $tpub_id)
		{
			$this->sql="UPDATE publicacion SET pub_año=$pub_año, pub_titulo='$pub_titulo', pub_revista='$pub_revista', pub_lugar_publica='$pub_lugar_publica', pub_autor='$pub_autor', tpub_id=$tpub_id ";
			$this->sql.="WHERE pub_id=$pub_id";
			//echo $this->sql.'<br><br>';
			parent::ejecutaQUERY();
		}
}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Estadistica extends General
{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 4
		//
		function generar_estadistica($item)
		{
			switch($item)
			{
				case 4:
						$this->sql="SELECT count(red_id) as contador FROM red where red_id in ";
						$this->sql.="( ";
						$this->sql.="SELECT red_id FROM ip_red where red_id in (select red_id from red where tr_id in  ";
						$this->sql.="(select tr_id from tipo_red where tr_descripcion='académica'))  ";
						$this->sql.="GROUP BY red_id ";
						$this->sql.=")";
						break;
						
				case 5:
/*
						$this->sql="SELECT count(red_id) as contador FROM red where red_id in ";
						$this->sql.="( ";
						$this->sql.="SELECT red_id FROM ip_red where red_id in (select red_id from red where tr_id in  ";
						$this->sql.="(select tr_id from tipo_red where tr_descripcion='social'))  ";
						$this->sql.="GROUP BY red_id";
*/
						$this->sql="SELECT count(red_id) as contador FROM red where red_id in ";
						$this->sql.="( ";
						$this->sql.="SELECT red_id FROM ip_red where red_id in (select red_id from red where tr_id in  ";
						$this->sql.="(select tr_id from tipo_red where tr_descripcion='académica'))  ";
						$this->sql.="GROUP BY red_id ";
						$this->sql.=")";
						break;
				
/*
				case 6:
						break;
*/				
				case 7:
						$this->sql="select count(*) as contador from inf_personal where ip_id in (SELECT ip_id FROM ip_gi GROUP BY ip_id)";
						break;
				
				case 8:
						$this->sql="select inf_personal.ip_id, ip_nombre, ip_apellido, sum(gi_horas_docencia) as docencia, sum(gi_horas_investigacion) as investigacion, sum(gi_horas_extension) as extension from ip_gi ";
						$this->sql.="inner join inf_personal on (inf_personal.ip_id = ip_gi.ip_id) ";
						$this->sql.="group by inf_personal.ip_id";
						break;
				
/*
				case 9:
						break;
*/				
				
				case 10:
						$this->sql="select count(ip_id) as contador from ip_nf where nf_id in (select nf_id from nivel_formacion where nf_descripcion like '%especialización%')";
						break;
				
				case 11:
						$this->sql="select count(ip_id) as contador from ip_nf where nf_id in (select nf_id from nivel_formacion where nf_descripcion like '%doctorado%')";
						break;
				
				case 12:
						$this->sql="select count(ip_id) as contador from ip_nf where nf_id in (select nf_id from nivel_formacion where nf_descripcion like '%phd%')";
						break;
				
				case 13:
						$this->sql="select count(ip_id) as contador from ip_nf where nf_id in (select nf_id from nivel_formacion where nf_descripcion like '%universitari%')";
						break;

				case 14:
						$this->sql="SELECT count(*) as contador FROM inf_personal ";
						$this->sql.="inner join ip_proy ";
						$this->sql.="inner join proyecto ";
						$this->sql.="inner join tipo_proyecto ";
						$this->sql.="inner join financiamiento ";
						$this->sql.="where inf_personal.ip_id = ip_proy.ip_id  ";
						$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
						$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
						$this->sql.="and tipo_proyecto.tp_descripcion = 'investigación' ";
						$this->sql.="and proyecto.finan_id = financiamiento.finan_id ";
						$this->sql.="and financiamiento.finan_descripcion = 'financiado' ";
						$this->sql.="and proyecto.proy_año = year( curdate( ) )";
						break;
				
				case 15:
						$this->sql="SELECT count(*) as contador FROM inf_personal ";
						$this->sql.="inner join ip_proy ";
						$this->sql.="inner join proyecto ";
						$this->sql.="inner join tipo_proyecto ";
						$this->sql.="inner join financiamiento ";
						$this->sql.="where inf_personal.ip_id = ip_proy.ip_id  ";
						$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
						$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
						$this->sql.="and tipo_proyecto.tp_descripcion = 'investigación' ";
						$this->sql.="and proyecto.finan_id = financiamiento.finan_id ";
						$this->sql.="and financiamiento.finan_descripcion = 'financiado' ";
						$this->sql.="and proyecto.proy_año between (YEAR(CURDATE())-1) AND YEAR(CURDATE())";
						break;
				
				case 16:
						$this->sql="SELECT count(*) as contador FROM inf_personal ";
						$this->sql.="inner join ip_proy ";
						$this->sql.="inner join proyecto ";
						$this->sql.="inner join tipo_proyecto ";
						$this->sql.="inner join financiamiento ";
						$this->sql.="where inf_personal.ip_id = ip_proy.ip_id  ";
						$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
						$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
						$this->sql.="and tipo_proyecto.tp_descripcion = 'investigación' ";
						$this->sql.="and proyecto.finan_id = financiamiento.finan_id ";
						$this->sql.="and financiamiento.finan_descripcion = 'financiado' ";
						$this->sql.="and proyecto.proy_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE())";
						break;
				
				case 17:
						$this->sql="SELECT count(*) as contador FROM inf_personal ";
						$this->sql.="inner join ip_proy ";
						$this->sql.="inner join proyecto ";
						$this->sql.="inner join tipo_proyecto ";
						$this->sql.="inner join financiamiento ";
						$this->sql.="where inf_personal.ip_id = ip_proy.ip_id  ";
						$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
						$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
						$this->sql.="and tipo_proyecto.tp_descripcion = 'investigación' ";
						$this->sql.="and proyecto.finan_id = financiamiento.finan_id ";
						$this->sql.="and financiamiento.finan_descripcion = 'no financiado' ";
						$this->sql.="and proyecto.proy_año = year( curdate( ) )";
						break;
				
				case 18:
						$this->sql="SELECT count(*) as contador FROM inf_personal ";
						$this->sql.="inner join ip_proy ";
						$this->sql.="inner join proyecto ";
						$this->sql.="inner join tipo_proyecto ";
						$this->sql.="inner join financiamiento ";
						$this->sql.="where inf_personal.ip_id = ip_proy.ip_id  ";
						$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
						$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
						$this->sql.="and tipo_proyecto.tp_descripcion = 'investigación' ";
						$this->sql.="and proyecto.finan_id = financiamiento.finan_id ";
						$this->sql.="and financiamiento.finan_descripcion = 'no financiado' ";
						$this->sql.="and proyecto.proy_año between (YEAR(CURDATE())-1) AND YEAR(CURDATE())";
						break;
				
				case 19:
						$this->sql="SELECT count(*) as contador FROM inf_personal ";
						$this->sql.="inner join ip_proy ";
						$this->sql.="inner join proyecto ";
						$this->sql.="inner join tipo_proyecto ";
						$this->sql.="inner join financiamiento ";
						$this->sql.="where inf_personal.ip_id = ip_proy.ip_id  ";
						$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
						$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
						$this->sql.="and tipo_proyecto.tp_descripcion = 'investigación' ";
						$this->sql.="and proyecto.finan_id = financiamiento.finan_id ";
						$this->sql.="and financiamiento.finan_descripcion = 'no financiado' ";
						$this->sql.="and proyecto.proy_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE())";
						break;
				
/*
				case 20:
						break;
				
				case 21:
						break;
				
				case 22:
						break;
*/				
				
				case 23:
						$this->sql="select count(*) as contador from inf_personal ";
						$this->sql.="where ip_id in  ";
						$this->sql.="( ";
						$this->sql.="select inf_personal.ip_id from inf_personal ";
						$this->sql.="inner join ip_proy ";
						$this->sql.="inner join proyecto ";
						$this->sql.="inner join tipo_proyecto ";
						$this->sql.="where inf_personal.ip_id = ip_proy.ip_id ";
						$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
						$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
						$this->sql.="and tipo_proyecto.tp_descripcion = 'extensión' ";
						$this->sql.="and proyecto.proy_año = year( curdate( ) ) ";
						$this->sql.="group by inf_personal.ip_id ";
						$this->sql.=")";
						break;
				
				case 24:
						$this->sql="select count(*) as contador from inf_personal ";
						$this->sql.="where ip_id in  ";
						$this->sql.="( ";
						$this->sql.="select inf_personal.ip_id from inf_personal ";
						$this->sql.="inner join ip_proy ";
						$this->sql.="inner join proyecto ";
						$this->sql.="inner join tipo_proyecto ";
						$this->sql.="where inf_personal.ip_id = ip_proy.ip_id ";
						$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
						$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
						$this->sql.="and tipo_proyecto.tp_descripcion = 'extensión' ";
						$this->sql.="and proyecto.proy_año between (YEAR(CURDATE())-1) AND YEAR(CURDATE()) ";
						$this->sql.="group by inf_personal.ip_id ";
						$this->sql.=")";
						break;
				
				case 25:
						$this->sql="select count(*) as contador from inf_personal ";
						$this->sql.="where ip_id in  ";
						$this->sql.="( ";
						$this->sql.="select inf_personal.ip_id from inf_personal ";
						$this->sql.="inner join ip_proy ";
						$this->sql.="inner join proyecto ";
						$this->sql.="inner join tipo_proyecto ";
						$this->sql.="where inf_personal.ip_id = ip_proy.ip_id ";
						$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
						$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
						$this->sql.="and tipo_proyecto.tp_descripcion = 'extensión' ";
						$this->sql.="and proyecto.proy_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE()) ";
						$this->sql.="group by inf_personal.ip_id ";
						$this->sql.=")";
						break;
				
/*
				case 26:
						break;
				
				case 27:
						break;
*/				
				
				case 28:
						$this->sql="select count(*) as contador from inf_personal where ip_id in  ";
						$this->sql.="( ";
						$this->sql.="select ip_id from publicacion ";
						$this->sql.="where publicacion.pub_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE()) ";
						$this->sql.="group by ip_id ";
						$this->sql.=")";
						break;
				
				case 29:
						$this->sql="select count(*) as contador from publicacion ";
						$this->sql.="where tpub_id = (select tpub_id from tipo_publicacion where tpub_descripcion='indexada') ";
						$this->sql.="and publicacion.pub_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE())";
						break;
				
				case 30:
						$this->sql="select count(*) as contador from publicacion ";
						$this->sql.="where tpub_id = (select tpub_id from tipo_publicacion where tpub_descripcion='especializada') ";
						$this->sql.="and publicacion.pub_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE())";
						break;
				
				case 31:
						$this->sql="select count(*) as contador from publicacion ";
						$this->sql.="where tpub_id = (select tpub_id from tipo_publicacion where tpub_descripcion='patente') ";
						$this->sql.="and publicacion.pub_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE())";
						break;
				
/*
				case 32:
						break;
				
				case 33:
						break;
				
				case 34:
						break;
				
				case 35:
						break;
*/				
				
				case 36:
						$this->sql="select count(*) as contador from inf_personal where ip_id in  ";
						$this->sql.="( ";
						$this->sql.="select ip_id from ponencia ";
						$this->sql.="where ponencia.pp_año = YEAR(CURDATE()) ";
						$this->sql.="group by ip_id ";
						$this->sql.=")";
						break;
				
				case 37:
						$this->sql="select count(*) as contador from inf_personal where ip_id in  ";
						$this->sql.="( ";
						$this->sql.="select ip_id from ponencia ";
						$this->sql.="where ponencia.pp_año between (YEAR(CURDATE())-1) AND YEAR(CURDATE()) ";
						$this->sql.="group by ip_id ";
						$this->sql.=")";
						break;
				
				case 38:
						$this->sql="select count(*) as contador from inf_personal where ip_id in  ";
						$this->sql.="( ";
						$this->sql.="select ip_id from ponencia ";
						$this->sql.="where ponencia.pp_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE()) ";
						$this->sql.="group by ip_id ";
						$this->sql.=")";
						break;
				
				case 39:
						$this->sql="select count(*) as contador from inf_personal ";
						$this->sql.="where ip_id in  ";
						$this->sql.="( ";
						$this->sql.="select inf_personal.ip_id from inf_personal ";
						$this->sql.="inner join ip_capac ";
						$this->sql.="inner join capacitacion ";
						$this->sql.="where inf_personal.ip_id = ip_capac.ip_id ";
						$this->sql.="and ip_capac.capac_id = capacitacion.capac_id ";
						$this->sql.="and ip_capac.capac_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE()) ";
						$this->sql.="group by inf_personal.ip_id ";
						$this->sql.=")";
						break;
				
				case 40:
						$this->sql="select count(pc_id) as contador from convenio  ";
						$this->sql.="where pc_id in (select pc_id from ip_convenio) ";
						$this->sql.="and convenio.pc_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE())";
						break;
				
				case 41:
						$this->sql="select tipo_material.tm_id, tipo_material.tm_descripcion, count(tipo_material.tm_descripcion) as contador  ";
						$this->sql.="from tipo_material  ";
						$this->sql.="inner join material on (material.tm_id = tipo_material.tm_id)  ";
						$this->sql.="group by tm_descripcion";
						break;
				
				default:
						$this->sql = 'SELECT count(*) as contador FROM inf_personal';
						break;
			}
			return parent::ejecutaQUERY();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 4
		//
		function estadistica_4()
		{
			$this->sql="SELECT count(red_id) as contador FROM red where red_id in ";
			$this->sql.="( ";
			$this->sql.="SELECT red_id FROM ip_red where red_id in (select red_id from red where tr_id in  ";
			$this->sql.="(select tr_id from tipo_red where tr_descripcion='académica'))  ";
			$this->sql.="GROUP BY red_id ";
			$this->sql.=")";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 5
		//
		function estadistica_5()
		{
			$this->sql="SELECT count(red_id) as contador FROM red where red_id in ";
			$this->sql.="( ";
			$this->sql.="SELECT red_id FROM ip_red where red_id in (select red_id from red where tr_id in  ";
			$this->sql.="(select tr_id from tipo_red where tr_descripcion='social'))  ";
			$this->sql.="GROUP BY red_id";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 7
		//
		function estadistica_7()
		{
			$this->sql="select count(*) as contador from inf_personal where ip_id in (SELECT ip_id FROM ip_gi GROUP BY ip_id)";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 8
		//
		function estadistica_8()
		{
			$this->sql="select inf_personal.ip_id, ip_nombre, ip_apellido, sum(gi_horas_docencia) as docencia, sum(gi_horas_investigacion) as investigacion, sum(gi_horas_extension) as extension from ip_gi ";
			$this->sql.="inner join inf_personal on (inf_personal.ip_id = ip_gi.ip_id) ";
			$this->sql.="group by inf_personal.ip_id";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 10
		//
		function estadistica_10()
		{
			$this->sql="select count(ip_id) as contador from ip_nf where nf_id in (select nf_id from nivel_formacion where nf_descripcion like '%especialización%')";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 11
		//
		function estadistica_11()
		{
			$this->sql="select count(ip_id) as contador from ip_nf where nf_id in (select nf_id from nivel_formacion where nf_descripcion like '%doctorado%')";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 12
		//
		function estadistica_12()
		{
			$this->sql="select count(ip_id) as contador from ip_nf where nf_id in (select nf_id from nivel_formacion where nf_descripcion like '%phd%')";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 13
		//
		function estadistica_13()
		{
			$this->sql="select count(ip_id) as contador from ip_nf where nf_id in (select nf_id from nivel_formacion where nf_descripcion like '%universitari%')";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 14
		//
		function estadistica_14()
		{
			$this->sql="SELECT count(*) as contador FROM inf_personal ";
			$this->sql.="inner join ip_proy ";
			$this->sql.="inner join proyecto ";
			$this->sql.="inner join tipo_proyecto ";
			$this->sql.="inner join financiamiento ";
			$this->sql.="where inf_personal.ip_id = ip_proy.ip_id  ";
			$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
			$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
			$this->sql.="and tipo_proyecto.tp_descripcion = 'investigación' ";
			$this->sql.="and proyecto.finan_id = financiamiento.finan_id ";
			$this->sql.="and financiamiento.finan_descripcion = 'financiado' ";
			$this->sql.="and proyecto.proy_año = year( curdate( ) )";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 15
		//
		function estadistica_15()
		{
			$this->sql="SELECT count(*) as contador FROM inf_personal ";
			$this->sql.="inner join ip_proy ";
			$this->sql.="inner join proyecto ";
			$this->sql.="inner join tipo_proyecto ";
			$this->sql.="inner join financiamiento ";
			$this->sql.="where inf_personal.ip_id = ip_proy.ip_id  ";
			$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
			$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
			$this->sql.="and tipo_proyecto.tp_descripcion = 'investigación' ";
			$this->sql.="and proyecto.finan_id = financiamiento.finan_id ";
			$this->sql.="and financiamiento.finan_descripcion = 'financiado' ";
			$this->sql.="and proyecto.proy_año between (YEAR(CURDATE())-1) AND YEAR(CURDATE())";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 16
		//
		function estadistica_16()
		{
			$this->sql="SELECT count(*) as contador FROM inf_personal ";
			$this->sql.="inner join ip_proy ";
			$this->sql.="inner join proyecto ";
			$this->sql.="inner join tipo_proyecto ";
			$this->sql.="inner join financiamiento ";
			$this->sql.="where inf_personal.ip_id = ip_proy.ip_id  ";
			$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
			$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
			$this->sql.="and tipo_proyecto.tp_descripcion = 'investigación' ";
			$this->sql.="and proyecto.finan_id = financiamiento.finan_id ";
			$this->sql.="and financiamiento.finan_descripcion = 'financiado' ";
			$this->sql.="and proyecto.proy_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE())";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 17
		//
		function estadistica_17()
		{
			$this->sql="SELECT count(*) as contador FROM inf_personal ";
			$this->sql.="inner join ip_proy ";
			$this->sql.="inner join proyecto ";
			$this->sql.="inner join tipo_proyecto ";
			$this->sql.="inner join financiamiento ";
			$this->sql.="where inf_personal.ip_id = ip_proy.ip_id  ";
			$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
			$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
			$this->sql.="and tipo_proyecto.tp_descripcion = 'investigación' ";
			$this->sql.="and proyecto.finan_id = financiamiento.finan_id ";
			$this->sql.="and financiamiento.finan_descripcion = 'no financiado' ";
			$this->sql.="and proyecto.proy_año = year( curdate( ) )";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 18
		//
		function estadistica_18()
		{
			$this->sql="SELECT count(*) as contador FROM inf_personal ";
			$this->sql.="inner join ip_proy ";
			$this->sql.="inner join proyecto ";
			$this->sql.="inner join tipo_proyecto ";
			$this->sql.="inner join financiamiento ";
			$this->sql.="where inf_personal.ip_id = ip_proy.ip_id  ";
			$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
			$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
			$this->sql.="and tipo_proyecto.tp_descripcion = 'investigación' ";
			$this->sql.="and proyecto.finan_id = financiamiento.finan_id ";
			$this->sql.="and financiamiento.finan_descripcion = 'no financiado' ";
			$this->sql.="and proyecto.proy_año between (YEAR(CURDATE())-1) AND YEAR(CURDATE())";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 19
		//
		function estadistica_19()
		{
			$this->sql="SELECT count(*) as contador FROM inf_personal ";
			$this->sql.="inner join ip_proy ";
			$this->sql.="inner join proyecto ";
			$this->sql.="inner join tipo_proyecto ";
			$this->sql.="inner join financiamiento ";
			$this->sql.="where inf_personal.ip_id = ip_proy.ip_id  ";
			$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
			$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
			$this->sql.="and tipo_proyecto.tp_descripcion = 'investigación' ";
			$this->sql.="and proyecto.finan_id = financiamiento.finan_id ";
			$this->sql.="and financiamiento.finan_descripcion = 'no financiado' ";
			$this->sql.="and proyecto.proy_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE())";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 23
		//
		function estadistica_23()
		{
			$this->sql="select count(*) as contador from inf_personal ";
			$this->sql.="where ip_id in  ";
			$this->sql.="( ";
			$this->sql.="select inf_personal.ip_id from inf_personal ";
			$this->sql.="inner join ip_proy ";
			$this->sql.="inner join proyecto ";
			$this->sql.="inner join tipo_proyecto ";
			$this->sql.="where inf_personal.ip_id = ip_proy.ip_id ";
			$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
			$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
			$this->sql.="and tipo_proyecto.tp_descripcion = 'extensión' ";
			$this->sql.="and proyecto.proy_año = year( curdate( ) ) ";
			$this->sql.="group by inf_personal.ip_id ";
			$this->sql.=")";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 24
		//
		function estadistica_24()
		{
			$this->sql="select count(*) as contador from inf_personal ";
			$this->sql.="where ip_id in  ";
			$this->sql.="( ";
			$this->sql.="select inf_personal.ip_id from inf_personal ";
			$this->sql.="inner join ip_proy ";
			$this->sql.="inner join proyecto ";
			$this->sql.="inner join tipo_proyecto ";
			$this->sql.="where inf_personal.ip_id = ip_proy.ip_id ";
			$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
			$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
			$this->sql.="and tipo_proyecto.tp_descripcion = 'extensión' ";
			$this->sql.="and proyecto.proy_año between (YEAR(CURDATE())-1) AND YEAR(CURDATE()) ";
			$this->sql.="group by inf_personal.ip_id ";
			$this->sql.=")";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 25
		//
		function estadistica_25()
		{
			$this->sql="select count(*) as contador from inf_personal ";
			$this->sql.="where ip_id in  ";
			$this->sql.="( ";
			$this->sql.="select inf_personal.ip_id from inf_personal ";
			$this->sql.="inner join ip_proy ";
			$this->sql.="inner join proyecto ";
			$this->sql.="inner join tipo_proyecto ";
			$this->sql.="where inf_personal.ip_id = ip_proy.ip_id ";
			$this->sql.="and ip_proy.proy_id = proyecto.proy_id ";
			$this->sql.="and proyecto.tp_id = tipo_proyecto.tp_id ";
			$this->sql.="and tipo_proyecto.tp_descripcion = 'extensión' ";
			$this->sql.="and proyecto.proy_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE()) ";
			$this->sql.="group by inf_personal.ip_id ";
			$this->sql.=")";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 28
		//
		function estadistica_28()
		{
			$this->sql="select count(*) as contador from inf_personal where ip_id in  ";
			$this->sql.="( ";
			$this->sql.="select ip_id from publicacion ";
			$this->sql.="where publicacion.pub_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE()) ";
			$this->sql.="group by ip_id ";
			$this->sql.=")";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 29
		//
		function estadistica_29()
		{
			$this->sql="select count(*) as contador from publicacion ";
			$this->sql.="where tpub_id = (select tpub_id from tipo_publicacion where tpub_descripcion='indexada') ";
			$this->sql.="and publicacion.pub_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE())";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 30
		//
		function estadistica_30()
		{
			$this->sql="select count(*) as contador from publicacion ";
			$this->sql.="where tpub_id = (select tpub_id from tipo_publicacion where tpub_descripcion='especializada') ";
			$this->sql.="and publicacion.pub_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE())";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 31
		//
		function estadistica_31()
		{
			$this->sql="select count(*) as contador from publicacion ";
			$this->sql.="where tpub_id = (select tpub_id from tipo_publicacion where tpub_descripcion='patente') ";
			$this->sql.="and publicacion.pub_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE())";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 36
		//
		function estadistica_36()
		{
			$this->sql="select count(*) as contador from inf_personal where ip_id in  ";
			$this->sql.="( ";
			$this->sql.="select ip_id from ponencia ";
			$this->sql.="where ponencia.pp_año = YEAR(CURDATE()) ";
			$this->sql.="group by ip_id ";
			$this->sql.=")";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 37
		//
		function estadistica_37()
		{
			$this->sql="select count(*) as contador from inf_personal where ip_id in  ";
			$this->sql.="( ";
			$this->sql.="select ip_id from ponencia ";
			$this->sql.="where ponencia.pp_año between (YEAR(CURDATE())-1) AND YEAR(CURDATE()) ";
			$this->sql.="group by ip_id ";
			$this->sql.=")";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 38
		//
		function estadistica_38()
		{
			$this->sql="select count(*) as contador from inf_personal where ip_id in  ";
			$this->sql.="( ";
			$this->sql.="select ip_id from ponencia ";
			$this->sql.="where ponencia.pp_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE()) ";
			$this->sql.="group by ip_id ";
			$this->sql.=")";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 39
		//
		function estadistica_39()
		{
			$this->sql="select count(*) as contador from inf_personal ";
			$this->sql.="where ip_id in  ";
			$this->sql.="( ";
			$this->sql.="select inf_personal.ip_id from inf_personal ";
			$this->sql.="inner join ip_capac ";
			$this->sql.="inner join capacitacion ";
			$this->sql.="where inf_personal.ip_id = ip_capac.ip_id ";
			$this->sql.="and ip_capac.capac_id = capacitacion.capac_id ";
			$this->sql.="and ip_capac.capac_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE()) ";
			$this->sql.="group by inf_personal.ip_id ";
			$this->sql.=")";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 40
		//
		function estadistica_40()
		{
			$this->sql="select count(pc_id) as contador from convenio  ";
			$this->sql.="where pc_id in (select pc_id from ip_convenio) ";
			$this->sql.="and convenio.pc_año between (YEAR(CURDATE())-2) AND YEAR(CURDATE())";
			return parent::ejecutaQUERY();
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//ESTADISTICA PARA ITEM 41
		//
		function estadistica_41()
		{
			$this->sql="select tipo_material.tm_id, tipo_material.tm_descripcion, count(tipo_material.tm_descripcion) as contador  ";
			$this->sql.="from tipo_material  ";
			$this->sql.="inner join material on (material.tm_id = tipo_material.tm_id)  ";
			$this->sql.="group by tm_descripcion";
			return parent::ejecutaQUERY();
		}

}


?>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class Sistema extends General
{
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//FUNCION QUE REALIZA EL LOGIN DEL USUARIO
		function login($usuario, $clave, $learning)
		{
			//Docente
			if($learning == "docente")
				$this->sql="select count(cedula) as cedula from admon where cedula=$cedula AND clave='$clave'";
			
			//Estudiante
			else
				$this->sql="select * from inscrito where cedula=$cedula AND clave='$clave' AND  modulo=$modulo";

			$this->rs = parent::ejecutaQUERY();

			$msg = false;
			if(mysql_fetch_array($this->rs)){
				$msg = true;
			}
			return $msg;
		}
		
		/*=========================================================================
		FUNCION reportar_a_email() PARA REPORTAR EL NUEVO REGISTRO DE DOCENTE EN APADA
		=========================================================================*/
		function reportar_a_email()
		{
			include("../phpMailer_v2.3/class.phpmailer.php");
			include("../phpMailer_v2.3/class.smtp.php");
		 
			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = "tls";
			//$mail->SMTPSecure = true;
			$mail->Host = "smtp.gmail.com";
			//$mail->Port = 465;
			$mail->Port = 587;
			//$mail->Username = "username@gmail.com";
			//$mail->Password = "userpass";
//			$mail->Username = "ingsistemasufps.apada@gmail.com";
			$mail->Username = "ingdesoftwareufps@gmail.com";
			$mail->Password = "Universidad1";
		
			$mail->From = "AGENTE MENSAJERO GIRET-CISCO";
			$mail->FromName = "AGENTE MENSAJERO GIRET-CISCO";
			$mail->Subject = "GIRET-CISCO - NUEVO REGISTRO";
			$mail->AltBody = "HA HABIDO UN NUEVO REGISTRO.. POR FAVOR VERIFICAR EN EL SISTEMA.";
			$mail->MsgHTML("HA HABIDO UN NUEVO REGISTRO.<BR><BR>POR FAVOR VERIFIQUE EN EL SISTEMA.");
			//$mail->AddAttachment("files/borde_i.bmp");
			//$mail->AddAttachment("files/borde_s.bmp");
			//$mail->AddAddress("destino@domain.com", "Destinatario");
			//$mail->AddAddress("cucutasistemas_vyc@hotmail.com", "Auxiliar de Base de Datos");
		//	$mail->AddAddress("cucutasistemas_vyc@hotmail.com", "Auxiliar de Sistemas");
		//	$mail->AddAddress("cucutabasedatos_vyc@hotmail.com", "Auxiliar de Base de Datos");
		//	$mail->AddAddress("cucutasistemas_vyc@hotmail.com", "Auxiliar de Sistemas");	
			$mail->AddAddress("engmiguelgarcia@gmail.com", "Administrador de GIRET-CISCO");
			$mail->IsHTML(true);
		
			if(!$mail->Send()) echo "Error: " . $mail->ErrorInfo;
			else echo "Mensaje enviado correctamente";
		}
		//=========================================================================


		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//LIMPIA EL CACHE ALMACENADO POR EL NAVEGADOR

		//----------------------------------------------------------------------------------------
		//http://blog.deliriumlabs.net/2008/01/28/php-snippet-deshabiliar-el-cache-del-explorador/
		//----------------------------------------------------------------------------------------
		// Esta función permite que las actualizaciones en tiempo real sean tomadas en
		// caliente y no con el caché de internet explorer.
		//-----------------
		function clear_cache(){
			 //PRIMERO MARCAMOS QUE ESTA PAGINA EXPIRO EN UNA FECHA ANTERIROR A HOY 
			 header("Expires: Mon, 23 Jun 1982 10:00:00 GMT");
			 //AHORA LA MARCAMOS CON FECHA DE MODIFICACION IGUAL A HOY
			 header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			 //CREAMOS LAS PROPIEDADES RESTRICTIVAS DEL CACHE
			 header("Cache-Control: no-store, no-cache, must-revalidate");
			 header("Cache-Control: post-check=0, pre-check=0", false);
			 header("Pragma: no-cache");
		} 
		//-----------------

}
?>