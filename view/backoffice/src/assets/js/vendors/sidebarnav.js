document.addEventListener('DOMContentLoaded', function () {
  var sidebarToggles = document.querySelectorAll('.sidebar-toggle');

  sidebarToggles.forEach(function (toggleButton) {
    toggleButton.addEventListener('click', function () {
      var expanded = localStorage.getItem('sidebarExpanded') === 'true';

      if (expanded) {
        document.documentElement.classList.add('collapsed');
        document.documentElement.classList.remove('expanded');
        localStorage.setItem('sidebarExpanded', 'false');
      } else {
        document.documentElement.classList.remove('collapsed');
        document.documentElement.classList.add('expanded');
        localStorage.setItem('sidebarExpanded', 'true');
      }
    });
  });

  var currentUrl = new URL(window.location.href);
  var currentPath = currentUrl.pathname.replace(/^\/+/, '');
  var currentQuery = currentUrl.search;

  document.querySelectorAll('#miniSidebar .nav-link, #miniSidebar .dropdown-item').forEach(function (link) {
    var href = link.getAttribute('href');

    if (!href || href === '#') {
      return;
    }

    var target;
    try {
      target = new URL(href, window.location.href);
    } catch (error) {
      return;
    }

    var targetPath = target.pathname.replace(/^\/+/, '');
    var pathMatches = targetPath === currentPath;
    var queryMatches = target.search === currentQuery;

    if (pathMatches && (target.search === '' || queryMatches)) {
      link.classList.add('active');
    }
  });

  setSidebarHeight();

  var content = document.getElementById('content');
  if (content) {
    var observer = new MutationObserver(setSidebarHeight);
    observer.observe(content, { childList: true, subtree: true });
  }
});

window.addEventListener('load', setSidebarHeight);
window.addEventListener('resize', setSidebarHeight);

function setSidebarHeight() {
  var sidebar = document.getElementById('miniSidebar');
  var content = document.getElementById('content');

  if (!sidebar || !content) {
    return;
  }

  var contentHeight = content.getBoundingClientRect().height;
  var viewportHeight = window.innerHeight;
  var offset = 45;

  sidebar.style.height = Math.max(viewportHeight - offset, contentHeight) + 'px';
}
