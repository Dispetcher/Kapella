<?php
include_once "$root/log/db.php";
include_once "$root/util/util.php";
include_once "$root/misc/db.php";
$rim = array( "I", "II", "III", "IV" );
$course_amount = 2; // количество курсов (не классов) в Училище
$alp = "АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЫЭЮЯ";
$restriction = true;
$now_year = date("Y");
$now_month = date("m");
if ( $now_month > 6 ) $now_year++;
$deadline = $now_year - 1;


/*
Returns relatives list for given person
$type: 1: find teachers; 0: find students.
*/
function getRelatives4Person( $personId, $type ) {
global $user_rights;
	$classMap = array(
		"c"   => "дирижирования",
		"cmp" => "композиции",
		"fl"  => "флейты",
		"ob"  => "гобоя",
		"cl"  => "кларнета",
		"sax" => "саксофона",
		"fg"  => "фагота",
		"tr"  => "трубы",
		"pe"  => "ударных",
		"ar"  => "арфы",
		"p"   => "фортепиано",
		"or"  => "органа",
		"sn"  => "вокала",
		"vl"  => "скрипки",
		"vc"  => "виолончели",
		"cb"  => "контрабаса"
	);
  $class_people = array();

  $cond  = sprintf($type==1 ? "id2=id AND id1=%u" : "id1=id AND id2=%u", $personId);
  $order = ($type==1 ? "start" : "lname, fname, mname");
  $query = sprintf( "SELECT id, lname, fname, mname, type FROM person, person2person WHERE $cond AND %u >= rights order by $order;", $user_rights );
  $rows = mysql_squery( "getRelatives4Person", $query );

  $num = mysql_num_rows($rows);
  for ( $i=0; $i<$num; $i++ ) {
	  $row = mysql_getRow( $rows, $i );
	  $name = getIOFlinkRow( $row );
	  if ( $classType = $classMap[ $row->type ] ) {
		if ( $class_people[ $row->type ] )
			$class_people[ $row->type ] .= ", $name";
		else
			$class_people[ $row->type ] = " по классу ".$classType." — $name";
	  }
  }

  $result = "";
  foreach( $class_people as $people ) {
		if ($people) $result = appendStr( $result, ", ", $people );
  }

  if ($result) $result = ($type==1 ? ($num>1? "Педагоги:" : "Педагог") : ($num>1? "Ученики" : "Ученик")).$result.".";
  return $result;
}


/*
Returns teachers list for given person
*/
function getTeachers4Person( $personId ) {
  return getRelatives4Person( $personId, 1 );
}


/**
 * Returns list of graduates of the specified teacher in the specified class, grouped by graduation year.
 */
function getGraduates4PersonByYear( $teacherId, $class_abbr, $lastYear = "" ) {
global $user_rights;
  if ( $lastYear ) $lastYear = sprintf(" AND end <= %u", $lastYear);
  $query = sprintf(
  	 "SELECT person.id, lname, fname, mname, end FROM person, person2person, person2course "
	."WHERE person2course.person=person.id AND id1=person.id AND id2=%u AND %u >= rights AND (person2person.type='%s') AND (person2course.type!='t') AND (person2course.type!='s'$lastYear) "
	."ORDER BY end, lname, fname, mname;", $teacherId, $user_rights, $class_abbr );
  $rows = mysql_squery( "getGraduates4PersonByYear", $query );

  $result = "";
  $course = "";
  $num = mysql_num_rows($rows);
  for ( $i=0; $i<$num; $i++ ) {
	  $row = mysql_getRow( $rows, $i );
	  $name = getIOFlinkRow( $row );
	  if ( $course != $row->end ) {
	  	$course = $row->end;
		$result .= "<div class=course>$course</div>";
	  }
	  $result .= "<div>$name</div>";
  }
  return "<div class=nameList>$result</div>";
}


function getStudents4Person( $personId ) {
  return getRelatives4Person( $personId, 0 );
}


function isNowStudied( $year ) {
  global $now_year, $now_month;
  return $year >= $now_year;
}


