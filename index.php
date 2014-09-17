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

/* ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1); */
date_default_timezone_set('Europe/Brussels');

function purgeOldFiles($ext) {
	$dir = opendir($ext);
	while (($file = readdir($dir)) == true)
	{
		$file = $ext.'/'.$file;
		if ((time() - fileatime($file) > 5*60) and !(is_dir($file))) // remove files older than 5 minutes
			unlink($file);
	}
}
function createNewFile($ext, $buffer)
{
	if (! is_dir($ext))
		mkdir($ext, 0777);
	purgeOldFiles($ext);
	// Get new file
	$nameFile= $ext.'/ade'.time().'.'.$ext;
	$file = fopen($nameFile, 'w+');
	fputs($file, $buffer);
	fclose($file);
	// chmod($nameFile, 0504);
	return $nameFile;
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Ajout d'un nouvel article</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- Bootstrap -->
	<link href="bootstrap.min.css" rel="stylesheet" media="screen">
	<style type="text/css">
	body {
		padding-top: 40px;
		padding-bottom: 40px;
		background-color: #f5f5f5;
	}
	.jumbotron {
		max-width: 80%;
		padding: 19px 29px 29px;
		margin: 0 auto 20px;
	}
	.instructions {
		max-width: 80%;
		margin-left: auto;
		margin-right: auto;
	}
	.bs-callout {
		margin: 20px 0;
		padding: 15px 30px 15px 15px;
		border-left: 5px solid #eee;
	}
	.bs-callout h4 {
		margin-top: 0;
	}
	.bs-callout p:last-child {
		margin-bottom: 0;
	}
	.bs-callout-danger {
		background-color: #fcf2f2;
		border-color: #dFb5b4;
	}
	.bs-callout-warning {
		background-color: #fefbed;
		border-color: #f1e7bc;
	}
	.bs-callout-info {
		background-color: #f0f7fd;
		border-color: #d0e3f0;
	}
	</style>
</head>
<body>

	<div class="jumbotron">
	<h1>ADExpert UCL Fixer</h1>
		<p>Depuis l'année académique 2014-2015, une nouvelle version d'ADExpert est disponible. Le site est beaucoup plus difficile à parser mais une nouvelle fonctionnalité d'export au format ICS est disponible. Seulement, cette méthode nous donne des fichiers non conformes avec une mauvaise heure...</p>
	</div>

	<?php
	if (! isset($_GET['step']) OR $_GET['step'] == "" OR $_GET['step'] == "0") // first time: no code
	{ ?>
		<div class="instructions">
			<div class="progress">
				<div class="progress-bar progress-bar-striped active"  role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 1%">
					<span class="sr-only">0% Complete</span>
				</div>
			</div>
			<div class="bs-callout bs-callout-danger">
				<h4>Étape 1/3</h4>
				<p>Le but ici est de créer le lien vers la page d'ADExpert contenant l'horaire correct et un bouton pour générer un fichier ICS.</p>
			</div>
		</div>

		<?php
		$weeks = '';
		$week = date("W"); // week: 1 -> 52
		$day = date("w");  // day: 0 (sunday) -> 6
		$start = 0;
		if ($day == 0 OR $day == 6) // week-end => next week
			$start = 1;
		if ($week > 36) // septembre => decembre
		{
			$week -= 38; // => S0 ADE = S1 of Q1
			$end = 13;   // => 14 weeks
		}
		else
		{
			$week += 14; // => S19 ADE = S1 of Q2
			$end = 32;   // => 14 weeks since S19
		}
		for ($i = $start; $i + $week <= $end; $i++)
		{
			$next = $i + $week;
			$weeks .= ','.$next;
		}
		$weeks = ltrim($weeks, ','); // remove the first comma
		if ($weeks === '')
			$weeks = "0,1,2";
		?>

		<div class="container">
			<form class="form-code" name="formcode" action="<?php echo basename($_SERVER['PHP_SELF']); ?>?step=1" method="post">
				<h2 class="form-signin-heading">Obtenir la liste des cours</h2>
				<div class="control-group">
					<label class="control-label" for="codes">Codes cours :</label>
					<div class="controls">
						<input type="text" class="input-block-level" id="codes" name="codes" placeholder="Codes" size="60" />
					</div>
					<p class="help-block">Séparés par des virgules, ex: <em>SINF11BA,FSAB11BA</em></p>
				</div>
				<div class="control-group">
					<label class="control-label" for="weeks">Semaines désirées :</label>
					<div class="controls">
						<input type="text" class="input-block-level" id="weeks" name="weeks" placeholder="Weeks" value="<?php echo $weeks; ?>" size="60" />
					</div>
					<p class="help-block">Séparées par des virgules, nous sommes en semaine <?php echo $week; ?> d'après ADExpert</p>
				</div>
				<div class="control-group">
					<label class="control-label" for="codes">Utilisateur et mot de passe :</label>
					<div class="controls">
						<input type="text" class="input-block-level" id="user" name="user" placeholder="User" value="etudiant" />
						<input type="text" class="input-block-level" id="pass" name="pass" placeholder="Password" value="student" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="codes">ID du projet: (6 pour 2014-2015) :</label>
					<div class="controls">
						<input type="text" class="input-block-level" id="projectid" name="projectid" placeholder="ProjectID" value="6" size="2" />
					</div>
				</div>
				<br />
				<div class="form-actions">
					<button type="submit" class="btn btn-primary">Suivant</button>
				</div>
			</form>
		</div>
	<?php
	}
	elseif (isset($_GET['step']) AND $_GET['step'] == "1")
	{
		if (isset($_POST['weeks']) AND isset($_POST['codes']) AND isset($_POST['user']) AND isset($_POST['pass']) AND isset($_POST['projectid']))
			$fullUrl = 'http://horairev6.uclouvain.be/direct/index.jsp?displayConfName=webEtudiant&showTree=true&showOptions=true&weeks='.$_POST['weeks'].'&code='.$_POST['codes'].'&login='.$_POST['user'].'&password='.$_POST['pass'].'&projectId='.$_POST['projectid'];
		else
			$fullUrl = false;
		?>
		<div class="instructions">
			<div class="progress">
				<div class="progress-bar progress-bar-striped active"  role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" style="width: 50%">
					<span class="sr-only">50% Complete</span>
				</div>
			</div>
			<div class="bs-callout bs-callout-danger">
				<h4>Étape 2/3</h4>
				<p>Il est maintenant nécessaire 
					<?php
					if ($fullUrl)
						echo "de se rendre sur <a href='".$fullUrl."' target='_blank'>le site d'ADExpert ici</a> et ";
					?>
					d'envoyer ci-dessous le fichier ICS (ICalendar) généré par ADExpert afin de régler les soucis d'heures, etc.</p>
			</div>
		</div>
		<div class="container">
			<form class="form-file" enctype="multipart/form-data" name="formfile" action="<?php echo basename($_SERVER['PHP_SELF']); ?>?step=2" method="post">
				<input type="hidden" name="MAX_FILE_SIZE" value="100000" />
				<div class="control-group">
					<label class="control-label" for="codes">Appliquer la convertion en heure d'été si besoin est :</label>
					<input type="checkbox" class="input-block-level" id="hours" name="hours" placeholder="Hours" value="DST" checked />
					<p class="help-block">En septembre 2014, les événements avant le changement d'heure étaient indiqués avec une heure en avance...</p>
				</div>
				<div class="control-group">
					<label class="control-label" for="codes">Fichier ICS (ICalendar) venant d'ADExpert :</label>
					<div class="controls">
						<input name="userfile" type="file" />
					</div>
					<p class="help-block">Il s'agit du fichier ADECal.ics généré par ADExpert grâce à l'outil d'export, 2ème icône en dessous à gauche.</p>
				</div>
				<br />
				<div class="form-actions">
					<button type="submit" class="btn btn-primary">Send File</button>
				</div>
			</form>
		</div>
	<?php
	}
	elseif (isset($_GET['step']) AND $_GET['step'] == "2")
	{ ?>
		<div class="instructions">
			<div class="progress">
				<div class="progress-bar progress-bar-striped active"  role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
					<span class="sr-only">100% Complete</span>
				</div>
			</div>
			<div class="bs-callout bs-callout-danger">
				<h4>Étape 3/3</h4>
				<p>Vous pouvez maintenant télécharger le fichier ICS ci-dessous et l'importer dans votre calendrier (exemple, dans un <a href="https://www.google.com/calendar" target="_blank">Google Calendar</a>, <a href="https://support.google.com/calendar/answer/37118?hl=fr" target="_blank">aide ici</a>).</p>
			</div>
		</div>
		<?php
		$fileName = "ERROR";
		if (is_uploaded_file($_FILES['userfile']['tmp_name']))
		{
			$content = file_get_contents($_FILES['userfile']['tmp_name']);
			if (substr($content, 0, 15) !== "BEGIN:VCALENDAR")
			{ ?>
				<div class="alert alert-error" role="alert">Erreur, fichier anormal</div>
				</body>
				</html>
			<?php
				return;
			}

			// remove \r, desc which is split, empty lines in desc
			$patterns = array("/\r/", "/\n /");
			$replacements = array("", "");
			$content = preg_replace($patterns, $replacements, $content);
			$content = str_replace("DESCRIPTION:\\n", "DESCRIPTION:", $content);

			if (! isset($_POST['hours']) OR $_POST['hours'] == "DST")
			{
				// get each lines
				$lines = explode(PHP_EOL, $content);
				$newContent = "";
				foreach ($lines as $line) {
					if (substr($line, 0, 7) === "DTSTART" OR substr($line, 0, 5) === "DTEND")
					{
						// we receive a file where hours doesn't respect DST
						$lineSplit = explode(':', $line, 2);
						/* if (FALSE AND version_compare(PHP_VERSION, '5.3.0', '>='))
						{
							$date = date_create_from_format('Ymd\THis\Z', $lineSplit[1], timezone_open ('Etc/GMT+1'));
							$date = date_timezone_set($date, 'Europe/Brussels');
							$dateOut = date_format($date, 'Ymd\THis\Z');
						}
						else
						{*/
							$time = strtotime($lineSplit[1]);
							$time = strtotime('-1 hour', $time);
							$dateOut = date('Ymd\THis\Z', $time);
						//}
						$line = $lineSplit[0].':'.$dateOut;
						// echo $lineSplit[1]." => ".$dateOut."<br />";
					}
					$newContent .= $line."\n";
				}
				$fileName = createNewFile("ics", $newContent);
			}
			else
				$fileName = createNewFile("ics", $content);
			echo "<center><h2><a href='".$fileName."'>".basename($fileName)."</a></h2></center>\n";
		}
		else
			echo "<center><h2>No file</h2></center>\n";
	}
	else
		echo "Step unknown";
	?>

	<!-- Github Ribbon -->
	<a href="https://github.com/matttbe/ade2ics"><img style="position: absolute; top: 0; left: 0; border: 0;" src="ribbon.png" alt="Fork me on GitHub"></a>
</body>
</html>
