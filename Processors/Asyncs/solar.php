<?php
/** -------------------------------------------------------------------------------------------------------------------- ** 
/** -------------------------------------------------------------------------------------------------------------------- ** 
/** ---																																					--- **
/** --- 											-----------------------------------------------											--- **
/** --- {}--- **
/** --- 											-----------------------------------------------											--- **
/** ---																																					--- **
/** ---		TAB SIZE			: 3																													--- **
/** ---																																					--- **
/** ---		AUTEUR			: Nicolas DUPRE																									--- **
/** ---																																					--- **
/** ---		RELEASE			: xx.xx.2017																										--- **
/** ---																																					--- **
/** ---		APP_VERSION		: 1.3.1.0																											--- **
/** ---																																					--- **
/** ---		FILE_VERSION	: 1.0 NDU																											--- **
/** ---																																					--- **
/** ---																																					--- **
/** --- 														-----------------------------														--- **
/** --- 															{ C H A N G E L O G } 															--- **
/** --- 														-----------------------------														--- **	
/** ---																																					--- **
/** ---																																					--- **
/** ---		VERSION 1.0 : xx.xx.2017 : NDU																									--- **
/** ---		------------------------------																									--- **
/** ---			- Première release																												--- **
/** ---																																					--- **
/** -------------------------------------------------------------------------------------------------------------------- **
/** -------------------------------------------------------------------------------------------------------------------- **

	Objectif du script :
	---------------------
	
	Description fonctionnelle :
	----------------------------
	
		Pour UTC, l'offset utilisé est GTM, car UTC & GMT ont tout deux la même référence geographique historique.
		UTC (Universal Time Coordinate) corresponad à une durée moyenne de la rotation de la terre
		GMT (Greenwich Mean Time) correspond à l'heure astronimique (plus précis en fin de compte)
	
/** -------------------------------------------------------------------------------------------------------------------- **
/** -------------------------------------------------------------------------------------------------------------------- **
/** ---																																					--- **
/** ---													PHASE 1 - INITIALISATION DU SCRIPT													--- **
/** ---																																					--- **
/** -------------------------------------------------------------------------------------------------------------------- **
/** -------------------------------------------------------------------------------------------------------------------- **
/** > Chargement des Paramètres **/
	require_once "../../Setups/setup.params.php";

/** > Ouverture des SESSIONS Globales **/
/** > Chargement des Classes **/

/** > Chargement des Configs **/
	require_once "../../Setups/setup.timezone.php";

/** > Chargement des Fonctions **/
	require_once "../../Processors/Functions/solstices.php";

/** -------------------------------------------------------------------------------------------------------------------- **
/** -------------------------------------------------------------------------------------------------------------------- **
/** ---																																					--- **
/** ---												PHASE 2 - CONTROLE DES AUTORISATIONS													--- **
/** ---																																					--- **
/** -------------------------------------------------------------------------------------------------------------------- **
/** -------------------------------------------------------------------------------------------------------------------- **/
/** -------------------------------------------------------------------------------------------------------------------- **
/** -------------------------------------------------------------------------------------------------------------------- **
/** ---																																					--- **
/** ---												PHASE 3 - INITIALISAITON DES DONNEES													--- **
/** ---																																					--- **
/** -------------------------------------------------------------------------------------------------------------------- **
/** -------------------------------------------------------------------------------------------------------------------- **/
/** > Lister les constantse utiles du script (aucune valeur fonctionnelle) **/
	GEO_EARTH_TILT;	// Angle de rotation de la terre (PHI Φ)
	GEO_LAT_DD;			// Latitude (epsilon ε)
	GEO_LNG_DD;			// Longitude (lambda λ)
	TIMEZONE;

/** > Déclaration des constantes locales **/
	const C = 360;	// INTEGER	:: circumference
	const Td = 24;	// INTEGER	:: Length of day in hour

/** > Déclaration des variables **/
	$doy;				// INTEGER	:: Day Of the Year
	$diy;				// INTEGER	:: Days In the Year
	$dos;				//	INTEGER	:: Day Of Solstice
	$radians_coef;	// FLOAT		:: Conversion Degree ° to Radians
	$Tutc;			// INTEGER	:: L'heure observé
	$elevation;		// FLOAT		:: Angle d'élévation du soleil (PSI Ψ)
	$declination;	// FLOAT		:: Angle de déclinaison du soleil (DELTA δ)


