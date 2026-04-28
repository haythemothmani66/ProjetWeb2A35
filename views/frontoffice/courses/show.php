<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?= htmlspecialchars((string) (substr($course['description'] ?? '', 0, 160))) ?>">
    <meta name="keywords" content="course, learning">		
    <title><?= htmlspecialchars((string) $course['title']) ?> - Eduleb</title>			
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">		
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="assets/owlcarousel/css/owl.carousel.css">
    <link rel="stylesheet" href="assets/owlcarousel/css/owl.theme.css">	
    <link rel="stylesheet" href="assets/css/jquery-simple-mobilemenu.css">			
    <link rel="stylesheet" href="assets/css/magnific-popup.css">		
    <link rel="stylesheet" href="assets/css/animate.css">	
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .course-header {
            background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%);
            color: white;
            padding: 60px 0;
            margin-top: 80px;
        }
        .course-header h1 {
            font-size: 45px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .course-header-meta {
            display: flex;
            gap: 25px;
            font-size: 16px;
        }
        .course-header-meta span {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .course-content {
            padding: 80px 0;
        }
        .course-image {
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 40px;
            max-height: 400px;
        }
        .course-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .course-description {
            font-size: 16px;
            line-height: 1.8;
            color: #666;
            margin-bottom: 40px;
        }
        .course-info-box {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 40px;
        }
        .course-info-box h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #1e293b;
        }
        .info-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-item i {
            font-size: 20px;
            color: #525fe1;
            width: 30px;
            text-align: center;
        }
        .info-item strong {
            min-width: 120px;
            color: #1e293b;
        }
        .learning-path-section {
            margin-top: 50px;
        }
        .learning-path-section h2 {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 30px;
        }
        .lesson-path-card {
            display: flex;
            gap: 20px;
            padding: 24px;
            background: white;
            border: 1px solid #e0e7ff;
            border-radius: 14px;
            margin-bottom: 18px;
            transition: 0.3s;
        }
        .lesson-path-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(82, 95, 225, 0.08);
            border-color: #c7d2fe;
        }
        .lesson-path-order {
            flex-shrink: 0;
            width: 54px;
            height: 54px;
            border-radius: 16px;
            background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
        }
        .lesson-path-body h4 {
            font-size: 22px;
            color: #1e293b;
            margin-bottom: 10px;
        }
        .lesson-path-body p {
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 14px;
        }
        .lesson-path-meta {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .lesson-path-meta span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #f8fafc;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
        }
        .btn-start-lesson {
            background: #f0f4ff;
            color: #525fe1;
            border: 1px solid #c7d2fe;
            padding: 11px 22px;
            border-radius: 8px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }
        .btn-start-lesson:hover {
            background: #e0e7ff;
            color: #3730a3;
            text-decoration: none;
        }
        .quizzes-section {
            margin-top: 60px;
            padding-top: 60px;
            border-top: 2px solid #e0e0e0;
        }
        .quizzes-section h2 {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 30px;
        }
        .quiz-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .quiz-card:hover {
            border-color: #525fe1;
            box-shadow: 0 4px 15px rgba(82, 95, 225, 0.1);
        }
        .quiz-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 20px;
            margin-bottom: 20px;
        }
        .quiz-title {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        .quiz-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            font-size: 14px;
            color: #666;
        }
        .quiz-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .quiz-meta-item i {
            color: #525fe1;
        }
        .btn-start-quiz {
            background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 6px;
            font-weight: 600;
            transition: 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }
        .btn-start-quiz:hover {
            transform: translateX(5px);
            color: white;
            text-decoration: none;
        }
        .no-quizzes {
            background: #f0f4ff;
            border-left: 4px solid #525fe1;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
        }
        .no-quizzes i {
            font-size: 42px;
            color: #525fe1;
            margin-bottom: 15px;
        }
        .no-quizzes p {
            color: #666;
            margin: 0;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            text-decoration: none;
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 30px;
            transition: 0.3s;
        }
        .btn-back:hover {
            background: #f8f9fa;
            text-decoration: none;
            color: #1e293b;
        }
    </style>
</head>