// Returns activities of the given person
function getActivities( $personId ) {
  global $root;
  $str = "";
  $rows = mysql_squery("getActivities()",sprintf("SELECT * FROM activity WHERE person_id = %u;",$personId));
  $num = mysql_num_rows($rows);
  for ( $i=0; $i<$num; $i++ ) {
	  $row = mysql_getRow( $rows, $i );
	  if ($row->type == 'l') $str .= file_get_contents( "$root$row->description" );
	  else {
		  $str .= "<p>";
		  $start = makeDate( $row->sdate, $row->smonth, $row->syear );
		  $end  = makeDate( $row->edate, $row->emonth, $row->eyear );
		  if (($start!="") || ($end!="")) {
			 if (($start!="") && ($end!="")) {
				if ( strlen( $start ) + strlen( $end ) > 8 )
					$str .= "$start – $end, ";
				else
					$str .= $start."–".$end.", ";
			 } else if ($start!="") $str .= "$start, ";
			 else if ($end!="") $str .= "$end, ";
		  }
		  $str .= "$row->description";
		  if ($i<$num-1) $str .= ";</p>";
		  else $str .= ".</p>";
	  }
  }
  return $str;
}


function congBorn( $sex ) {
	return ($sex == "F") ? "Родилась " : "Родился ";
}

function congDead( $sex ) {
	return ($sex == "F") ? "Умерла " : "Умер ";
}

function congStudied( $sex, $now ) {
	return $now ? "Учится " :
		(($sex == "F") ? "Училась " : "Учился ");
}

function congSchoolPP( $year ) {
	return ($year < 1945 ) ? "в Капелле " : "в Хоровом училище ";
}

function congGraduated( $sex ) {
	return ($sex == "F") ? "Окончила " : "Окончил ";
}

function congSchoolVP( $year, $org ) {
    if ($org == "i") return "Инструментальные классы Капеллы ";
    if ($org == "r") return "Регентские классы Капеллы ";
    if ($org == "t") return "Дирижёрско-хоровой техникум при Капелле ";
    if ($org == "d") return "Десятилетку при Консерватории ";
    if ($org == "m") return "Московское хоровое училище ";
    if ($year > 1945) return "Хоровое училище им. Глинки ";
    return "Капеллу ";
}

function congClasses( $amount ) {
	return ($amount > 1 ) ? "в классах " : "в классе ";
}


/**
  Returns true if person is still alive
 */
function isAlive( $personId ) {
	$row = getRow( sprintf("SELECT ddate, dmonth, dyear FROM person WHERE id = %u;",$personId) );
    return !($row->ddate || $row->dmonth || $row->dyear);
}


/**
* Returns map with all the study courses info
*/
function getAcademInfo( $personId ) {
	$classes  = 0;
	$tclasses = 0;
	$displayCourse = "";
	$studied   = "";
	$graduated = "";
	$taught    = "";
	$now = false;

	$rows = mysql_squery("getAcademInfo()", sprintf("SELECT * FROM person2course WHERE person=%u;", $personId));
	$num = mysql_num_rows($rows);
	for ( $i=0; $i<$num; $i++ ) {
	  $row = mysql_getRow( $rows, $i );
	  $type = $row->type;
	  $org = $row->org;
	  $year = $row->course;
	  $end   = $row->end;
	  $left_class = $end && ( $end <= $year );
      $now = $now || ( isNowStudied( $year ) && !$left_class  );
	  $cur = " <a href='/course$year' class='diplom'>$year</a>";
      $start = $row->start;
	  if ($left_class && ($end > 1400))
			if ($start) $detail = $start."–".$end;
			else $detail = "до $end";
      else if ($start)
	  	$detail = "с $start";
	  else $detail = false;

	  if ($detail)
		  $cur .= " <span class='course_detail'>($detail)</span>";

	  switch ($type) {
		case "t":	// классный руководитель
			$taught = appendStr( $taught, ",", $cur );
			$tclasses++;
			break;
	  	case "s":	// учился, но не окончил
			$studied = appendStr( $studied, ",", $cur );
			$syear = $year;
			$sdetail = $detail;
			$classes++;
			$displayCourse = getDisplayCourse($year);
			break;
		default:	// класс диплома
			$displayCourse = getDisplayCourse($year);
			$graduated = $cur;
			$academ["honour"] = ($type == "r");
			$academ["moscow"] = ($org == "m");
			break;
	  }
	  if ($org) $academ["org"] = $org;
	}

	$academ["now"]       = $now;       // is currently studying
	$academ["year"]      = $year;      // class graduation year
	$academ["syear"]     = $syear;     // non-graduation class year
	$academ["studied"]   = $studied;   // comma separated study classes
	$academ["sdetail"]   = $sdetail;   // study details (starting and ending years)
	$academ["graduated"] = $graduated; // graduation year
	$academ["taught"]    = $taught;    // comma separated teaching classes
	$academ["displayCourse"] = $displayCourse; // main course (if only one should be displayed)
	$academ["classes"]   = $classes;   // number of study classes
	$academ["tclasses"]  = $tclasses;  // number of teaching classes
	return $academ;
}


