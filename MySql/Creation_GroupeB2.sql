/*
Fichier : Creation_GroupeB2.sql
Auteurs :
Louis LAMALLE 21708839
Romain JAMINET 21707709
Nom du groupe : B2
*/

/*
Création de la base de données
*/
DROP DATABASE IF EXISTS MyEvents;
CREATE DATABASE MyEvents CHARACTER SET 'utf8';
USE MyEvents;
/*
Création des relations
*/
DROP TABLE IF EXISTS participe;
DROP TABLE IF EXISTS notation;
DROP TABLE IF EXISTS evenement;
DROP TABLE IF EXISTS lieu;
DROP TABLE IF EXISTS utilisateur;
DROP TABLE IF EXISTS theme;

CREATE TABLE utilisateur(
    ID VARCHAR(20) PRIMARY KEY,
    MDP VARCHAR(70),
    TYPE TINYINT(1) NOT NULL 
);

CREATE TABLE theme(
    NOM VARCHAR(20) PRIMARY KEY,
    TOTAL INT DEFAULT 0
);

CREATE TABLE lieu(
    L_ADRESSE VARCHAR(100) PRIMARY KEY,
    LATITUDE DECIMAL(35,6),
    LONGITUDE DECIMAL(35,6)    
);

CREATE TABLE evenement(
    NUM INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    PROPOSE_PAR VARCHAR(20) NOT NULL,
    E_NOM VARCHAR(100),
    E_ADRESSE VARCHAR(100) NOT NULL,
    E_THEME VARCHAR(20),
    NOTE DECIMAL(2,1) DEFAULT 0,
    DATE DATE,
    MIN INT,
    MAX INT,
    FOREIGN KEY (PROPOSE_PAR) REFERENCES utilisateur(ID),
    FOREIGN KEY (E_ADRESSE) REFERENCES lieu(L_ADRESSE),
    FOREIGN KEY (E_THEME) REFERENCES theme(NOM)
);

CREATE TABLE participe(
    P_ID VARCHAR(20),
    P_EVENEMENT INT,
    PRIMARY KEY (P_ID,P_EVENEMENT),
    FOREIGN KEY (P_ID) REFERENCES utilisateur(ID) ON DELETE CASCADE,
    FOREIGN KEY (P_EVENEMENT) REFERENCES evenement(NUM) ON DELETE CASCADE
);

CREATE TABLE notation(
    N_ID VARCHAR(20),
    N_EVENEMENT INT,
    N_NOTE DECIMAL(2,1),
    PRIMARY KEY (N_ID, N_EVENEMENT),
    FOREIGN KEY (N_ID) REFERENCES utilisateur(ID) ON DELETE CASCADE,
    FOREIGN KEY (N_EVENEMENT) REFERENCES evenement(NUM) ON DELETE CASCADE
);




DROP PROCEDURE IF EXISTS Noter;
DROP PROCEDURE IF EXISTS AugmenterMax;
DROP PROCEDURE IF EXISTS nettoyer;
DROP PROCEDURE IF EXISTS reconduiteEvenement;

/*
Procédure permettant d'augmenter le nombre maximum de partcipant des évènement s'ils sont remplis aux 3/4
*/
DELIMITER /
CREATE PROCEDURE AugmenterMax()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE MAXIMUM INT;
    DECLARE NUMERO INT;
    DECLARE NB_PARTICIPANT INT;
    DECLARE CURSEUR CURSOR FOR SELECT NUM, MAX FROM evenement;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    OPEN CURSEUR;
    boucle : LOOP
        FETCH CURSEUR INTO NUMERO, MAXIMUM;
        IF done = 1 THEN 
        LEAVE boucle;
        END IF;
        SELECT NUMERO;
        SELECT COUNT(*) INTO NB_PARTICIPANT FROM participe WHERE P_EVENEMENT=NUMERO;
        SELECT NB_PARTICIPANT;
        IF NB_PARTICIPANT > MAXIMUM*0.75 THEN
        UPDATE evenement SET MAX=MAX*2 WHERE NUM=NUMERO;
        END IF;
    END LOOP;
    CLOSE CURSEUR;
END/

