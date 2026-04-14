<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Eduleb - Education Platform">
    <meta name="keywords" content="courses, education, learning">		
    <title>Our Courses - Eduleb</title>			
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
        .courses_section {
            padding: 80px 0;
        }
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        .section-title h2 {
            font-size: 40px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #1e293b;
        }
        .section-title p {
            font-size: 16px;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }
        .course-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            height: 100%;
        }
        .course-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .course-card-img {
            height: 250px;
            object-fit: cover;
        }
        .course-card .card-body {
            padding: 25px;
        }
        .course-card .card-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #1e293b;
            line-height: 1.4;
        }
        .course-card .card-text {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
            min-height: 60px;
        }
        .course-level {
            display: inline-block;
            padding: 6px 12px;
            background: #f0f4ff;
            color: #525fe1;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .btn-view-course {
            background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            font-weight: 600;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-view-course:hover {
            transform: translateX(5px);
            color: white;
            text-decoration: none;
        }
        .no-courses {
            text-align: center;
            padding: 60px 20px;
        }
        .no-courses h3 {
            color: #1e293b;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .no-courses p {
            color: #666;
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
                            <li class="menu-item-has-children"><a href="<?= htmlspecialchars(frontofficeRoute('courses', 'index')); ?>" class="active">Courses</a></li>
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

    <section class="home_bg hb_height" style="background-image: url(assets/img/bg/home-bg.jpg); background-size: cover; background-position: center center;">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-sm-12 col-xs-12">
                    <div class="hero-text ht_top">
                        <h1><span>Explore</span> Our Courses & Start Learning</h1>
                        <p>Discover a wide range of courses designed to help you learn new skills and advance your career</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="courses_section">
        <div class="container">
            <div class="section-title">
                <h2>Featured Courses</h2>
                <p>Choose from our collection of carefully curated courses that will help you master new skills</p>
            </div>

            <?php if (!empty($courses)): ?>
                <div class="row">
                    <?php foreach ($courses as $c): ?>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-4 wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.2s" data-wow-offset="0">
                            <div class="card course-card h-100">
                                <?php if (!empty($c['image'])): ?>
                                    <img src="<?= htmlspecialchars((string) $c['image']) ?>" class="card-img-top course-card-img" alt="<?= htmlspecialchars((string) $c['title']) ?>">
                                <?php else: ?>
                                    <div class="course-card-img" style="background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 48px;">
                                        <i class="fas fa-book"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <span class="course-level"><?= htmlspecialchars((string) ($c['level'] ?? 'beginner')) ?></span>
                                    <h5 class="card-title"><?= htmlspecialchars((string) $c['title']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars((string) (substr($c['description'] ?? '', 0, 100))) ?><?= strlen($c['description'] ?? '') > 100 ? '...' : '' ?></p>
                                    <a href="<?= htmlspecialchars(frontofficeRoute('courses', 'show', ['id' => (int) $c['id']])); ?>" class="btn-view-course">
                                        <i class="fas fa-arrow-right"></i> View Course
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-courses">
                    <i class="fas fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
                    <h3>No Courses Available</h3>
                    <p>Check back soon for new courses</p>
                </div>
            <?php endif; ?>
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
