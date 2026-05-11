<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Completion - <?= htmlspecialchars((string) $certificate['student_name']) ?></title>
    <meta property="og:title" content="Certificate of Completion - <?= htmlspecialchars((string) $certificate['course_title']) ?>" />
    <meta property="og:description" content="I just achieved the <?= htmlspecialchars((string) $certificate['course_title']) ?> certificate!" />
    <meta property="og:url" content="<?= 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>" />
    <!-- LinkedIn requires a direct image URL for the preview. Here we use the site logo, but you could link to a generated image of the certificate if you have one. -->
    <meta property="og:image" content="<?= 'http://' . $_SERVER['HTTP_HOST'] . '/web/haythemweb/assets/img/logo.png' ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Great+Vibes&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Montserrat', sans-serif;
        }

        .cert-wrapper {
            background: #fff;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 12px;
            margin: 20px;
        }

        .certificate {
            width: 900px;
            height: 650px;
            padding: 40px;
            box-sizing: border-box;
            background: #fff;
            position: relative;
            border: 15px solid #2c3e50;
            outline: 5px solid #d4af37;
            outline-offset: -20px;
            text-align: center;
            color: #2c3e50;
            background-image: linear-gradient(rgba(255,255,255,0.9), rgba(255,255,255,0.9)), url('data:image/svg+xml;utf8,<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path d="M50 0 L100 50 L50 100 L0 50 Z" fill="%23f0f0f0"/></svg>');
        }

        .header {
            margin-top: 40px;
            margin-bottom: 40px;
        }

        .title {
            font-family: 'Cinzel', serif;
            font-size: 54px;
            font-weight: 700;
            color: #2c3e50;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin: 0;
        }

        .subtitle {
            font-size: 18px;
            color: #7f8c8d;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .content {
            margin: 40px 0;
        }

        .text {
            font-size: 20px;
            color: #34495e;
            margin-bottom: 20px;
        }

        .student-name {
            font-family: 'Great Vibes', cursive;
            font-size: 64px;
            color: #c0392b;
            margin: 20px 0;
            border-bottom: 2px solid #bdc3c7;
            display: inline-block;
            padding: 0 40px;
            line-height: 1;
        }

        .course-title {
            font-family: 'Cinzel', serif;
            font-size: 32px;
            font-weight: 700;
            color: #2980b9;
            margin: 20px 0;
        }

        .footer {
            position: absolute;
            bottom: 60px;
            left: 60px;
            right: 60px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .signature, .date {
            width: 250px;
            text-align: center;
        }

        .signature-line, .date-line {
            border-top: 1px solid #7f8c8d;
            margin-top: 10px;
            padding-top: 5px;
            font-size: 14px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .signature img {
            height: 50px;
            margin-bottom: -10px;
        }

        .seal {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 120px;
            background: #d4af37;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            border: 4px dashed #fff;
            box-shadow: 0 0 0 6px #d4af37, 0 5px 15px rgba(0,0,0,0.2);
        }

        .seal::before {
            content: '\f0a3';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 50px;
            color: #fff;
        }

        .actions {
            margin-top: 20px;
            text-align: center;
        }

        .btn {
            background: #2c3e50;
            color: #fff;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            margin: 0 10px;
        }

        .btn:hover {
            background: #34495e;
            transform: translateY(-2px);
        }

        .btn-print {
            background: #d4af37;
            color: #fff;
        }
        
        .btn-print:hover {
            background: #b5952f;
        }

        .btn-linkedin {
            background: #0A66C2;
            color: #fff;
        }
        
        .btn-linkedin:hover {
            background: #004182;
        }

        @media print {
            body {
                background: none;
            }
            .cert-wrapper {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div>
        <div class="cert-wrapper">
            <div class="certificate" id="certificate">
                <div class="header">
                    <h1 class="title">Certificate of Completion</h1>
                    <div class="subtitle">This is to certify that</div>
                </div>

                <div class="content">
                    <div class="student-name"><?= htmlspecialchars((string) $certificate['student_name']) ?></div>
                    <div class="text">has successfully completed the course</div>
                    <div class="course-title"><?= htmlspecialchars((string) $certificate['course_title']) ?></div>
                    <div class="text">and achieved a passing grade in the final assessment</div>
                </div>

                <div class="footer">
                    <div class="date">
                        <div style="font-size: 20px; margin-bottom: 5px;">
                            <?= date('F j, Y', strtotime($certificate['issued_at'])) ?>
                        </div>
                        <div class="date-line">Date Issued</div>
                    </div>

                    <div class="signature">
                        <div style="font-family: 'Great Vibes', cursive; font-size: 30px; color: #2c3e50;">EduMatch Instructor</div>
                        <div class="signature-line">Authorized Signature</div>
                    </div>
                </div>

                <div class="seal"></div>
            </div>
        </div>

        <div class="actions">
            <button onclick="window.print()" class="btn btn-print">
                <i class="fas fa-print"></i> Print / Save as PDF
            </button>
            <button id="shareLinkedIn" class="btn btn-linkedin">
                <i class="fab fa-linkedin"></i> Share on LinkedIn
            </button>
            <a href="<?= frontofficeRoute('courses', 'index') ?>" class="btn">
                <i class="fas fa-home"></i> Back to Courses
            </a>
        </div>
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
  document.getElementById('shareLinkedIn').addEventListener('click', function() {
    const btn = this;
    const originalText = btn.innerHTML;
    
    // Show a loading state on the button
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparing Image...';
    btn.disabled = true;

    // 1. Generate an Image from the HTML certificate using html2canvas
    const certElement = document.getElementById('certificate');
    
    html2canvas(certElement, { scale: 2 }).then(canvas => {
        // 2. Download the generated image for the user
        const imgData = canvas.toDataURL('image/jpeg', 0.9);
        const link = document.createElement('a');
        link.download = 'My_EduMatch_Certificate.jpg';
        link.href = imgData;
        link.click(); // Triggers the download
        
        // 3. Prepare the LinkedIn Text (Without the localhost URL as requested!)
        const courseTitle = <?= json_encode($certificate['course_title']) ?>;
        const postText = `I am thrilled to announce that I have successfully completed the course and achieved the Certificate of Completion for "${courseTitle}" on EduMatch! 🎓🚀`;
        const encodedText = encodeURIComponent(postText);
        
        // 4. Open LinkedIn Feed to create the post
        const linkedInShareUrl = `https://www.linkedin.com/feed/?shareActive=true&text=${encodedText}`;
        const popupWidth = 600;
        const popupHeight = 600;
        const left = (window.innerWidth / 2) - (popupWidth / 2) + window.screenX;
        const top = (window.innerHeight / 2) - (popupHeight / 2) + window.screenY;
        
        const popup = window.open(
          linkedInShareUrl, 
          'linkedinShareWindow', 
          `toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=${popupWidth},height=${popupHeight},top=${top},left=${left}`
        );

        if (!popup || popup.closed || typeof popup.closed === 'undefined') {
            alert("Popup blocked! Please allow popups to share on LinkedIn.");
        } else {
            // Give them a helpful instruction to upload the image
            alert("✅ We have downloaded your certificate as an image!\n\nPlease attach 'My_EduMatch_Certificate.jpg' from your Downloads folder to your LinkedIn post.");
            if (window.focus) {
                popup.focus();
            }
        }

        // Restore button state
        btn.innerHTML = originalText;
        btn.disabled = false;
    }).catch(err => {
        console.error("Error generating image", err);
        alert("Failed to generate the certificate image.");
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
  });
</script>
</body>
</html>