// Prints all the information related to the given person
function printPersonalInfo( $personId ) {
	global $user_rights, $title, $now_year;
  	echo "<div class='personalia'>\n";
  	$row = getRow(sprintf("SELECT * FROM person WHERE id = %u AND %u >= rights;", $personId, $user_rights))
    	or stop("Invalid query: " . mysql_error());
	if ( !$row ) return;
	$personId = $row->id;
	$sex = $row->sex;
	$fname = trim( $row->fname );
	$lname = $row->lname;
	if ( $pname = $row->pname ) echo "<p>(Прежняя фамилия — $pname)</p>\n";
	$born = makeDatePP( $row->bdate, $row->bmonth, $row->byear );
	$dead = makeDatePP( $row->ddate, $row->dmonth, $row->dyear );
	if ($born!="") echo "<p>".congBorn( $sex )."$born.</p>\n";

	$academ = getAcademInfo( $personId );
	$classes   = $academ["classes"];
	$tclasses  = $academ["tclasses"];
	$studied   = $academ["studied"];
	$sdetail   = $academ["sdetail"];
	$graduated = $academ["graduated"];
	$taught    = $academ["taught"];
	$now       = $academ["now"];
	$displayCourse = $academ["displayCourse"];
	$year      = $academ["year"];
	$syear     = $academ["syear"];

	if ($studied) {
		if ( $classes == 1 && $sdetail)
			echo "<p>".congStudied( $sex, $now  ).congSchoolPP( $year ).congClasses( $classes )." <a href='/course$syear' class='diplom'>$syear</a> г. выпуска <span class='course_detail'>($sdetail)</span>.</p>\n";
		else
			echo "<p>".congStudied( $sex, $now  ).congSchoolPP( $year ).congClasses( $classes ).$studied." г. выпуска.</p>\n";
	}
	if ($graduated) echo "<p>".congGraduated( $sex ).(!$studied ? congSchoolVP( $year, $academ["org"] ) : "").($academ["honour"] ? "с отличием " : "" )."в$graduated.</p>\n";
	if ($teachers = getTeachers4Person( $personId )) echo "<p>$teachers</p>\n";
	if ($act = getActivities( $personId )) echo "$act\n";
	if ($taught) echo "<p>Классный руководитель ".congSchoolPP( $year ).congClasses( $tclasses ).$taught." г. выпуска.</p>\n";
	if ($students = getStudents4Person( $personId )) echo "<p>$students</p>\n";
	if ($dead!="") echo "<p>".congDead( $sex )."$dead.</p>\n";

	$materials = getMaterials4Person( $personId );
	if ($materials) echo "<p>$materials.</p>\n";
	if ($biblio = getSources( "person", $personId, true, true )) echo "<p><i>Ссылки:</i></p><ul>$biblio</ul>\n";
  	if ($fotos = getThumbnails4Person( $personId )) echo "<p><i>Фото:</i></p></div>\n$fotos";
	else print "</div>\n";

/*	      if ($name == "RELATIVE") {
            if ($isLocal) {
    	        $rc = array(
    	          "son" => "сын",
    	          "daughter" => "дочь",
    	          "sister" => "сестра",
    	          "brother" => "брат",
	              "father" => "отец",
	              "mother" => "мать",
    	          "class-mate" => "однокл.",
    	          "son-in-law" => "зять",
    	          "father-in-law" => "тесть"
    	        );
                $class = "$attrs[CLASS]";
    	        print ", $rc[$class]: ";
    	        if ($refId = "$attrs[ID]")
    			  print "<a href='/id$refId'>$refId</a>";
    			printAbbrNames($attrs);
    		}
*/

	printContactInfo( $personId );

/*============= Fill profile ==================*/
	if($user_rights <= USER_MODERATOR) {
		$button_val = "<button class='btn_profile'><a href='/profile/?id=$personId'>Заполнить анкету</a></button>";			
		echo $button_val;
	}
/*============ (End) Fill Profile =============*/
	if ($user_rights == USER_ROOT) {
		$link_value = "<a class=\"name\" ".
			"href=\"/id$personId\">$fname $lname</a> $displayCourse";
?>
<script src="/util/clipboard.js"></script>
	<a onclick="return cc(this);"><img src=/img/copy.gif width=14 height=13 border=0></a>
	<input type='text' style='display:none;' value='<?php echo $link_value; ?>' />
	Копировать текст ссылки
<?php
	}
}


