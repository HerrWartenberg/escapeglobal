/* ============================================
   GLOBAL WATCH - Escape Room Logik
   ============================================ */

// Codes pro Station (case-insensitive, ohne Bindestriche/Leerzeichen geprüft)
const CODES = {
  station1: '3214',
  station2: 'TNKG',
  station3: 'OAGLLB',
  station4: 'GVGV',
  station5: '8632',
  final: '5102'
};

// Nächste Seite nach erfolgreicher Eingabe
const NEXT_PAGE = {
  station1: 'station2.html',
  station2: 'station3.html',
  station3: 'station4.html',
  station4: 'station5.html',
  station5: 'final.html',
  final: 'success.html'
};

// Normalisiert Eingabe (Großbuchstaben, ohne Leerzeichen/Bindestriche)
function normalize(str) {
  return String(str).toUpperCase().replace(/[\s\-_]/g, '');
}

// Code prüfen
function checkCode(stationId) {
  const input = document.getElementById('code-input');
  const feedback = document.getElementById('feedback');
  const userInput = normalize(input.value);
  const correctCode = normalize(CODES[stationId]);

  if (!userInput) {
    feedback.className = 'feedback error';
    feedback.textContent = '⚠ Kein Code eingegeben.';
    return;
  }

  if (userInput === correctCode) {
    feedback.className = 'feedback success';
    feedback.textContent = '✓ Code korrekt. Weiterleitung läuft …';
    markStationDone(stationId);
    setTimeout(() => {
      window.location.href = NEXT_PAGE[stationId];
    }, 1200);
  } else {
    feedback.className = 'feedback error';
    feedback.textContent = '✗ Falscher Code. Prüft eure Lösung erneut.';
    input.value = '';
    input.focus();
  }
}

// Fortschritt im sessionStorage speichern
function markStationDone(stationId) {
  try {
    const progress = JSON.parse(sessionStorage.getItem('gw_progress') || '[]');
    if (!progress.includes(stationId)) {
      progress.push(stationId);
      sessionStorage.setItem('gw_progress', JSON.stringify(progress));
    }
  } catch (e) {
    // sessionStorage nicht verfügbar – kein Problem, dann gibt's nur keine Persistenz
  }
}

function getProgress() {
  try {
    return JSON.parse(sessionStorage.getItem('gw_progress') || '[]');
  } catch (e) {
    return [];
  }
}

// Progress-Bar rendern
function renderProgress(currentStation) {
  const trail = document.getElementById('progress-trail');
  if (!trail) return;

  const stations = ['station1', 'station2', 'station3', 'station4', 'station5', 'final'];
  const done = getProgress();

  trail.innerHTML = '';
  stations.forEach(s => {
    const div = document.createElement('div');
    div.className = 'step';
    if (done.includes(s)) div.classList.add('done');
    if (s === currentStation) div.classList.add('current');
    trail.appendChild(div);
  });
}

// TIMER
function startTimer(durationMinutes = 45) {
  let storedStart = null;
  try {
    storedStart = sessionStorage.getItem('gw_timer_start');
  } catch (e) {}

  let startTime;
  if (storedStart) {
    startTime = parseInt(storedStart, 10);
  } else {
    startTime = Date.now();
    try {
      sessionStorage.setItem('gw_timer_start', startTime.toString());
    } catch (e) {}
  }

  const timerEl = document.getElementById('timer');
  if (!timerEl) return;

  function update() {
    const elapsed = Math.floor((Date.now() - startTime) / 1000);
    const totalSec = durationMinutes * 60;
    const remaining = totalSec - elapsed;

    if (remaining <= 0) {
      timerEl.textContent = '⏱ 00:00';
      timerEl.classList.add('warning-time');
      return;
    }

    const mins = Math.floor(remaining / 60);
    const secs = remaining % 60;
    timerEl.textContent = `⏱ ${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;

    if (remaining <= 300) {
      timerEl.classList.add('warning-time');
    }
  }

  update();
  setInterval(update, 1000);
}

// Timer zurücksetzen (für Neustart)
function resetGame() {
  if (confirm('Wirklich neu starten? Aller Fortschritt und Timer werden zurückgesetzt.')) {
    try {
      sessionStorage.removeItem('gw_progress');
      sessionStorage.removeItem('gw_timer_start');
    } catch (e) {}
    window.location.href = 'index.html';
  }
}

// Enter-Taste = Code prüfen
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('code-input');
  if (input) {
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        const stationId = input.dataset.station;
        if (stationId) checkCode(stationId);
      }
    });
  }
});
