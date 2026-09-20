const translations = {
  en: {
    'nav.brand': 'GST Billing',
    'nav.dashboard': 'Dashboard',
    'nav.billing': 'Billing',
    'nav.invoices': 'Invoices',
    'nav.deliverySheet': 'Delivery Sheet',
    'nav.payments': 'Payments',
    'nav.customers': 'Customers',
    'nav.products': 'Products',
    'nav.productSales': 'Product Sales',
    'nav.settings': 'Settings',
    'nav.logout': 'Logout',
    'dashboard.title': 'Dashboard Overview',
    'dashboard.totalInvoices': 'Total Invoices',
    'dashboard.overallValue': 'Overall Value',
    'dashboard.thisMonthValue': 'This Month Value',
    'dashboard.outstandingAmount': 'Outstanding Amount',
    'dashboard.recentInvoices': 'Recent Invoices',
    'billing.createInvoice': 'Create Invoice',
    'billing.customer': 'Customer',
    'billing.customerGstin': 'Customer GSTIN',
    'billing.buyerAddress': 'Buyer Address',
    'billing.billDate': 'Bill Date',
    'billing.contactNumber': 'Contact Number',
    'billing.buyerEmail': 'Buyer Email',
    'billing.eWayBillNo': 'e-Way Bill No',
    'billing.supplyType': 'Supply Type',
    'common.state': 'State',
    'common.save': 'Save',
    'common.cancel': 'Cancel',
    'common.delete': 'Delete',
    'common.print': 'Print',
    'common.reset': 'Reset',
    'common.search': 'Search'
  },
  hi: {
    'nav.brand': 'जीएसटी बिलिंग',
    'nav.dashboard': 'डैशबोर्ड',
    'nav.billing': 'बिलिंग',
    'nav.invoices': 'चालान (इनवॉइस)',
    'nav.deliverySheet': 'डिलीवरी शीट',
    'nav.payments': 'भुगतान',
    'nav.customers': 'ग्राहक',
    'nav.products': 'उत्पाद',
    'nav.productSales': 'उत्पाद बिक्री',
    'nav.settings': 'सेटिंग्स',
    'nav.logout': 'लॉग आउट',
    'dashboard.title': 'डैशबोर्ड विवरण',
    'dashboard.totalInvoices': 'कुल चालान',
    'dashboard.overallValue': 'कुल मूल्य',
    'dashboard.thisMonthValue': 'इस महीने का मूल्य',
    'dashboard.outstandingAmount': 'बकाया राशि',
    'dashboard.recentInvoices': 'हाल के चालान',
    'billing.createInvoice': 'चालान बनाएं',
    'billing.customer': 'ग्राहक',
    'billing.customerGstin': 'ग्राहक जीएसटी नंबर',
    'billing.buyerAddress': 'खरीदार का पता',
    'billing.billDate': 'बिल की तारीख',
    'billing.contactNumber': 'संपर्क नंबर',
    'billing.buyerEmail': 'खरीदार का ईमेल',
    'billing.eWayBillNo': 'ई-वे बिल नंबर',
    'billing.supplyType': 'आपूर्ति प्रकार',
    'common.state': 'राज्य',
    'common.save': 'सहेजें',
    'common.cancel': 'रद्द करें',
    'common.delete': 'हटाएं',
    'common.print': 'प्रिंट करें',
    'common.reset': 'रीसेट',
    'common.search': 'खोजें'
  },
  ta: {
    'nav.brand': 'ஜிஎஸ்டி பில்லிங்',
    'nav.dashboard': 'டாஷ்போர்டு',
    'nav.billing': 'பில்லிங்',
    'nav.invoices': 'இன்வாய்ஸ்கள்',
    'nav.deliverySheet': 'டெலிவரி ஷீட்',
    'nav.payments': 'பணப்பரிமாற்றம்',
    'nav.customers': 'வாடிக்கையாளர்கள்',
    'nav.products': 'தயாரிப்புகள்',
    'nav.productSales': 'தயாரிப்பு விற்பனை',
    'nav.settings': 'அமைப்புகள்',
    'nav.logout': 'வெளியேறு',
    'dashboard.title': 'டாஷ்போர்டு மேலோட்டம்',
    'dashboard.totalInvoices': 'மொத்த இன்வாய்ஸ்கள்',
    'dashboard.overallValue': 'மொத்த மதிப்பு',
    'dashboard.thisMonthValue': 'இந்த மாத மதிப்பு',
    'dashboard.outstandingAmount': 'நிலுவைத் தொகை',
    'dashboard.recentInvoices': 'சமீபத்திய இன்வாய்ஸ்கள்',
    'billing.createInvoice': 'இன்வாய்ஸ் உருவாக்கு',
    'billing.customer': 'வாடிக்கையாளர்',
    'billing.customerGstin': 'வாடிக்கையாளர் ஜிஎஸ்டி எண்',
    'billing.buyerAddress': 'வாங்குபவர் முகவரி',
    'billing.billDate': 'பில் தேதி',
    'billing.contactNumber': 'தொடர்பு எண்',
    'billing.buyerEmail': 'மின்னஞ்சல்',
    'billing.eWayBillNo': 'இ-வே பில் எண்',
    'billing.supplyType': 'சப்ளை வகை',
    'common.state': 'மாநிலம்',
    'common.save': 'சேமி',
    'common.cancel': 'ரத்து செய்',
    'common.delete': 'நீக்கு',
    'common.print': 'அச்சிடு',
    'common.reset': 'மீட்டமை',
    'common.search': 'தேடு'
  }
};

let currentLang = localStorage.getItem('gst_app_lang') || 'en';

function setLanguage(lang) {
  if (!translations[lang]) lang = 'en';
  currentLang = lang;
  localStorage.setItem('gst_app_lang', lang);

  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (translations[lang][key]) {
      el.textContent = translations[lang][key];
    }
  });

  const select = document.getElementById('languageSwitcher');
  if (select) select.value = lang;
}

document.addEventListener('DOMContentLoaded', () => {
  setLanguage(currentLang);

  const select = document.getElementById('languageSwitcher');
  if (select) {
    select.value = currentLang;
    select.addEventListener('change', (e) => {
      setLanguage(e.target.value);
    });
  }
});
