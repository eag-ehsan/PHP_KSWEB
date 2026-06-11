<?php
// guitar.php - تمرین آکوردهای گیتار الکتریک با فرتبورد حرفه‌ای
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
            overflow-x: auto;
        }
        .fretboard-svg {
            max-width: 100%;
            height: auto;
            font-family: monospace;
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
    // ========== داده‌های آکوردها (فرمت: frets از سیم 6 تا 1) ==========
    const chordsData = {
        // ماژورها
        'C':  { name: 'دو ماژور (C)',  frets: [0, 1, 0, 2, 3, 0], fingers: [0, 1, 0, 2, 3, 0], mute: [0,0,0,0,0,0] },
        'D':  { name: 'ر ماژور (D)',  frets: [0, 0, 0, 2, 3, 2], fingers: [0, 0, 0, 1, 3, 2], mute: [1,1,1,0,0,0] },
        'E':  { name: 'می ماژور (E)',  frets: [0, 2, 2, 1, 0, 0], fingers: [0, 3, 2, 1, 0, 0], mute: [0,0,0,0,0,0] },
        'F':  { name: 'فا ماژور (F)',  frets: [1, 3, 3, 2, 1, 1], fingers: [1, 3, 4, 2, 1, 1], mute: [0,0,0,0,0,0] },
        'G':  { name: 'سل ماژور (G)',  frets: [3, 2, 0, 0, 0, 3], fingers: [2, 1, 0, 0, 0, 3], mute: [0,0,0,0,0,0] },
        'A':  { name: 'لا ماژور (A)',  frets: [0, 2, 2, 2, 0, 0], fingers: [0, 2, 3, 1, 0, 0], mute: [1,0,0,0,0,0] },
        'B':  { name: 'سی ماژور (B)',  frets: [2, 4, 4, 4, 2, 0], fingers: [1, 3, 4, 2, 1, 0], mute: [0,0,0,0,0,1] },
        // مینورها
        'Am': { name: 'لا مینور (Am)', frets: [0, 2, 2, 2, 0, 0], fingers: [0, 2, 3, 1, 0, 0], mute: [1,0,0,0,0,0] },
        'Dm': { name: 'ر مینور (Dm)', frets: [0, 0, 0, 2, 3, 1], fingers: [0, 0, 0, 2, 3, 1], mute: [1,1,1,0,0,0] },
        'Em': { name: 'می مینور (Em)', frets: [0, 2, 2, 0, 0, 0], fingers: [0, 3, 2, 0, 0, 0], mute: [0,0,0,0,0,0] },
        'Fm': { name: 'فا مینور (Fm)', frets: [1, 3, 3, 1, 1, 1], fingers: [1, 3, 4, 1, 1, 1], mute: [0,0,0,0,0,0] },
        'Gm': { name: 'سل مینور (Gm)', frets: [3, 5, 5, 3, 3, 3], fingers: [1, 3, 4, 1, 1, 1], mute: [0,0,0,0,0,0] },
        'Cm': { name: 'دو مینور (Cm)', frets: [3, 5, 5, 5, 3, 0], fingers: [1, 3, 4, 2, 1, 0], mute: [0,0,0,0,0,1] },
        'Bm': { name: 'سی مینور (Bm)', frets: [2, 4, 4, 4, 2, 0], fingers: [1, 3, 4, 2, 1, 0], mute: [0,0,0,0,0,1] }
    };

    const majorChords = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
    const minorChords = ['Am', 'Dm', 'Em', 'Fm', 'Gm', 'Cm', 'Bm'];
    const stringNames = ['E', 'A', 'D', 'G', 'B', 'e'];
    let currentChordKey = 'C';





    // ========== رسم فرتبورد حرفه‌ای با SVG (اصلاح شده با فاصله مناسب) ==========
    function drawProfessionalFretboard2(chordKey) {
    const chord = chordsData[chordKey];
    if (!chord) return;
    
    const frets = chord.frets;
    const fingers = chord.fingers;
    const mute = chord.mute;
    
    const svgWidth = 480;
    const svgHeight = 520;
    const startX = 70;
    const startY = 65;        // افزایش یافته برای فضای بالایی بیشتر
    const stringSpacing = 44;
    const fretSpacing = 54;
    const numFrets = 5;
    
    let svg = `<svg class="fretboard-svg" width="${svgWidth}" height="${svgHeight}" viewBox="0 0 ${svgWidth} ${svgHeight}" xmlns="http://www.w3.org/2000/svg">`;
    
    // پس زمینه
    svg += `<rect width="100%" height="100%" fill="#f5f5dc" rx="12" ry="12"/>`;
    
    // خطوط فرت (افقی)
    for (let i = 0; i <= numFrets; i++) {
        const y = startY + i * fretSpacing;
        svg += `<line x1="${startX - 12}" y1="${y}" x2="${startX + stringSpacing * 5 + 25}" y2="${y}" stroke="#333" stroke-width="${i === 0 ? 4 : 2}" />`;
        
        if (i > 0 && i < 5) {
            svg += `<text x="${startX - 20}" y="${y + 7}" font-size="13" fill="#666" text-anchor="end" font-weight="bold">${i}</text>`;
        }
    }
    
    // خط پایانی فرت 5 (نوار جداکننده)
    const lastFretY = startY + fretSpacing * (numFrets - 1);
    svg += `<line x1="${startX - 12}" y1="${lastFretY}" x2="${startX + stringSpacing * 5 + 25}" y2="${lastFretY}" stroke="#333" stroke-width="3" />`;
    
    // سیم‌ها (عمودی) و نام سیم‌ها در بالا
    for (let i = 0; i < 6; i++) {
        const x = startX + (5 - i) * stringSpacing;
        svg += `<line x1="${x}" y1="${startY}" x2="${x}" y2="${startY + fretSpacing * (numFrets - 1)}" stroke="#333" stroke-width="${i === 0 || i === 5 ? 2.5 : 1.8}" />`;
        // نام سیم در بالا (با فاصله بیشتر از دایره سبز)
        svg += `<text x="${x}" y="${startY - 28}" font-size="14" fill="#555" text-anchor="middle" font-weight="bold">${stringNames[i]}</text>`;
    }
    
    // نقاط انگشت‌گذاری (دایره‌های مشکی پررنگ با اعداد سفید)
    for (let i = 0; i < 6; i++) {
        const fret = frets[i];
        if (fret > 0 && fret <= numFrets) {
            const x = startX + (5 - i) * stringSpacing;
            const y = startY + (fret - 1) * fretSpacing + fretSpacing / 2;
            const finger = fingers[i];
            
            svg += `<circle cx="${x}" cy="${y}" r="16" fill="#1a1a2e" stroke="#ffd700" stroke-width="2.5" />`;
            svg += `<text x="${x}" y="${y + 6}" font-size="16" fill="#ffd700" text-anchor="middle" font-weight="bold">${finger}</text>`;
        }
    }
    
    // علامت‌های X و O (در بالای فرت اول، با فاصله از نام سیم‌ها)
    for (let i = 0; i < 6; i++) {
        const x = startX + (5 - i) * stringSpacing;
        const yAbove = startY - 48;  // فاصله مناسب از نام سیم‌ها
        
        if (mute[i] === 1) {
            // ضربدر قرمز پررنگ (با فاصله کافی از لبه)
            svg += `<text x="${x}" y="${yAbove}" font-size="24" fill="#e74c3c" text-anchor="middle" font-weight="bold">✗</text>`;
        } else if (frets[i] === 0) {
            // دایره سبز پررنگ (با فاصله کافی از لبه)
            svg += `<circle cx="${x}" cy="${yAbove - 2}" r="10" fill="none" stroke="#2ecc71" stroke-width="3.5" />`;
            svg += `<circle cx="${x}" cy="${yAbove - 2}" r="6" fill="#2ecc71" />`;
        }
    }
    
    // نام نت‌ها در پایین سیم‌ها
    const noteNames = chord.notes || ['E', 'A', 'D', 'G', 'B', 'e'];
    for (let i = 0; i < 6; i++) {
        const x = startX + (5 - i) * stringSpacing;
        svg += `<text x="${x}" y="${startY + fretSpacing * (numFrets - 1) + 35}" font-size="13" fill="#333" text-anchor="middle" font-weight="bold">${noteNames[i]}</text>`;
    }
    
    svg += `</svg>`;
    document.getElementById('diagram-container').innerHTML = svg;
}


    // ========== رسم فرتبورد حرفه‌ای با SVG (اصلاح نهایی) ==========
    function drawProfessionalFretboard3(chordKey) {
    const chord = chordsData[chordKey];
    if (!chord) return;
    
    const frets = chord.frets;
    const fingers = chord.fingers;
    const mute = chord.mute;
    
    const svgWidth = 500;
    const svgHeight = 560;
    const startX = 65;
    const startY = 70;
    const stringSpacing = 66;
    const fretSpacing = 76;
    const numFrets = 5;
    
    let svg = `<svg class="fretboard-svg" width="${svgWidth}" height="${svgHeight}" viewBox="0 0 ${svgWidth} ${svgHeight}" xmlns="http://www.w3.org/2000/svg">`;
    
    // پس زمینه
    svg += `<rect width="100%" height="100%" fill="#f5f5dc" rx="12" ry="12"/>`;
    
    // ========== 1. خطوط فرت (افقی) ==========
    // خطوط فرت 1 تا 4
    for (let i = 0; i <= numFrets - 1; i++) {
        const y = startY + i * fretSpacing;
        svg += `<line x1="${startX - 15}" y1="${y}" x2="${startX + stringSpacing * 5 + 35}" y2="${y}" stroke="#333" stroke-width="${i === 0 ? 4 : 2}" />`;
        
        // شماره فرت
        if (i > 0 && i <= 4) {
            svg += `<text x="${startX - 22}" y="${y + 7}" font-size="13" fill="#666" text-anchor="end" font-weight="bold">${i}</text>`;
        }
    }
    
    // ========== 2. خط فرت 5 (آخرین خط، ضخیم‌تر) ==========
    const lastFretY = startY + fretSpacing * 4;
    svg += `<line x1="${startX - 15}" y1="${lastFretY}" x2="${startX + stringSpacing * 5 + 35}" y2="${lastFretY}" stroke="#333" stroke-width="3.5" />`;
    
    // ========== 3. خطوط سیم (عمودی) ==========
    for (let i = 0; i < 6; i++) {
        const x = startX + (5 - i) * stringSpacing;
        svg += `<line x1="${x}" y1="${startY}" x2="${x}" y2="${lastFretY}" stroke="#333" stroke-width="${i === 0 || i === 5 ? 2.5 : 1.8}" />`;
    }
    
    // ========== 4. نام سیم‌ها در بالا (با فاصله زیاد از فرت‌ها) ==========
    for (let i = 0; i < 6; i++) {
        const x = startX + (5 - i) * stringSpacing;
        svg += `<text x="${x}" y="${startY - 28}" font-size="14" fill="#555" text-anchor="middle" font-weight="bold">${stringNames[i]}</text>`;
    }
    
    // ========== 5. نقاط انگشت‌گذاری (دایره‌های مشکی) ==========
    for (let i = 0; i < 6; i++) {
        const fret = frets[i];
        if (fret > 0 && fret <= numFrets) {
            const x = startX + (5 - i) * stringSpacing;
            const y = startY + (fret - 1) * fretSpacing + fretSpacing / 2;
            const finger = fingers[i];
            
            svg += `<circle cx="${x}" cy="${y}" r="16" fill="#1a1a2e" stroke="#ffd700" stroke-width="2.5" />`;
            svg += `<text x="${x}" y="${y + 6}" font-size="16" fill="#ffd700" text-anchor="middle" font-weight="bold">${finger}</text>`;
        }
    }
    
    // ========== 6. علامت‌های X و O در بالای صفحه ==========
    for (let i = 0; i < 6; i++) {
        const x = startX + (5 - i) * stringSpacing;
        const yMark = startY - 52;  // بالاتر از نام سیم‌ها
        
        if (mute[i] === 1) {
            svg += `<text x="${x}" y="${yMark}" font-size="26" fill="#e74c3c" text-anchor="middle" font-weight="bold">✗</text>`;
        } else if (frets[i] === 0) {
            svg += `<circle cx="${x}" cy="${yMark - 2}" r="14" fill="none" stroke="#2ecc71" stroke-width="3.5" />`;
            svg += `<circle cx="${x}" cy="${yMark - 2}" r="6" fill="#2ecc71" />`;
        }
    }
    
    // ========== 7. نام نت‌ها در پایین صفحه (زیر خط فرت 5 با فاصله مناسب) ==========
    const noteNames = chord.notes || ['E', 'A', 'D', 'G', 'B', 'e'];
    const noteY = lastFretY + 32;  // فاصله کافی از خط آخر
    
    for (let i = 0; i < 6; i++) {
        const x = startX + (5 - i) * stringSpacing;
        svg += `<text x="${x}" y="${noteY}" font-size="13" fill="#888" text-anchor="middle" font-weight="bold">${noteNames[i]}</text>`;
    }
    
    svg += `</svg>`;
    document.getElementById('diagram-container').innerHTML = svg;
}



    function drawProfessionalFretboard(chordKey) {
    const chord = chordsData[chordKey];
    if (!chord) return;
    
    const frets = chord.frets;
    const fingers = chord.fingers;
    const mute = chord.mute;
    
    const svgWidth = 560;
    const svgHeight = 600;
    const startX = 85;
    const startY = 80;
    const stringSpacing = 72;
    const fretSpacing = 82;
    const numFrets = 5;
    
    let svg = `<svg class="fretboard-svg" width="${svgWidth}" height="${svgHeight}" viewBox="0 0 ${svgWidth} ${svgHeight}" xmlns="http://www.w3.org/2000/svg">`;
    
    // پس زمینه
    svg += `<rect width="100%" height="100%" fill="#f5f5dc" rx="12" ry="12"/>`;
    
    // ========== 1. خطوط افقی فرت (1 تا 5) ==========
    // خط فرت 0 (خط ابتدای صفحه) - ضخیم
    const nutY = startY;
    svg += `<line x1="${startX - 15}" y1="${nutY}" x2="${startX + stringSpacing * 5 + 40}" y2="${nutY}" stroke="#333" stroke-width="4" />`;
    
    // خطوط فرت 1 تا 4
    for (let i = 1; i <= 4; i++) {
        const y = startY + i * fretSpacing;
        svg += `<line x1="${startX - 15}" y1="${y}" x2="${startX + stringSpacing * 5 + 40}" y2="${y}" stroke="#333" stroke-width="2" />`;
        
        // شماره فرت در سمت چپ (اعداد 1 تا 4)
        svg += `<text x="${startX - 28}" y="${y - 8}" font-size="18" fill="#333" text-anchor="end" font-weight="bold">${i}</text>`;
    }
    
    // ========== 2. خط فرت 5 (آخرین خط، ضخیم‌تر) ==========
    const lastFretY = startY + fretSpacing * 5;
    svg += `<line x1="${startX - 15}" y1="${lastFretY}" x2="${startX + stringSpacing * 5 + 40}" y2="${lastFretY}" stroke="#333" stroke-width="3.5" />`;
    
    // ========== 3. خطوط عمودی سیم‌ها (6 سیم) ==========
    for (let i = 0; i < 6; i++) {
        const x = startX + (5 - i) * stringSpacing;
        svg += `<line x1="${x}" y1="${startY}" x2="${x}" y2="${lastFretY}" stroke="#333" stroke-width="${i === 0 || i === 5 ? 2.8 : 2}" />`;
    }
    
    // ========== 4. نام سیم‌ها در بالا (E A D G B e) ==========
    for (let i = 0; i < 6; i++) {
        const x = startX + (5 - i) * stringSpacing;
        svg += `<text x="${x}" y="${startY - 15}" font-size="24" fill="#555" text-anchor="middle" font-weight="bold">${stringNames[i]}</text>`;
    }
    
    // ========== 5. نقاط انگشت‌گذاری (دایره‌های مشکی با اعداد سفید) ==========
    for (let i = 0; i < 6; i++) {
        const fret = frets[i];
        if (fret > 0 && fret <= 5) {
            const x = startX + (5 - i) * stringSpacing;
            const y = startY + (fret - 0.5) * fretSpacing;
            const finger = fingers[i];
            
            svg += `<circle cx="${x}" cy="${y}" r="18" fill="#1a1a2e" stroke="#ffd700" stroke-width="2.5" />`;
            svg += `<text x="${x}" y="${y + 7}" font-size="20" fill="#ffd700" text-anchor="middle" font-weight="bold">${finger}</text>`;
        }
    }
    
    // ========== 6. علامت‌های X و O در بالای صفحه ==========
    for (let i = 0; i < 6; i++) {
        const x = startX + (5 - i) * stringSpacing;
        const yMark = startY - 60;
        
        if (mute[i] === 1) {
            svg += `<text x="${x}" y="${yMark}" font-size="32" fill="#e74c3c" text-anchor="middle" font-weight="bold">✗</text>`;
        } else if (frets[i] === 0) {
            svg += `<circle cx="${x}" cy="${yMark - 2}" r="16" fill="none" stroke="#2ecc71" stroke-width="4" />`;
            svg += `<circle cx="${x}" cy="${yMark - 2}" r="7" fill="#2ecc71" />`;
        }
    }
    
    // ========== 7. نام نت‌ها در پایین صفحه (زیر فرت 5) ==========
    const noteNames = chord.notes || ['E', 'A', 'D', 'G', 'B', 'e'];
    const noteY = lastFretY + 45;
    
    for (let i = 0; i < 6; i++) {
        const x = startX + (5 - i) * stringSpacing;
        svg += `<text x="${x}" y="${noteY - 15}" font-size="24" fill="#888" text-anchor="middle" font-weight="bold">${noteNames[i]}</text>`;
    }
    
    svg += `</svg>`;
    document.getElementById('diagram-container').innerHTML = svg;
}



    // ========== ساخت دکمه‌ها ==========
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

    // ========== انتخاب آکورد ==========
    function selectChord(chordKey) {
        currentChordKey = chordKey;
        const chord = chordsData[chordKey];
        if (!chord) return;
        
        document.getElementById('chord-display').style.display = 'block';
        document.getElementById('chord-name').textContent = chord.name;
        drawProfessionalFretboard(chordKey);
        
        document.querySelectorAll('.chord-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.textContent === chordKey) btn.classList.add('active');
        });
    }

    // ========== پخش صدا ==========
    function playChordSound() {
        const freqMap = {
            'C': [261.63, 329.63, 392.00], 'D': [293.66, 369.99, 440.00],
            'E': [329.63, 414.30, 493.88], 'F': [349.23, 440.00, 523.25],
            'G': [392.00, 493.88, 587.33], 'A': [440.00, 554.37, 659.25],
            'B': [493.88, 622.25, 739.99], 'Am': [440.00, 523.25, 659.25],
            'Dm': [293.66, 349.23, 440.00], 'Em': [329.63, 392.00, 493.88],
            'Fm': [349.23, 415.30, 523.25], 'Gm': [392.00, 466.16, 587.33],
            'Cm': [261.63, 311.13, 392.00], 'Bm': [493.88, 587.33, 739.99]
        };
        
        const frequencies = freqMap[currentChordKey];
        const statusDiv = document.getElementById('audio-status');
        
        if (!frequencies) {
            statusDiv.innerHTML = '⚠️ صدایی برای این آکورد وجود ندارد';
            return;
        }
        
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            const audioCtx = new AudioContext();
            statusDiv.innerHTML = '🎵 در حال پخش...';
            
            frequencies.forEach((freq, index) => {
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
            statusDiv.innerHTML = '⚠️ مرورگر پشتیبانی نمی‌کند';
        }
    }

    // ========== مقداردهی اولیه ==========
    buildButtons();
    selectChord('C');
    document.getElementById('play-btn').onclick = playChordSound;
</script>
</body>
</html>