function formatContact( $type, $value, $status=0 ) {
	global $user_rights;
	$class = ($status == 0) ? "" : "deleted";
	switch ( $type ) {
		case "e-mail": return formatEmail( $value, $class );
		case "lj"    : return formatLjUser( $value, $class );
		case "icq"   : return "<a class='$class' href=\"//www.icq.com/people/full_details_show.php?uin=$value\">$value</a>";
		case "vk"    : return formatKontUser( $value, $class );
		case "fb"    : return formatFBUser( $value, $class );
		case "ok"    : return "<a class='$class' href=\"//www.odnoklassniki.ru/dk?st.cmd=friendMain&st.friendId=$value\">$value</a>";
		case "ig"    : return formatIGUser( $value, $class );
		case "yt"    : return "<a class='$class' href=\"//www.youtube.com/user/".$value."\">$value</a>";
	}
	return $value;
}


function getUserName4Person( $personId ) {
	$row = getRow( sprintf("SELECT name FROM user WHERE person_id=%u;", $personId));
	if ($row > 0) return $row->name;
}


// Prints a contact info for the requested person
function printContactInfo( $personId ) {
	$result = getContacts($personId);
	if ($result) print "<div class='contacts'><div class='header'>Контактная информация:</div>\n".$result."</div>";
}


/**
 * @param personId
 */

function getContacts($personId) {
global $user_rights;
    $contact_types = array( "tel" => "Телефон", "e-mail" => "E-mail", "addr" => "Адрес", "lj" => "ЖЖ", "vk" => "В контакте", "ok" => "Одноклассники", "fb" => "Facebook", "ig" => "Instagram", "yt" => "Youtube" );
    $rows = mysql_squery("printContactInfo()", sprintf("SELECT * FROM contacts4person WHERE id = %u ORDER BY type;", $personId));
    $num = mysql_num_rows($rows);
    $result = "";
    $all   = false;
    $registered = false;
    $admin = false;
    $root  = false;
    for ( $i=0; $i<$num; $i++ ) {
    	$row = mysql_getRow( $rows, $i );
    	if ( !$row->status || $user_rights >= USER_ADMIN ) {
        	if ($user_rights >= $row->rights) {
        		$all  = true;
        		$type = $row->type;
        		$type_text = $contact_types[ $type ];
        		if (!$type_text) $type_text = $type;
        		$result .= "<div class='item'><span class='type'>$type_text:</span>";
        		$value = $row->value;
        		$result .= "<span class='value'>".formatContact( $type, $value, $row->status )."</span></div>\n";
        	} else {
        		switch ( $row->rights ) {
        			case ( USER_REGISTERED ): $registered = true; break;
        			case ( USER_ADMIN      ): $admin      = true; break;
        			case ( USER_ROOT       ): $root       = true; break;
        		}
        	}
    	}
    }
    if ($registered || $admin /*|| $root*/ ) {
    	$no_access = ( $all ? "Часть информации" : "Информация" ). " этого раздела доступна только для ".
    		( $registered ? "<a href='/login/' onClick=\"userform.action=this.href; userform.submit(); return false;\">зарегистрированных</a> пользователей." :
    		( $admin ? "<a href='mailto:info@kapellanin.ru'>администрации</a> сайта." : "<a href='mailto:info@kapellanin.ru'>создателя</a> сайта." ));
    	$no_access = "<div class='item'>$no_access</div>";
    } else $no_access = "";
    
    if ($user_rights >= USER_ADMIN)
       if ($user_name = getUserName4Person( $personId )) {
     		$result .= "<div class='item'><span class='type'>Капелланин:</span>"
    			."<span class='value'>".'<a style="FONT-WEIGHT: 800" href="/id'.$personId.'"><img height="17" border="0" src="/img/bird.gif" align="absmiddle" width="17">'.$user_name.'</a>'."</span></div>\n";
    }
    return $result;
}


/**
 * @param personId
 */

function getContactsLight($personId) {
    $rows = mysql_squery("printContactInfo()", sprintf("SELECT * FROM contacts4person WHERE id = %u ORDER BY type;", $personId));
    $num = mysql_num_rows($rows);
    $result = "";
    for ( $i=0; $i<$num; $i++ ) {
        $row = mysql_getRow( $rows, $i );
        if ($row->status == -1) continue;
        $type = $row->type;
        $value = $row->value;
        if ($type =="lj") $value .= ".livejournal.com";
        else if ($type == "vk") $value = "vk.com/id".$value;
        else if ($type == "fb")   $value = "facebook.com/".$value;
        else if ($type == "ig")   $value = "instagram.com/".$value;
        $result = appendStr($result, ", ", $value);
    }
    return $result;
}

