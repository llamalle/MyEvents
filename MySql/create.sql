CREATE DATABASE myevents CHARACTER SET 'utf8';

USE myevents;
DROP TABLE utilisateur;
DROP TABLE evenement;
DROP TABLE lieu;

CREATE TABLE utilisateur(
    ID VARCHAR(20) PRIMARY KEY,
    MDP VARCHAR(20),
    TYPE VARCHAR(20) 
);
/* ajouter un trigger pour limiter le type a adlinistrateur contributeur ou visteur*/

CREATE TABLE lieu(
    L_ADRESSE VARCHAR(100) PRIMARY KEY,
    LATITUDE DECIMAL(35,30),
    LONGITUDE DECIMAL(35,30)    
);

CREATE TABLE evenement(
    NUM INTEGER PRIMARY KEY,
    NOM VARCHAR(20),
    E_ADRESSE VARCHAR(100),
    FOREIGN KEY (E_ADRESSE) REFERENCES lieu(L_ADRESSE)
)

