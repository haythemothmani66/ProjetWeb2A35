<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Completion - <?= htmlspecialchars((string) $certificate['student_name']) ?></title>
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
            <a href="<?= frontofficeRoute('courses', 'index') ?>" class="btn">
                <i class="fas fa-home"></i> Back to Courses
            </a>
        </div>
    </div>

</body>
</html>