/** > Initialisation des variables **/
	$doy = date("z", time()) + 1;// car z entre 0 et 364
	$diy = (date("L", time())) ? 366 : 365;
	$dos = date("z", solstices(null, GEO_LAT_DD, GEO_LNG_DD, 0, TIMEZONE)["summer"]) + 1;// car z entre 0 et 364
	//$Tutc = $_TIME_OFFSET / 3600;
	$radians_coef = pi() / 180;
	
	
/** > Déclaration et Intialisation des variables pour le moteur (référence) **/
/** -------------------------------------------------------------------------------------------------------------------- **
/** -------------------------------------------------------------------------------------------------------------------- **
/** ---																																					--- **
/** ---									PHASE 4 - EXECUTION DU SCRIPT DE TRAITEMENT DE DONNEES										--- **
/** ---																																					--- **
/** -------------------------------------------------------------------------------------------------------------------- **
/** -------------------------------------------------------------------------------------------------------------------- **/
/** 1. Calculer la déclinaison du soleil  **/
/*
 *              |  C ( doy - dos ) |
 *  δ = Φ * cos |  --------------- |
 *              |       diy        |
 *
 */
	$declination = GEO_EARTH_TILT * cos( $radians_coef * (C * ($doy - $dos) ) / ($diy) );


/** 2. Calculer l'heure actuelle **/
$Tutc = time() - $_TIME_OFFSET;
$Tutc = date("H", $Tutc) + (date("i", $Tutc) / 60) + (date("s", $Tutc) / 3600);


/** 3. Calculer l'élevation du soleil **/
/*
 *                                                   |  C * Tutc       |
 *  sin(Ψ) = sin(ε) * sin(δ) - cos(ε) * cos(δ) * cos |  --------  - λ  |
 *                                                   |    Td           |
 *
 *                                                               |  C * Tutc       |
 *  sin-1(sin(Ψ)) = sin-(sin(ε) * sin(δ) - cos(ε) * cos(δ) * cos |  --------  - λ  | )
 *                                                               |    Td           |
 *
 *                                                    |  C * Tutc       |
 *  Ψ = sin-1(sin(ε) * sin(δ) - cos(ε) * cos(δ) * cos |  --------  - λ  | )
 *                                                    |    Td           |
 *
 */
	$elevation = asin( 
		(sin(GEO_LAT_DD * $radians_coef ) * sin($declination * $radians_coef))
		-
		(
			cos(GEO_LAT_DD * $radians_coef) * cos($declination * $radians_coef) 
			* 
			cos(
				((C * $Tutc * $radians_coef) / Td)
				- (GEO_LNG_DD * $radians_coef)
			)
		)
	) * (180 / pi());


/** Affichage **/
echos(
	"TILT = ".GEO_EARTH_TILT,
	"CIRCUM = ".C,
	"DayOfYear = $doy",
	"DayOfSolstice = $dos",
	"DayInYear = $diy",
	"Declination = $declination",
	"Elevation = $elevation"
);

















/** -------------------------------------------------------------------------------------------------------------------- **
/** -------------------------------------------------------------------------------------------------------------------- **
/** ---																																					--- **
/** ---											PHASE 5 - GENERATION DES DONNEES DE SORTIE												--- **
/** ---																																					--- **
/** -------------------------------------------------------------------------------------------------------------------- **
/** -------------------------------------------------------------------------------------------------------------------- **/
/** -------------------------------------------------------------------------------------------------------------------- **
/** -------------------------------------------------------------------------------------------------------------------- **
/** ---																																					--- **
/** ---												PHASE 6 - AFFICHER LES SORTIES GENEREE													--- **
/** ---																																					--- **
/** -------------------------------------------------------------------------------------------------------------------- **
/** -------------------------------------------------------------------------------------------------------------------- **/
/** > Création du moteur **/
/** > Configuration du moteur **/
/** > Envoie des données **/
/** > Execution du moteur **/

?>