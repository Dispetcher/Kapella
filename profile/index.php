<?php
$root=$_SERVER["DOCUMENT_ROOT"];

include_once "$root/log/db.php";
include_once "../header.php";

global $f_name, $m_name, $l_name, $birth_date, $year_grad, $education_level, $education_degree, $education_speciality, $education_degree_1, $education_speciality_1, $location, $phone, $email, $www, $achivements;

$id = $_GET["id"];

getData($id);


/*Check for data presentation in the new table (profile). If not, get previous existing data*/
function getData($id){
	

	/*Get data from old tables*/
	$row = getRow("SELECT * FROM person where id=$id");
	if($row){
		$f_name = trim($row->fname);
		$m_name = trim($row->mname);
		$l_name = trim($row->lname);
		$sex_tab = $row->sex;
		if($sex_tab == "M"){
			$sexM = "selected";
			$sexF = "";
		}else if($sex_tab == "F"){
			$sexM = "";
			$sexF = "selected";
		}
		$birth_date = date('Y-m-d', strtotime($row->byear.'-'.$row->bmonth.'-'.$row->bdate));
	}

	$rows = mysql_query("SELECT * FROM person2course where person=$id");
	$num_rows = mysql_num_rows($rows);
	if($num_rows > 0){
		for ( $i=0; $i < $num_rows; $i++ ) {
	 		$row = mysql_getRow( $rows, $i );
	 		if(!$year_grad){
	 			$year_grad = $row->course;
	 		}else if($year_grad && $row->course < $year_grad){
				$year_grad = $row->course;
			}

		}	
	}

	$rows = mysql_query("SELECT * FROM contacts4person where id=$id");
	$num_rows = mysql_num_rows($rows);
	if($num_rows > 0){
		for ( $i=0; $i < $num_rows; $i++ ) {
	 		$row = mysql_getRow( $rows, $i );
			if($row->type == "tel"){
				if(!$phone){
					$phone = $row->value;
				}
			}else if($row->type == "addr"){
				$location = $row->value;
			}else if($row->type == "e-mail"){
				$email = $row->value;
			}else if($row->type == "vk"){
				$www .= "VK_id-".$row->value."; ";
			}else if($row->type == "lj"){
				$www .= "LJ_id-".$row->value."; ";
			}else if($row->type == "fb"){
				$www .= "Fb_id-".$row->value."; ";
			}else if($row->type == "ok"){
				$www .= "Ok_id-".$row->value."; ";
			}
		}
	}
	printProfile($f_name, $l_name, $m_name, $sexM, $sexF, $birth_date, $year_grad, $education_level, $education_degree, $education_speciality, $education_degree_1, $education_speciality_1, $location, $phone, $email, $www, $achivements);

}