<body>
    <div id="navigation" class="navbar-light bg-faded site-navigation">
        <div class="container-fluid">
            <div class="row">
                <div class="col-20 align-self-center">
                    <div class="site-logo">
                        <a href="index.html"><img src="edumatch.png" alt="Eduleb"></a>          				
                    </div>
                </div>
                
                <div class="col-60 d-flex">
                    <nav id="main-menu">
                        <ul>
                            <li class="menu-item-has-children"><a href="index.html">Home</a></li>
                            <li class="menu-item-has-children"><a href="<?= htmlspecialchars(frontofficeRoute('courses', 'index')); ?>">Courses</a></li>
                            <li class="menu-item-has-children"><a href="">Contact</a></li>
                        </ul>
                    </nav>
                </div>
                
                <div class="col-20 d-none d-xl-block text-end align-self-center">
                    <a href="#" class="header-btn">Sign In</a>
                    <a href="#" class="btn_one">Sign Up</a>
                </div>
                
                <ul class="mobile_menu">						
                    <li><a href="index.html">Home</a></li>
                    <li><a href="<?= htmlspecialchars(frontofficeRoute('courses', 'index')); ?>">Courses</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>			
            </div>
        </div>
    </div> 	  

    <section class="course-header">
        <div class="container">
            <a href="<?= htmlspecialchars(frontofficeRoute('courses', 'index')); ?>" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Courses
            </a>
            <h1><?= htmlspecialchars((string) $course['title']) ?></h1>
            <div class="course-header-meta">
                <span>
                    <i class="fas fa-tag"></i>
                    <?= htmlspecialchars((string) ($course['level'] ?? 'beginner')) ?>
                </span>
                <span>
                    <i class="fas fa-road"></i>
                    <?= count($lessons ?? []) ?> Lesson<?= count($lessons ?? []) !== 1 ? 's' : '' ?>
                </span>
                <span>
                    <i class="fas fa-book"></i>
                    <?= count($quizzes ?? []) ?> Quiz<?= count($quizzes ?? []) !== 1 ? 'zes' : '' ?>
                </span>
            </div>
        </div>
    </section>

    <section class="course-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <?php if (!empty($course['image'])): ?>
                        <div class="course-image">
                            <img src="<?= htmlspecialchars((string) $course['image']) ?>" alt="<?= htmlspecialchars((string) $course['title']) ?>">
                        </div>
                    <?php else: ?>
                        <div class="course-image" style="background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-book" style="font-size: 80px; color: white;"></i>
                        </div>
                    <?php endif; ?>

                    <div class="course-description">
                        <h3 style="font-size: 24px; font-weight: 600; color: #1e293b; margin-bottom: 20px;">About this course</h3>
                        <p><?= nl2br(htmlspecialchars((string) ($course['description'] ?? ''))) ?></p>
                    </div>

                    <div class="learning-path-section">
                        <h2>Learning Path</h2>

                        <?php if (empty($lessons)): ?>
                            <div class="no-quizzes">
                                <i class="fas fa-road"></i>
                                <p>No lessons are available for this course yet. The learning path will appear here once lessons are published.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($lessons as $lesson): ?>
                                <article class="lesson-path-card">
                                    <div class="lesson-path-order"><?= (int) ($lesson['lesson_order'] ?? 1) ?></div>
                                    <div class="lesson-path-body">
                                        <h4><?= htmlspecialchars((string) $lesson['title']) ?></h4>
                                        <p><?= htmlspecialchars((string) ($lesson['summary'] ?? 'No summary available for this lesson.')) ?></p>
                                        <div class="lesson-path-meta">
                                            <span><i class="fas fa-clock"></i> <?= (int) ($lesson['duration_minutes'] ?? 10) ?> minutes</span>
                                            <span><i class="fas fa-book-open"></i> Guided lesson</span>
                                        </div>
                                        <a href="<?= htmlspecialchars(frontofficeRoute('lessons', 'show', ['id' => (int) $lesson['id']])); ?>" class="btn-start-lesson">
                                            <i class="fas fa-play"></i> Open Lesson
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="quizzes-section">
                        <h2>Knowledge Check</h2>
                        
                        <?php if (empty($quizzes)): ?>
                            <div class="no-quizzes">
                                <i class="fas fa-inbox"></i>
                                <p>No quizzes available for this course yet. Check back soon!</p>
                            </div>
                        <?php else: ?>
                            <div>
                                <?php foreach ($quizzes as $q): ?>
                                    <div class="quiz-card">
                                        <div class="quiz-card-header">
                                            <div>
                                                <h4 class="quiz-title"><?= htmlspecialchars((string) $q['title']) ?></h4>
                                                <?php if (!empty($q['description'])): ?>
                                                    <p style="font-size: 14px; color: #666; margin-top: 8px; margin-bottom: 0;">
                                                        <?= htmlspecialchars((string) $q['description']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                <div class="quiz-meta" style="margin-top: 12px;">
                                                    <div class="quiz-meta-item">
                                                        <i class="fas fa-clock"></i>
                                                        <?= (int) ($q['duration_minutes'] ?? 30) ?> minutes
                                                    </div>
                                                    <div class="quiz-meta-item">
                                                        <i class="fas fa-check-circle"></i>
                                                        <?= htmlspecialchars((string) ($q['passing_score'] ?? 50)) ?>% to pass
                                                    </div>
                                                    <div class="quiz-meta-item">
                                                        <i class="fas fa-repeat"></i>
                                                        <?= (int) ($q['max_attempts'] ?? 1) ?> attempt<?= ((int) ($q['max_attempts'] ?? 1)) !== 1 ? 's' : '' ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="<?= htmlspecialchars(frontofficeRoute('quizzes', 'take', ['id' => (int) $q['id']])); ?>" class="btn-start-quiz">
                                                <i class="fas fa-play"></i> Start Quiz
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="course-info-box">
                        <h3>Course Information</h3>
                        <div class="info-item">
                            <i class="fas fa-layer-group"></i>
                            <div>
                                <strong>Level</strong>
                                <p style="margin: 0;"><?= htmlspecialchars((string) ($course['level'] ?? 'Beginner')) ?></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-road"></i>
                            <div>
                                <strong>Lessons</strong>
                                <p style="margin: 0;"><?= count($lessons ?? []) ?></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-list"></i>
                            <div>
                                <strong>Quizzes</strong>
                                <p style="margin: 0;"><?= count($quizzes ?? []) ?></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-check"></i>
                            <div>
                                <strong>Status</strong>
                                <p style="margin: 0;">
                                    <span style="display: inline-block; padding: 4px 12px; background: #d4edda; color: #155724; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                        <?= htmlspecialchars((string) ($course['status'] ?? 'draft')) ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($lessons) || !empty($quizzes)): ?>
                        <div class="course-info-box" style="background: #f0f4ff; border-left: 4px solid #525fe1;">
                            <h3>Ready to Learn?</h3>
                            <?php if (!empty($lessons)): ?>
                                <p style="font-size: 14px; color: #666; margin-bottom: 20px;">Start with lesson 1 and follow the learning path step by step.</p>
                                <a href="<?= htmlspecialchars(frontofficeRoute('lessons', 'show', ['id' => (int) $lessons[0]['id']])); ?>" class="btn-start-quiz" style="width: 100%; justify-content: center;">
                                    <i class="fas fa-play"></i> Start First Lesson
                                </a>
                            <?php else: ?>
                                <p style="font-size: 14px; color: #666; margin-bottom: 20px;">Start with the first quiz and test your knowledge!</p>
                                <a href="<?= htmlspecialchars(frontofficeRoute('quizzes', 'take', ['id' => (int) $quizzes[0]['id']])); ?>" class="btn-start-quiz" style="width: 100%; justify-content: center;">
                                    <i class="fas fa-play"></i> Begin Learning
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="footer section-padding">
        <div class="container">				
            <div class="row">						
                <div class="col-lg-3 col-sm-6 col-xs-12">
                    <div class="single_footer">
                        <a href="index.html"><img src="edumatch.png" alt="Eduleb"></a>         
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vitae risus nec dui venenatis dignissim.</p>
                        <div class="social_profile">
                            <ul>
                                <li><a class="f_facebook" href="#"><i class="fa-solid fa-x"></i></a></li>
                                <li><a class="f_twitter" href="#"><i class="fa-brands fa-facebook-f"></i></a></li>
                                <li><a class="f_instagram"href="#"><i class="fa-brands fa-instagram"></i></a></li>
                                <li><a class="f_linkedin" href="#"><i class="fa-brands fa-linkedin-in"></i></a></li>
                            </ul>
                        </div>
                    </div>			
                </div>
                <div class="col-lg-2 col-sm-6 col-xs-12">
                    <div class="single_footer">
                        <h4>About Eduleb</h4>
                        <ul>
                            <li><a href="#">About us</a></li>
                            <li><a href="#">Instructor Registration</a></li>
                            <li><a href="#">Become A Teacher</a></li>
                            <li><a href="#">All Instructors</a></li>
                            <li><a href="#">Asked Question</a></li>
                            <li><a href="#">Contact us</a></li>
                        </ul>
                    </div>
                </div>	
                <div class="col-lg-2 col-sm-6 col-xs-12">
                    <div class="single_footer">
                        <h4>Popular Course</h4>
                        <ul>
                            <li><a href="#">Development</a></li>
                            <li><a href="#">Arts & design</a></li>
                            <li><a href="#">Visual Design</a></li>
                            <li><a href="#">Graphic Design</a></li>
                            <li><a href="#">Code Inspection</a></li>						
                            <li><a href="#">Digital Marketing</a></li>						
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-xs-12">
                    <div class="single_footer">
                        <h4>Contact Info</h4>
                        <div class="sf_contact">
                            <span class="ti-map"></span>
                            <p>2570 Quadra Street Victoria Road, New York, Canada</p>
                        </div>
                        <div class="sf_contact">
                            <span class="ti-mobile"></span>
                            <p>+88 457 845 695</p>
                        </div>
                        <div class="sf_contact">
                            <span class="ti-mobile"></span>
                            <p><a href="tel:+88457845695">Contact Whatsapp</a></p>
                        </div>
                        <div class="sf_contact">
                            <span class="ti-email"></span>
                            <p>example@yourmail.com</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-sm-6 col-xs-12">
                    <div class="single_footer">
                        <h4>Download App</h4>
                        <p>Download our app from app store and google play store.</p>
                        <a href="index.html"><img src="assets/img/google-play.jpg" class="foot_img" alt=""></a>  
                        <a href="index.html"><img src="assets/img/app-store.jpg" class="foot_img" alt=""></a>  
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="foot_copy">
        <div class="footer_copyright">
            <p>&copy; 2024. All Rights Reserved by <a href="https://bestwpware.com/">Bestwpware</a></p>
        </div>	
    </div>

    <script src="assets/js/jquery-1.12.4.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/scripts.js"></script>

    <script>
        new WOW().init();
    </script>
</body>
</html>
