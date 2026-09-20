// Indian States List with Official GST State Codes
const INDIAN_STATES = [
  { code: '33', name: 'Tamil Nadu' },
  { code: '27', name: 'Maharashtra' },
  { code: '29', name: 'Karnataka' },
  { code: '07', name: 'Delhi' },
  { code: '09', name: 'Uttar Pradesh' },
  { code: '19', name: 'West Bengal' },
  { code: '24', name: 'Gujarat' },
  { code: '32', name: 'Kerala' },
  { code: '36', name: 'Telangana' },
  { code: '37', name: 'Andhra Pradesh' },
  { code: '03', name: 'Punjab' },
  { code: '06', name: 'Haryana' },
  { code: '08', name: 'Rajasthan' },
  { code: '10', name: 'Bihar' },
  { code: '23', name: 'Madhya Pradesh' },
  { code: '21', name: 'Odisha' },
  { code: '18', name: 'Assam' },
  { code: '02', name: 'Himachal Pradesh' },
  { code: '05', name: 'Uttarakhand' },
  { code: '30', name: 'Goa' },
  { code: '11', name: 'Sikkim' }
];

// Helper to make AJAX API requests with Laravel CSRF & standard URL prefixing
async function apiFetch(url, options = {}) {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken,
    ...(options.headers || {})
  };

  const fullUrl = url.startsWith('http') ? url : (window.APP_URL || '') + url;
  const response = await fetch(fullUrl, { ...options, headers });
  
  if (response.status === 401) {
    window.location.href = (window.APP_URL || '') + '/login';
    return;
  }

  const data = await response.json();
  if (!response.ok) {
    throw new Error(data.message || 'An error occurred.');
  }
  return data;
}

// Global modal handling
function showModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) modal.classList.remove('hidden');
}

function hideModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) modal.classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
  // Populate State Select elements
  document.querySelectorAll('select.state-select').forEach(select => {
    select.innerHTML = '<option value="">Select State</option>';
    INDIAN_STATES.forEach(st => {
      const opt = document.createElement('option');
      opt.value = st.name;
      opt.setAttribute('data-code', st.code);
      opt.textContent = `${st.code} - ${st.name}`;
      select.appendChild(opt);
    });
  });

  // Logout Confirmation Modal
  const logoutBtn = document.getElementById('logoutBtn');
  const logoutModal = document.getElementById('logoutConfirmModal');
  const confirmLogoutYes = document.getElementById('confirmLogoutYes');
  const confirmLogoutNo = document.getElementById('confirmLogoutNo');

  if (logoutBtn && logoutModal) {
    logoutBtn.addEventListener('click', () => showModal('logoutConfirmModal'));
    if (confirmLogoutNo) confirmLogoutNo.addEventListener('click', () => hideModal('logoutConfirmModal'));
    if (confirmLogoutYes) {
      confirmLogoutYes.addEventListener('click', async () => {
        try {
          await apiFetch('/api/logout', { method: 'POST' });
          window.location.href = (window.APP_URL || '') + '/login';
        } catch (e) {
          alert('Logout failed: ' + e.message);
        }
      });
    }
  }
});
