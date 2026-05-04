<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Your quiz result for <?= htmlspecialchars((string) $quiz['title']) ?>">		
    <title>Quiz Result - Eduleb</title>			
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
        body {
            background: #f8f9fa;
        }
        .result-header {
            background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%);
            color: white;
            padding: 60px 0;
            margin-top: 80px;
        }
        .result-header h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .result-container {
            padding: 60px 0;
        }
        .result-card {
            background: white;
            border-radius: 12px;
            padding: 60px 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        .result-icon {
            font-size: 80px;
            margin-bottom: 30px;
        }
        .result-icon.passed {
            color: #4caf50;
        }
        .result-icon.failed {
            color: #f44336;
        }
        .result-message {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 30px;
        }
        .result-message.passed {
            color: #4caf50;
        }
        .result-message.failed {
            color: #f44336;
        }
        .score-display {
            margin: 40px 0;
        }
        .score-large {
            font-size: 64px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
        }
        .score-label {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }
        .result-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 30px;
            margin: 40px 0;
            text-align: left;
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .detail-item:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #1e293b;
        }
        .detail-value {
            font-size: 18px;
            font-weight: 700;
            color: #525fe1;
        }
        .result-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        .btn-action {
            padding: 14px 30px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            transition: 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            border: none;
        }
        .btn-try-again {
            background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%);
            color: white;
        }
        .btn-try-again:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(82, 95, 225, 0.3);
            color: white;
            text-decoration: none;
        }
        .btn-back {
            background: white;
            color: #1e293b;
            border: 2px solid #e0e0e0;
        }
        .btn-back:hover {
            background: #f8f9fa;
            border-color: #525fe1;
            color: #525fe1;
            text-decoration: none;
        }
        .btn-back-header {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            text-decoration: none;
            color: white;
            font-weight: 600;
            transition: 0.3s;
            margin-bottom: 30px;
        }
        .btn-back-header:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
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

    <?php $percent = (float) ($result['percent'] ?? 0); ?>
    <?php $passed = (bool) ($result['passed'] ?? false); ?>

    <section class="result-header">
        <div class="container">
            <a href="<?= htmlspecialchars(frontofficeRoute('courses', 'show', ['id' => (int) $course['id']])); ?>" class="btn-back-header">
                <i class="fas fa-arrow-left"></i> Back to Course
            </a>
            <h1>Quiz Results</h1>
            <p><?= htmlspecialchars((string) $quiz['title']) ?></p>
        </div>
    </section>

    <section class="result-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="result-card">
                        <div class="result-icon <?= $passed ? 'passed' : 'failed' ?>">
                            <?php if ($passed): ?>
                                <i class="fas fa-check-circle"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle"></i>
                            <?php endif; ?>
                        </div>

                        <div class="result-message <?= $passed ? 'passed' : 'failed' ?>">
                            <?= $passed ? 'Congratulations!' : 'Keep Learning' ?>
                        </div>

                        <div class="score-label">
                            <?= $passed ? 'You passed the quiz!' : 'You didn\'t pass this time. Try again!' ?>
                        </div>

                        <div class="score-display">
                            <div class="score-large">
                                <?= number_format($percent, 1) ?>%
                            </div>
                            <div class="score-label">
                                Your Score
                            </div>
                        </div>

                        <div class="result-details">
                            <div class="detail-item">
                                <span class="detail-label">
                                    <i class="fas fa-star" style="color: #ffc107;"></i> Total Score
                                </span>
                                <span class="detail-value">
                                    <?= number_format((float) ($result['earned'] ?? 0), 2) ?> / <?= number_format((float) ($result['total'] ?? 0), 2) ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">
                                    <i class="fas fa-bullseye" style="color: #2196f3;"></i> Passing Score
                                </span>
                                <span class="detail-value">
                                    <?= htmlspecialchars((string) ($quiz['passing_score'] ?? 50)) ?>%
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">
                                    <i class="fas fa-flag" style="color: #ff9800;"></i> Status
                                </span>
                                <span class="detail-value">
                                    <?= $passed ? 'Ã¢Å“â€œ Passed' : 'Ã¢Å“â€” Failed' ?>
                                </span>
                            </div>
                        </div>

                        <div class="result-actions">
                            <a href="<?= htmlspecialchars(frontofficeRoute('quizzes', 'take', ['id' => (int) $quiz['id']])); ?>" class="btn-action btn-try-again">
                                <i class="fas fa-redo"></i> Try Again
                            </a>
                            <a href="<?= htmlspecialchars(frontofficeRoute('courses', 'show', ['id' => (int) $course['id']])); ?>" class="btn-action btn-back">
                                <i class="fas fa-book"></i> Back to Course
                            </a>
                        </div>

                        <?php if ($passed): ?>
                        <div class="mt-5 p-4" style="background: #e8f5e9; border-radius: 8px; border-left: 4px solid #4caf50;">
                            <h4 style="color: #2e7d32; margin-bottom: 15px;"><i class="fas fa-award"></i> Claim Your Certificate</h4>
                            <p style="color: #1b5e20;">You've successfully passed the quiz! Enter your name below to generate your certificate.</p>
                            <form action="<?= htmlspecialchars(frontofficeRoute('certificates', 'generate')); ?>" method="POST" class="d-flex align-items-center justify-content-center gap-2 mt-3" style="flex-wrap: wrap;">
                                <input type="hidden" name="quiz_id" value="<?= (int) $quiz['id']; ?>">
                                <input type="text" name="student_name" placeholder="Enter your full name" required class="form-control" style="max-width: 300px; padding: 12px; border-radius: 6px; border: 1px solid #c8e6c9;">
                                <button type="submit" class="btn-action" style="background: #4caf50; color: white; border: none;">
                                    <i class="fas fa-certificate"></i> Generate Certificate
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
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
