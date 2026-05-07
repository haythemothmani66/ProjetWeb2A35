<div id="miniSidebar">
  <div class="brand-logo">
    <a class="d-none d-md-flex align-items-center gap-2" href="/gestion_users/view/backoffice/src/pages/backoffice/dashboard.php">
      <img src="/gestion_users/view/backoffice/src/assets/images/brand/logo/logo-icon.svg" alt="EduMatch logo" />
      <span class="fw-bold fs-4 site-logo-text">EduMatch</span>
    </a>
  </div>

  <ul class="navbar-nav flex-column">
    <li class="nav-item">
      <div class="nav-heading">Administration</div>
      <hr class="mx-5 nav-line mb-1" />
    </li>

    <li class="nav-item">
      <a class="nav-link" href="/gestion_users/view/backoffice/src/pages/backoffice/dashboard.php">
        <span class="nav-icon"><i class="ti ti-dashboard"></i></span>
        <span class="text">Tableau de bord</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="/gestion_users/view/backoffice/src/pages/backoffice/users.php">
        <span class="nav-icon"><i class="ti ti-users"></i></span>
        <span class="text">Liste utilisateurs</span>
      </a>
    </li>

    <li class="nav-item">
      <div class="nav-heading">Partenariat</div>
      <hr class="mx-5 nav-line mb-1" />
    </li>

    <li class="nav-item">
      <a class="nav-link" href="/gestion_users/index.php?controller=partenaire&action=list">
        <span class="nav-icon"><i class="ti ti-building"></i></span>
        <span class="text">Partenaires</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="/gestion_users/index.php?controller=contract&action=list">
        <span class="nav-icon"><i class="ti ti-file-text"></i></span>
        <span class="text">Contrats</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="/gestion_users/index.php?controller=partenaire&action=verification">
        <span class="nav-icon"><i class="ti ti-shield-check"></i></span>
        <span class="text">Verification</span>
      </a>
    </li>
  </ul>
</div>

<div class="offcanvasNav offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
  <div class="offcanvas-header">
    <a class="d-flex align-items-center gap-2" href="/gestion_users/view/backoffice/src/pages/backoffice/dashboard.php">
      <img src="/gestion_users/view/backoffice/src/assets/images/brand/logo/logo-icon.svg" alt="EduMatch logo" />
      <span class="fw-bold fs-4 site-logo-text">EduMatch</span>
    </a>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body p-0">
    <ul class="navbar-nav flex-column">
      <li class="nav-item"><a class="nav-link" href="/gestion_users/view/backoffice/src/pages/backoffice/dashboard.php"><i class="ti ti-dashboard me-2"></i><span>Tableau de bord</span></a></li>
      <li class="nav-item"><a class="nav-link" href="/gestion_users/view/backoffice/src/pages/backoffice/users.php"><i class="ti ti-users me-2"></i><span>Utilisateurs</span></a></li>
      <li class="nav-item"><a class="nav-link" href="/gestion_users/index.php?controller=partenaire&action=list"><i class="ti ti-building me-2"></i><span>Partenaires</span></a></li>
      <li class="nav-item"><a class="nav-link" href="/gestion_users/index.php?controller=contract&action=list"><i class="ti ti-file-text me-2"></i><span>Contrats</span></a></li>
      <li class="nav-item"><a class="nav-link" href="/gestion_users/index.php?controller=partenaire&action=verification"><i class="ti ti-shield-check me-2"></i><span>Verification</span></a></li>
      <li class="nav-item"><a class="nav-link" href="/gestion_users/view/template/index.php"><i class="ti ti-home me-2"></i><span>FrontOffice</span></a></li>
      <li class="nav-item"><a class="nav-link text-danger" href="/gestion_users/auth/logout"><i class="ti ti-logout me-2"></i><span>Deconnexion</span></a></li>
    </ul>
  </div>
</div>
