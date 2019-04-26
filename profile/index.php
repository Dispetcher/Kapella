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
		$f_name = $row->fname;
		$m_name = $row->mname;
		$l_name = $row->lname;
		$sex = $row->sex;
		$birth_date = date('Y-m-d', strtotime($row->byear.'-'.$row->bmonth.'-'.$row->bdate));
	}

	$row = getRow("SELECT * FROM person2course where id=$id");
	if($row){
		$year_grad = $row->course;
	}

	$rows = mysql_query("SELECT * FROM contacts4person where id=$id");
	$num_rows = mysql_num_rows($rows);
	if($num_rows > 0){
		for ( $i=0; $i < $num_rows; $i++ ) {
	 		$row = mysql_getRow( $rows, $i );
	 		echo $row->type;
			if($row->type == "tel"){
				if(!$phone){
					$phone = $row->value;
				}
			}
		}
	}
	printProfile($f_name, $l_name, $m_name);

}

/*Print the profile for the person */
function printProfile($f_name, $l_name, $m_name){
	$form = "
		<form action='/profile/action.php' method='post'>
	<table class='form'>
		<caption>Анкета</caption>
	<tbody>
	<tr class='row'>
		<td class='cell_name'>Фамилия</td>
		<td class='cell_val'><input name='f_name' type='text' value='$f_name'></td>
	</tr>
	<tr class='row'>
		<td class='cell_name'>Имя</td>
		<td class='cell_val'><input name='l_name' type='text' value='$l_name'></td>
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
			<option value='male'>Мужской</option>
			<option value='female'>Женский</option>
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
		<td class='cell_val'><input name='location' type='text' value='$location'></td>
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
		<td class='cell_val'><input name='www' type='text' value='$www'></td>
	</tr>
	
	<tr class='row'>
		<td class='cell_title' colspan='2'>Персональные данные</td>
	</tr>

	<tr class='row'>
		<td class='cell_name'>Род занятий </td>
		<td class='cell_val'><input name='occupation' type='text' value='$occupation'></td>
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
		<td class='cell_val'><input name='job' type='text' value='$job'></td>
	</tr>
	<tr class='row'>
		<td class='cell_name'>Место работы (WWW) </td>
		<td class='cell_val'><input name='job_www' type='text' value='$job_www'></td>
	</tr>

	<tr class='row'>
		<td class='cell_name'>Достижения </td>
		<td class='cell_val'><textarea name='achivements' type='text' value='$achivements'></textarea></td>
	</tr>

	<tr class='row'>
		<td class='cell_name'>Сможете ли вы принять участие в мероприятиях? </td>
		<td class='cell_val'>
			<select name='events'>
				<option value='yes'>Да</option>
				<option value='no'>Нет</option>
			</select>
		</td>
	</tr>

	<tr class='row'>
		<td class='cell_name'>В качестве гостя </td>
		<td class='cell_val'>
			<select name='events_guest'>
				<option value='yes' selected >Да</option>
				<option value='no'>Нет</option>
			</select>
		</td>
	</tr>

	<tr class='row'>
		<td class='cell_name'>В качестве исполнителя </td>
		<td class='cell_val'>
			<select name='events_participant'>
				<option value='yes'>Да</option>
				<option value='no' selected >Нет</option>
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
