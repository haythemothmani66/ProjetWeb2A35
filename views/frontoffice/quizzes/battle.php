<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Battle vs AI - <?= htmlspecialchars((string) $quiz['title']) ?></title>			
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">		
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        body { background: #0f172a; font-family: 'Jost', sans-serif; color: white; min-height: 100vh; }
        .battle-container {
            max-width: 900px;
            margin: 40px auto;
            background: #1e293b;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            border: 1px solid #334155;
        }
        .battle-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            padding: 40px;
            text-align: center;
            border-bottom: 1px solid #4f46e5;
        }
        .battle-header h2 { font-size: 38px; font-weight: 800; margin: 0; text-transform: uppercase; letter-spacing: 2px; }
        
        .difficulty-selector {
            padding: 40px;
            text-align: center;
        }
        .difficulty-btn {
            padding: 15px 30px;
            margin: 10px;
            border-radius: 12px;
            border: 2px solid #334155;
            background: #1e293b;
            color: white;
            font-weight: 700;
            transition: 0.3s;
            cursor: pointer;
            width: 200px;
        }
        .difficulty-btn:hover { border-color: #6366f1; transform: translateY(-3px); }
        .difficulty-btn.easy:hover { border-color: #22c55e; color: #22c55e; }
        .difficulty-btn.medium:hover { border-color: #f59e0b; color: #f59e0b; }
        .difficulty-btn.hard:hover { border-color: #ef4444; color: #ef4444; }

        .score-board {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            padding: 30px;
            background: #0f172a;
            border-bottom: 1px solid #334155;
        }
        .player-info { text-align: center; }
        .player-name { font-size: 18px; font-weight: 600; color: #94a3b8; margin-bottom: 5px; }
        .score-value { font-size: 54px; font-weight: 800; color: #6366f1; }
        .vs-badge {
            background: #ef4444;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);
        }

        .battle-arena { padding: 40px; display: none; }
        .question-card {
            background: #334155;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
        }
        .question-meta { color: #94a3b8; font-size: 14px; margin-bottom: 10px; }
        .question-text { font-size: 24px; font-weight: 600; line-height: 1.4; }
        
        .options-list { display: grid; gap: 12px; margin-top: 30px; }
        .option-btn {
            background: #1e293b;
            border: 2px solid #475569;
            padding: 18px 25px;
            border-radius: 12px;
            color: white;
            text-align: left;
            font-size: 17px;
            font-weight: 500;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .option-btn:hover:not(:disabled) { border-color: #6366f1; background: #334155; }
        .option-btn.selected { border-color: #6366f1; background: rgba(99, 102, 241, 0.1); }
        .option-btn.correct { border-color: #22c55e; background: rgba(34, 197, 94, 0.1); color: #4ade80; }
        .option-btn.wrong { border-color: #ef4444; background: rgba(239, 68, 68, 0.1); color: #f87171; }
        
        .ai-choice-badge {
            font-size: 12px;
            padding: 4px 8px;
            background: #ef4444;
            border-radius: 6px;
            color: white;
            font-weight: 700;
            display: none;
        }

        .battle-footer {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .btn-action {
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-next { background: #6366f1; color: white; display: none; }
        .btn-next:hover { background: #4f46e5; transform: scale(1.05); }

        .summary-screen { padding: 50px; text-align: center; display: none; }
        .result-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 8px solid #6366f1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            font-weight: 800;
            margin: 0 auto 30px;
        }
        .level-badge {
            display: inline-block;
            padding: 10px 25px;
            border-radius: 999px;
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 20px;
        }
        .level-easy { background: #22c55e; color: white; }
        .level-medium { background: #f59e0b; color: white; }
        .level-hard { background: #ef4444; color: white; }
        
        .battle-log {
            text-align: left;
            margin-top: 40px;
            background: #0f172a;
            border-radius: 12px;
            padding: 20px;
            max-height: 300px;
            overflow-y: auto;
        }
        .log-item {
            padding: 10px 0;
            border-bottom: 1px solid #1e293b;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="battle-container">
        <div class="battle-header">
            <h2><i class="fas fa-bolt"></i> Battle vs AI</h2>
        </div>

        <!-- 1. Difficulty Selection -->
        <div class="difficulty-selector" id="difficultyScreen">
            <h3 class="mb-4">Select AI Opponent Level</h3>
            <div class="d-flex flex-wrap justify-content-center">
                <button class="difficulty-btn easy" onclick="startBattle('Easy', 0.50)">
                    <i class="fas fa-seedling"></i><br>EASY (50%)
                </button>
                <button class="difficulty-btn medium" onclick="startBattle('Medium', 0.70)">
                    <i class="fas fa-robot"></i><br>MEDIUM (70%)
                </button>
                <button class="difficulty-btn hard" onclick="startBattle('Hard', 0.85)">
                    <i class="fas fa-skull"></i><br>HARD (85%)
                </button>
            </div>
        </div>

        <!-- 2. The Battle Interface -->
        <div id="battleScreen" style="display: none;">
            <div class="score-board">
                <div class="player-info">
                    <div class="player-name">HUMAN</div>
                    <div class="score-value" id="userScore">0</div>
                </div>
                <div class="vs-badge">VS</div>
                <div class="player-info">
                    <div class="player-name">AI (<span id="aiLevelTag">Medium</span>)</div>
                    <div class="score-value" style="color: #ef4444;" id="aiScore">0</div>
                </div>
            </div>

            <div class="battle-arena" id="battleArena" style="display: block;">
                <div class="question-card">
                    <div class="question-meta" id="qCounter">QUESTION 1 / 10</div>
                    <div class="question-text" id="qText">Wait...</div>
                    <div class="options-list" id="qOptions">
                        <!-- Options injected via JS -->
                    </div>
                </div>

                <div id="feedbackBox" class="text-center mb-4" style="font-weight: 700; font-size: 18px;"></div>

                <div class="battle-footer">
                    <button class="btn-action btn-next" id="nextBtn" onclick="nextQuestion()">
                        CONTINUE <i class="fas fa-chevron-right ms-2"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- 3. Summary Screen -->
        <div class="summary-screen" id="summaryScreen">
            <h2 class="mb-4" id="battleResultText">VICTORY!</h2>
            <div class="result-circle" id="finalScoreCircle">7/10</div>
            
            <div class="mb-2 text-muted">Battle Difficulty:</div>
            <div class="level-badge" id="finalLevelBadge">MEDIUM</div>

            <div class="battle-log" id="battleLog">
                <h5 class="mb-3">Battle Log Recap</h5>
                <!-- Log entries here -->
            </div>

            <div class="mt-5">
                <a href="<?= htmlspecialchars(frontofficeRoute('quizzes', 'take', ['id' => $quizId])) ?>" class="btn-action" style="background: #6366f1; color: white; text-decoration: none;">
                    GO TO OFFICIAL QUIZ
                </a>
                <a href="<?= htmlspecialchars(frontofficeRoute('courses', 'show', ['id' => $quiz['course_id']])) ?>" class="btn-action ms-3" style="background: transparent; color: #94a3b8; border: 1px solid #334155; text-decoration: none;">
                    QUIT BATTLE
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    const questions = <?= json_encode($battleQuestions) ?>;
    let currentIdx = 0;
    let userPoints = 0;
    let aiPoints = 0;
    let aiProb = 0.7;
    let aiLevel = 'Medium';
    let battleLog = [];

    function startBattle(level, prob) {
        aiLevel = level;
        aiProb = prob;
        document.getElementById('aiLevelTag').innerText = level;
        document.getElementById('difficultyScreen').style.display = 'none';
        document.getElementById('battleScreen').style.display = 'block';
        loadQuestion();
    }

    function loadQuestion() {
        const q = questions[currentIdx];
        document.getElementById('qCounter').innerText = `QUESTION ${currentIdx + 1} / ${questions.length}`;
        document.getElementById('qText').innerText = q.question_text;
        document.getElementById('nextBtn').style.display = 'none';
        document.getElementById('feedbackBox').innerText = '';

        const optionsHtml = q.responses.map(res => `
            <button class="option-btn" onclick="handleSelect(${res.id}, ${res.is_correct}, '${res.response_text.replace(/'/g, "\\'")}')">
                <span>${res.response_text}</span>
                <span class="ai-choice-badge" id="ai-badge-${res.id}">AI CHOICE</span>
            </button>
        `).join('');
        document.getElementById('qOptions').innerHTML = optionsHtml;
    }

    function handleSelect(selectedId, isCorrect, text) {
        const q = questions[currentIdx];
        const buttons = document.querySelectorAll('.option-btn');
        buttons.forEach(b => b.disabled = true);

        // Determine AI Choice
        const correctRes = q.responses.find(r => parseInt(r.is_correct) === 1);
        const wrongRes = q.responses.filter(r => parseInt(r.is_correct) !== 1);
        let aiRes;
        if (Math.random() <= aiProb) {
            aiRes = correctRes;
        } else {
            aiRes = wrongRes[Math.floor(Math.random() * wrongRes.length)] || correctRes;
        }

        const userWon = isCorrect === 1;
        const aiWon = parseInt(aiRes.is_correct) === 1;

        if (userWon) userPoints++;
        if (aiWon) aiPoints++;

        document.getElementById('userScore').innerText = userPoints;
        document.getElementById('aiScore').innerText = aiPoints;

        // Visual Feedback
        const aiBadge = document.getElementById(`ai-badge-${aiRes.id}`);
        if (aiBadge) aiBadge.style.display = 'block';

        buttons.forEach(btn => {
            const btnId = parseInt(btn.getAttribute('onclick').match(/\d+/)[0]);
            if (btnId === correctRes.id) btn.classList.add('correct');
            if (btnId === selectedId && !userWon) btn.classList.add('wrong');
        });

        // Add to log
        battleLog.push({
            q: q.question_text,
            user: userWon ? 'CORRECT' : 'WRONG',
            ai: aiWon ? 'CORRECT' : 'WRONG'
        });

        const feedback = document.getElementById('feedbackBox');
        if (userWon && aiWon) feedback.innerHTML = '<span style="color:#f59e0b">CLASH! BOTH CORRECT!</span>';
        else if (userWon) feedback.innerHTML = '<span style="color:#22c55e">DIRECT HIT! AI MISSED!</span>';
        else if (aiWon) feedback.innerHTML = '<span style="color:#ef4444">CRITICAL HIT BY AI!</span>';
        else feedback.innerHTML = '<span style="color:#94a3b8">BOTH MISSED! PATIENCE IS KEY.</span>';

        document.getElementById('nextBtn').style.display = 'inline-block';
    }

    function nextQuestion() {
        currentIdx++;
        if (currentIdx < questions.length) {
            loadQuestion();
        } else {
            finishBattle();
        }
    }

    function finishBattle() {
        document.getElementById('battleScreen').style.display = 'none';
        document.getElementById('summaryScreen').style.display = 'block';

        const win = userPoints > aiPoints;
        const tie = userPoints === aiPoints;
        document.getElementById('battleResultText').innerText = win ? 'VICTORY!' : (tie ? 'DRAW!' : 'DEFEAT!');
        document.getElementById('battleResultText').style.color = win ? '#22c55e' : (tie ? '#f59e0b' : '#ef4444');
        
        document.getElementById('finalScoreCircle').innerText = `${userPoints}/${questions.length}`;

        // The user wants to see the same difficulty they chose
        const finalLvl = aiLevel.toUpperCase();
        let lvlClass = 'level-medium';
        if (finalLvl === 'EASY') lvlClass = 'level-easy';
        if (finalLvl === 'HARD') lvlClass = 'level-hard';
        
        const badge = document.getElementById('finalLevelBadge');
        badge.innerText = finalLvl;
        badge.className = `level-badge ${lvlClass}`;

        // Show performance hint below
        const pct = (userPoints / questions.length) * 100;
        let perf = 'Excellent';
        if (pct < 50) perf = 'Needs Practice';
        else if (pct < 80) perf = 'Good';
        
        const logContainer = document.getElementById('battleLog');
        logContainer.innerHTML = `<div class="mb-3 text-center"><small>Performance: <strong>${perf}</strong></small></div>` + logContainer.innerHTML;
        
        battleLog.forEach((item, i) => {
            logContainer.innerHTML += `
                <div class="log-item">
                    <strong>Q${i+1}:</strong> ${item.q.substring(0,60)}...<br>
                    <small>YOU: <span style="color:${item.user === 'CORRECT' ? '#22c55e' : '#ef4444'}">${item.user}</span> | 
                    AI: <span style="color:${item.ai === 'CORRECT' ? '#22c55e' : '#ef4444'}">${item.ai}</span></small>
                </div>
            `;
        });
    }
</script>
</body>
</html>
