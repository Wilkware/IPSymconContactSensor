# Toolmatic Contact Sensor (Fenster- und Türkontakt)

[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Product](https://img.shields.io/badge/Symcon%20Version-5.2%20%3E-blue.svg)](https://www.symcon.de/produkt/)
[![Version](https://img.shields.io/badge/Modul%20Version-1.2.20201219-orange.svg)](https://github.com/Wilkware/IPSymconContactSensor)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Actions](https://github.com/Wilkware/IPSymconContactSensor/workflows/Check%20Style/badge.svg)](https://github.com/Wilkware/IPSymconContactSensor/actions)

Die *Toolmatic Bibliothek* ist eine kleine Tool-Sammlung im Zusammenhang mit HomeMatic/IP Geräten.  
Hauptsächlich beinhaltet sie kleine Erweiterung zur Automatisierung von Aktoren oder erleichtert das Steuern von Geräten bzw. bietet mehr Komfort bei der Bedienung.  
  
Der Fenster- und Türkontakt reagiert entsprechend hinterlegter Verzögerungszeit und Bedingungen auf das Öffnen bzw. Schließen selbiger und führt eine Temperaturabsenkung durch.  
  
Wer die Meldungsverwaltung (Thema: [Meldungsanzeige im Webfront](https://www.symcon.de/forum/threads/12115-Meldungsanzeige-im-WebFront?highlight=Meldungsverwaltung)) kann sich über den Schaltvorgang informieren lassen.

## Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Installation](#3-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)
8. [Versionshistorie](#8-versionshistorie)

### 1. Funktionsumfang

* Überwachen von bis zu 4 Kontaktsensoren (z.B. pro Raum)
* Verzögertes Absenken der Heizung entsprechend eingestellter Zeit
* Schalten von bis zu 2 Heizkörpern (Thermostaten bzw. Ventilantrieben)
* Bedingtes Schalten in Abhängigkeit ...
  * der Ventilstellung / Ventilöffnung
  * der Differenz zwischen Aussen- und Innentemperatur
  * Wiederholtes Testen der Bedingungen nach einstellbarer Zeit
* Automatisches Aufheben der Absenkung unabhänig von Zustand der Sensoren

### 2. Voraussetzungen

* IP-Symcon ab Version 5.2
* Heizkörpersteuerung via HmIP-WTH2 oder HmIP-eTRV(-2)

### 3. Installation

* Über den Modul Store das Modul *Toolmatic Contact Sensor* installieren.
* Alternativ Über das Modul-Control folgende URL hinzufügen.  
`https://github.com/Wilkware/IPSymconContactSensor` oder `git://github.com/Wilkware/IPSymconContactSensor.git`

### 4. Einrichten der Instanzen in IP-Symcon

* Unter 'Instanz hinzufügen' ist das *Contact Sensor*-Modul (Alias: *Türkontakt* oder *Fensterkontakt*) unter dem Hersteller '(Geräte)' aufgeführt.

__Konfigurationsseite__:

Name                            | Gruppierung          | Beschreibung
------------------------------- | -------------------- | -----------------------------------------------------------------
1.Sensor                        | Kontakt-Sensoren    | Statusvariable, eines Kontaktsensors (offen/geschlossen)
2.Sensor                        | Kontakt-Sensoren    | StatusVariable, eines weiteren Kontaktsensors (offen/geschlossen)
Reaktionszeit (Verzögerung)     | Bedingtes Schalten  | Zeit zwischen Erkennen und Schalten
Checkbox Ventilöffnung          | Bedingtes Schalten  | Nur Absenken wenn gerade geheizt wird (Ventilstellung > 0%)
Positionsvariable               | Bedingtes Schalten  | Variable, welche die aktuelle Ventilposition enthält
Checkbox Temperatur             | Bedingtes Schalten  | Nur Absenken wenn Differenz (Schwellwert) zwischen Außen- und Innentemperatur eingestellten Wert überschreitet
Temeraturdifferenz              | Bedingtes Schalten  | Schwellert zwischen Außen- und Innentemperatur
Checkbox Wiederholungsintervall | Bedingtes Schalten  | Zeitraum in welchem wiederholt die eingstellten Bedingungen (Ventilposition & Temperaturdifferenz) getestet werden
Zeitspanne (Wiederholung)       | Bedingtes Schalten  | Intervall (Zeit) zwischen den Tests
Checkbox Absenkung aufheben     | Bedingtes Schalten  | Aktivierung der automatischen Aufhebung der Absenkung unabhängig vom Zustand der Sensoren
Zeitspanne (Aufhebung)          | Bedingtes Schalten  | Zeitraum nach dem die Absenkung aufgehoben werden soll
1.Heizkörper                    | Heizungssystem      | Steuerungskanal des ersten Heizungsthermostats oder -stellantriebs
2.Heizkörper                    | Heizungssystem      | Steuerungskanal des zweiten Heizungsthermostats oder -stellantriebs
Außentemperatur                 | Klimawerte          | Aktuelle Außentemperatur
Innentemperatur                 | Klimawerte          | Aktuelle Raumtemperatur
Meldungsscript                  | Meldungsverwaltung  | Skript ID des Meldungsverwaltungsscripts
Raumname                        | Meldungsverwaltung  | Text zur eindeutigen Zuordnung des Raums
Lebensdauer der Nachricht       | Meldungsverwaltung  | Wie lange so die Info angezeigt werden?

### 5. Statusvariablen und Profile

Es werden keine zusätzlichen Statusvariablen/Profile benötigt.

### 6. WebFront

Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

_Hinweis:_ Das Script 'Meldungsanzeige im Webfront' (Meldungsverwaltung) wird unterstützt.

### 7. PHP-Befehlsreferenz

Das Modul stellt keine direkten Funktionsaufrufe zur Verfügung.

### 8. Versionshistorie

v1.2.20201219

* _NEU_: 3. und 4. Kontaktsensor hinzugefügt
* _FIX_: Meldungslogik verbessert

v1.1.20201204

* _NEU_: 2. Kontaktsensor hinzugefügt
* _NEU_: Wiederholungsintervall für bedingtes Schalten hinzugefügt
* _NEU_: Zeitspanne für Aufhebung der Absenkung hinzugefügt
* _NEU_: Aliase für Modul auf Türkontakt und Fensterkontakt geändert
* _FIX_: Schaltungslogik komplett neu umgesetzt (via *WINDOW_STATE*)
* _FIX_: Zugriff auf interne Funktionen aufgehoben
* _FIX_: Meldungslogik umgebaut

v1.0.20200515

* _NEU_: Initialversion

## Entwickler

* Heiko Wilknitz ([@wilkware](https://github.com/wilkware))

## Spenden

Die Software ist für die nicht kommzerielle Nutzung kostenlos, Schenkungen als Unterstützung für den Entwickler bitte hier:

[![License](https://img.shields.io/badge/Einfach%20spenden%20mit-PayPal-blue.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8816166)

## Lizenz

[![Licence](https://licensebuttons.net/i/l/by-nc-sa/transparent/00/00/00/88x31-e.png)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
