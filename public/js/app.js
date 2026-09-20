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

// Global Toast Notification function
function showToast(message, type = 'success') {
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    document.body.appendChild(container);
  } else {
    container.innerHTML = '';
  }

  const iconName = type === 'success' ? 'check_circle' : (type === 'error' ? 'cancel' : 'info');
  
  const toast = document.createElement('div');
  toast.className = `toast-notification ${type}`;
  toast.innerHTML = `
    <span class="material-symbols-outlined toast-icon">${iconName}</span>
    <span style="flex: 1; font-size: 14px; font-weight: 700; color: #ffffff;">${message}</span>
  `;

  container.appendChild(toast);

  requestAnimationFrame(() => {
    toast.classList.add('show');
  });

  setTimeout(() => {
    toast.classList.remove('show');
    toast.addEventListener('transitionend', () => {
      toast.remove();
    });
  }, 3500);
}

// Disable browser autofill / autocomplete globally across all forms & inputs
function disableAutofill() {
  document.querySelectorAll('form').forEach(form => {
    form.setAttribute('autocomplete', 'off');
    form.setAttribute('autocorrect', 'off');
    form.setAttribute('autocapitalize', 'off');
    form.setAttribute('spellcheck', 'false');
  });

  document.querySelectorAll('input, select, textarea').forEach(input => {
    input.setAttribute('autocomplete', 'new-password');
    input.setAttribute('autocorrect', 'off');
    input.setAttribute('autocapitalize', 'off');
    input.setAttribute('spellcheck', 'false');
    input.setAttribute('aria-autocomplete', 'none');
    input.removeAttribute('list');

    // Prevent Chrome/Edge history popups using readonly focus toggle
    const inputType = (input.type || '').toLowerCase();
    if (!['date', 'time', 'datetime-local', 'checkbox', 'radio', 'hidden', 'file', 'submit', 'button', 'select-one'].includes(inputType)) {
      if (!input.dataset.autofillDisabled) {
        input.dataset.autofillDisabled = 'true';
        input.setAttribute('readonly', 'readonly');
        input.addEventListener('focus', function() {
          this.removeAttribute('readonly');
        });
        input.addEventListener('blur', function() {
          this.setAttribute('readonly', 'readonly');
        });
      }
    }
  });

  // Remove any datalist elements to stop native autocomplete menus
  document.querySelectorAll('datalist').forEach(dl => dl.remove());
}

// Global Export Helper for CSV / Excel Download
function downloadCSV(filename, headers, rows, totalsRow) {
  let csvStr = "\uFEFF"; // UTF-8 BOM for MS Excel compatibility
  csvStr += headers.map(h => `"${String(h ?? '').replace(/"/g, '""')}"`).join(",") + "\r\n";
  
  rows.forEach(row => {
    csvStr += row.map(val => `"${String(val ?? '').replace(/"/g, '""')}"`).join(",") + "\r\n";
  });

  if (totalsRow) {
    csvStr += totalsRow.map(val => `"${String(val ?? '').replace(/"/g, '""')}"`).join(",") + "\r\n";
  }

  // Clean filename for Windows OS
  let safeFilename = String(filename || 'Export_Report.csv').replace(/[/\\?%*:|"<>]/g, '_');
  if (!safeFilename.toLowerCase().endsWith('.csv')) {
    safeFilename += '.csv';
  }

  const blob = new Blob([csvStr], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.setAttribute("href", url);
  link.setAttribute("download", safeFilename);
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  setTimeout(() => URL.revokeObjectURL(url), 1000);
}

// Global Date-Time Timestamp Formatter (e.g. 20-09-2026_23-42)
function getFormattedTimestamp() {
  const now = new Date();
  const dd = String(now.getDate()).padStart(2, '0');
  const mm = String(now.getMonth() + 1).padStart(2, '0');
  const yyyy = now.getFullYear();
  const hh = String(now.getHours()).padStart(2, '0');
  const min = String(now.getMinutes()).padStart(2, '0');
  return `${dd}-${mm}-${yyyy}_${hh}-${min}`;
}

// Global Export Helper for PDF Document Window / Save as PDF
function openPDFReport(title, headers, rows, summaryCards = [], docFileName = null) {
  const printWin = window.open('', '_blank');
  if (!printWin) {
    alert('Please allow popups in your browser to download/print PDF reports!');
    return;
  }

  const documentTitle = docFileName || title;

  let summaryHtml = '';
  if (summaryCards.length > 0) {
    summaryHtml = `<div style="display: flex; gap: 16px; margin-bottom: 20px; flex-wrap: wrap;">`;
    summaryCards.forEach(c => {
      summaryHtml += `
        <div style="flex: 1; min-width: 140px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
          <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">${c.label}</div>
          <div style="font-size: 18px; font-weight: 800; color: #0f172a; margin-top: 4px;">${c.val}</div>
        </div>
      `;
    });
    summaryHtml += `</div>`;
  }

  let tableHeaderHtml = headers.map(h => `<th style="padding: 10px 12px; text-align: left; background: #f1f5f9; color: #334155; font-size: 12px; font-weight: 700; border-bottom: 2px solid #cbd5e1;">${h}</th>`).join('');
  let tableBodyHtml = rows.map(r => {
    return `<tr style="border-bottom: 1px solid #e2e8f0;">` + r.map(c => `<td style="padding: 9px 12px; font-size: 13px; color: #1e293b;">${c}</td>`).join('') + `</tr>`;
  }).join('');

  const html = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>${documentTitle}</title>
      <style>
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; color: #0f172a; margin: 24px; padding: 0; }
        h1 { font-size: 22px; font-weight: 800; margin: 0 0 4px 0; color: #0f172a; }
        p { font-size: 12px; color: #64748b; margin: 0 0 20px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        @media print {
          body { margin: 0; }
          .no-print { display: none; }
        }
      </style>
    </head>
    <body>
      <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 2px solid #0f172a; padding-bottom: 12px;">
        <div>
          <h1>${title}</h1>
          <p>Generated on ${new Date().toLocaleDateString('en-GB')} | Filtered Ledger Export</p>
        </div>
        <button class="no-print" onclick="window.print()" style="background: #6366f1; color: white; border: none; padding: 10px 18px; font-weight: 700; border-radius: 8px; cursor: pointer;">Save as PDF / Print</button>
      </div>
      ${summaryHtml}
      <table>
        <thead><tr>${tableHeaderHtml}</tr></thead>
        <tbody>${tableBodyHtml}</tbody>
      </table>
      <script>
        window.onload = function() {
          setTimeout(function() { window.print(); }, 400);
        };
      </script>
    </body>
    </html>
  `;

  printWin.document.write(html);
  printWin.document.close();
}

document.addEventListener('DOMContentLoaded', () => {
  disableAutofill();

  // Dynamically observe new modal or form additions
  const observer = new MutationObserver(() => disableAutofill());
  observer.observe(document.body, { childList: true, subtree: true });

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
