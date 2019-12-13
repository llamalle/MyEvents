<?php
	session_start();
	ini_set('display_errors',1);
	try {
		$dbh = new PDO('mysql:host=masqué;dbname=masqué', 'masqué', 'masqué',array(\PDO::MYSQL_ATTR_INIT_COMMAND =>  'SET NAMES utf8'));
	} catch (PDOException $e) {
	    print "Erreur !: " . $e->getMessage() . "<br/>";
	    die();
	}
?>
<?php
	if (isset($_GET['id']) && isset($_GET['valeurNote']) && intval($_GET['valeurNote']) >= 0 && intval($_GET['valeurNote']) <= 5) {
		$stmt = $dbh->query('SELECT * FROM participe,evenement WHERE P_ID="'.$_SESSION['id'].'" AND P_EVENEMENT="'.$_GET['id'].'" AND P_EVENEMENT=NUM');
		if ($row = $stmt->fetch()) {
			$dateJour = new DateTime();
			$dateEvent = new DateTime($row['DATE']);
			if ($dateEvent < $dateJour) {
				$stmt = $dbh->query('SELECT * FROM notation WHERE N_ID="'.$_SESSION['id'].'" AND N_EVENEMENT="'.$_GET['id'].'"');
				if ($row = $stmt->fetch()) {
					$data = [
					    'id' => $_SESSION['id'],
					    'event' => $_GET['id'],
					    'note' => $_GET['valeurNote']
					];
					$sql = "UPDATE notation SET N_NOTE=:note WHERE N_ID=:id AND N_EVENEMENT=:event";
					$stmt= $dbh->prepare($sql);
					$stmt->execute($data);
				} else {
					//insert
					$stmt = $dbh->prepare("INSERT INTO notation (N_ID, N_EVENEMENT, N_NOTE) VALUES (:id, :event, :note)");
					$stmt->bindParam(':id', $_SESSION['id']);
					$stmt->bindParam(':event', $_GET['id']);
					$stmt->bindParam(':note', intval($_GET['valeurNote']));
					$stmt->execute();
				}
			}
		}
		header('Location: http://www.domainedesdieux.com/myevents/index.php');
  		exit();
	} else if (isset($_GET['id'])) {
		echo '
			<!DOCTYPE html>
			<html>
				<head>
					<title>MyEvents</title>
					<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
					<link rel="stylesheet" type="text/css" href="style/styleIndex.css">
					<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
					<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
					</script>
					<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
					<script src="jquery-ui-1.12.1/jquery-ui.min.js"></script>
					<link rel="stylesheet" href="jquery-ui-1.12.1/jquery-ui.min.css"></link>
				</head>
				<body>
					<h1><a href="index.php">MyEvents</a></h1>
					<form method="get" style="height: 100px; width: 200px; position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);">
						<input style="display:none;" type="text" name="id" value="'.$_GET['id'].'" />
						<input style="width: 300px; margin-bottom: 5px;text-align: center;" type="number" min="0" max="5" placeholder="Note (de 0 à 5)" name="valeurNote" />
						<button style="width: 300px" type="submit" class="btn btn-primary btn-block">Envoyer</button>
					</form>
				</body>
			</html>
		';
		exit;
	}