// Prints a list of names sorted by given field
function printNames( $sort, $charIndex ) {
//  print strlen( $char )." ".ord( substr( $char, 0, 1 ) )." ".$char."<br/>\n";
  global $alp, $user_rights;
  $char = substr( $alp, $charIndex-1, 2 );
  print "<div class='personalia'>\n";
  $condition = sprintf(" WHERE %u >= rights", $user_rights);
  if ( $char ) $condition .= " AND lname LIKE '".$char."%'";
  if ( $sort ) $condition .= sprintf(" ORDER BY %s",$sort);
//  print $condition."<br>";
  $rows = mysql_squery("printNames()", "SELECT * FROM person$condition;");
  $num = mysql_num_rows($rows);
  for ( $i=0; $i<$num; $i++ ) {
	  $row = mysql_getRow( $rows, $i );
	  $personId = $row->id;
	  if ( $pname = $row->pname ) $pname = " ($pname)";
	  $core = "<a href='/id$personId' class='name'>$row->lname$pname $row->fname $row->mname</a>";
	  if ($href= $row->uri) $core = "<a href='$href'>$core</a>";
      $core .= makeLivingDates( $row );
	  if ($course = getCourse4person( $personId, true, true )) $core .= $course;
//	  $user_name = $row->user ? getUserName4Person( $row->id ) : "";
//	  $usr_img = "<img src='". ( $user_name ? "/img/bird.gif" : "/img/menu/0.gif" ) . "' width=17 height=17 align=absmiddle title='$user_name'>";
	  print "<p id='$personId'>$core</p>\n";
  }
  print "</div>\n";
}


function makeLivingDates( $row ) {
  $born = makeDate( $row->bdate, $row->bmonth, $row->byear );
  $dead = makeDate( $row->ddate, $row->dmonth, $row->dyear );
  if (($born!="") || ($dead!="")) {
	 if (($born!="") && ($dead!="")) {
		if ( strlen( $born ) + strlen( $dead ) > 8 )
			$res .= " (".$born." – "."$dead)";
		else
			$res .= " (".$born."–"."$dead)";
	 }
	 else if ($born!="") $res .= " (р. $born)";
	 else if ($dead!="") $res .= " (ум. $dead)";
  }
  return $res;
}


function getCourse4Person( $personId, $needHtml, $needTeacher ) {
  $img = array( "r" => "red", "b" => "blue", "s" => "green", "t" => "teach", "m" => "moscow" );
  $cond = sprintf("person=%u", $personId);
  if (!$needTeacher) $cond .= " AND type <> 't'";
  $rows = mysql_squery("getCourse4Person", "SELECT * FROM person2course WHERE $cond;");
  $num = mysql_num_rows($rows);
  for ( $i=0; $i<$num; $i++ ) {
	  $row = mysql_getRow( $rows, $i );
	  $type = $row->type;
	  $year = $row->course;
	  $cur = $needHtml? " <a href='/course$year' class='diplom'><img src='/img/$img[$type].gif' border='0' align='absmiddle' /> $year</a>" : " $year";
	  switch ($type) {
		case "t":	// классный руководитель
		  	$teach = appendStr( $teach, ",", $cur );
			break;
	  	case "s":	// учился, но не окончил
		  	$study = appendStr( $study, ",", $cur );
			break;
		default:
		  	$lastCourse = $cur; // класс диплома -- всегда последний
			break;
	  }
  }
  $result = $study;
  if ($lastCourse) $result = appendStr( $result, ",", $lastCourse );
  if ($teach)      $result = appendStr( $result, ",", $teach );
  return $result;
}


function printCoursesList() {
  global $deadline;
  $noabuse = " WHERE end <= $deadline";
  startBox( "Воспитанники прошлых лет" );
  print "<table width=100%><tr valign=top>\n";
  $rows = mysql_squery("printCoursesList()", "SELECT * FROM course$noabuse ORDER BY end;");
  $num = mysql_num_rows($rows);
  $percol = round( ceil( $num / 7 ) );
  $unclosed = false;
  for ( $i=0; $i<$num; $i++ ) {
	  if ($i % $percol == 0) {
	  	print "<td align=center>\n";
	  	$unclosed = true;
	  }
	  $row = mysql_getRow( $rows, $i );
	  print "Выпуск <a href='/course$row->end'>$row->end</a><br />\n";
	  if ($i % $percol == $percol-1) {
	  	print "</td>\n";
		$unclosed = false;
	  }
  }
  if ($unclosed) print "</td>\n";
  print "</tr></table>\n";
  finishBox();
}


