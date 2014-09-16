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

<br />
<b>Depuis 2014-2015, ce script de conversion n'est plus valable (nouvelle version d'ADE).<br />Une solution alternative est possible en se rendant sur <a href="http://horairev6.uclouvain.be/direct/index.jsp?displayConfName=webEtudiant&showTree=true&showOptions=true&login=etudiant&password=student&projectId=6">ADExpert</a>,<br />en choisissant l'option <em>Export Agenda</em> en bas à gauche<br />puis enfin, en utilisant le script <em>change_hour.sh</em> disponible sur github: <a href="https://github.com/matttbe/ade2ics">ici</a>.</b>

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
		<label for="user_pass">Utilisateur et mot de passe: </label><br /><input type="text" name="user" id="user" value="etudiant" size="25" /> <input type="text" name="pass" id="pass" value="student" size="25" /><br />
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
$url = 'http://horairev6.uclouvain.be';
$user = $_POST['user'];
$pass = $_POST['pass'];
$projectID = $_POST['projectid'];
// -----------------------------------------------------------------------------------------------------------------------------


$full_url = $url.'/direct/index.jsp?displayConfName=webEtudiant&showTree=true&showOptions=true&weeks='.$semaines.'&code='.$codes.'&login='.$user.'&password='.$pass.'&projectId='.$projectID;
echo '<br />Voir l\'horaire sur ADE: <a href="'.$full_url.'" target="_blank">ici</a>';
}
?>
</body>
</html>