/*Print the profile for the person */
function printProfile($f_name, $l_name, $m_name, $sexM, $sexF, $birth_date, $year_grad, $education_level, $education_degree, $education_speciality, $education_degree_1, $education_speciality_1, $location, $phone, $email, $www, $occupation, $marital_status, $children, $job, $job_www, $achivements, $events_yes, $events_no, $events_guest_yes, $events_guest_no, $events_participant_yes, $events_participant_no){
	$form = "
		<form action='/profile/action.php' method='post'>
	<table class='form'>
		<caption>Анкета</caption>
	<tbody>
	<tr class='row'>
		<td class='cell_name'>Фамилия</td>
		<td class='cell_val'><input name='l_name' type='text' value='$l_name'></td>
	</tr>
	<tr class='row'>
		<td class='cell_name'>Имя</td>
		<td class='cell_val'><input name='f_name' type='text' value='$f_name'></td>
	</tr>
	<tr class='row'>
		<td class='cell_name'>Отчество</td>
		<td class='cell_val'><input name='m_name' type='text' value='$m_name'></td>
	</tr>
	
	<tr class='row'>
		<td class='cell_name'>Дата рождения</td>
		<td class='cell_val'><input name='birth_date' type='date' value='$birth_date'></td>
	</tr>
	
	<tr class='row'>
		<td class='cell_name'>Пол</td>
		<td class='cell_val'>
		<select name='sex'>
			<option value='male' $sexM >Мужской</option>
			<option value='female' $sexF >Женский</option>
		</select>
		</td>
	</tr>
	
	<tr class='row'>
		<td class='cell_title' colspan='2'>Образование</td>
	</tr>

	<tr class='row'>
		<td class='cell_name'>Год выпуска</td>
		<td class='cell_val'><input name='year_grad' type='text' value='$year_grad'></td>
	</tr>	

	<tr class='row'>
		<td class='cell_name'>Образование (тип)</td>
		<td class='cell_val'><input name='education_level' type='text' value='$education_level'></td>
	</tr>
	<tr class='row'>
		<td class='cell_name'>Высшее образование (Уч. заведение) </td>
		<td class='cell_val'><input name='education_degree' type='text' value='$education_degree'></td>
	</tr>
	<tr class='row'>
		<td class='cell_name'>Высшее образование (Специальность) </td>
		<td class='cell_val'><input name='education_speciality' type='text' value='$education_speciality'></td>
	</tr>

	<tr class='row'>
		<td class='add'><input id='add' type='button' value='Добавить высшее образование'></td>
	</tr>

	<tr class='row add_education'>
		<td class='cell_name'>Высшее образование (доп) (Учеб. заведение) </td>
		<td class='cell_val'><input name='education_degree_1' type='text' value='$education_degree_1'></td>
	</tr>
	<tr class='row add_education'>
		<td class='cell_name'>Высшее образование (доп) (Специальность) </td>
		<td class='cell_val'><input name='education_speciality_1' type='text' value='$education_speciality_1'></td>
	</tr>
	
	<tr class='row'>
		<td class='cell_title' colspan='2'>Контактные данные</td>
	</tr>

	<tr class='row'>
		<td class='cell_name'>Местонахождение (страна, город) </td>
		<td class='cell_val'><textarea name='location' type='text'>$location</textarea></td>
	</tr>
	<tr class='row'>
		<td class='cell_name'>Контактный телефон </td>
		<td class='cell_val'><input name='phone' type='text' value='$phone'></td>
	</tr>
	<tr class='row'>
		<td class='cell_name'>Email </td>
		<td class='cell_val'><input name='email' type='email' value='$email'></td>
	</tr>
	<tr class='row'>
		<td class='cell_name'>WWW / Соц.сети </td>
		<td class='cell_val'><textarea name='www' type='text'>$www</textarea></td>
	</tr>
	
	<tr class='row'>
		<td class='cell_title' colspan='2'>Персональные данные</td>
	</tr>

	<tr class='row'>
		<td class='cell_name'>Род занятий </td>
		<td class='cell_val'><textarea name='occupation' type='text'>$occupation</textarea></td>
	</tr>
	<tr class='row'>
		<td class='cell_name'>Семейное положение </td>
		<td class='cell_val'><input name='marital_status' type='text' value='$marital_status'></td>
	</tr>
	<tr class='row'>
		<td class='cell_name'>Дети </td>
		<td class='cell_val'><input name='children' type='text' value='$children'></td>
	</tr>

	<tr class='row'>
		<td class='cell_name'>Место работы (Наименование) </td>
		<td class='cell_val'><textarea name='job' type='text'>$job</textarea></td>
	</tr>
	<tr class='row'>
		<td class='cell_name'>Место работы (WWW) </td>
		<td class='cell_val'><textarea name='job_www' type='text'>$job_www</textarea></td>
	</tr>

	<tr class='row'>
		<td class='cell_name'>Достижения </td>
		<td class='cell_val'><textarea name='achivements' type='text'>$achivements</textarea></td>
	</tr>

	<tr class='row'>
		<td class='cell_name'>Сможете ли вы принять участие в мероприятиях? </td>
		<td class='cell_val'>
			<select name='events'>
				<option value='yes' $events_yes>Да</option>
				<option value='no' $events_no>Нет</option>
			</select>
		</td>
	</tr>

	<tr class='row'>
		<td class='cell_name'>В качестве гостя </td>
		<td class='cell_val'>
			<select name='events_guest'>
				<option value='yes' $events_guest_yes>Да</option>
				<option value='no' $events_guest_no>Нет</option>
			</select>
		</td>
	</tr>

	<tr class='row'>
		<td class='cell_name'>В качестве исполнителя </td>
		<td class='cell_val'>
			<select name='events_participant'>
				<option value='yes' $events_participant_yes>Да</option>
				<option value='no' $events_participant_no>Нет</option>
			</select>
		</td>
	</tr>

</table>
<div class='profile_submit'><input type='submit'></div>

<?php
		</form>
	";

	echo $form;

}

echo "<script src='/profile/profile.js'></script>";

include_once "../footer.php";

?>