function printClassesList() {
  global $now_year;
  $year_1cl = $now_year + 10;
  $noabuse = " WHERE end >= $now_year AND end <= $year_1cl";
  startBox( "Нынешние учащиеся" );
  print "<table width=100%><tr valign=top>\n";
  $rows = mysql_squery("printClassesList()", "SELECT * FROM course$noabuse ORDER BY end DESC;");
  $num = mysql_num_rows($rows);
  $percol = round( ceil( $num / 4 ) );
  $unclosed = false;
  for ( $i=0; $i<$num; $i++ ) {
	  if ($i % $percol == 0) {
	  	print "<td align=left>\n";
	  	$unclosed = true;
	  }
	  $row = mysql_getRow( $rows, $i );
      $course = $row->end;
	$now_course = getNowCourse( $course, $now_year );
	  print "<a href='/course$course'>$now_course</a> (выпуск $course)<br />\n";
	  if ($i % $percol == $percol-1) {
	  	print "</td>\n";
		$unclosed = false;
	  }
  }
  if ($unclosed) print "</td>\n";
  print "</tr></table>\n";
  finishBox();
}

function getNowCourse($course, $now_year, $parallel="") {
global $course_amount, $rim;
$cl = $now_year + 11 - $course;
if ($cl > (11 - $course_amount)) return ($rim[$course_amount - 1 - $course + $now_year]." $parallel курс");
return  // ($cl > 3) ? ($cl + 1)." класс" : // действовало в первое полугодие 2014-15 уч. года
		"$cl $parallel класс";
}


function printConductors() {
printTeachers( "Педагоги по дирижированию", 'c' );
}

function printPianists() {
printTeachers( "Педагоги по фортепиано", 'p' );
}


function printTeachers( $caption, $type ) {
  global $user_rights;
	$rows = mysql_squery("printTeachers()", sprintf("SELECT person.id, lname, fname, mname, person.user FROM person, person2person WHERE person.id = person2person.id2 AND person2person.type='%s' AND %u >= rights GROUP BY person.id ORDER BY lname, fname, mname;", $type, $user_rights ) );
	$num = mysql_num_rows($rows);
	if (!$num) return;
    startBox( $caption );
	print "<table width=100%><tr valign=top>\n";
	$maxcol = min( $num, 4 );
	$percol = floor( $num / $maxcol );
	$unclosed = false;
	$col=0;
	for ( $i=0; $i<$num; $i++ ) {
		if (!$unclosed) {
			$align = ($col==0) ? "left" : (($col == $maxcol-1) ? "right" : "center" );
			print "<td align='$align'><table cellspacing=0 cellpad
ding=0>\n";
			$unclosed = true;
			$col++;
			$thiscol = 1;
			$perthiscol = $percol + (($col <= ($num % $maxcol)) ? 1 : 0 );
	    }
		$row = mysql_getRow( $rows, $i );
//		$user_name = $row->user ? getUserName4Person( $row->id ) : "";
//		$usr_img = "<img src='". ( $user_name ? "/img/bird.gif" : "/img/menu/0.gif" ) . "' width=17 height=17 align=absmiddle title='$user_name'>";
		print "<tr valign=top><td>".getFIOlinkRowStyled($row, "name")."</td></tr>\n";
		if ($thiscol >= $perthiscol) {
			print "</table></td>\n";
			$unclosed = false;
		} else $thiscol++;
	}
	if ($unclosed) print "</table></td>\n";
	print "</tr></table>\n";
	finishBox();
}

function getDisplayCourse( $course ) {
  global $now_year;
  if ( isNowStudied( $course ) ) return "(".getNowCourse( $course, $now_year ).")";
  return "(вып. $course)";
}

