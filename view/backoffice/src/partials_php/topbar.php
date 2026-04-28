<div class="navbar-glass navbar navbar-expand-lg px-0 px-lg-4">
  <div class="container-fluid px-lg-0">
    <div class="d-flex align-items-center gap-4">
      <div class="d-block d-lg-none">
        <a class="text-inherit" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">
          <i class="ti ti-menu-2 fs-4"></i>
        </a>
      </div>
      <div class="d-none d-lg-block">
        <a class="sidebar-toggle d-flex texttooltip p-3" href="javascript:void(0)" data-template="collapseMessage">
          <span class="collapse-mini"><i class="ti ti-arrow-bar-left text-secondary"></i></span>
          <span class="collapse-expanded"><i class="ti ti-arrow-bar-right text-secondary"></i>
            <div id="collapseMessage" class="d-none"><span class="small">Collapse</span></div>
          </span>
        </a>
      </div>
      <!-- Home button -->
      <a href="/gestion_users/view/template/index.php" class="btn btn-ghost btn-sm d-none d-lg-inline-flex align-items-center gap-1" title="Retour au FrontOffice">
        <i class="ti ti-home fs-5"></i>
        <span>Home</span>
      </a>
    </div>

    <ul class="list-unstyled d-flex align-items-center mb-0 gap-2">
      <!-- Theme toggle -->
      <li>
        <div class="dropdown">
          <button class="btn btn-ghost btn-icon rounded-circle d-flex align-items-center" type="button" aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme">
            <i class="ti theme-icon-active lh-1 fs-5"><i class="ti theme-icon ti-sun"></i></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <li><button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="light"><i class="ti theme-icon ti ti-sun"></i><span class="ms-2">Light</span></button></li>
            <li><button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark"><i class="ti theme-icon ti-moon-stars"></i><span class="ms-2">Dark</span></button></li>
            <li><button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="auto"><i class="ti theme-icon ti-circle-half-2"></i><span class="ms-2">Auto</span></button></li>
          </ul>
        </div>
      </li>
      <!-- User dropdown -->
      <li class="ms-3 dropdown">
        <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" class="d-flex align-items-center gap-2 text-decoration-none">
          <img src="/gestion_users/uploads/photos/<?= htmlspecialchars($_SESSION['user_photo'] ?? 'default.png') ?>" alt="" class="avatar avatar-sm rounded-circle" style="object-fit:cover;" onerror="this.src='/gestion_users/uploads/photos/default.png';" />
          <span class="d-none d-lg-inline fw-semibold"><?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?></span>
          <i class="ti ti-chevron-down d-none d-lg-inline" style="font-size:12px;"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end p-0" style="min-width:220px;">
          <div class="d-flex gap-3 align-items-center border-bottom px-4 py-3">
            <img src="/gestion_users/uploads/photos/<?= htmlspecialchars($_SESSION['user_photo'] ?? 'default.png') ?>" alt="" class="avatar avatar-md rounded-circle" style="object-fit:cover;" onerror="this.src='/gestion_users/uploads/photos/default.png';" />
            <div>
              <h5 class="mb-0 fs-6"><?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?></h5>
              <p class="mb-0 text-secondary small text-capitalize"><?= htmlspecialchars($_SESSION['user_role'] ?? '') ?></p>
            </div>
          </div>
          <div class="p-2">
            <a href="/gestion_users/view/backoffice/src/pages/backoffice/profil-admin.php" class="dropdown-item rounded"><i class="ti ti-user me-2"></i>Mon Profil</a>
            <a href="/gestion_users/view/template/index.php" class="dropdown-item rounded"><i class="ti ti-home me-2"></i>FrontOffice</a>
            <hr class="my-1">
            <a href="/gestion_users/auth/logout" class="dropdown-item rounded text-danger"><i class="ti ti-logout me-2"></i>Deconnexion</a>
          </div>
        </div>
      </li>
    </ul>
  </div>
</div>
