# GLOBAL WATCH – Escape Room „Globalisierung"

Ein interaktiver HTML-Escape-Room für den Erdkunde-Unterricht in Klasse 10.
Dauer: 45 Minuten · Teams: 4–5 SuS

## Inhalt des Pakets

| Datei | Zweck |
|-------|-------|
| `index.html` | Startseite mit Story-Einführung |
| `station1.html` – `station5.html` | Die fünf Rätsel-Stationen |
| `final.html` | Finalrätsel (Türcode) |
| `success.html` | Erfolgsseite mit Auflösung |
| `style.css` | Gemeinsame Gestaltung (Detective-Noir-Look) |
| `script.js` | Timer, Code-Validierung, Fortschritt |
| `LEHRERHANDREICHUNG.md` | Setup, Zeitplan, Lösungen, Differenzierung |

## So starten die SuS

Alle SuS rufen denselben Link auf (z. B. den GitHub-Pages-Link, siehe unten).
Beim Klick auf „Recherche starten" beginnt der 45-Minuten-Timer im Browser.
Nach jeder korrekten Code-Eingabe geht es automatisch zur nächsten Station.
Tipps gibt es pro Station als ausklappbare Hinweis-Umschläge (3 Stufen).

## Lokal testen

Einfach `index.html` doppelklicken — läuft komplett offline im Browser.

## Auf GitHub Pages veröffentlichen (kostenlos, ca. 3 Minuten)

1. Auf [github.com](https://github.com) einloggen (kostenloser Account reicht).
2. Oben rechts auf **„+" → „New repository"** klicken.
3. Repository-Name: z. B. `escape-room-globalisierung`. **„Public"** auswählen. Auf **„Create repository"** klicken.
4. Auf der nächsten Seite: **„uploading an existing file"** anklicken.
5. Alle Dateien aus diesem ZIP (entpackt!) per Drag & Drop in das Browser-Fenster ziehen.
   **Wichtig:** Nicht den Ordner ziehen, sondern die *einzelnen Dateien* aus dem entpackten Ordner.
6. Unten auf **„Commit changes"** klicken.
7. Im Repo oben auf **„Settings"** → links **„Pages"**.
8. Bei *„Branch"* **„main"** auswählen, Ordner **„/ (root)"** lassen, **„Save"** klicken.
9. Nach 1–2 Minuten erscheint oben der Link:
   `https://DEIN-NUTZERNAME.github.io/escape-room-globalisierung/`
10. Diesen Link an die SuS verteilen — fertig!

## Lösungen auf einen Blick

| Station | Code |
|---------|------|
| 1 – Welthandel | `3214` |
| 2 – TNK | `TNKG` |
| 3 – Wertschöpfungskette | `OAGLLB` |
| 4 – Gewinner/Verlierer | `GVGV` |
| 5 – SDGs | `8632` |
| **Final (Türcode)** | **`5102`** |

Ausführliche Lösungen, Hinweise und Differenzierung: siehe `LEHRERHANDREICHUNG.md`.

## Anpassen

Codes lassen sich oben in `script.js` ändern (`CODES`-Objekt). Texte in den HTML-Dateien direkt bearbeiten.
