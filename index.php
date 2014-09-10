<?php
/*
 * index.php
 *
 * Copyright Philippe Virouleau (http://philippevirouleau.free.fr/ade/)
 *  Modified by Mathias Leroy for the UCL (29 January 2010)
 *  Updated bu Matthieu Baerts since September 2011 (http://www.mbaerts.be/ade/)
 *
 *  Inspired by ade2ics.pl: http://code.google.com/p/ade2ics/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 */

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<title>ADE expert</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" media="screen" type="text/css" title="design" href="style.css" />
	<!-- Script inspiré de celui de Philippe Virouleau sur http://philippevirouleau.free.fr/ade/ et adapté par Mathias Leroy pour l'UCL le 29 janvier 2010 et mis à jour par Matthieu Baerts depuis le 16 septembre 2011-->
</head>

<body>
<h1>ADExpert converter</h1>

<?php
function CutDate($date) {
	$ar = explode("/",$date);
	return $ar[2].$ar[0].$ar[1];
}

function CatHeure($heure) {
	$heures = explode('h',$heure); //0=h,1=min
	return $heures;
}

function HFin($debut,$duree) {
	$duree = str_replace('min','',$duree);
	$duree_ar = explode('h',$duree);
	$dureeh = $duree_ar[0]; // comme il y a des cours de la forme 15min, on pourrait donc avoir des minutes dans duree_ar[0]
	$dureem = (isset($duree_ar[1]))?$duree_ar[1]:$duree_ar[0]; // si on a une duree de la forme 2h , dureem est vide (0 pour une addition)

	$finm = $debut[1]+$dureem;
		if(($finm)>=60) {
		$debut[0]=$debut[0]+1;
		$finm = $finm-60;
		}
	$finm = (strlen($finm)<2)?'0'.$finm:$finm;// on rajoute le 0 si l'heure de fin est 8h09 par exemple
	$finh = $debut[0]+$dureeh; // on suppose qu'on aura jamais cours après minuit, et qu'on inclu pas les s*n* dans l'edt ADE...
	$finh = (strlen($finh)<2)?'0'.$finh:$finh; // la meme que pour les minutes
	$final = $finh.$finm.'00'; // la forme ics est hhmmss, donc on met ss à 00
	return $final;
}

function getFile($mode,$buffer){
    //Purge des anciens fichiers
    $dir = opendir($mode);
    while(($file=readdir($dir))==true){
      $file=$mode.'/'.$file;
      if ((time()-fileatime($file) > 60*60) and !(is_dir($file))){ // verification de la date, supression au bout d'une heure
         unlink($file);
         }
    }
    // Sortie du fichier
    $nameFile= $mode.'/ade'.time().'.'.$mode; // Création du nom de fichier cause variation de timestamp
    $file = fopen($nameFile,'w+');
    fputs($file,$buffer);
    fclose($file);
	chmod($nameFile, 0504);
    return($nameFile);
}
?>

<form class="top" id="formulaire" method="post" action="index.php">
	<p>
		<?php
		$semaines='';
		$week = date("W"); // week: 1 -> 52
		$day = date("w");  // day: 0 (sunday) -> 6
		$start = 0;
		if ($day == 0 or $day == 6) // week-end => next week
			$start = 1;
		if ($week > 36) // septembre => decembre
		{
			$week -= 38; // => S0 ADE = S1 de Q1
			$end = 13;   // => 14 semaines de cours
		}
		else
		{
			$week += 14; // => S19 ADE = S1 de Q2
			$end = 32;   // => 14 semaines de cours depuis S19
		}
		for ($i = $start; $i + $week <= $end; $i++)
		{
			$suivant = $i + $week;
			$semaines .= ','.$suivant;
		}
		$semaines = ltrim($semaines,','); //supprime la premiere virgule
		if ($semaines === '')
			$semaines = "0,1,2";
		?>
		<label for="codes"><b>Codes cours</b> (séparés par virgules, ex: <em>SINF11BA,FSAB11BA</em>) :</label><br /><input type="text" name="codes" id="codes" size="60" value="<?php $_POST['codes']; ?>"/><!-- ex: BIRE21MSG,optbire2mm521,optbire2m10e21,BIRE21MTC --><br />
		<label for="semaines"><b>Semaines désirées</b> (séparés par virgules) :</label><br /><input type="text" name="semaines" id="semaines" value="<?php echo $semaines; ?>" size="60" /><br />
		<label for="user_pass">Utilisateur et mot de passe: </label><br /><input type="text" name="user" id="user" value="etudiantbv" size="25" /> <input type="text" name="pass" id="pass" value="studentbv" size="25" /><br />
		<label for="projectid">ID du projet: (6 pour 2014-2015) :</label><br /><input type="text" name="projectid" id="projectid" value="6" size="2" /><br /><br />
		<b>NOTE</b>: D'après ADE, nous sommes aujourd'hui en semaine <b>S<?php echo $week; ?></b> (depuis lundi).
		<br />
		En effet, selon lui, la première semaine de cours en septembre est la semaine 0.<br />Les semaines ne sont pas remises à 0 au 2ème quadri.
		<br /><br />
		<input type="submit" value="Convertir" />
	</p>
