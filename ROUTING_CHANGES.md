# Backoffice Routes & File Organization - Summary of Changes

## Overview
The backoffice routing has been restructured and organized for better maintainability and consistency. All routes now follow a centralized routing pattern with helper functions.

## Key Changes

### 1. **New Routing Infrastructure**
- ✅ Created `app/Router.php` - Central router class handling HTTP requests
- ✅ Created `app/routes.php` - Route helper functions (`route()`, `backofficeRoute()`, `frontofficeRoute()`)
- Provides consistent URL generation across the application

### 2. **Entry Points**
- ✅ **`index.php`** - Frontoffice (public website) entry point
  - Default route: `frontoffice/courses/index`
  - Usage: `index.php?route=frontoffice/courses/index`

- ✅ **`admin.php`** - Backoffice (admin dashboard) entry point (NEW)
  - Default route: `backoffice/dashboard/index`
  - Usage: `admin.php?route=backoffice/courses/index`

### 3. **Controllers Updated**
- ✅ `BackofficeCoursesController` - Fixed all redirects
- ✅ `BackofficeQuizzesController` - Fixed all redirects
- ✅ `BackofficeDashboardController` - NEW dashboard controller
- All now use `backofficeRoute()` helper for consistent URLs

### 4. **Views Updated**
All backoffice views now use route helper functions:

**Courses Views:**
- ✅ `views/backoffice/courses/index.php`
- ✅ `views/backoffice/courses/create.php`
- ✅ `views/backoffice/courses/edit.php`

**Quizzes Views:**
- ✅ `views/backoffice/quizzes/index.php`
- ✅ `views/backoffice/quizzes/create.php`
- ✅ `views/backoffice/quizzes/edit.php`

**Dashboard Views:**
- ✅ `views/backoffice/dashboard/index.php` - NEW

### 5. **File Organization**
```
app/
├── Router.php          (NEW) - Main routing class
└── routes.php          (NEW) - Route helper functions

controllers/
├── BackofficeDashboardController.php  (NEW)
├── BackofficeCoursesController.php    (UPDATED)
├── BackofficeQuizzesController.php    (UPDATED)
├── FrontofficeCoursesController.php
└── FrontofficeQuizzesController.php

views/
├── backoffice/
│   ├── dashboard/
│   │   └── index.php       (NEW) - Admin dashboard
│   ├── courses/            (UPDATED)
│   └── quizzes/            (UPDATED)
└── frontoffice/
```

## URL Examples

### Old Routes (Deprecated)
```
index.php?route=backoffice/courses/create
index.php?route=backoffice/courses/edit&id=1
```

### New Routes
```
admin.php?route=backoffice/dashboard/index
admin.php?route=backoffice/courses/index
admin.php?route=backoffice/courses/create
admin.php?route=backoffice/courses/edit&id=1
```

### Route Helper Usage
```php
// In views:
backofficeRoute('courses', 'index')           // admin.php?route=backoffice/courses/index
backofficeRoute('courses', 'create')          // admin.php?route=backoffice/courses/create
backofficeRoute('courses', 'edit', ['id' => 1])  // admin.php?route=backoffice/courses/edit&id=1

frontofficeRoute('courses', 'index')          // index.php?route=frontoffice/courses/index
```

## Benefits
1. **Centralized Routing** - All routes go through Router class
2. **Consistent URLs** - Route helpers prevent typos and inconsistencies
3. **Better Organization** - Clear separation of frontoffice and backoffice
4. **Dashboard** - New admin dashboard to track statistics
5. **Maintainability** - Easier to change URLs globally
6. **Type Safety** - Helper functions make routing explicit

## Notes
- The old `admin.html` file can be removed as it's no longer used (served by `admin.php`)
- All links in views now use htmlspecialchars() for XSS protection
- Router validates areas and resources before dispatching

## Next Steps (Optional)
- Add authentication middleware to admin routes
- Create a base layout template for backoffice
- Add more dashboard widgets and analytics
- Implement URL rewriting for clean URLs (e.g., `/admin/courses` instead of `admin.php?route=...`)
