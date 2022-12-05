# Fenster- und Türkontakt (Contact Sensor)

[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Product](https://img.shields.io/badge/Symcon%20Version-6.0%20%3E-blue.svg)](https://www.symcon.de/produkt/)
[![Version](https://img.shields.io/badge/Modul%20Version-2.0.20221204-orange.svg)](https://github.com/Wilkware/IPSymconContactSensor)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Actions](https://github.com/Wilkware/IPSymconContactSensor/workflows/Check%20Style/badge.svg)](https://github.com/Wilkware/IPSymconContactSensor/actions)

Das Modul reagiert entsprechend hinterlegter Verzögerungszeit und Bedingungen auf das Öffnen bzw. Schließen von Fenster- bzw. Türkontakten und führt eine Temperaturabsenkung durch.  
  
Wer die Meldungsverwaltung (Thema: [Meldungsanzeige im Webfront](https://www.symcon.de/forum/threads/12115-Meldungsanzeige-im-WebFront?highlight=Meldungsverwaltung)) kann sich über den Schaltvorgang informieren lassen.

## Inhaltverzeichnis

1. [Funktionsumfang](#user-content-1-funktionsumfang)
2. [Voraussetzungen](#user-content-2-voraussetzungen)
3. [Installation](#user-content-3-installation)
4. [Einrichten der Instanzen in IP-Symcon](#user-content-4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#user-content-5-statusvariablen-und-profile)
6. [WebFront](#user-content-6-webfront)
7. [PHP-Befehlsreferenz](#user-content-7-php-befehlsreferenz)
8. [Versionshistorie](#user-content-8-versionshistorie)

### 1. Funktionsumfang

* Überwachen von bis zu 4 Kontaktsensoren (z.B. pro Raum)
* Verzögertes Absenken der Heizung entsprechend eingestellter Zeit
* Schalten von bis zu 2 Heizkörpern (Thermostaten bzw. Ventilantrieben)
* Bedingtes Schalten in Abhängigkeit ...
  * der Ventilstellung / Ventilöffnung
  * der Differenz zwischen Aussen- und Innentemperatur
  * Wiederholtes Testen der Bedingungen nach einstellbarer Zeit
* Automatisches Aufheben der Absenkung unabhängig von Zustand der Sensoren

### 2. Voraussetzungen

* IP-Symcon ab Version 6.0
* Heizkörpersteuerung getestet mit HmIP-WTH2 und/oder HmIP-eTRV(-2)

### 3. Installation

* Über den Modul Store das Modul _Contact Sensor_ installieren.
* Alternativ Über das Modul-Control folgende URL hinzufügen.  
`https://github.com/Wilkware/IPSymconContactSensor` oder `git://github.com/Wilkware/IPSymconContactSensor.git`

### 4. Einrichten der Instanzen in IP-Symcon

* Unter 'Instanz hinzufügen' ist das _Contact Sensor_-Modul (Alias: _Türkontakt_ oder _Fensterkontakt_) unter dem Hersteller '(Geräte)' aufgeführt.

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

v2.0.20221204

* _NEU_: Konfigurationsformular überarbeitet und vereinheitlicht
* _NEU_: Kompatibilität auf 6.0 hoch gesetzt
* _NEU_: Meldungswesen komplett überarbeitet und erweitert
* _FIX_: Interne Bibliotheken überarbeitet und vereinheitlicht
* _FIX_: Dokumentation überarbeitet

v1.2.20201219

* _NEU_: 3. und 4. Kontaktsensor hinzugefügt
* _FIX_: Meldungslogik verbessert

v1.1.20201204

* _NEU_: 2. Kontaktsensor hinzugefügt
* _NEU_: Wiederholungsintervall für bedingtes Schalten hinzugefügt
* _NEU_: Zeitspanne für Aufhebung der Absenkung hinzugefügt
* _NEU_: Aliase für Modul auf Türkontakt und Fensterkontakt geändert
* _FIX_: Schaltungslogik komplett neu umgesetzt (via _WINDOW_STATE_)
* _FIX_: Zugriff auf interne Funktionen aufgehoben
* _FIX_: Meldungslogik umgebaut

v1.0.20200515

* _NEU_: Initialversion

## Entwickler

Seit nunmehr über 10 Jahren fasziniert mich das Thema Haussteuerung. In den letzten Jahren betätige ich mich auch intensiv in der IP-Symcon Community und steuere dort verschiedenste Skript und Module bei. Ihr findet mich dort unter dem Namen @pitti ;-)

[![GitHub](https://img.shields.io/badge/GitHub-@wilkware-181717.svg?style=for-the-badge&logo=github)](https://wilkware.github.io/)

## Spenden

Die Software ist für die nicht kommzerielle Nutzung kostenlos, über eine Spende bei Gefallen des Moduls würde ich mich freuen.

[![PayPal](https://img.shields.io/badge/PayPal-spenden-00457C.svg?style=for-the-badge&logo=paypal)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8816166)

## Lizenz

Namensnennung - Nicht-kommerziell - Weitergabe unter gleichen Bedingungen 4.0 International

[![Licence](https://img.shields.io/badge/License-CC_BY--NC--SA_4.0-EF9421.svg?style=for-the-badge&logo=creativecommons)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
