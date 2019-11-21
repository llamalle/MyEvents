CREATE DATABASE myevents CHARACTER SET 'utf8';

USE myevents;
DROP TABLE participe;
DROP TABLE evenement;
DROP TABLE lieu;
DROP TABLE utilisateur;

CREATE TABLE utilisateur(
    ID VARCHAR(20) PRIMARY KEY,
    MDP VARCHAR(70),
    TYPE TINYINT(1) 
);
/* ajouter un trigger pour limiter le type a adlinistrateur contributeur ou visteur*/

CREATE TABLE lieu(
    L_ADRESSE VARCHAR(100) PRIMARY KEY,
    LATITUDE DECIMAL(35,6),
    LONGITUDE DECIMAL(35,6)    
);

CREATE TABLE evenement(
    NUM INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    NOM VARCHAR(100),
    E_ADRESSE VARCHAR(100),
    THEME VARCHAR(20),
    NOTE TINYINT(1),
    DATE DATE,
    MIN INT,
    MAX INT,
    FOREIGN KEY (E_ADRESSE) REFERENCES lieu(L_ADRESSE)
);

CREATE TABLE participe(
    P_ID VARCHAR(20),
    P_EVENEMENT INT,
    PRIMARY KEY (P_ID,P_EVENEMENT),
    FOREIGN KEY (P_ID) REFERENCES utilisateur(ID),
    FOREIGN KEY (P_EVENEMENT) REFERENCES evenement(NUM)
);

INSERT INTO utilisateur
    VALUES
    ('AAA','vnj4RF2Snsxdc234nkfd45fneek2343D4GE1',3),
    ('kokokoko45','d,cdsvndf676cd7Bhsjnvf',2),
    ('ADMIN','dfghjk2efghj65fgluy',1);

INSERT INTO lieu
    VALUES
    ('Quai Georges Clemenceau, 34250 Palavas-les-Flots',43.527962,3.931302),
    ('50 Avenue Agropolis, 34090 Montpellier',43.643718,3.880135),
    ('28 Rue des Prés 34430 Saint-Jean-de-Vedas',43.580636,3.816732);

INSERT INTO evenement(NOM,E_ADRESSE,NOTE,THEME,DATE,MIN,MAX)
    VALUES
    ('Soirée Jeux','Quai Georges Clemenceau, 34250 Palavas-les-Flots',1,'divertissement','2019-12-12',50,500),
    ('Visite découverte','50 Avenue Agropolis, 34090 Montpellier',3,'decouverte','2020-01-16',15,90),
    ('Finale regional rugby féminin','28 Rue des Prés 34430 Saint-Jean-de-Vedas',5,'sport','2020-04-30',0,200);

INSERT INTO participe
    VALUES
    ('AAA',1),
    ('kokokoko45',2),
    ('ADMIN',3);