DELIMITER ;

/*
Procédure détruisant les évènement antérieur à la date passée en paramètre ainsi que les évènements ayant une note inférieure à celle passée en parametre
*/
DELIMITER /
CREATE PROCEDURE nettoyer(IN date DATE, IN noteMin INT)
BEGIN
    DECLARE mauvaiseSaisie CONDITION FOR SQLSTATE '45001';    
    IF noteMin>5 THEN
        SIGNAL mauvaiseSaisie SET MESSAGE_TEXT = "la note doit etre inferieur a 5";
    END IF;
    DELETE FROM evenement WHERE DATEDIFF(date, evenement.date)>0 ;
    DELETE FROM evenement WHERE noteMin>evenement.note;
END/

DELIMITER ;

/*Procédure qui permet de reconduire tout les évènements à l'année suivante si la note est supérieure à celle passée en paramètre */

DELIMITER /
CREATE PROCEDURE reconduiteEvenement(IN noteMin INT)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE noter INT;
    DECLARE numero INT;
    DECLARE mauvaiseSaisie CONDITION FOR SQLSTATE '45000';
    DECLARE CURSEUR CURSOR FOR SELECT NUM, NOTE FROM evenement; 
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    IF noteMin>5 THEN
        SIGNAL mauvaiseSaisie SET MESSAGE_TEXT="la note doit etre inferieur à 5";
    END IF;   
    OPEN CURSEUR;
    boucle: LOOP
        FETCH CURSEUR INTO numero, noter;
        IF done = 1 THEN
            LEAVE boucle;
        END IF;
        SELECT numero, noter;
        IF noter>noteMin THEN
            INSERT INTO evenement(E_NOM,E_ADRESSE,NOTE,E_THEME,DATE,MIN,MAX,PROPOSE_PAR)SELECT E_NOM,E_ADRESSE,NOTE,E_THEME,ADDDATE(DATE,365),MIN,MAX,PROPOSE_PAR FROM evenement WHERE NUM=numero;
        END IF;
        END LOOP;
    CLOSE CURSEUR;
END/

DELIMITER ;

/*
Definition des triggers
*/
/*
Trigger mettant à jour la note moyenne des évenements après l'insertion d'une notation
*/
DELIMITER /

CREATE TRIGGER after_insert_notation AFTER INSERT
ON notation FOR EACH ROW
BEGIN
    DECLARE X DECIMAL(2,1);
    SELECT AVG(N_NOTE) INTO X FROM notation WHERE N_EVENEMENT = NEW.N_EVENEMENT;
    UPDATE evenement
    SET NOTE = X
    WHERE evenement.NUM = NEW.N_EVENEMENT;
END/
DELIMITER ;

/*
Trigger verififant que l'on à bien assigné le type 1,2 ou 3 à un utilisateur inséré
*/
DELIMITER /
CREATE TRIGGER before_insert_utilisateur BEFORE INSERT
ON utilisateur FOR EACH ROW
BEGIN
    DECLARE mauvaisNumero CONDITION FOR SQLSTATE '45300';
    IF NEW.TYPE!=1 AND NEW.TYPE !=2 AND NEW.TYPE!=3 THEN
        SIGNAL mauvaisNumero SET MESSAGE_TEXT = "LE ROLE DOIT ETRE 1, 2 OU 3";
    END IF;
END/
DELIMITER ;

/*
Trigger vérifiant que la note inséré dans la table notation soit inférieur à 5
*/
DELIMITER /
CREATE TRIGGER before_insert_notation BEFORE INSERT
ON notation FOR EACH ROW
BEGIN
    DECLARE mauvaisNumero CONDITION FOR SQLSTATE '45400';
    IF NEW.N_NOTE>5 THEN
        SIGNAL mauvaisNumero SET MESSAGE_TEXT = "LA NOTE DOIT ETRE INFERIEUR A 5";
    END IF;
END/
DELIMITER ;

