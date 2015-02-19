<div id="tip">
<?
if(isset($_GET['selectMany'])) { $this->control("separator"); $this->control("use"); }
		echo '<table id="drawControls" align="'.$this->controls['align'].'"><tr>';
		if(count($this->controls['schema'])>0)
		{
			foreach($this->controls['schema'] as $id=>$data)
			{
				$item = $data['control'];
				$link = $data['link'];
				$tip = $data['tooltip'];
				if ($item != NULL)
				{
					echo '<td>';
					if ($item=='insert')
					{
						echo '<INPUT TYPE="image" src="../images/control/insert.png" NAME="insert" VALUE="Insertar" title="'.$tip.'" onclick="this.form.action='."'".$link."'".';this.form.submit();" />';
					}
					if ($item=='insertMany')
					{
						echo '<INPUT TYPE="image" src="../images/control/insertMany.png" NAME="insertMany" VALUE="Insertar Muchos" title="'.$tip.'" onclick="PopupCenter('."'".$link."'".', '."'insertMany'".',650, 500);" />';
					}
					if ($item=='delete')
					{
						echo '<INPUT TYPE="image" src="../images/control/delete.png" VALUE="Borrar" title="'.$tip.'" onclick="isMarked(this.form, '."'".$link."'".', '."'delete'".');" />';
					}
					if ($item=='update')
					{
						echo '<INPUT TYPE="image" src="../images/control/update.png" VALUE="Modificar" title="'.$tip.'" onclick="isMarked(this.form, '."'".$link."'".', '."'update'".');" />';
					}
					if ($item=='use')
					{
						echo '<INPUT TYPE="image" src="../images/control/listo.png" VALUE="Usar" title="'.$tip.'" onclick="selectMany(this.form);" />';
					}
					if ($item=='back')
					{
						echo '<INPUT TYPE="image" src="../images/back.png" NAME="back" VALUE="Atras" title="'.$tip.'" onclick="this.form.action='."'".$link."'".';this.form.submit();" />';
					}
					if ($item=='refresh')
					{
						echo '<INPUT TYPE="image" src="../images/control/refresh.png" NAME="refresh" VALUE="recargar" title="'.$tip.'" onclick="window.location.reload();" />';
					}
					if ($item=='selectAll')
					{
						echo '<input type="image" src="../images/control/selectAll.png" name="selectAll" value="Todos!" title="'.$tip.'" onclick="mark(1,this.form); return false;" />';
					}
					if ($item=='selectNone')
					{
						echo '<input type="image" src="../images/control/selectNone.png" name="selectNone" value="Ninguno!" title="'.$tip.'" onclick="mark(0,this.form); return false;" />';
					}
					if ($item=='selectInvert')
					{
						echo '<input type="image" src="../images/control/selectInvert.png" name="selectInvert" value="Invertido" title="'.$tip.'" onclick="mark(-1,this.form); return false;" />';
					}
					if ($item=='separator')
					{
						echo '<img src="../images/separator.png" />';
					}
					if ($item=='confirmado')
					{
						echo '<INPUT TYPE="image" src="../images/iconMenu/ex_confirmado.png" VALUE="confirmado" onclick="isMarked(this.form, '."'".$link."'".', '."'delete'".');" />';
					}
					if ($item=='despachado')
					{
						echo '<INPUT TYPE="image" src="../images/iconMenu/ex_despachado.png" VALUE="despachado" onclick="isMarked(this.form, '."'".$link."'".', '."'delete'".');" />';
					}
					if ($item=='ingresado')
					{
						echo '<INPUT TYPE="image" src="../images/iconMenu/ex_ingresado.png" VALUE="ingresado" onclick="isMarked(this.form, '."'".$link."'".', '."'delete'".');" />';
					}
					if ($item=='en espera')
					{
						echo '<INPUT TYPE="image" src="../images/iconMenu/ex_en_espera.png" VALUE="en espera" onclick="isMarked(this.form, '."'".$link."'".', '."'delete'".');" />';
					}
					if ($item=='pausado')
					{
						echo '<INPUT TYPE="image" src="../images/pausado.png" VALUE="Pausado" onclick="isMarked(this.form, '."'".$link."'".', '."'delete'".');" />';
					}
					if ($item=='finalizado')
					{
						echo '<INPUT TYPE="image" src="../images/finalizado.png" VALUE="Finalizado" onclick="isMarked(this.form, '."'".$link."'".', '."'delete'".');" />';
					}
					if ($item=='descartado')
					{
						echo '<INPUT TYPE="image" src="../images/descartado.png" VALUE="Descartado" onclick="isMarked(this.form, '."'".$link."'".', '."'delete'".');" />';
					}
					if ($item=='exception')
					{
						echo '<INPUT TYPE="image" src="../images/control/exception.png" VALUE="Excepcion" onclick="this.form.action='."'modules/".$link."'".';this.form.submit();" />';
					}
					if ($item=='today')
					{
						echo '<INPUT TYPE="image" src="../images/today.png" VALUE="Hoy" onclick="this.form.action='."'modules/".$link."'".';this.form.submit();" />';
					}
					if ($item=='month')
					{
						echo '<INPUT TYPE="image" src="../images/month.png" VALUE="Mes" onclick="this.form.action='."'modules/".$link."'".';this.form.submit();" />';
					}
					if ($item=='cheque')
					{
						echo '<INPUT TYPE="image" src="../images/cheque.png" VALUE="Cheques" onclick="this.form.action='."'".$link."'".';this.form.submit();" />';
					}
					if ($item=='cash')
					{
						echo '<INPUT TYPE="image" src="../images/cash.png" VALUE="Efectivo" onclick="this.form.action='."'".$link."'".';this.form.submit();" />';
					}
					if ($item=='bono')
					{
						echo '<INPUT TYPE="image" src="../images/control/abono.png" VALUE="Bono" onclick="this.form.action='."'".$link."'".';this.form.submit();" />';
					}
					if ($item=='particular')
					{
						echo '<INPUT TYPE="image" src="../images/control/particular.png" VALUE="Particular" onclick="this.form.action='."'".$link."'".';this.form.submit();" />';
					}
					if ($item=='help')
					{
						echo '<INPUT TYPE="image" src="../images/control/help.png" NAME="help" VALUE="Ayuda" title="'.$tip.'" onclick="PopupCenter('."'".$link."'".', '."'help'".',800, 500);" />';
					}
					if ($item=='dicom')
					{
						echo '<INPUT TYPE="image" src="../images/control/insert.png" VALUE="Dicom" onclick="isMarked(this.form, '."'".$link."'".', '."'delete'".');" />';
					}
					if ($item=='warranty')
					{
						echo '<INPUT TYPE="image" src="../images/control/warranty.png" VALUE="Garantia" onclick="this.form.action='."'".$link."'".';this.form.submit();" />';
					}
					if ($item=='warrantyFree')
					{
						echo '<INPUT TYPE="image" src="../images/control/warrantyFree.png" VALUE="Garantia" title="'.$tip.'" onclick="this.form.action='."'".$link."'".';this.form.submit();" />';
					}
					if ($item=='WarrantyAll')
					{
						echo '<INPUT TYPE="image" src="../images/control/warranty.png" VALUE="Garantia" title="'.$tip.'" onclick="this.form.action='."'".$link."'".';this.form.submit();" />';
					}
					if ($item=='WarrantyExpired')
					{
						echo '<INPUT TYPE="image" src="../images/control/warrantyExpired.png" VALUE="Garantia" title="'.$tip.'" onclick="this.form.action='."'".$link."'".';this.form.submit();" />';
					}
					if ($item=='WarrantyNo')
					{
						echo '<INPUT TYPE="image" src="../images/control/warrantyNo.png" VALUE="Garantia" title="'.$tip.'" onclick="this.form.action='."'".$link."'".';this.form.submit();" />';
					}
					if ($item=='pago')
					{
						echo '<INPUT TYPE="image" src="../images/control/payment.png" VALUE="Pago" onclick="this.form.action='."'".$link."'".';this.form.submit();" />';
					}
					if ($item=='excel')
					{
						echo '<INPUT TYPE="image" src="../images/control/excel.png" VALUE="excel" onclick="PopupCenter('."'".$link."'".', '."'excel'".',800, 500);" />';
					}
					if ($item=='excelGen')
					{
						echo '<INPUT TYPE="image" src="../images/control/excel.png" VALUE="excel" onclick="this.form.action='."'".$link."'".'; genera();" />';
						echo '<INPUT TYPE="hidden" name="tabla" />';
					}
					if ($item=='doctores')
					{
						$drs = new DB();
						echo $drs->fillComboDB($drs->doSql("SELECT * FROM users WHERE role=(SELECT id FROM role WHERE name='Medico informante') AND state='activo'"), "username", "id", "doctores", $drs->actualResults, NULL);
					}
					if ($item=='asignar')
					{
						echo '<INPUT TYPE="image" src="../images/iconMenu/ex_asignado.png" VALUE="Asignar" onclick="isMarked(this.form, '."'$link&dr='+doctores.value".', '."'delete'".');" />';
					}
					if ($item=='reasignar')
					{
						echo '<INPUT TYPE="image" src="../images/iconMenu/ex_asignado.png" VALUE="Reasignar" onclick="isMarked(this.form, '."'$link&dr='+doctores.value".', '."'delete'".');" />';
					}
					if ($item=='validate')
					{
						echo '<INPUT TYPE="image" src="../images/control/validate.png" VALUE="validar" title="'.$tip.'" onclick="this.form.action='."'".$link."'".';this.form.submit();" />';
					}
					if ($item=='unvalidate')
					{
						echo '<INPUT TYPE="image" src="../images/control/unvalidate.png" VALUE="desvalidar" title="'.$tip.'" onclick="this.form.action='."'".$link."'".';this.form.submit();" />';
					}
					echo '</td>';
				}
			}
		}
		if(count($this->controls['schema'])>0)
		{
			echo '</tr><tr>';
			foreach($this->controls['schema'] as $id=>$data)
			{
				$item = $data['control'];
				$link = $data['link'];
				if ($item != NULL)
				{
					echo '<td>';
					if ($item=='insert')
					{
						echo 'Insertar';
					}
					if ($item=='insertMany')
					{
						echo 'Insertar Muchos';
					}
					if ($item=='delete')
					{
						echo 'Eliminar';
					}
					if ($item=='update')
					{
						echo 'Modificar';
					}
					if ($item=='use')
					{
						echo 'Usar';
					}
					if ($item=='back')
					{
						echo 'Atras';
					}
					if ($item=='refresh')
					{
						echo 'Recargar';
					}
					if ($item=='selectAll')
					{
						echo 'Todos!';
					}
					if ($item=='selectNone')
					{
						echo 'Ninguno!';
					}
					if ($item=='selectInvert')
					{
						echo 'Invertido!';
					}
					if ($item=='separator')
					{
						echo '';
					}
					if ($item=='confirmado')
					{
						echo 'Confirmar';
					}
					if ($item=='despachado')
					{
						echo 'Despachar';
					}
					if ($item=='ingresado')
					{
						echo 'Ingresar';
					}
					if ($item=='en espera')
					{
						echo 'En Espera';
					}
					if ($item=='pausado')
					{
						echo 'Pausado';
					}
					if ($item=='finalizado')
					{
						echo 'Finalizado';
					}
					if ($item=='descartado')
					{
						echo 'Descartado';
					}
					if ($item=='exception')
					{
						echo 'Excepcion';
					}
					if ($item=='today')
					{
						echo 'Hoy';
					}
					if ($item=='month')
					{
						echo 'Mes';
					}
					if ($item=='cheque')
					{
						echo 'Cheques';
					}
					if ($item=='cash')
					{
						echo 'Efectivo';
					}
					if ($item=='bono')
					{
						echo 'Bono';
					}
					if ($item=='particular')
					{
						echo 'Particular';
					}
					if ($item=='help')
					{
						echo 'Ayuda';
					}
					if ($item=='warranty')
					{
						echo 'Garantia';
					}
					if ($item=='warrantyFree')
					{
						echo 'Pagadas';
					}
					if ($item=='WarrantyAll')
					{
						echo 'Todas';
					}
					if ($item=='WarrantyExpired')
					{
						echo 'Vencidas';
					}
					if ($item=='WarrantyNo')
					{
						echo 'No Pagadas';
					}
					if ($item=='pago')
					{
						echo 'Pago';
					}
					if ($item=='excel')
					{
						echo 'Excel';
					}
					if ($item=='excelGen')
					{
						echo 'Descargar';
					}
					if ($item=='doctores')
					{
						echo '';
					}
					if ($item=='asignar')
					{
						echo 'Asignar';
					}
					if ($item=='reasignar')
					{
						echo 'Re Asignar';
					}
					if ($item=='validate')
					{
						echo 'Validar';
					}
					if ($item=='dicom')
					{
						echo 'Descargar';
					}
					if ($item=='unvalidate')
					{
						echo 'Desvalidar';
					}
					echo '</td>';
				}
			}		
		}
		echo '</tr></table>';
?>
</div>