</form>

<?php
if (isset($_POST['codes']) && $_POST['codes']!='')
{
// PARAMETRES -------------------------------------------------------------------------------------------------------------
$codes = $_POST['codes'];
$semaines = $_POST['semaines'];
$url = 'http://horaire.sgsi.ucl.ac.be:8080';
$user = $_POST['user'];
$pass = $_POST['pass'];
$projectID = $_POST['projectid'];
// -----------------------------------------------------------------------------------------------------------------------------

// CREATION DU COOKIE
$id= rand(0,10000);
$fh = fopen("cookie_".$id.".txt","w");
fclose($fh);

// OUVERTURE DE SESSION
$ch1 = curl_init();
curl_setopt($ch1, CURLOPT_HEADER, 0);
curl_setopt($ch1, CURLOPT_COOKIEJAR, realpath("cookie_".$id.".txt"));
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch1, CURLOPT_USERAGENT, 'Mozilla 5/0'); // utf-8
$full_url = $url.'/ade/custom/modules/plannings/direct_planning.jsp?weeks='.$semaines.'&code='.$codes.'&login='.$user.'&password='.$pass.'&projectId='.$projectID;
echo '<br />Voir l\'horaire sur ADE: <a href="'.$full_url.'" target="_blank">ici</a>';
curl_setopt($ch1, CURLOPT_URL, $full_url);
$ploupi = curl_exec($ch1);
curl_close($ch1);

// CHARGEMENT DE LA SESSION ET AFFICHAGE EN TABLEAU
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_HEADER, 0);
curl_setopt($ch2, CURLOPT_COOKIEFILE, realpath("cookie_".$id.".txt"));
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_USERAGENT, 'Mozilla 5/0'); // utf-8
curl_setopt($ch2, CURLOPT_URL, $url.'/ade/custom/modules/plannings/info.jsp?order=slot');
$horaire = curl_exec($ch2);
curl_close($ch2);

// SUPRESSION DU COOKIE
unlink(realpath("cookie_".$id.".txt"));

// TRAITEMENT DE L'HORAIRE
$horaire = str_replace('<BODY>','',$horaire);
$horaire = str_replace('&amp;','&',$horaire);
$horaire = str_replace('&','&amp;',$horaire);
$dom = new DOMDocument(); // creation d'un objet DOM pour lire le html 
$dom->loadHTML($horaire) or die('erreur');
$lignes = $dom->getElementsByTagName('tr'); // on recupere toute les lignes

// CREATION DU FORMAT ICS
$buf_ics = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//ETSIL 3//iCal 1.0//EN
CALSCALE:GREGORIAN
METHOD:PUBLISH
BEGIN:VTIMEZONE
TZID:Bruxelles\, Copenhague\, Madrid\, Paris
BEGIN:STANDARD
DTSTART:20001029T030000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
TZNAME:Paris\, Madrid
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
END:STANDARD
BEGIN:DAYLIGHT
DTSTART:20000326T020000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3
TZNAME:Paris\, Madrid (heure d'été)
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
END:DAYLIGHT
END:VTIMEZONE\n";

// EXTRACTION DES DONNEES DU DOMDOCUMENT
$stamp_id = rand(10,59);
$i=0;
foreach ($lignes as $ligne)
{
	if($i>1)
	{
		// les deux premiers tr sont des titres, osef
		$content = $ligne->childNodes;
		$noms = array('date','mat','sem','jour','heure','duree','etudiant','prof','salle');
		$entree = array();
		for($i=0;$i<=8;$i++)
		{
			$entree[$noms[$i]] = $content->item($i)->nodeValue; // attribution des valeurs aux variables
		}
		$heuress = CatHeure($entree['heure']); // tableau avec heure en 0 et minute en 1
		$hfin = HFin($heuress,$entree['duree']); // hhmmss
		$date = CutDate($entree['date']); // aaaammjj
		$salle = $entree['salle'];
		$cours='';
		$buf_ics .= "BEGIN:VEVENT\n";
		$description = $entree['mat']." - Salle : ".$salle." - Enseignant : ".$entree['prof']."\n";
		$buf_ics .= "DESCRIPTION:".$description;
		$buf_ics .= "DTSTAMP:20100130T1200".$stamp_id."Z\n";
		$buf_ics .= 'DTSTART;TZID="Bruxelles, Copenhague, Madrid, Paris":'.$date.'T'.$heuress[0].$heuress[1]."00\n";
		$buf_ics .= 'DTEND;TZID="Bruxelles, Copenhague, Madrid, Paris":'.$date.'T'.$hfin."\n";
		$buf_ics .= 'LOCATION:'.$salle."\n";
		$buf_ics .= "SUMMARY:".$entree['mat']." ".$cours."\nEND:VEVENT\n";
	}
	$i++;
};
$buf_ics .= "END:VCALENDAR";

// CREATION DU FICHIER.ICS ET LIEN POUR TELECHARGER
$link = getFile('ics',$buf_ics);
echo '<h3>Télécharger le fichier</h3><a href="'.$link.'">'.ltrim($link,'ics/').'</a>';
}
?>
</body>
</html>