/*
Trigger vérifiant que le minimum est bien inférieur au maximum pour un évènement inséré
*/
DELIMITER /
CREATE TRIGGER before_insert_evenement BEFORE INSERT
ON evenement FOR EACH ROW
BEGIN
    DECLARE droit INT;
    DECLARE droitInsuffisant CONDITION FOR SQLSTATE '45500';
    DECLARE mauvaisNumero CONDITION FOR SQLSTATE '45400';
    SELECT TYPE INTO droit FROM utilisateur WHERE utilisateur.id = NEW.PROPOSE_PAR; 
    IF NEW.MIN>NEW.MAX THEN
        SIGNAL mauvaisNumero SET MESSAGE_TEXT = "LE MAXIMUM DOIT ETRE SUPERIEUR AU MINIMUM";
    END IF;
    IF droit<2 THEN
        SIGNAL mauvaisNumero SET MESSAGE_TEXT = "L'UTILISATEUR DOIT ETRE CONTRIBUTEUR POUR AJOUTER UN EVENEMENT";
    END IF;

END/
DELIMITER ;

/*
Trigger permettant d'incrémenter le nombre d'évènement pour le thème correspondant à l'évènement inséré
*/
DELIMITER /
CREATE TRIGGER after_insert_evenement AFTER INSERT
ON evenement FOR EACH ROW
BEGIN
    UPDATE theme
    SET TOTAL = TOTAL+1 
    WHERE NOM = NEW.E_THEME;
END/
DELIMITER ;

/*
Trigger permettant d'empecher un insertion dans participe si le nombre maximum de partiipant pour l'évènement est atteint
*/
DELIMITER /
CREATE TRIGGER before_insert_participe BEFORE INSERT
ON participe FOR EACH ROW
BEGIN
    DECLARE nb_participant INT;
    DECLARE limite INT;
    DECLARE mauvaisNumero CONDITION FOR SQLSTATE '45600';
    SELECT MAX INTO limite FROM evenement WHERE NUM=NEW.P_EVENEMENT;
    SELECT COUNT(*) INTO nb_participant FROM participe WHERE P_EVENEMENT=NEW.P_EVENEMENT;
    IF limite<nb_participant+1 THEN
        SIGNAL mauvaisNumero SET MESSAGE_TEXT = "NOMBRE MAXIMAL DE PARTICIPANT ATTEINT";
    END IF;
END/

DELIMITER ;

/*
Insertion de tuples dans la relation
*/

 INSERT INTO theme(NOM)
    VALUES
    ('sport'),
    ('decouverte'),
    ('divertissement');

INSERT INTO utilisateur
    VALUES
    ('AAA','vnj4RF2Snsxdc234nkfd45fneek2343D4GE1',3),
    ('kokokoko45','ducdsvndf676cd7Bhsjnvf',1),
    ('ADMIN','dfghjk2efghj65fgluy',3);

INSERT INTO lieu
    VALUES
    ('test',1,1),
    ('Quai Georges Clemenceau, 34250 Palavas-les-Flots',43.527962,3.931302),
    ('50 Avenue Agropolis, 34090 Montpellier',43.643718,3.880135),
    ('28 Rue des Prés 34430 Saint-Jean-de-Vedas',43.580636,3.816732);

INSERT INTO evenement(E_NOM,E_ADRESSE,NOTE,E_THEME,DATE,MIN,MAX,PROPOSE_PAR)
    VALUES
    ('Soirée Jeux','Quai Georges Clemenceau, 34250 Palavas-les-Flots',1,'divertissement','2019-12-12',1,50,'AAA'),
    ('Visite découverte','50 Avenue Agropolis, 34090 Montpellier',3,'decouverte','2020-01-16',15,90,'AAA'),
    ('Finale regional rugby féminin','28 Rue des Prés 34430 Saint-Jean-de-Vedas',5,'sport','2020-04-30',0,200,'AAA');

INSERT INTO participe
    VALUES
    ('AAA',1),
    ('kokokoko45',2),
    ('ADMIN',3);

INSERT INTO notation
    VALUES
    ('AAA',1,3),
    ('kokokoko45',2,1),
    ('ADMIN',3,4);

SELECT * FROM theme;
SELECT * FROM utilisateur;
SELECT * FROM lieu;
SELECT * FROM evenement;
SELECT * FROM participe;
SELECT * FROM notation;

