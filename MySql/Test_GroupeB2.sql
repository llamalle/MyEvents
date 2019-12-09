/*
Fichier : Test_GroupeB2.sql
Auteurs :
Louis LAMALLE 21708839
Romain JAMINET 21707709
Nom du groupe : B2
*/

/*
TEST DES TRIGGERS
*/

/*(1)--Test trigger mettant a jour la note moyenne des évènements*/
SELECT "-----------------------Test numero 1-------------------------------";
SELECT note FROM evenement WHERE num=3;
INSERT INTO notation VALUES ('AAA',3,5);
SELECT note FROM evenement WHERE num=3;

/*(2)--Test trigger vérifiant la valeur du type d'un utilisateur (1,2 ou 3)*/
SELECT "-----------------------Test numero 2--------------------------------";
INSERT INTO utilisateur VALUES ('MP','GOAT',5);

/*(3)--Test trigger vérifiant que la note insérée dans la table notation soit inférieur à 5*/
SELECT "-----------------------Test numero 3---------------------------------";
INSERT INTO notation VALUES ('AAA',2,6);

/*(4)--Test trigger vérifiant que le maximum d'un évènement soit supérieur à son minimim */
SELECT "---------------------Test numero 4---------------------------------";
INSERT INTO evenement(MIN,MAX,PROPOSE_PAR,E_ADRESSE) VALUES (2,1,'AAA','test');
INSERT INTO evenement(MIN,MAX,PROPOSE_PAR,E_ADRESSE) VALUES (1,2,'kokokoko45','test');

/*(5)--Test trigger permettant d'incrémenter le nombre total d'évènement pour un thème*/
SELECT "----------------------Test numero 5--------------------------------";
SELECT total FROM theme WHERE nom='sport';
INSERT INTO evenement(E_THEME,PROPOSE_PAR,E_ADRESSE) VALUES ('sport','AAA','test');
SELECT total FROM theme WHERE nom='sport';

/*(6)--Test trigger empechant l'insertion d'une participation si le nombre maximum de participant est atteint*/
SELECT "-----------------------Test numero 6-----------------------------";
INSERT INTO evenement(num,min,max,propose_par,e_adresse) VALUES (100,0,1,'AAA','test');
INSERT INTO participe VALUES ('AAA',100);
INSERT INTO participe VALUES ('ADMIN',100);

/*
TEST DES PROCÉDURES
*/

/*(7)--Test de la procédure augmentant le nombre maximum de participant si l'évènement est plein aux 3/4*/
SELECT "-------------------------Test numero 7----------------------------";
SELECT * FROM evenement WHERE num=100;
CALL AugmenterMax();
SELECT * FROM evenement WHERE num=100;

/*(8)--Test de la procédure détruisant les évènements en fonction de la date et la note passées en paramètre*/
SELECT "--------------------------Test numero 8---------------------------";
INSERT INTO evenement(num,note,propose_par,e_adresse) VALUES (1000,0,'AAA','test');
INSERT INTO evenement(num,date,propose_par,e_adresse) VALUES (2000,'1999-08-21','AAA','test');
SELECT * FROM evenement;
CALL nettoyer('2000-01-01',2);
SELECT * FROM evenement;

/*(9)--Test de la procédure qui permet de reconduire pour l'année suivante tout les évènements ayant une note supérieur à une note donnée*/
SELECT "--------------------------Test numero 9----------------------------";
CALL reconduiteEvenement(4);
SELECT * FROM evenement;