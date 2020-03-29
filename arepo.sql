# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: blubber.it (MySQL 5.7.29-0ubuntu0.16.04.1)
# Datenbank: arepo
# Erstellt am: 2020-03-29 07:37:03 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Export von Tabelle cards
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cards`;

CREATE TABLE `cards` (
  `card_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci,
  `times` int(11) DEFAULT '1',
  PRIMARY KEY (`card_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `cards` WRITE;
/*!40000 ALTER TABLE `cards` DISABLE KEYS */;

INSERT INTO `cards` (`card_id`, `name`, `description`, `times`)
VALUES
	(1,'Improvisation','Du kannst statt eines Talentes für eine Probe ein ganz anderes Talent einsetzen, musst aber erklären und beschreiben, wie es dazu gekommen ist, dass Du jetzt eine ganz andere Fähigkeiten nutzt.',3),
	(2,'Konzentration','Du kannst für die nächste Probe zwei Würfel zusätzlich würfeln.',54),
	(3,'PlanB','Dir gelingt eine Verteidigungsprobe auf jeden Fall.',2),
	(4,'Ablenkung','Der Spielleiter muss eine beliebige schon gewürfelte Probe wiederholen.',2),
	(5,'Multitasking','Der Charakter kann anstatt einer Aktion in dieser Runde in derselben Zeit zwei Aktionen durchführen, die aber unterschiedlich sein müssen.',2),
	(6,'Wille','Wirf eine andere Karte ab. Du kannst für die nächste Probe fünf Würfel zusätzlich würfeln. Falls Du keine Karte abwerfen kannst, kannst Du diese Karte nicht spielen.',5),
	(7,'GuterHinweis','Eine andere Person in Sprechreichweite kann zwei Würfel mehr für die nächste Probe einsetzen.',6),
	(8,'MagischeEingebung','Du kannst Dir ein magisches Talent oder eine magische Spezialisierung ausdenken und es jetzt einsetzen, als wenn Du Talent 3 darin hättest.',1),
	(9,'Reroll','Würfel einen von Dir geworfenen Würfel neu.',3),
	(10,'NiemalsAufgeben','Ignoriere für einen Wurf Deinen kompletten Schaden und Müdigkeit.',3),
	(11,'Unfassbar','Erhöhe einen von Dir geworfenen Würfel um 1. So kann aus einer 1 eine 2 oder aus einer 6 sogar eine 7 werden.',1),
	(12,'NurEineFleischwunde','Wenn Du gerade nicht in einem Kampf bist, heile zwei Schaden.',2),
	(13,'Provokation','Beim nächsten Wurf Deines Gegners zählen 2en wie einsen, löschen also auch jeweils einen höchsten Würfel und sich selbst.',2),
	(14,'Irritation','Ein Gegner hat für den nächsten Wurf 2 Würfel weniger.',2),
	(15,'Koordinationstalent','An einer Gruppenprobe können auch Personen teilnehmen, deren Kenntnisstand vom Gruppenleiter abweichen, wenn Du der Gruppenleiter bist.',1),
	(16,'Geistesblitz','Du kommst nicht weiter, erinnerst Dich an eine Sache nicht mehr? Der Spielleiter muss Dir auf die Sprünge helfen.',1),
	(17,'GuteLektion','Diese Karte kann nur zum Lernen verwendet werden und sie zählt dann wie zwei Karten.',2),
	(18,'AhaMoment','Du kannst mit dieser Karte (und anderen) jetzt sofort eine Fähigkeit oder Eigenschaft steigern. Die Kosten zum Steigern sind normal. Du darfst das jederzeit einsetzen - sogar im hektischen Kampfgemenge. Diese Karte zählt aber nicht als bezahlte Karmakarte.',2),
	(19,'IchGlaubAnDich','Du kannst eine andere Karte an einen Mitspieler abgeben, der Dir danach eine Karte zurück geben muss. Der andere Spieler muss nicht unbedingt in Rufreichweite sein.',2),
	(20,'FehlendesBauteil','Wenn Du etwas reparierst und Dir fehlt dazu ein Bauteil, so findest Du es plötzlich in einer Ecke. „Man, ich könnte schwören, genau da hatte ich eben noch danach gesucht.“ Das fehlende Bauteil sollte kein superteures magisches Artefakt sein und auch kein Glas Wasser in einer Wüste. Es sollte wirklich einfach ein Bauteil sein, das realistischerweise an Deinem Ort hätte sein können; und jetzt ist es da.',1),
	(21,'ImRichtigenMoment','Du kannst - als wärest Du selbst Spielleiter - bestimmen, was ein Nichtspielercharakter, der kein unmittelbarer Bösewicht ist (aber vielleicht ist er ein gedankenloser Scherge), in dieser Situation tut. Er könnte zum Beispiel eine Tür öffnen oder ein Fenster. Dies ist keine Gedankenmanipulation. Was der Charakter tut, muss aus dessen Sicht Sinn ergeben und Du musst es begründen können.',2),
	(22,'PassAuf','Wende einer Deiner Karten auf einen Freund in Rufreichweite an, als hätte dieser die Karte benutzt. Ziehe eine Karte.',2),
	(23,'BitteBitte','Du forderst den Spielleiter auf, dass er euch als Gruppe helfen soll. Wie er das tut, ist seine Sache. Aber er muss euch irgendwie helfen.',1),
	(24,'Huch','Du wachst jetzt auf. Das kann auch aus einer Narkose sein oder wenn Du ohnmächtig bist oder gar aus einem magischen Zauberschlaf (nicht aber aus Kälteschlaf, wo ein alleiniges Aufwachen physisch und chemisch ausgeschlossen ist).',1),
	(25,'DasWarKnapp','Du kannst eine gewürfelte Probe sofort wiederholen mit derselben Würfelzahl.',4),
	(26,'LiebeAufDenErstenBlick','Ein Tier fasst sofort Zutrauen zu Dir. Es wird Dich nicht angreifen und wenn es weiß, was Du möchtest, würde es Dir sogar helfen.',1),
	(27,'NaGut','Eine Lügen-Probe gelingt Dir auf jeden Fall ohne zu würfeln. Dein Gegenüber glaubt Dir einfach, sofern das, was Du sagst, nicht offensichtlich unmöglich ist. Dieser Effekt gilt überdies auch, wenn Du gar nicht lügst, sondern die Wahrheit sagst. Manchmal ist die Wahrheit ja unfassbarer als eine Lüge. Mit dieser Karte ist alles glaubhaft.',2),
	(28,'Anfängerglück','Dir gelingt etwas, das Dein Charakter in seinem Leben noch nie gemacht hat, ohne dass Du dafür würfeln müsstest. Falls ein Wert verlangt wird, hast Du praktisch den Wert 15 gewürfelt, ohne dafür gewürfelt zu haben. Gilt auch trotz eventueller Verwundungen.',2),
	(29,'Verzweiflungstat','Wirf bis zu zwei Karten ab und ziehe genau so viele neue Karten nach.',2),
	(30,'Vorgemogelt','Du kannst Deine nächste Aktion vorziehen und jetzt sofort ausführen, bevor jemand anderes reagieren kann.',3);

/*!40000 ALTER TABLE `cards` ENABLE KEYS */;
UNLOCK TABLES;


# Export von Tabelle groupchats
# ------------------------------------------------------------

CREATE TABLE `groupchats` (
  `chat_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `title` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`chat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Export von Tabelle playercards
# ------------------------------------------------------------

CREATE TABLE `playercards` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `card_id` int(11) DEFAULT NULL,
  `player_id` int(11) DEFAULT NULL,
  `chat_id` int(11) DEFAULT NULL,
  `mkdate` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Export von Tabelle privatechats
# ------------------------------------------------------------

CREATE TABLE `privatechats` (
  `player_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `chat_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
