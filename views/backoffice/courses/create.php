<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice | New Course</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/backoffice.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: #f5f7fc; color: #1e293b; overflow-x: hidden; }

        .page-title h1 {
            font-size: 26px;
            font-weight: 700;
            color: #0b104a;
        }

        .admin-badge {
            background: #525fe1;
            color: white;
            padding: 8px 18px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 14px;
        }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 25px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b; font-size: 14px; }
        input[type="text"],
        input[type="email"],
        textarea,
        select { width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-family: inherit; font-size: 14px; transition: 0.3s; }
        input[type="text"]:focus,
        textarea:focus,
        select:focus { outline: none; border-color: #525fe1; box-shadow: 0 0 0 3px rgba(82, 95, 225, 0.1); }
        textarea { resize: vertical; min-height: 120px; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .image-upload { border: 2px dashed #e0e0e0; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: 0.3s; background: #f8f9fa; }
        .image-upload:hover { border-color: #525fe1; background: #f0f4ff; }
        .image-upload i { font-size: 36px; color: #525fe1; margin-bottom: 12px; display: block; }
        .image-upload p { color: #666; margin-bottom: 8px; font-size: 14px; }
        .image-upload .small { font-size: 12px; color: #999; }
        input[type="file"] { display: none; }
        .image-preview { margin-bottom: 20px; display: none; }
        .image-preview img { max-width: 100%; height: auto; border-radius: 8px; }
        .preview-label { font-size: 12px; color: #999; margin-top: 8px; text-align: center; }
        .form-actions { display: flex; gap: 12px; margin-top: 30px; }
        .btn { padding: 12px 24px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; transition: 0.3s; font-size: 14px; text-decoration: none; display: inline-block; text-align: center; }
        .btn-primary { background: #525fe1; color: white; }
        .btn-primary:hover { background: #3d4bc7; }
        .btn-secondary { background: #f0f4ff; color: #525fe1; border: 1px solid #525fe1; }
        .btn-secondary:hover { background: #e3f2fd; }
        .error-message { color: #ef4444; font-size: 13px; margin-top: 8px; display: none; }
        .invalid { border-color: #ef4444 !important; }
        @media (max-width: 600px) { .row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="admin-wrapper" id="adminWrapper">
        <?php
        $sidebarSection = 'courses';
        $sidebarCourseId = null;
        require dirname(__DIR__) . '/partials/sidebar.php';
        ?>

        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <h1>Create New Course</h1>
                </div>
                <div class="admin-badge">Admin</div>
            </div>

            <div class="container">
        
        <form id="courseForm" method="post" action="<?= htmlspecialchars(backofficeRoute('courses', 'store')); ?>" enctype="multipart/form-data" novalidate>
            <div class="form-group">
                <label for="title">Course Title</label>
                <input type="text" id="title" name="title" placeholder="Enter course title">
                <div class="error-message" id="titleError"></div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Enter course description"></textarea>
                <div class="error-message" id="descriptionError"></div>
            </div>

            <div class="form-group">
                <label>Course Image</label>
                <div class="image-upload" onclick="document.getElementById('imageInput').click();">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click to upload image</p>
                    <p class="small">PNG, JPG, GIF up to 5MB</p>
                </div>
                <div class="image-preview">
                    <img id="imagePreview" src="" alt="Preview">
                    <p class="preview-label"><i class="fas fa-check-circle" style="color: #4caf50; margin-right: 5px;"></i> Image selected</p>
                </div>
                <input type="file" id="imageInput" name="image" accept="image/*" onchange="previewImage(event)">
                <div class="error-message" id="imageInputError"></div>
            </div>

            <div class="row">
                <div class="form-group">
                    <label for="level">Level</label>
                    <select id="level" name="level">
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                    <div class="error-message" id="levelError"></div>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                    <div class="error-message" id="statusError"></div>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Create Course</button>
                <a href="<?= htmlspecialchars(backofficeRoute('courses', 'index')); ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('imagePreview');
            const previewContainer = document.querySelector('.image-preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        document.getElementById('courseForm').addEventListener('submit', function(event) {
            let isValid = true;
            const title = document.getElementById('title');
            const description = document.getElementById('description');
            const imageInput = document.getElementById('imageInput');
            const level = document.getElementById('level');
            const status = document.getElementById('status');

            const clearError = (element) => {
                element.classList.remove('invalid');
                const message = document.getElementById(`${element.id}Error`);
                if (message) {
                    message.textContent = '';
                    message.style.display = 'none';
                }
            };

            const showError = (element, text) => {
                element.classList.add('invalid');
                const message = document.getElementById(`${element.id}Error`);
                if (message) {
                    message.textContent = text;
                    message.style.display = 'block';
                }
            };

            [title, description, level, status].forEach(clearError);
            clearError(imageInput);

            if (!title.value.trim()) {
                showError(title, 'Course title is required.');
                isValid = false;
            } else if (title.value.trim().length < 3) {
                showError(title, 'Course title must have at least 3 characters.');
                isValid = false;
            }

            if (!description.value.trim()) {
                showError(description, 'Description is required.');
                isValid = false;
            } else if (description.value.trim().length < 10) {
                showError(description, 'Description must have at least 10 characters.');
                isValid = false;
            }

            if (!level.value) {
                showError(level, 'Please select a level.');
                isValid = false;
            }

            if (!status.value) {
                showError(status, 'Please select a status.');
                isValid = false;
            }

            if (imageInput.files.length > 0) {
                const file = imageInput.files[0];
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                const maxSize = 5 * 1024 * 1024;

                if (!allowedTypes.includes(file.type)) {
                    showError(imageInput, 'Allowed image types are JPG, PNG, and GIF.');
                    isValid = false;
                } else if (file.size > maxSize) {
                    showError(imageInput, 'Image size must be 5MB or less.');
                    isValid = false;
                }
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    </script>
        </div>
    </div>
</body>
</html>
