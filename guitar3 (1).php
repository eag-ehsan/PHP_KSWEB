<?php
// guitar.php - تمرین آکوردهای گیتار الکتریک با SVG ساده
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>آموزش آکوردهای گیتار الکتریک</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .container { max-width: 550px; margin: 0 auto; }
        .header {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            border: 1px solid rgba(255, 215, 0, 0.3);
        }
        .header h1 { color: #ffd700; font-size: 1.3rem; }
        .back-btn {
            background: rgba(255, 215, 0, 0.2);
            color: #ffd700;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 30px;
            font-weight: bold;
            transition: all 0.3s;
            border: 1px solid #ffd700;
        }
        .section {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            border-radius: 20px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .section-title {
            color: #ffd700;
            font-size: 1.2rem;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #ffd700;
        }
        .chords-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 10px;
        }
        .chord-btn {
            background: rgba(30, 30, 60, 0.9);
            border: 1px solid rgba(255, 215, 0, 0.3);
            color: #fff;
            padding: 12px 8px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        .chord-btn:hover, .chord-btn.active {
            background: #ffd700;
            color: #1a1a2e;
            transform: scale(1.02);
        }
        .chord-display {
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .current-chord-name {
            font-size: 2rem;
            font-weight: bold;
            color: #ffd700;
            margin-bottom: 20px;
        }
        .diagram-container {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            background: #f5f5dc;
            border-radius: 16px;
            padding: 15px;
            direction: ltr;
        }
        .fretboard-svg {
            max-width: 100%;
            height: auto;
        }
        .play-btn {
            background: #2ecc71;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
        }
        .status { margin-top: 10px; font-size: 0.75rem; color: #aaa; }
        @media (max-width: 480px) {
            .chords-grid { grid-template-columns: repeat(auto-fill, minmax(70px, 1fr)); }
            .chord-btn { padding: 10px 5px; font-size: 0.85rem; }
            .current-chord-name { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🎸 آموزش آکورد گیتار</h1>
        <a href="main.php" class="back-btn">🔙 بازگشت</a>
    </div>

    <div class="section">
        <div class="section-title">🎵 آکوردهای ماژور</div>
        <div class="chords-grid" id="major-chords"></div>
    </div>

    <div class="section">
        <div class="section-title">🎶 آکوردهای مینور</div>
        <div class="chords-grid" id="minor-chords"></div>
    </div>

    <div class="chord-display" id="chord-display" style="display: none;">
        <div class="current-chord-name" id="chord-name"></div>
        <div class="diagram-container" id="diagram-container"></div>
        <button class="play-btn" id="play-btn">🔊 پخش صدای آکورد</button>
        <div class="status" id="audio-status"></div>
    </div>
</div>

<script>
    // داده‌های آکوردها (فرمت ساده)
    const chordsData = {
        'C':  { name: 'دو ماژور (C)',  frets: [0,1,0,2,3,0], fingers: [0,1,0,2,3,0] },
        'D':  { name: 'ر ماژور (D)',  frets: [2,3,2,0,0,0], fingers: [1,3,2,0,0,0] },
        'E':  { name: 'می ماژور (E)',  frets: [0,0,1,2,2,0], fingers: [0,0,1,2,3,0] },
        'F':  { name: 'فا ماژور (F)',  frets: [1,1,2,3,3,1], fingers: [1,1,2,3,4,1] },
        'G':  { name: 'سل ماژور (G)',  frets: [3,0,0,0,2,3], fingers: [2,0,0,0,1,3] },
        'A':  { name: 'لا ماژور (A)',  frets: [0,2,2,2,0,0], fingers: [0,1,2,3,0,0] },
        'B':  { name: 'سی ماژور (B)',  frets: [2,4,4,4,2,0], fingers: [1,3,4,2,1,0] },
        'Am': { name: 'لا مینور (Am)', frets: [0,1,2,2,0,0], fingers: [0,1,2,3,0,0] },
        'Dm': { name: 'ر مینور (Dm)', frets: [1,3,2,0,0,0], fingers: [1,3,2,0,0,0] },
        'Em': { name: 'می مینور (Em)', frets: [0,0,0,2,2,0], fingers: [0,0,0,2,3,0] },
        'Fm': { name: 'فا مینور (Fm)', frets: [1,1,1,3,3,1], fingers: [1,1,1,3,4,1] },
        'Gm': { name: 'سل مینور (Gm)', frets: [3,3,3,5,5,3], fingers: [1,1,1,3,4,1] },
        'Cm': { name: 'دو مینور (Cm)', frets: [3,4,5,5,3,0], fingers: [1,2,3,4,1,0] },
        'Bm': { name: 'سی مینور (Bm)', frets: [2,3,4,4,2,0], fingers: [1,2,3,4,1,0] }
    };

    const majorChords = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
    const minorChords = ['Am', 'Dm', 'Em', 'Fm', 'Gm', 'Cm', 'Bm'];
    let currentChordKey = 'C';

    const stringNames = ['E', 'A', 'D', 'G', 'B', 'e'];

    function drawFretboard(chordKey) {
        const chord = chordsData[chordKey];
        if (!chord) return;
        
        const container = document.getElementById('diagram-container');
        const frets = chord.frets;
        const fingers = chord.fingers;
        
        const svgWidth = 300;
        const svgHeight = 180;
        const startX = 40;
        const startY = 20;
        const stringSpacing = 22;
        const fretSpacing = 35;
        
        let svg = `<svg width="${svgWidth}" height="${svgHeight}" viewBox="0 0 ${svgWidth} ${svgHeight}" xmlns="http://www.w3.org/2000/svg">`;
        
        // پس زمینه
        svg += `<rect width="100%" height="100%" fill="#f5f5dc" rx="8" ry="8"/>`;
        
        // سیم‌ها (خطوط عمودی)
        for (let i = 0; i < 6; i++) {
            const x = startX + (5 - i) * stringSpacing;
            svg += `<line x1="${x}" y1="${startY}" x2="${x}" y2="${startY + fretSpacing * 4}" stroke="#333" stroke-width="${i === 0 || i === 5 ? 1.5 : 1}" />`;
            svg += `<text x="${x - 3}" y="${startY - 5}" font-size="10" fill="#555" text-anchor="end">${stringNames[i]}</text>`;
        }
        
        // فرت‌ها (خطوط افقی)
        for (let i = 0; i <= 4; i++) {
            const y = startY + i * fretSpacing;
            svg += `<line x1="${startX - 5}" y1="${y}" x2="${startX + stringSpacing * 5 + 5}" y2="${y}" stroke="#333" stroke-width="1.5" />`;
            if (i === 0) {
                svg += `<rect x="${startX - 15}" y="${y - 8}" width="12" height="16" rx="3" fill="#333" />`;
                svg += `<text x="${startX - 9}" y="${y + 4}" font-size="10" fill="white" text-anchor="middle">${chord.frets.includes(1) && !chord.frets.includes(0) ? '1' : ''}</text>`;
            } else {
                svg += `<text x="${startX - 10}" y="${y + 4}" font-size="9" fill="#888">${i + 1}</text>`;
            }
        }
        
        // نقاط انگشت‌گذاری
        for (let i = 0; i < 6; i++) {
            const fret = frets[i];
            if (fret && fret > 0 && fret <= 4) {
                const x = startX + (5 - i) * stringSpacing;
                const y = startY + (fret - 1) * fretSpacing + fretSpacing / 2;
                const finger = fingers[i];
                svg += `<circle cx="${x}" cy="${y}" r="10" fill="${finger ? '#2ecc71' : '#3498db'}" opacity="0.8" />`;
                svg += `<text x="${x}" y="${y + 4}" font-size="12" fill="white" text-anchor="middle" font-weight="bold">${finger || '•'}</text>`;
            }
        }
        
        svg += `</svg>`;
        container.innerHTML = svg;
    }

    function buildButtons() {
        const majorContainer = document.getElementById('major-chords');
        const minorContainer = document.getElementById('minor-chords');
        
        majorContainer.innerHTML = '';
        minorContainer.innerHTML = '';
        
        majorChords.forEach(chord => {
            const btn = document.createElement('button');
            btn.className = 'chord-btn';
            btn.textContent = chord;
            btn.onclick = () => selectChord(chord);
            majorContainer.appendChild(btn);
        });
        
        minorChords.forEach(chord => {
            const btn = document.createElement('button');
            btn.className = 'chord-btn';
            btn.textContent = chord;
            btn.onclick = () => selectChord(chord);
            minorContainer.appendChild(btn);
        });
    }

    function selectChord(chordKey) {
        currentChordKey = chordKey;
        const chord = chordsData[chordKey];
        if (!chord) return;
        
        document.getElementById('chord-display').style.display = 'block';
        document.getElementById('chord-name').textContent = chord.name;
        drawFretboard(chordKey);
        
        document.querySelectorAll('.chord-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.textContent === chordKey) btn.classList.add('active');
        });
    }

    function playChordSound() {
        const chord = chordsData[currentChordKey];
        if (!chord) return;
        
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            const audioCtx = new AudioContext();
            
            const freqMap = {
                'C':  [261.63, 329.63, 392.00], 'D':  [293.66, 369.99, 440.00],
                'E':  [329.63, 414.30, 493.88], 'F':  [349.23, 440.00, 523.25],
                'G':  [392.00, 493.88, 587.33], 'A':  [440.00, 554.37, 659.25],
                'B':  [493.88, 622.25, 739.99], 'Am': [440.00, 523.25, 659.25],
                'Dm': [293.66, 349.23, 440.00], 'Em': [329.63, 392.00, 493.88],
                'Fm': [349.23, 415.30, 523.25], 'Gm': [392.00, 466.16, 587.33],
                'Cm': [261.63, 311.13, 392.00], 'Bm': [493.88, 587.33, 739.99]
            };
            
            const frequencies = freqMap[currentChordKey] || freqMap['C'];
            const statusDiv = document.getElementById('audio-status');
            statusDiv.innerHTML = '🎵 در حال پخش...';
            
            frequencies.forEach((freq) => {
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();
                oscillator.type = 'sine';
                oscillator.frequency.value = freq;
                gainNode.gain.value = 0.2;
                gainNode.gain.exponentialRampToValueAtTime(0.00001, audioCtx.currentTime + 2);
                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);
                oscillator.start();
                oscillator.stop(audioCtx.currentTime + 1.5);
            });
            
            setTimeout(() => statusDiv.innerHTML = '💡 برای شنیدن دوباره کلیک کنید', 2000);
        } catch(e) {
            document.getElementById('audio-status').innerHTML = '⚠️ مرورگر پشتیبانی نمی‌کند';
        }
    }

    buildButtons();
    selectChord('C');
    document.getElementById('play-btn').onclick = playChordSound;
</script>
</body>
</html>