?>
<?php
	class Evenement {
		public $numero;
		public $nom;
		public $theme;
		public $date;
		public $lieu;
		public $estInscrit;
		public $note;
		public $coordX;
		public $coordY;
		public function __construct($num,$n,$t,$d,$l,$est,$no,$x,$y) {
			$this->numero = $num;
			$this->nom = $n;
			$this->theme = $t;
			$this->date = $d;
			$this->lieu = $l;
			$this->estInscrit = $est;
			$this->note = $no;
			$this->coordX = $x;
			$this->coordY = $y;
		}
		public function construireMarkeretPopup() {
			echo '
				<img id="marker'.$this->numero.'" class="marker" src="open/marker.png" width="50" height="50" onClick="switchMarker(\'popup'.$this->numero.'\');" />
				<div id="popup'.$this->numero.'" style="display:none; font-size:15pt; color:black; width:auto; height:100px; text-align: center;">
					<p style="background-color: rgba(255,255,255,0.7);border-radius: 15%;">'.$this->nom.'</p>
				</div>
			';
		}
		public function creerScript() {
			echo '
				var marker'.$this->numero.' = document.getElementById(\'marker'.$this->numero.'\');
				map.addOverlay(new ol.Overlay({
					position: ol.proj.fromLonLat(['.$this->coordX.','.$this->coordY.']),
					element: marker'.$this->numero.'
				}));

				var popup'.$this->numero.' = document.getElementById(\'popup'.$this->numero.'\');
				map.addOverlay(new ol.Overlay({
					offset: [-20, -30],
					position: ol.proj.fromLonLat(['.$this->coordX.','.$this->coordY.']),
					element: popup'.$this->numero.'
				}));
			';
		}
		public function creerLigne() {
			echo '
				<tr>
			      <td>'.$this->nom.'</td>
			      <td>'.$this->theme.'</td>
			      <td>'.$this->date.'</td>
			      <td>'.$this->lieu.'</td>';
			      if (isset($_SESSION['id'])) {
			      		$dateJour = new DateTime();
			      		$dateEvent = new DateTime($this->date);
				      if ($this->estInscrit == 0) {
				      	if ($dateEvent >= $dateJour) {
				      		echo '<td>Non <a href="index.php?ins='.$this->numero.'">(S\'inscrire)</a></td>';
				      	} else {
				      		echo '<td>Non</td>';
				      	}
				      } else {
				      	if ($dateEvent >= $dateJour) {
				      		echo '<td>Oui <a href="index.php?des='.$this->numero.'">(Se désinscrire)</a></td>';
				      	} else {
				      		echo '<td>Oui <a href="index.php?id='.$this->numero.'">(Noter)</a></td>';
				      	}
				      }
				  } else {
				  	echo '<td>Non</td>';
				  }
			      echo '
			    </tr>
			';
		}
	}

	if (isset($_GET['ins']) && isset($_SESSION['id'])) {
		$stmt3 = $dbh->query('SELECT * FROM participe WHERE P_ID="'.$_SESSION['id'].'" AND P_EVENEMENT="'.$_GET['ins'].'"');
		if ($row3 = $stmt3->fetch()) {
			
		} else {
			$stmt4 = $dbh->prepare("INSERT INTO participe (P_ID, P_EVENEMENT) VALUES (:id, :event)");
			$stmt4->bindParam(':id', $_SESSION['id']);
			$stmt4->bindParam(':event', $_GET['ins']);
			$stmt4->execute();
		}
	}

	if (isset($_GET['des'])) {
		$stmt3 = $dbh->exec('DELETE FROM participe WHERE P_ID="'.$_SESSION['id'].'" AND P_EVENEMENT="'.$_GET['des'].'"');
	}



	//On récupère les évents ici en fonction des $_GET
	$event = array();

	$requete = "SELECT * FROM evenement,lieu WHERE L_ADRESSE = E_ADRESSE";
	if (isset($_GET['theme']) AND $_GET['theme'] != "Thème" AND $_GET['theme'] != "undefined") {
		$requete = $requete . " AND E_THEME = '".$_GET['theme']."'";
	}
	if (isset($_GET['lieu']) AND $_GET['lieu'] != "Lieu" AND $_GET['lieu'] != "undefined") {
		$requete = $requete . " AND E_ADRESSE like '%".$_GET['lieu']."%'";
	}
	if (isset($_GET['date']) AND $_GET['date'] != "undefined" AND $_GET['date'] != '') {
		$requete = $requete . " AND DATE = '".$_GET['date']."'";
	}
	$stmt = $dbh->query($requete);
	$i = 0;
	while ($row = $stmt->fetch()) {
		$ins = 0;
		if (isset($_SESSION['id'])) {
			$stmt2 = $dbh->query('SELECT * FROM participe WHERE P_ID="'.$_SESSION['id'].'" AND P_EVENEMENT="'.$row['NUM'].'"');
			if ($row2 = $stmt2->fetch()) {
				$ins = 1;
			}
		}
		array_push($event,new Evenement($row['NUM'],$row['E_NOM'],$row['E_THEME'],$row['DATE'],$row['E_ADRESSE'],$ins,null,$row['LONGITUDE'],$row['LATITUDE']));
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>MyEvents</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" href="style/styleIndex.css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
		</script>
		<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
		<script src="jquery-ui-1.12.1/jquery-ui.min.js"></script>
		<link rel="stylesheet" href="jquery-ui-1.12.1/jquery-ui.min.css"></link>
		<script src="https://cdn.rawgit.com/openlayers/openlayers.github.io/master/en/v6.0.1/build/ol.js"></script>
		<link rel="stylesheet" href="https://cdn.rawgit.com/openlayers/openlayers.github.io/master/en/v6.0.1/css/ol.css"/>
		<style>
			.points_interet, .map {
				width: 100%; 
			}
		</style>
	</head>
	<body>
		<!-- HEADER -->
		<nav class="navbar" id="header-event">
		  <a class="navbar-brand"><h1>MyEvents</h1></a>
		  <?php 
		  	if (isset($_SESSION['id']) && $_SESSION['type'] != '1') {
		  		echo '<a class="nav-link" href="gestion.php">Gestion</a>';
		  	} else if (isset($_SESSION['id']) && $_SESSION['type'] == '1') {
		  		echo '<a class="nav-link" href="deconnexion.php">Deconnexion</a>';
		  	} else {
		  		echo '<a class="nav-link" href="connexion.php">Connexion</a>';
		  	}
		  ?>
		</nav>
		<!-- IMAGE INTRO + TEXTE -->
		<div class="container-fluid" id="div-intro">
			<p class="text-intro">Tu cherches un évènement proche de chez toi ?</p>
			<p class="text-intro">MyEvents est fait pour toi !</p>
		</div>
		<div class="container-fluid" id="liste-carte">
			<div class="row">
				<!-- LISTE DES EVENTS -->
				<div class="col" id="liste-event">
					<!-- Rechercher -->
					<div class="row">
						<div class="col">
							<span>Rechercher par :</span>
						</div>
						<div class="col">
							<select class="custom-select" id="theme-event">
							  <option <?php if ($_GET['theme'] == "Thème") {echo " selected ";} ?>>Thème</option>
							  <?php
							  	$stmt = $dbh->query("SELECT nom FROM theme");
							  	$i = 1;
							  	while ($row = $stmt->fetch()) {
								    echo '<option value='.$i++.' ';
								    if ($_GET['theme'] == $row['nom']) {
								    	echo "selected";
								    }
								    echo '>'.$row['nom'].'</option>';
								}
							  ?>
							</select>
						</div>
						<div class="col">
							<input class="form-control" type="date" id="date-event" <?php if ($_GET['date'] != 'undefined') {echo 'value='.$_GET['date'];} ?>>
						</div>
						<div class="col">
							<select class="custom-select" id="lieu-event">
							  <option <?php if ($_GET['lieu'] == "lieu") {echo "selected";} ?>>Lieu</option>
							  <?php
							  	$stmt = $dbh->query("SELECT L_ADRESSE FROM lieu");
							  	$i = 1;
							  	while ($row = $stmt->fetch()) {
							  		$split = explode(" ", $row['L_ADRESSE']);
								    echo '<option value='.$i++.' ';
								    if ($_GET['lieu'] == $split[count($split)-1]) {
								    	echo "selected";
								    }
								    echo '>'.$split[count($split)-1].'</option>';
								}
							  ?>
							</select>
						</div>
					</div>
					<!-- Liste -->
					<div class="row" id="row-liste">
						<table class="table" id="table-event">
						  <thead>
						    <tr>
						      <th scope="col">Nom de l'évènement</th>
						      <th scope="col">Thème</th>
						      <th scope="col">Date</th>
						      <th scope="col">Lieu</th>
						      <th scope="col">Inscrit ?</th>
						    </tr>
						  </thead>
						  <tbody>
						    <?php
								foreach ($event as $key => $value){
								    $value->creerLigne();
								}
							?>
						  </tbody>
						</table>
					</div>
				</div>
				<!-- CARTE -->
				<div class="col">
					<div id="map" class="map" style="width:100%; height:100%"></div>
					<?php
						foreach ($event as $key => $value){
						    $value->construireMarkeretPopup();
						}
					?>
					<script>
						var map = new ol.Map({
							target: 'map',
							layers: [new ol.layer.Tile({source: new ol.source.OSM()})],
							view: new ol.View({
								center: ol.proj.fromLonLat([3.87667,43.6111]),
								zoom: 11
							})
						});

						<?php
							foreach ($event as $key => $value){
							    $value->creerScript();
							}
						?>

						function switchMarker(elem) {
							elem = document.getElementById(elem);
							(elem.style.display == "none"? elem.style.display = "block" : elem.style.display = "none")
						};

						function $_GET(param) {
							var vars = {};
							window.location.href.replace( location.hash, '' ).replace( 
								/[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
								function( m, key, value ) { // callback
									vars[key] = value !== undefined ? value : '';
								}
							);

							if ( param ) {
								return vars[param] ? vars[param] : null;	
							}
							return vars;
						}

						var $_GET = $_GET(),
							theme = decodeURI($_GET['theme']),
							lieu = decodeURI($_GET['lieu']),
							date = decodeURI($_GET['date']),
							checked = decodeURI($_GET['checked']);

						$("#theme-event").change(function() {
						    $("#theme-event option:selected" ).each(function() {
						    	if (theme != $(this).text()) {
						      		var str = "theme="+$(this).text()+"&lieu="+lieu+"&date="+date+"&checked="+checked;
						      		document.location.href="index.php?"+str;
						      	}
						    });
						  }).trigger( "change" );
						$("#lieu-event").change(function() {
						    $("#lieu-event option:selected" ).each(function() {
						    	if (lieu != $(this).text()) {
							    	var str = "theme="+theme+"&lieu="+$(this).text()+"&date="+date+"&checked="+checked;
							    	document.location.href="index.php?"+str;
							    }
						    });
						  }).trigger( "change" );
						$(function() {
						    $("#date-event").datepicker();
						    $("#date-event").on("change",function(){
						    	if (date != $(this).val()) {
							        var selected = $(this).val();
							        var str = "theme="+theme+"&lieu="+lieu+"&date="+selected+"&checked="+checked;
							        document.location.href="index.php?"+str;
							    }
						    });
						});
					</script>
				</div>
			</div>
		</div>
		<div id="txt"></div>
	</body>
</html>