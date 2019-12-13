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
	if (isset($_SESSION['id'])) {
		header('Location: http://www.domainedesdieux.com/myevents/index.php');
  		exit();
	}
	if (!isset($_SESSION['id']) && isset($_POST['id']) && isset($_POST['mdp'])) {
		$stmt = $dbh->query('SELECT * FROM utilisateur WHERE ID="'.$_POST['id'].'" AND MDP="'.$_POST['mdp'].'"');
		if ($row = $stmt->fetch()) {
			$_SESSION['id'] = $_POST['id'];
			$_SESSION['type'] = $row['TYPE'];
			header('Location: http://www.domainedesdieux.com/myevents/gestion.php');
  			exit();
		}
	}
	if (!isset($_SESSION['id']) && isset($_POST['id_c']) && isset($_POST['mdp_c'])) {
		$stmt = $dbh->query('SELECT * FROM utilisateur WHERE ID="'.$_POST['id'].'"');
		if ($row = $stmt->fetch()) {
			header('Location: http://www.domainedesdieux.com/connexion.php?erreur=1');
  			exit();
		} else {
			$stmt = $dbh->prepare("INSERT INTO utilisateur (ID, MDP, TYPE) VALUES (:name, :value, :type)");
			$stmt->bindParam(':name', $_POST['id_c']);
			$stmt->bindParam(':value', $_POST['mdp_c']);
			$val = 1;
			$stmt->bindParam(':type', $val);
			$_SESSION['id'] = $_POST['id_c'];
			$_SESSION['type'] = '1';
			$stmt->execute();
			header('Location: http://www.domainedesdieux.com/myevents/index.php');
  			exit();
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>MyEvents</title>
		<link rel="stylesheet" type="text/css" href="style/styleConnexion.css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/all.css">
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	</head>
	<body>
		<div class="card bg-light" id="div1">
			<nav class="navbar" id="header-event">
			  <a class="navbar-brand" href="index.php"><h1>MyEvents</h1></a>
			</nav>
			<article class="card-body mx-auto" id="article1">
				<h4 class="card-title mt-3 text-center">Se connecter</h4>
				<form method="post">
					<div class="form-group input-group">
						<div class="input-group-prepend">
						    <span class="input-group-text"> <i class="fa fa-user"></i> </span>
						 </div>
				        <input name="id" class="form-control" placeholder="Pseudonyme" type="text">
				    </div>
				    <div class="form-group input-group">
				    	<div class="input-group-prepend">
						    <span class="input-group-text"> <i class="fa fa-lock"></i> </span>
						</div>
				        <input name="mdp" class="form-control" placeholder="Mot de passe" type="password">
				    </div> <!-- form-group// -->                              
				    <div class="form-group">
				        <button type="submit" class="btn btn-primary btn-block"> Envoyer  </button>
				    </div>                                                               
				</form>
				<p class="divider-text">
			        <span class="bg-light">OU</span>
			    </p>
			    <h4 class="card-title mt-3 text-center">S'inscrire</h4>
				<form method="post">
					<div class="form-group input-group">
						<div class="input-group-prepend">
						    <span class="input-group-text"> <i class="fa fa-user"></i> </span>
						 </div>
				        <input name="id_c" class="form-control" placeholder="Pseudonyme" type="text">
				    </div>
				    <div class="form-group input-group">
				    	<div class="input-group-prepend">
						    <span class="input-group-text"> <i class="fa fa-lock"></i> </span>
						</div>
				        <input name="mdp_c" class="form-control" placeholder="Mot de passe" type="password">
				    </div> <!-- form-group// -->                              
				    <div class="form-group">
				        <button type="submit" class="btn btn-primary btn-block"> Envoyer  </button>
				    </div>                                                               
				</form>
			</article>
		</div>
	</body>
</html>