function printCourseList( $course, $parallel="" ) {
global $now_year, $user_rights;
    $img = array( "r" => "red", "b" => "blue", "m" => "moscow", "s" => "menu/0" );
    if ( $is_NowStudied = isNowStudied( $course ) ) {
        $now_course = getNowCourse( $course, $now_year, $parallel );
        startBox( "$now_course (выпуск <a href='/course$course'>$course</a> года)" );
    } else
        startBox( "Выпуск <a href='/course$course'>$course</a> года".($parallel ? ". Класс $parallel" : "") );
    $parallelCondition = $parallel ? sprintf(" AND parallel = '%s'", $parallel) : "";
    $rows = mysql_squery("printCourseList()", sprintf("SELECT person.id, lname, fname, mname, type, end AS end_year, user, parallel FROM person, person2course WHERE person.id = person2course.person AND person2course.course = %u AND %u >= rights  AND type <> 't'%s ORDER BY lname, fname, mname;",$course,$user_rights, $parallelCondition));
    $num = mysql_num_rows($rows);
    if ( $num ) {
        print "<table width=100%><tr valign=top>\n";
        $maxcol = min( $num, 4 );
        $percol = floor( $num / $maxcol );
        $unclosed = false;
        $col=0;
        for ( $i=0; $i<$num; $i++ ) {
            if (!$unclosed) {
                $align = ($col==0) ? "left" : (($col == $maxcol-1) ? "right" : "center" );
                print "<td align='$align'><table>\n";
                $unclosed = true;
                $col++;
                $thiscol = 1;
                $perthiscol = $percol + (($col <= ($num % $maxcol)) ? 1 : 0 );
            }
            $row = mysql_getRow( $rows, $i );
            $type = $row->type;
            $end_year = $row->end_year;
    //      $user_name = $row->user ? getUserName4Person( $row->id ) : "";
    //      $usr_img = "<img src='". ( $user_name ? "/img/bird.gif" : "/img/menu/0.gif" ) . "' width=17 height=17 align=absmiddle title='$user_name'>";
            $style_name = ((!$is_NowStudied && ($type == "s")) || ($is_NowStudied && ($end_year < $course) && ($end_year > 0))) ? "name_ex" : "name";
            print "<tr><td>".getFIOlinkRowStyled($row, $style_name)."</td><td><img src='/img/$img[$type].gif' border='0' align='absmiddle' />";
            if ($row->parallel) print " <span class=parallel>".$row->parallel."</span>";
            print "</td></tr>\n";
            
            if ($thiscol >= $perthiscol) {
                print "</table></td>\n";
                $unclosed = false;
            } else $thiscol++;
        }
        if ($unclosed) print "</table></td>\n";
        print "</tr></table>\n";
        $teachers = getTeachers4Course( $course, $parallelCondition );
        if ( $teachers ) {
        if ( strpos( $teachers, "," ) === false ) print "Классный руководитель: ".$teachers;
        else print "Классные руководители: ".$teachers;
        }
    } else print "К сожалению, сведения об этом курсе отсутствуют.";
    if ( $album = getFotos4Course( $course ) ) finishBoxFooter( $album );
    else finishBox();
}


function printGeneration( $birthYear ) {
  global $user_rights;
  startBox( "Рождённые в $birthYear году" );
  $rows = mysql_squery("printGeneration()", sprintf("SELECT * FROM person WHERE person.byear = %u ORDER BY lname, fname, mname;", $birthYear));
  $num = mysql_num_rows($rows);
  if ( $num ) {
	  print "<table width=100%><tr valign=top>\n";
	  $maxcol = min( $num, 4 );
	  $percol = floor( $num / $maxcol );
	  $unclosed = false;
	  $col=0;
	  for ( $i=0; $i<$num; $i++ ) {
		  if (!$unclosed) {
			$align = ($col==0) ? "left" : (($col == $maxcol-1) ? "right" : "center" );
			print "<td align='$align'><table>\n";
			$unclosed = true;
			$col++;
			$thiscol = 1;
			$perthiscol = $percol + (($col <= ($num % $maxcol)) ? 1 : 0 );
		  }
		  $row = mysql_getRow( $rows, $i );
		  $course = getCourse4person( $personId, true, true );
		  print "<tr><td>".getFIOlinkRowStyled($row, "name")."</td><td>$course</td></tr>\n";
		  if ($thiscol >= $perthiscol) {
			print "</table></td>\n";
			$unclosed = false;
		  } else $thiscol++;
	  }
	  if ($unclosed) print "</table></td>\n";
	  print "</tr></table>\n";
  }
  finishBox();
}


function getTeachers4Course( $course, $parallelCondition="" ) {
global $user_rights;
	$str = "";
	$rows = mysql_squery("getTeachers4Course()",sprintf("SELECT person.id, lname, fname, mname, parallel FROM person, person2course WHERE person.id = person2course.person AND person2course.course = %u AND type = 't' AND %u >= rights%s  ORDER BY person2course.start;",$course,$user_rights,$parallelCondition));
	$num = mysql_num_rows($rows);
	for ( $i=0; $i<$num; $i++ ) {
		$row = mysql_getRow( $rows, $i );
		$str = appendStr( $str, ", ", getFIOlinkRowStyled($row, "name") );
		if ( $row->parallel ) $str .= " <span class=parallel>($row->parallel)</span>";
	}
	return $str;
}


