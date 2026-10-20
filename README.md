# cluuub

### Vereinsinformationen einfach auf eurer WordPress-Website zeigen

Das **cluuub Embed** verbindet eure Website mit den freigegebenen öffentlichen Informationen eures Vereins in cluuub. Fügt Inhalte über den Gutenberg-Block oder einen Shortcode ein – ohne HTML-Kenntnisse und ohne manuelle Pflege einzelner Kursdaten.

<p align="center">
  <img src="assets/readme-preview.svg" alt="Vorschau einer cluuub-Einbettung mit Beispielangeboten und Einstellungen" width="900">
</p>

> **Hinweis:** Die Abbildung ist eine eigens erstellte Produktillustration mit Demo-Inhalten. Sie bildet keine echte WordPress-Installation ab.

[Hilfe & Support auf cluuub.app](https://cluuub.app/hilfe) · [Produktumfang](docs/PRODUCT-SCOPE.md) · [API-Vertrag](docs/API-CONTRACT.md) · [Sicherheit & Datenschutz](docs/SECURITY.md)

## Was das Plugin kann

- **Gutenberg-Block:** Vereinskennung, Inhaltstyp, Anzahl und Suchfilter im Editor einstellen.
- **Shortcode:** Inhalte auch in klassischen Editoren und kompatiblen Page Buildern einfügen.
- **Fünf öffentliche Inhaltstypen:** Sportangebote, Trainer, Sportstätten, Ansprechpersonen und Sponsoren.
- **Serverseitiger Abruf:** WordPress ruft die öffentliche cluuub-API ab; der Browser eurer Website-Besucher verbindet sich für die Anzeige nicht direkt mit cluuub.
- **Kurzer Cache:** Antworten werden fünf Minuten in WordPress zwischengespeichert.
- **Responsive Darstellung:** Die Karten passen sich schmalen und breiten Ansichten an.

## Installation

1. Ladet das Plugin als ZIP aus dem Repository herunter: **Code → Download ZIP**.
2. Öffnet im WordPress-Admin **Plugins → Installieren → Plugin hochladen** und wählt die ZIP-Datei aus.
3. Aktiviert **cluuub Embed**.
4. Öffnet **Einstellungen → cluuub Embed**. Die Standardadresse `https://cluuub.app` ist bereits eingetragen. Ändert sie nur, wenn euer Verein eine andere cluuub-Umgebung nutzt.
5. Fügt den Gutenberg-Block **cluuub Einbettung** ein oder verwendet einen Shortcode.

<p align="center">
  <img src="assets/readme-setup.svg" alt="Drei Schritte: Plugin installieren, Vereinskennung eintragen und Einbettung veröffentlichen" width="780">
</p>

## Gutenberg-Block verwenden

Sucht im Blockeditor nach **cluuub Einbettung** und legt fest:

- **Club slug:** die öffentliche Vereinskennung aus der cluuub-Adresse, zum Beispiel `sv-halle`;
- **Inhalt:** `angebote`, `trainer`, `hallen`, `vorstand` oder `sponsoren`;
- **Maximale Einträge:** 1 bis 50;
- **Suchfilter:** optionaler Begriff, zum Beispiel `Kinder`.

Der Editor zeigt eine Vorschau der Einstellungen. Die aktuellen Vereinsdaten werden auf der veröffentlichten Seite serverseitig geladen.

## Shortcode verwenden

Fügt diesen Shortcode in einen Shortcode-Block oder ein kompatibles Page-Builder-Element ein:

```text
[cluuub club="sv-halle" type="angebote"]
```

Optionale Attribute:

```text
[cluuub club="sv-halle" type="trainer" limit="8" filter="Jugend"]
```

| Attribut | Werte | Standard |
|---|---|---|
| `club` | Vereinskennung aus Kleinbuchstaben, Zahlen und Bindestrichen | erforderlich |
| `type` | `angebote`, `trainer`, `hallen`, `vorstand`, `sponsoren` | `angebote` |
| `limit` | 1 bis 50 | 12 |
| `filter` | Freitext, optional | leer |

## Datenschutz kurz erklärt

Bei einem Cache-Miss sendet der **WordPress-Server** eine HTTPS-Anfrage an die konfigurierte cluuub-Umgebung. Dabei erhält cluuub die Netzwerkadresse des Servers und die angefragte Vereinskennung. Die Antwort wird bis zu fünf Minuten in WordPress zwischengespeichert. Der Browser des Seitenbesuchers ruft die cluuub-API für dieses Widget nicht direkt auf.

Das Plugin ruft nur öffentliche Showcase-Daten ab. Es überträgt keine Mitgliederdaten aus WordPress an cluuub und enthält in dieser Version keine Formulare zum Absenden persönlicher Daten. Website-Betreiber sollten den Server-zu-Server-Abruf in ihrer Datenschutzerklärung berücksichtigen. Details stehen in [Sicherheit & Datenschutz](docs/SECURITY.md).

## Aktueller Funktionsumfang

Neuigkeiten sind noch nicht als Widget verfügbar. Der API-Vertrag muss dafür zunächst sicherstellen, dass Nachrichten ausschließlich dem angefragten Verein zugeordnet sind. Probetrainings- und Buchungsformulare, Mitglieder-Synchronisierung und Analyse-Tracking sind ebenfalls nicht Teil dieser Plugin-Version.

## Entwicklung und Tests

Voraussetzungen: Node.js für die statischen Prüfungen; PHP 7.4 oder neuer für PHP-Syntax- und Smoke-Tests.

```bash
npm test
php -l cluuub.php
php tests/php-smoke.php
```

Der GitHub-Actions-Workflow führt die statischen Prüfungen, den PHP-Syntaxcheck und den PHP-Smoke-Test bei Pushes und Pull Requests aus.

## Lizenz

GPL-2.0-or-later. Siehe [LICENSE](LICENSE).
