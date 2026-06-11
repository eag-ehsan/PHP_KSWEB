<?php
// guitar.php - تمرین آکوردهای گیتار الکتریک
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>آموزش آکوردهای گیتار الکتریک</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', 'Courier New', monospace;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            margin: 0 auto;
        }

        /* هدر */
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

        .header h1 {
            color: #ffd700;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn {
            background: rgba(255, 215, 0, 0.2);
            color: #ffd700;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 30px;
            font-weight: bold;
            transition: all 0.3s;
            border: 1px solid #ffd700;
            font-size: 0.9rem;
        }

        .back-btn:hover {
            background: #ffd700;
            color: #1a1a2e;
        }

        /* دسته‌بندی آکوردها */
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
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* شبکه آکوردها */
        .chords-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
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
            font-family: monospace;
        }

        .chord-btn:hover, .chord-btn.active {
            background: #ffd700;
            color: #1a1a2e;
            transform: scale(1.02);
            border-color: #ffd700;
        }

        /* بخش نمایش تبلچر */
        .tab-section {
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .current-chord {
            text-align: center;
            margin-bottom: 20px;
        }

        .current-chord-name {
            font-size: 2rem;
            font-weight: bold;
            color: #ffd700;
            background: rgba(0, 0, 0, 0.5);
            display: inline-block;
            padding: 8px 25px;
            border-radius: 50px;
            letter-spacing: 2px;
        }

        /* تبلچر گیتار */
        .tab-container {
            background: #0a0a1a;
            border-radius: 16px;
            padding: 15px;
            overflow-x: auto;
            direction: ltr;
        }

        .tab-line {
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            line-height: 1.6;
            white-space: nowrap;
            color: #ffd700;
            background: #0a0a1a;
        }

        .tab-label {
            display: inline-block;
            width: 35px;
            color: #ff6b6b;
            font-weight: bold;
        }

        .tab-notes {
            display: inline-block;
            letter-spacing: 2px;
        }

        /* توضیحات انگشت‌گذاری */
        .finger-info {
            margin-top: 15px;
            padding: 12px;
            background: rgba(255, 215, 0, 0.1);
            border-radius: 12px;
            font-size: 0.85rem;
            color: #ccc;
            text-align: center;
            direction: ltr;
        }

        /* پخش صدا */
        .audio-section {
            display: flex;
            justify-content: center;
            margin-top: 15px;
        }

        .play-btn {
            background: #2ecc71;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .play-btn:hover {
            background: #27ae60;
            transform: scale(1.02);
        }

        .play-btn:active {
            transform: scale(0.98);
        }

        .no-audio {
            background: #555;
            cursor: not-allowed;
        }

        /* وضعیت عدم اتصال */
        .status {
            text-align: center;
            padding: 8px;
            font-size: 0.75rem;
            color: #aaa;
        }

        /* responsive */
        @media (max-width: 480px) {
            body {
                padding: 12px;
            }
            
            .chords-grid {
                grid-template-columns: repeat(auto-fill, minmax(75px, 1fr));
                gap: 8px;
            }
            
            .chord-btn {
                padding: 10px 5px;
                font-size: 0.85rem;
            }
            
            .tab-line {
                font-size: 0.7rem;
            }
            
            .tab-label {
                width: 25px;
            }
            
            .current-chord-name {
                font-size: 1.5rem;
            }
            
            .section-title {
                font-size: 1rem;
            }
        }
    </style>
    
    <script src="fretdrom.min.js"></script>
    
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🎸 آموزش آکورد گیتار الکتریک</h1>
        <a href="main.php" class="back-btn">🔙 بازگشت به داشبورد</a>
    </div>

    <!-- آکوردهای ماژور -->
    <div class="section">
        <div class="section-title">
            <span>🎵</span> آکوردهای ماژور
        </div>
        <div class="chords-grid" id="major-chords"></div>
    </div>

    <!-- آکوردهای مینور -->
    <div class="section">
        <div class="section-title">
            <span>🎶</span> آکوردهای مینور
        </div>
        <div class="chords-grid" id="minor-chords"></div>
    </div>

    <!-- نمایش تبلچر -->
    <div class="tab-section" id="tab-section" style="display: none;">
        <div class="current-chord">
            <span class="current-chord-name" id="current-chord-name">C</span>
        </div>
        <div class="tab-container">
            <pre id="tablature" class="tab-line"></pre>
        </div>
        <div class="finger-info" id="finger-info"></div>
        <div class="audio-section">
            <button id="play-audio" class="play-btn">🔊 پخش صدای آکورد</button>
        </div>
        <div class="status" id="audio-status"></div>
    </div>
</div>

<script>
    // ========== دیتابیس آکوردها با تبلچر ==========
    const chords = {
        // آکوردهای ماژور
        'C': {
            name: 'دو ماژور (C)',
            tab: 'e|----0----\nB|----1----\nG|----0----\nD|----2----\nA|----3----\nE|---------',
            fingers: 'انگشت 1: سیم B (فرت 1) | انگشت 2: سیم D (فرت 2) | انگشت 3: سیم A (فرت 3)',
            audioUrl: 'https://api.guitarparty.com/v2/chords/C/audio' // placeholder
        },
        'D': {
            name: 'ر ماژور (D)',
            tab: 'e|----2----\nB|----3----\nG|----2----\nD|----0----\nA|--------- \nE|---------',
            fingers: 'انگشت 1: سیم G (فرت 2) | انگشت 2: سیم e (فرت 2) | انگشت 3: سیم B (فرت 3)',
            audioUrl: ''
        },
        'E': {
            name: 'می ماژور (E)',
            tab: 'e|----0----\nB|----0----\nG|----1----\nD|----2----\nA|----2----\nE|----0----',
            fingers: 'انگشت 1: سیم G (فرت 1) | انگشت 2: سیم A (فرت 2) | انگشت 3: سیم D (فرت 2)',
            audioUrl: ''
        },
        'F': {
            name: 'فا ماژور (F)',
            tab: 'e|----1----\nB|----1----\nG|----2----\nD|----3----\nA|----3----\nE|----1----',
            fingers: 'باره روی فرت 1 + انگشت 3 و 4',
            audioUrl: ''
        },
        'G': {
            name: 'سل ماژور (G)',
            tab: 'e|----3----\nB|----0----\nG|----0----\nD|----0----\nA|----2----\nE|----3----',
            fingers: 'انگشت 1: سیم A (فرت 2) | انگشت 2: سیم e (فرت 3) | انگشت 3: سیم E (فرت 3)',
            audioUrl: ''
        },
        'A': {
            name: 'لا ماژور (A)',
            tab: 'e|----0----\nB|----2----\nG|----2----\nD|----2----\nA|----0----\nE|---------',
            fingers: 'انگشت 1،2،3 پشت سر هم روی فرت 2 (D,G,B)',
            audioUrl: ''
        },
        'B': {
            name: 'سی ماژور (B)',
            tab: 'e|----2----\nB|----4----\nG|----4----\nD|----4----\nA|----2----\nE|---------',
            fingers: 'باره روی فرت 2',
            audioUrl: ''
        },
        
        // آکوردهای مینور
        'Am': {
            name: 'لا مینور (Am)',
            tab: 'e|----0----\nB|----1----\nG|----2----\nD|----2----\nA|----0----\nE|---------',
            fingers: 'انگشت 1: سیم B (فرت 1) | انگشت 2: سیم G (فرت 2) | انگشت 3: سیم D (فرت 2)',
            audioUrl: ''
        },
        'Dm': {
            name: 'ر مینور (Dm)',
            tab: 'e|----1----\nB|----3----\nG|----2----\nD|----0----\nA|--------- \nE|---------',
            fingers: 'انگشت 1: سیم e (فرت 1) | انگشت 2: سیم G (فرت 2) | انگشت 3: سیم B (فرت 3)',
            audioUrl: ''
        },
        'Em': {
            name: 'می مینور (Em)',
            tab: 'e|----0----\nB|----0----\nG|----0----\nD|----2----\nA|----2----\nE|----0----',
            fingers: 'انگشت 1 و 2 روی فرت 2 (A و D)',
            audioUrl: ''
        },
        'Fm': {
            name: 'فا مینور (Fm)',
            tab: 'e|----1----\nB|----1----\nG|----1----\nD|----3----\nA|----3----\nE|----1----',
            fingers: 'باره روی فرت 1',
            audioUrl: ''
        },
        'Gm': {
            name: 'سل مینور (Gm)',
            tab: 'e|----3----\nB|----3----\nG|----3----\nD|----5----\nA|----5----\nE|----3----',
            fingers: 'باره روی فرت 3',
            audioUrl: ''
        },
        'Cm': {
            name: 'دو مینور (Cm)',
            tab: 'e|----3----\nB|----4----\nG|----5----\nD|----5----\nA|----3----\nE|---------',
            fingers: 'باره روی فرت 3',
            audioUrl: ''
        },
        'Bm': {
            name: 'سی مینور (Bm)',
            tab: 'e|----2----\nB|----3----\nG|----4----\nD|----4----\nA|----2----\nE|---------',
            fingers: 'باره روی فرت 2',
            audioUrl: ''
        }
    };

    // آکوردهای اصلی
    const majorChords = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
    const minorChords = ['Am', 'Dm', 'Em', 'Fm', 'Gm', 'Cm', 'Bm'];
    
    let currentChord = 'C';
    let audio = null;

    // ========== ساخت دکمه‌های آکورد ==========
    function buildChordButtons() {
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
    function selectChord(chord) {
        currentChord = chord;
        const chordData = chords[chord];
        if (!chordData) return;
        
        // نمایش بخش تبلچر
        document.getElementById('tab-section').style.display = 'block';
        
        // به‌روزرسانی نام آکورد
        document.getElementById('current-chord-name').textContent = chordData.name;
        
        // نمایش تبلچر با فرمت مناسب
        const tabText = chordData.tab;
        document.getElementById('tablature').innerHTML = formatTab(tabText);
        
        // نمایش راهنمای انگشت‌گذاری
        document.getElementById('finger-info').innerHTML = `🎸 ${chordData.fingers}`;
        
        // تغییر دکمه فعال
        document.querySelectorAll('.chord-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.textContent === chord) {
                btn.classList.add('active');
            }
        });
        
        // تنظیم صدای آکورد
        setupAudio(chord);
    }
    
    // ========== فرمت کردن تبلچر ==========
    function formatTab(tab) {
        // اضافه کردن شماره سیم‌ها به صورت خواناتر
        let lines = tab.split('\n');
        let formatted = '';
        lines.forEach(line => {
            if (line.trim()) {
                formatted += line + '\n';
            }
        });
        return formatted;
    }
    
    // ========== تنظیم صدا (با استفاده از Web Audio API) ==========
    function setupAudio(chord) {
        const playBtn = document.getElementById('play-audio');
        const audioStatus = document.getElementById('audio-status');
        
        // برای آکوردهای مختلف، صداهای متفاوت (سینت سایزر ساده)
        // در حالت واقعی می‌توانید فایل‌های MP3 را در سرور قرار دهید
        
        playBtn.onclick = () => {
            playSynthesizedChord(chord);
        };
        
        audioStatus.innerHTML = '💡 برای شنیدن صدا، روی دکمه پخش کلیک کنید (صدای سینت سایزر)';
    }
    
    // ========== پخش صدای شبیه‌سازی شده (با Web Audio API) ==========
    function playSynthesizedChord(chord) {
        try {
            // ایجاد context صوتی
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            const audioCtx = new AudioContext();
            
            // نت‌های هر آکورد (فرکانس‌ها)
            const chordNotes = {
                'C': [261.63, 329.63, 392.00],      // C - E - G
                'D': [293.66, 369.99, 440.00],      // D - F# - A
                'E': [329.63, 414.30, 493.88],      // E - G# - B
                'F': [349.23, 440.00, 523.25],      // F - A - C
                'G': [392.00, 493.88, 587.33],      // G - B - D
                'A': [440.00, 554.37, 659.25],      // A - C# - E
                'B': [493.88, 622.25, 739.99],      // B - D# - F#
                'Am': [440.00, 523.25, 659.25],     // A - C - E
                'Dm': [293.66, 349.23, 440.00],     // D - F - A
                'Em': [329.63, 392.00, 493.88],     // E - G - B
                'Fm': [349.23, 415.30, 523.25],     // F - Ab - C
                'Gm': [392.00, 466.16, 587.33],     // G - Bb - D
                'Cm': [261.63, 311.13, 392.00],     // C - Eb - G
                'Bm': [493.88, 587.33, 739.99]      // B - D - F#
            };
            
            const frequencies = chordNotes[chord] || chordNotes['C'];
            
            // پخش هر نت
            frequencies.forEach((freq, index) => {
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();
                
                oscillator.type = 'sine';  // صدای سینوسی شبیه گیتار الکتریک
                oscillator.frequency.value = freq;
                
                gainNode.gain.value = 0.3;
                gainNode.gain.exponentialRampToValueAtTime(0.00001, audioCtx.currentTime + 2);
                
                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);
                
                oscillator.start();
                oscillator.stop(audioCtx.currentTime + 1.5);
            });
            
            document.getElementById('audio-status').innerHTML = '🎵 در حال پخش... (صدای سینت سایزر)';
            setTimeout(() => {
                document.getElementById('audio-status').innerHTML = '💡 برای شنیدن صدا، روی دکمه پخش کلیک کنید';
            }, 2000);
            
        } catch(e) {
            console.error('خطا در پخش صدا:', e);
            document.getElementById('audio-status').innerHTML = '⚠️ مرورگر از پخش صدا پشتیبانی نمی‌کند';
        }
    }
    
    // ========== برای آکوردهای واقعی، می‌توانید فایل‌های MP3 قرار دهید ==========
    // فایل‌های صوتی را در پوشه audio/ قرار دهید:
    // audio/C.mp3, audio/D.mp3, audio/Em.mp3 و ...
    
    // بارگذاری اولیه
    buildChordButtons();
    selectChord('C');
</script>
</body>
</html>