function printCoursesListFull() {
  global $user_rights, $deadline;
  $noabuse = ($user_rights < USER_REGISTERED) ? " WHERE end <= $deadline" : "";
  $rows = mysql_squery("printCoursesListFull()", "SELECT * FROM course$noabuse ORDER BY end DESC;");
  $num = mysql_num_rows($rows);
  for ( $i=0; $i<$num; $i++ ) {
	  $row = mysql_getRow( $rows, $i );
	  printCourseList( $row->end );
  }
}

function printFullListByBirthYear() {
  global $user_rights, $deadline;
  $noabuse = ($user_rights < USER_REGISTERED) ? " WHERE end <= $deadline" : "";
  $rows = mysql_squery("printCoursesListFull()", "SELECT byear FROM person WHERE byear > 0 GROUP BY byear;");
  $num = mysql_num_rows($rows);
  for ( $i=0; $i<$num; $i++ ) {
	  $row = mysql_getRow( $rows, $i );
	  printGeneration( $row->byear );
  }
}

function getAlphabetLine( $current ) {
	global $prevLink, $nextLink, $upLink, $alp;
	$result = "<div class='range'>Певчие, педагоги, учащиеся, руководители, персонал по алфавиту: ";
	for ( $i = 0; $i < strlen( $alp ); $i += 2 ) {
		$c = substr( $alp, $i, 2 );
		if ( $i == $current-1 ) $result .= $c;
		else $result .= getCharLink( $i, $c );
		if ( $current ) {
			if ( $i == $current-1 ) $prevLink = "/names/?char=".($i-1);
			if ( $i == $current+1 ) $nextLink = "/names/?char=".($i+1);
		}
	}
	$result .=  "</div>\n";
	if ( $current ) $upLink = "./";
	return $result;
}


function getCharLink( $i, $c ) {
	return "<a href='/names/?char=".($i+1)."'>$c</a>";
}


function getCourseArrows( $course ) {
global $prevLink, $nextLink, $upLink;
  $result = "";
  $prev = ($course == 1981) ? 1979
        : ($course == 1937) ? 1935
        :  $course-1;
  if (getExistingId("course", "end", $prev)) {
    $prevLink = "/course$prev";
    $result .= "<a href='$prevLink' title='Ctrl+Left'><img src='/img/prev.gif' align='absmiddle' border=0 /> На курс старше</a> | ";
  }
  $upLink = "./?form=2";
  $result .= "<a href='$upLink' title='Ctrl+Up'>Оглавление</a> | ";
  $result .= "<a href='/names/?form=3'>Все выпуски</a> ";
  $next = ($course == 1979) ? 1981
        : ($course == 1935) ? 1937
        :  $course+1;
  if (getExistingId("course", "end", $next)) {
    $nextLink = "/course$next";
    $result .= " | <a href='$nextLink' title='Ctrl+Right'>На курс младше <img src='/img/next.gif' align='absmiddle' border=0 /></a>";
  }
  return "<p class='arrows'>$result</p>";
}

function getPersonId( $shortName, $no ) {
	$lname = my_ucfirst( getRus4Translit( $shortName ) );
	$q = sprintf("select id from person where lname='%s' order by id;", $lname);
	$rows = mysql_squery( "getPersonId()", $q );
	if (!$rows) return 0;
	$num = mysql_num_rows($rows);
	if ( $no > $num ) return 0;
	$row = mysql_getRow( $rows, $no-1 );
	return $row->id;
}

function getRus4Translit( $translit ) {
	$mapEn = explode( "|", "eh|yu|ya|shch|ch|sh|ts|kh|zh|yo|a|b|v|g|d|e|z|i|j|k|l|m|n|o|p|r|s|t|u|f|y" );
	$mapRu = explode( "|", "э|ю|я|щ|ч|ш|ц|х|ж|ё|а|б|в|г|д|е|з|и|й|к|л|м|н|о|п|р|с|т|у|ф|ы" );
    $ru = $translit;
    for($i = 0; $i < count($mapEn); $i++) {
		$ru = str_replace( $mapEn[$i], $mapRu[$i], $ru );
	}
    return $ru;
}

function getNameSuggestions( $name ) {
  $parts = split( " ", $name );
  $condition = "";
  foreach ( $parts as $part ) {
  	if ($condition) $condition .= " AND ";
  	$condition .= sprintf( "CONCAT_WS(' ',lname,pname,fname,mname) like '%%%s%%'", $part );
  }

  $query = "select * from person where $condition order by lname, fname, mname, byear, bmonth, bdate;";
  $rows = mysql_squery( "getSuggestions", $query );
  $len = mysql_num_rows($rows);
  $results = array();
  for ($i = 0; $i < $len; $i++) {
    $results[] = mysql_getRow( $rows, $i );
  }
  return $results;
}

?>