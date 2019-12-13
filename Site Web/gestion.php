<?php
	session_start();
	ini_set('display_errors',1);
	try {
		$dbh = new PDO('mysql:host=masqué;dbname=masqué', 'masqué', 'masqué',array(\PDO::MYSQL_ATTR_INIT_COMMAND =>  'SET NAMES utf8'));
	} catch (PDOException $e) {
	    print "Erreur !: " . $e->getMessage() . "<br/>";
	    die();
	}
	if (intval($_SESSION['type']) == 1) {
		header('Location: http://www.domainedesdieux.com/myevents/index.php');
  		exit();
	}
?>
<?php
	if (intval($_SESSION['type']) == 3 && isset($_POST['add_c'])) {
		$stmt = $dbh->query("SELECT * FROM utilisateur WHERE TYPE=1 AND ID='".$_POST['add_c']."'");
		if ($row = $stmt->fetch()) {
			$sql = "UPDATE utilisateur SET TYPE=? WHERE ID=?";
			$stmt= $dbh->prepare($sql);
			$val = 2;
			$stmt->execute([$val, $_POST['add_c']]);
		}
	}
	if (intval($_SESSION['type']) == 3 && isset($_POST['rm_c'])) {
		$stmt = $dbh->query("SELECT * FROM utilisateur WHERE TYPE=2 AND ID='".$_POST['rm_c']."'");
		if ($row = $stmt->fetch()) {
			$sql = "UPDATE utilisateur SET TYPE=? WHERE ID=?";
			$stmt= $dbh->prepare($sql);
			$val = 1;
			$stmt->execute([$val, $_POST['rm_c']]);
		}
	}
	if (intval($_SESSION['type']) == 3 && isset($_POST['add_t'])) {
		$sql = "INSERT INTO theme (NOM) VALUES (?)";
		$stmt= $dbh->prepare($sql);
		$stmt->execute([$_POST['add_t']]);
	}
	if (intval($_SESSION['type']) == 3 && isset($_POST['rm_t'])) {
		$sql = "DELETE FROM theme WHERE NOM=?";
		$stmt= $dbh->prepare($sql);
		$stmt->execute([$_POST['rm_t']]);
	}
	if (intval($_SESSION['type']) >= 2 && isset($_POST['s_theme']) && isset($_POST['s_lieu']) && isset($_POST['s_date']) && isset($_POST['s_desc']) && isset($_POST['s_long']) && isset($_POST['s_lat'])) {

		$sql0 = "INSERT INTO lieu (L_ADRESSE,LATITUDE,LONGITUDE) VALUES (?,?,?)";
		$stmt0= $dbh->prepare($sql0);
		$stmt0->execute([$_POST['s_lieu'],$_POST['s_lat'],$_POST['s_long']]);
		
		
		$sql = "INSERT INTO `evenement`(PROPOSE_PAR, E_NOM, E_ADRESSE, E_THEME, DATE, MIN, MAX) VALUES (?,?,?,?,?,?,?)";
		$stmt= $dbh->prepare($sql);
		if (isset($_POST['s_min'])) {
			$min = $_POST['s_min'];
		} else {
			$min = NULL;
		}
		if (isset($_POST['s_max'])) {
			$max = $_POST['s_max'];
		} else {
			$max = NULL;
		}
		$stmt->execute([$_SESSION['id'],$_POST['s_desc'],$_POST['s_lieu'],$_POST['s_theme'],$_POST['s_date'],$min,$max]);
	}
	if (intval($_SESSION['type']) >= 2 && isset($_POST['rm_ev'])) {
		$sql = "DELETE FROM evenement WHERE NUM=?";
		$stmt= $dbh->prepare($sql);
		$stmt->execute([$_POST['rm_ev']]);
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>MyEvents</title>
		<link rel="stylesheet" type="text/css" href="style/styleGestion.css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/all.css">
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	</head>
	<body>
		<div class="container-fluid">
			<nav class="navbar" id="header-event">
			  <a class="navbar-brand" href="index.php"><h1>MyEvents</h1></a>
			  <p>Vous êtes connecté comme : <?= $_SESSION['id'] ?></p>
			  <a class="navbar-brand" href="deconnexion.php">Déconnexion</a>
			</nav>

			<!-- Panel admin -->
			<?php
				if (intval($_SESSION['type']) == 3) {
					echo '
			<div class="tab row">
				<div class="col-12">
					<h2>Tableau de bord administrateur</h2>
				</div>
				<div class="add-rm col-6">
					<form method="post">
						<p class="text1">Ajouter un contributeur</p>
						<select name="add_c" class="custom-select select1">
						  <option selected>Utilisateur</option>';
							  	$stmt = $dbh->query("SELECT ID FROM utilisateur WHERE TYPE=1");
							  	while ($row = $stmt->fetch()) {
								    echo '<option value='.$row['ID'].' >'.$row['ID'].'</option>';
								}
								echo '
						</select>
						<button type="submit" class="btn btn-success btnV">Valider</button>
					</form>
				</div>
				<div class="add-rm col-6">
					<form method="post">
						<p class="text1">Enlever un contributeur</p>
						<select name="rm_c" class="custom-select select1">
						  <option selected>Contributeur</option>';
							  	$stmt = $dbh->query("SELECT * FROM utilisateur WHERE TYPE=2");
							  	while ($row = $stmt->fetch()) {
								    echo '<option value='.$row['ID'].' >'.$row['ID'].'</option>';
								}
							echo '
						</select>
						<button type="submit" class="btn btn-danger btnV">Valider</button>
					</form>
				</div>
				<div class="add-rm col-6">
					<form method="post">
						<p class="text1">Ajouter un thème</p>
						<input name="add_t" type="text" class="custom-select form-control select1" id="inputTheme" placeholder="Nom du thème">
						<button type="submit" class="btn btn-success btnV">Valider</button>
					</form>
				</div>
				<div class="add-rm col-6">
					<form method="post">
						<p class="text1">Enlever un thème</p>
						<select name="rm_t" class="custom-select select1">
						  <option selected>Thème</option>';
							  	$stmt = $dbh->query("SELECT * FROM theme");
							  	while ($row = $stmt->fetch()) {
								    echo '<option value='.$row['NOM'].' >'.$row['NOM'].'</option>';
								}
							echo '
						</select>
						<button type="submit" class="btn btn-danger btnV">Valider</button>
					</form>
				</div>
			</div>';
				}

			//<!-- Panel contrib -->
				if (intval($_SESSION['type']) >= 2) {
					echo '
					<div class="tab row">
						<div class="col-12">
							<h2>Tableau de bord contributeur</h2>
						</div>
						<div class="col-6">
							<form method="post">
								<p class="text1">Ajouter un évènement</p>
								<select name="s_theme" class="custom-select select2" id="theme-event">
								  <option selected>Thème</option>';
									  	$stmt = $dbh->query("SELECT * FROM theme");
									  	while ($row = $stmt->fetch()) {
										    echo '<option value='.$row['NOM'].' >'.$row['NOM'].'</option>';
										}
									echo '
								</select>
								<input name="s_lieu" class="form-control select2" type="text" id="lieu-event" placeholder="Lieu">
								<input name="s_date" class="form-control custom-select select2" type="date" id="date-event">
								<input name="s_min" type="number" class="custom-select form-control select3" id="min" min=1 placeholder="Effectif min">
								<input name="s_max" type="number" class="custom-select form-control select3" id="max" min=1 placeholder="Effectif max">
								<input name="s_lat" type="text" class="form-control select3" id="max" min=1 placeholder="Latitude">
								<input name="s_long" type="text" class="form-control select3" id="min" min=1 placeholder="Longitude">
								<textarea name="s_desc" class="form-control rounded-0" id="descriptif" placeholder="Ecriver une description" rows="3"></textarea>
								<button type="submit" class="btn btn-success btnV2">Valider</button>
							</form>
						</div>
						<div class="col-6">
							<form method="post">
								<p class="text1">Enlever un évènement</p>
								<select name="rm_ev" class="custom-select select1">
								  <option selected>Evenement</option>';
									  	$stmt = $dbh->query("SELECT * FROM evenement");
									  	while ($row = $stmt->fetch()) {
										    echo '<option value='.$row['NUM'].' >'.$row['E_NOM'].'</option>';
										}
									echo '
								</select>
								<button type="submit" class="btn btn-danger btnV">Valider</button>
							</form>
						</div>
					</div>';
				}
			?>
		</div>
	</body>
</html>