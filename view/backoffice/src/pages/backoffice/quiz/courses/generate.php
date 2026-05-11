<?php $BO = '/gestion_users/view/backoffice/src'; ?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Back Office - AI Course Generation | EduMatch Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" />
    <link rel="stylesheet" href="<?= $BO ?>/assets/css/theme.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <script src="<?= $BO ?>/assets/js/vendors/color-modes.js"></script>
    <script>
        if (localStorage.getItem('sidebarExpanded') === 'false') { document.documentElement.classList.add('collapsed'); document.documentElement.classList.remove('expanded'); }
        else { document.documentElement.classList.remove('collapsed'); document.documentElement.classList.add('expanded'); }
    </script>
    <style>

        
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); position: relative; }
        
        .ai-header { text-align: center; margin-bottom: 30px; }
        .ai-header .icon { font-size: 48px; color: #8b5cf6; margin-bottom: 15px; }
        .ai-header p { color: #64748b; font-size: 15px; line-height: 1.6; }

        .form-group { margin-bottom: 25px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b; font-size: 14px; }
        input[type="text"], select { width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-family: inherit; font-size: 14px; transition: 0.3s; }
        input[type="text"]:focus, select:focus { outline: none; border-color: #8b5cf6; box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1); }
        
        .form-actions { display: flex; gap: 12px; margin-top: 30px; }
        .btn { padding: 12px 24px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; transition: 0.3s; font-size: 14px; text-decoration: none; display: inline-block; text-align: center; flex: 1; }
        .btn-ai { background: linear-gradient(135deg, #8b5cf6, #6366f1); color: white; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3); }
        .btn-ai:hover { background: linear-gradient(135deg, #7c3aed, #4f46e5); box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4); transform: translateY(-2px); }
        .btn-secondary { background: #f1f5f9; color: #475569; }
        .btn-secondary:hover { background: #e2e8f0; }

        .error-alert { background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #fecaca; display: flex; gap: 10px; align-items: flex-start; }
        .error-alert i { margin-top: 2px; }

        /* Loading Overlay */
        #loadingOverlay {
            display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255, 255, 255, 0.9); border-radius: 12px;
            z-index: 10; align-items: center; justify-content: center; flex-direction: column;
        }
        .spinner {
            width: 50px; height: 50px; border: 5px solid #f3f3f3;
            border-top: 5px solid #8b5cf6; border-radius: 50%;
            animation: spin 1s linear infinite; margin-bottom: 20px;
        }
        .loading-text { color: #8b5cf6; font-weight: 600; font-size: 18px; }
        .loading-subtext { color: #64748b; font-size: 14px; margin-top: 8px; }

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    
    </style>
</head>
<body>
    <div>
        <?php include __DIR__ . '/../../../../partials_php/sidebar.php'; ?>
        <div id="content" class="position-relative h-100">
            <?php include __DIR__ . '/../../../../partials_php/topbar.php'; ?>
            <div class="custom-container">

                <div class="row mb-6 g-6 align-items-end">
                    <div class="col-lg-8">
                        <p class="text-uppercase text-secondary small mb-2">Module Quiz</p>
                        <h1 class="mb-0">AI Course Generator</h1>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="<?= htmlspecialchars(backofficeRoute('courses', 'index')); ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Courses</a>
                    </div>
                </div>

            <div class="container">
                <div id="loadingOverlay">
                    <div class="spinner"></div>
                    <div class="loading-text">Generating Course...</div>
                    <div class="loading-subtext">This might take 30-60 seconds as the AI writes the lessons and quizzes.</div>
                </div>

                <div class="ai-header">
                    <div class="icon"><i class="fas fa-brain"></i></div>
                    <h2>Create a Course Instantly</h2>
                    <p>Enter a topic below, and our AI will automatically structure a complete course with lessons, summaries, and a final quiz with questions and answers.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="error-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <strong>Error:</strong><br>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    </div>
                <?php endif; ?>
        
                <form id="aiForm" method="post" action="<?= htmlspecialchars(backofficeRoute('courses', 'storeAi')); ?>">
                    <div class="form-group">
                        <label for="topic">Course Topic</label>
                        <input type="text" id="topic" name="topic" placeholder="e.g., Introduction to Python Programming" required>
                    </div>

                    <div class="form-group">
                        <label for="level">Target Level</label>
                        <select id="level" name="level">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-ai" type="submit"><i class="fas fa-wand-magic-sparkles"></i> Generate Full Course</button>
                        <a href="<?= htmlspecialchars(backofficeRoute('courses', 'index')); ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.js"></script>
    <script src="<?= $BO ?>/assets/js/main.js"></script>
    <script src="<?= $BO ?>/assets/js/vendors/sidebarnav.js"></script>
<script>
        document.getElementById('aiForm').addEventListener('submit', function(e) {
            const topic = document.getElementById('topic').value.trim();
            if(topic) {
                document.getElementById('loadingOverlay').style.display = 'flex';
            }
        });
    </script>

</body>
</html>