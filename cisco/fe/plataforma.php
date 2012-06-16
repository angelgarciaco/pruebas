<style>
	#plataforma{
		width:962px;
		margin:10px auto;
		border:1px solid red;
	}
	#plataforma td{
		font-family:Arial;
		font-size:14px;
		border:1px solid #eeeeee;
		padding:10px;
	}
	#plataforma .titulo{
		font-weight:bold;
		text-align:center;
		background-color:#aaaaaa;
		color:#ffffff;
	}
</style>

<div id="plataforma">
	<form name="form1" method="post" action="control.php">
		<center>
			<table>
				<tr>
					<td colspan="2" class="titulo">FORMULARIO DE INGRESO</td>
				</tr>
				<tr>
					<td>C&eacute;dula</td>
					<td><input type="text" name="cedula" id="cedula"></td>
				</tr>
				<tr>
					<td>Clave</td>
					<td><input type="password" name="clave" id="clave" autocomplete="off"></td>
				</tr>
				<tr>
					<td>Learning</td>
					<td>
						<select name="learning" id="learning">
							<option value="estudiante">Estudiante</option>
							<option value="docente">Docente</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>M&oacute;dulo</td>
					<td>
						<select name="modulo" id="modulo">
							<?php
								for($i=1;$i<=4;$i++)
								{
									echo "<option value='$i'>$i</option>";
								}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center"><input type="submit" value="Enviar"></td>
				</tr>
			</table>
		</center>
	</form>
</div>