<?php

$id = $_POST["id"];
$f_name = $_POST["f_name"];
$l_name = $_POST["l_name"];
$m_name = $_POST["m_name"];
$sex = $_POST["sex"];
$birth_date = $_POST["birth_date"];
$year_grad = $_POST["year_grad"];
$education_level = $_POST["education_level"];
$education_degree = $_POST["education_degree"];
$education_speciality = $_POST["education_speciality"];
$education_degree_1 = $_POST["education_degree_1"];
$education_speciality_1 = $_POST["education_speciality_1"];
$location = $_POST["location"];
$phone = $_POST["phone"];
$email = $_POST["email"];
$www = $_POST["www"];
$occupation = $_POST["occupation"];
$marital_status = $_POST["marital_status"];
$children = $_POST["children"];
$job = $_POST["job"];
$job_www = $_POST["job_www"];
$achivements = $_POST["achivements"];
$events = $_POST["events"];
$events_guest = $_POST["events_guest"];
$events_participant = $_POST["events_participant"];

include_once "../log/db.php";

$row = getRow("SELECT * FROM profile where id=$id");
if($row){
	$unid = $row->unid;
	$query = "UPDATE profile SET id = '$id', f_name='$f_name', m_name='$m_name', l_name='$l_name', birth_date='$birth_date', sex='$sex', year_grad='$year_grad', education_level='$education_level', education_degree='$education_degree', education_speciality='$education_speciality', education_degree_1='$education_degree_1', education_speciality_1='$education_speciality_1', location='$location', phone='$phone', email='$email', www='$www', occupation='$occupation', marital_status='$marital_status', children='$children', job='$job', job_www='$job_www', achivements='$achivements', events='$events', events_guest='$events_guest', events_participant='$events_participant' WHERE unid='$unid'";
	$res = mysql_query($query);
	$res_out = "Данные обновлены";
}else{
	$query = "INSERT INTO profile (unid,id,f_name,m_name,l_name,birth_date,sex,year_grad,education_level,education_degree,education_speciality, education_degree_1,education_speciality_1,location,phone,email,www,occupation,marital_status,children,job,job_www,achivements,events,events_guest,events_participant) VALUES (NULL,'$id','$f_name','$m_name','$l_name','$birth_date','$sex','$year_grad','$education_level','$education_degree','$education_speciality','$education_degree_1','$education_speciality_1','$location','$phone','$email','$www','$occupation','$marital_status','$children','$job','$job_www','$achivements','$events','$events_guest','$events_participant')";
	$res = mysql_query($query);
	$res_out = "Данные добавлены";
}

if(!$res){
	$res_out = "Ошибка сервера. Повторите попытку";
}
echo $res_out;

?>