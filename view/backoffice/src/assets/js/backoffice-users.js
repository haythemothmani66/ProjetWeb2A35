'use strict';

(() => {
  const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const PHONE_REGEX = /^\d+$/;
  const VALID_ROLES = ['student', 'teacher', 'admin'];
  const VALID_STATUSES = ['active', 'pending', 'suspended'];

  const state = {
    users: [],
    search: '',
    role: '',
    status: '',
  };

  const apiBase = window.location.port === '4000' ? '' : 'http://localhost:4000';
  const currentRole = localStorage.getItem('edumatch-user-role') || 'admin';

  const elements = {
    searchInput: document.getElementById('searchInput'),
    roleFilter: document.getElementById('roleFilter'),
    statusFilter: document.getElementById('statusFilter'),
    clearFiltersBtn: document.getElementById('clearFiltersBtn'),
    usersTableBody: document.getElementById('usersTableBody'),
    usersCountBadge: document.getElementById('usersCountBadge'),
    openCreateUserBtn: document.getElementById('openCreateUserBtn'),
    userForm: document.getElementById('userForm'),
    userFormModalLabel: document.getElementById('userFormModalLabel'),
    saveUserBtn: document.getElementById('saveUserBtn'),
    userId: document.getElementById('userId'),
    email: document.getElementById('email'),
    password: document.getElementById('password'),
    role: document.getElementById('role'),
    status: document.getElementById('status'),
    nom: document.getElementById('nom'),
    prenom: document.getElementById('prenom'),
    telephone: document.getElementById('telephone'),
    photo: document.getElementById('photo'),
    userDetailsBody: document.getElementById('userDetailsBody'),
    statTotalUsers: document.getElementById('statTotalUsers'),
    statActiveUsers: document.getElementById('statActiveUsers'),
    statPendingUsers: document.getElementById('statPendingUsers'),
    statSuspendedUsers: document.getElementById('statSuspendedUsers'),
    teacherValidationNotice: document.getElementById('teacherValidationNotice'),
    pendingTeachersCount: document.getElementById('pendingTeachersCount'),
    filterPendingTeachersBtn: document.getElementById('filterPendingTeachersBtn'),
    toastContainer: document.getElementById('toastContainer'),
  };

  const userFormModal = new bootstrap.Modal(document.getElementById('userFormModal'));
  const userDetailsModal = new bootstrap.Modal(document.getElementById('userDetailsModal'));

  function setSidebarActiveState() {
    const links = document.querySelectorAll('.navbar-nav .nav-link');
    links.forEach((link) => {
      const href = link.getAttribute('href') || '';
      const isUsersLink = href.includes('pages/backoffice/users.html');
      link.classList.toggle('active', isUsersLink);
    });
  }

  function escapeHtml(value) {
    return String(value || '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#39;');
  }

  function roleBadge(role) {
    if (role === 'admin') {
      return '<span class="badge text-primary-emphasis bg-primary-subtle">Admin</span>';
    }

    if (role === 'teacher') {
      return '<span class="badge text-info-emphasis bg-info-subtle">Teacher</span>';
    }

    return '<span class="badge text-secondary-emphasis bg-secondary-subtle">Student</span>';
  }

  function statusBadge(status) {
    if (status === 'active') {
      return '<span class="badge text-success-emphasis bg-success-subtle">Active</span>';
    }

    if (status === 'pending') {
      return '<span class="badge text-warning-emphasis bg-warning-subtle">Pending</span>';
    }

    return '<span class="badge text-danger-emphasis bg-danger-subtle">Suspended</span>';
  }

  function showToast(message, variant) {
    const color = variant === 'danger' ? 'danger' : 'success';
    const toastId = `toast-${Date.now()}`;

    const toastMarkup = `
      <div id="${toastId}" class="toast align-items-center text-bg-${color} border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">${escapeHtml(message)}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    `;

    elements.toastContainer.insertAdjacentHTML('beforeend', toastMarkup);
    const toastElement = document.getElementById(toastId);
    const toast = bootstrap.Toast.getOrCreateInstance(toastElement, { delay: 2500 });

    toast.show();

    toastElement.addEventListener('hidden.bs.toast', () => {
      toastElement.remove();
    });
  }

  async function apiRequest(path, options = {}) {
    const headers = {
      'Content-Type': 'application/json',
      'x-user-role': currentRole,
      ...(options.headers || {}),
    };

    const response = await fetch(`${apiBase}${path}`, {
      ...options,
      headers,
    });

    let payload = {};

    try {
      payload = await response.json();
    } catch (error) {
      payload = {};
    }

    if (!response.ok) {
      const requestError = new Error(payload.message || 'Request failed.');
      requestError.status = response.status;
      requestError.payload = payload;
      throw requestError;
    }

    return payload;
  }

  function updateStats(stats) {
    elements.statTotalUsers.textContent = String(stats.totalUsers || 0);
    elements.statActiveUsers.textContent = String(stats.activeUsers || 0);
    elements.statPendingUsers.textContent = String(stats.pendingUsers || 0);
    elements.statSuspendedUsers.textContent = String(stats.suspendedUsers || 0);

    const pendingTeachers = Number(stats.pendingTeacherValidations || 0);
    elements.pendingTeachersCount.textContent = String(pendingTeachers);

    if (pendingTeachers > 0) {
      elements.teacherValidationNotice.classList.remove('d-none');
    } else {
      elements.teacherValidationNotice.classList.add('d-none');
    }
  }

  function renderUsersTable() {
    elements.usersCountBadge.textContent = `${state.users.length} user(s)`;

    if (!state.users.length) {
      elements.usersTableBody.innerHTML = `
        <tr>
          <td colspan="6" class="text-center py-5 text-secondary">No users found for current filters.</td>
        </tr>
      `;
      return;
    }

    const rows = state.users
      .map((user) => {
        const toggleActionLabel = user.status === 'active' ? 'Deactivate' : 'Activate';
        const toggleActionName = user.status === 'active' ? 'deactivate' : 'activate';

        return `
          <tr>
            <td>${user.id}</td>
            <td>${escapeHtml(user.name)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${roleBadge(user.role)}</td>
            <td>${statusBadge(user.status)}</td>
            <td class="text-end">
              <div class="d-flex justify-content-end flex-wrap gap-2">
                <button type="button" class="btn btn-sm btn-white" data-action="view" data-id="${user.id}">View</button>
                <button type="button" class="btn btn-sm btn-white" data-action="edit" data-id="${user.id}">Edit</button>
                <button type="button" class="btn btn-sm btn-warning" data-action="${toggleActionName}" data-id="${user.id}">${toggleActionLabel}</button>
                <button type="button" class="btn btn-sm btn-danger" data-action="suspend" data-id="${user.id}">Suspend</button>
                ${
                  user.role === 'teacher' && user.status === 'pending'
                    ? `<button type="button" class="btn btn-sm btn-success" data-action="validateTeacher" data-id="${user.id}">Validate Teacher</button>`
                    : ''
                }
                <button type="button" class="btn btn-sm btn-dark" data-action="delete" data-id="${user.id}">Delete</button>
              </div>
            </td>
          </tr>
        `;
      })
      .join('');

    elements.usersTableBody.innerHTML = rows;
  }

  function clearValidationErrors() {
    ['email', 'password', 'role', 'status', 'nom', 'prenom', 'telephone', 'photo'].forEach((field) => {
      const target = document.getElementById(`error-${field}`);
      if (target) {
        target.textContent = '';
      }
    });
  }

  function applyValidationErrors(errors) {
    Object.entries(errors).forEach(([field, message]) => {
      const target = document.getElementById(`error-${field}`);

      if (target) {
        target.textContent = message;
      }
    });
  }

  function getFormValues() {
    return {
      id: elements.userId.value.trim(),
      email: elements.email.value.trim().toLowerCase(),
      password: elements.password.value,
      role: elements.role.value,
      status: elements.status.value,
      nom: elements.nom.value.trim(),
      prenom: elements.prenom.value.trim(),
      telephone: elements.telephone.value.trim(),
      photo: elements.photo.value.trim(),
    };
  }

  function validateForm(values, isEdit) {
    const errors = {};

    if (!EMAIL_REGEX.test(values.email)) {
      errors.email = 'Email format is invalid.';
    }

    if (!isEdit || values.password) {
      if (values.password.length < 6) {
        errors.password = 'Password must be at least 6 characters long.';
      }
    }

    if (!VALID_ROLES.includes(values.role)) {
      errors.role = 'Role is invalid.';
    }

    if (!VALID_STATUSES.includes(values.status)) {
      errors.status = 'Status is invalid.';
    }

    if (!values.nom) {
      errors.nom = 'Nom is required.';
    }

    if (!values.prenom) {
      errors.prenom = 'Prenom is required.';
    }

    if (!PHONE_REGEX.test(values.telephone)) {
      errors.telephone = 'Telephone must contain numbers only.';
    }

    return errors;
  }

  function resetFormForCreate() {
    elements.userForm.reset();
    elements.userId.value = '';
    elements.role.value = 'student';
    elements.status.value = 'pending';
    elements.userFormModalLabel.textContent = 'Add User';
    elements.saveUserBtn.textContent = 'Create User';
    clearValidationErrors();
  }

  function fillFormForEdit(user) {
    elements.userId.value = String(user.id);
    elements.email.value = user.email;
    elements.password.value = '';
    elements.role.value = user.role;
    elements.status.value = user.status;
    elements.nom.value = user.nom || '';
    elements.prenom.value = user.prenom || '';
    elements.telephone.value = user.telephone || '';
    elements.photo.value = user.photo || '';

    elements.userFormModalLabel.textContent = `Edit User #${user.id}`;
    elements.saveUserBtn.textContent = 'Update User';
    clearValidationErrors();
  }

  async function loadUsers() {
    const params = new URLSearchParams();

    if (state.search) {
      params.set('search', state.search);
    }

    if (state.role) {
      params.set('role', state.role);
    }

    if (state.status) {
      params.set('status', state.status);
    }

    const suffix = params.toString() ? `?${params.toString()}` : '';

    try {
      const payload = await apiRequest(`/users${suffix}`);
      state.users = payload.data || [];
      updateStats(payload.stats || {});
      renderUsersTable();
    } catch (error) {
      if (error.status === 403) {
        elements.usersTableBody.innerHTML = `
          <tr>
            <td colspan="6" class="text-center py-5 text-danger">Access denied. Only admin can access this module.</td>
          </tr>
        `;
        return;
      }

      elements.usersTableBody.innerHTML = `
        <tr>
          <td colspan="6" class="text-center py-5 text-danger">Unable to load users. Please verify the API server is running.</td>
        </tr>
      `;
    }
  }

  async function openDetails(userId) {
    try {
      const payload = await apiRequest(`/users/${userId}`);
      const user = payload.data;

      elements.userDetailsBody.innerHTML = `
        <div class="d-flex flex-column gap-2">
          <div><strong>ID:</strong> ${user.id}</div>
          <div><strong>Name:</strong> ${escapeHtml(user.name)}</div>
          <div><strong>Email:</strong> ${escapeHtml(user.email)}</div>
          <div><strong>Role:</strong> ${escapeHtml(user.role)}</div>
          <div><strong>Status:</strong> ${escapeHtml(user.status)}</div>
          <div><strong>Nom:</strong> ${escapeHtml(user.nom)}</div>
          <div><strong>Prenom:</strong> ${escapeHtml(user.prenom)}</div>
          <div><strong>Telephone:</strong> ${escapeHtml(user.telephone)}</div>
          <div><strong>Photo:</strong> ${escapeHtml(user.photo || 'N/A')}</div>
          <div><strong>Created At:</strong> ${new Date(user.created_at).toLocaleString()}</div>
        </div>
      `;

      userDetailsModal.show();
    } catch (error) {
      showToast(error.message || 'Failed to load user details.', 'danger');
    }
  }

  async function submitUserForm(event) {
    event.preventDefault();

    clearValidationErrors();

    const values = getFormValues();
    const isEdit = Boolean(values.id);
    const formErrors = validateForm(values, isEdit);

    if (Object.keys(formErrors).length) {
      applyValidationErrors(formErrors);
      return;
    }

    const payload = {
      email: values.email,
      role: values.role,
      status: values.status,
      nom: values.nom,
      prenom: values.prenom,
      telephone: values.telephone,
      photo: values.photo,
    };

    if (!isEdit || values.password) {
      payload.password = values.password;
    }

    const method = isEdit ? 'PUT' : 'POST';
    const endpoint = isEdit ? `/users/${values.id}` : '/users';

    try {
      await apiRequest(endpoint, {
        method,
        body: JSON.stringify(payload),
      });

      userFormModal.hide();
      showToast(isEdit ? 'User updated successfully.' : 'User created successfully.', 'success');
      await loadUsers();
    } catch (error) {
      if (error.payload && error.payload.errors) {
        applyValidationErrors(error.payload.errors);
      }

      showToast(error.message || 'Unable to save user.', 'danger');
    }
  }

  async function deleteUser(userId) {
    const confirmed = window.confirm('Are you sure you want to delete this user?');

    if (!confirmed) {
      return;
    }

    try {
      await apiRequest(`/users/${userId}`, {
        method: 'DELETE',
      });

      showToast('User deleted successfully.', 'success');
      await loadUsers();
    } catch (error) {
      showToast(error.message || 'Unable to delete user.', 'danger');
    }
  }

  async function performSpecialAction(userId, action) {
    const endpointMap = {
      activate: `/users/${userId}/activate`,
      deactivate: `/users/${userId}/deactivate`,
      suspend: `/users/${userId}/suspend`,
      validateTeacher: `/users/${userId}/validate-teacher`,
    };

    try {
      await apiRequest(endpointMap[action], {
        method: 'PATCH',
      });

      showToast('User status updated successfully.', 'success');
      await loadUsers();
    } catch (error) {
      showToast(error.message || 'Unable to update user status.', 'danger');
    }
  }

  async function handleTableActions(event) {
    const button = event.target.closest('button[data-action]');

    if (!button) {
      return;
    }

    const userId = Number(button.getAttribute('data-id'));
    const action = button.getAttribute('data-action');

    if (!userId || !action) {
      return;
    }

    const localUser = state.users.find((item) => item.id === userId);

    if (action === 'view') {
      await openDetails(userId);
      return;
    }

    if (action === 'edit') {
      if (!localUser) {
        return;
      }

      fillFormForEdit(localUser);
      userFormModal.show();
      return;
    }

    if (action === 'delete') {
      await deleteUser(userId);
      return;
    }

    if (['activate', 'deactivate', 'suspend', 'validateTeacher'].includes(action)) {
      await performSpecialAction(userId, action);
    }
  }

  function bindEvents() {
    let searchTimeout = null;

    elements.searchInput.addEventListener('input', () => {
      if (searchTimeout) {
        clearTimeout(searchTimeout);
      }

      searchTimeout = setTimeout(() => {
        state.search = elements.searchInput.value.trim();
        loadUsers();
      }, 250);
    });

    elements.roleFilter.addEventListener('change', () => {
      state.role = elements.roleFilter.value;
      loadUsers();
    });

    elements.statusFilter.addEventListener('change', () => {
      state.status = elements.statusFilter.value;
      loadUsers();
    });

    elements.clearFiltersBtn.addEventListener('click', () => {
      state.search = '';
      state.role = '';
      state.status = '';
      elements.searchInput.value = '';
      elements.roleFilter.value = '';
      elements.statusFilter.value = '';
      loadUsers();
    });

    elements.openCreateUserBtn.addEventListener('click', () => {
      resetFormForCreate();
      userFormModal.show();
    });

    elements.userForm.addEventListener('submit', submitUserForm);

    elements.usersTableBody.addEventListener('click', handleTableActions);

    elements.filterPendingTeachersBtn.addEventListener('click', () => {
      state.role = 'teacher';
      state.status = 'pending';
      elements.roleFilter.value = 'teacher';
      elements.statusFilter.value = 'pending';
      loadUsers();
    });
  }

  function init() {
    setSidebarActiveState();
    bindEvents();
    loadUsers();
  }

  document.addEventListener('DOMContentLoaded', init);
})();
