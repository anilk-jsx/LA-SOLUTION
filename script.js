/**
 * LA SOLUTION PARTNERSHIP - INTERACTIVE SCRIPT
 * Handles:
 * 1. Navbar active state updates on click & on scroll (ScrollSpy)
 * 2. Mobile menu toggle
 * 3. Interactive umbrella rib clicks -> Scroll to vertical card with highlight pulse
 * 4. Filter chips for 8 verticals -> Filter & scroll down to verticals section
 * 5. Card button actions -> Smooth scroll to connect form & pre-select vertical in dropdown
 * 6. Form validation, localStorage persistence, and success confirmation modal
 * 7. Instant WhatsApp message dispatch with pre-filled details
 */

document.addEventListener('DOMContentLoaded', () => {

  // --- 1. Navbar Active State & ScrollSpy ---
  const navMenu = document.getElementById('navMenu');
  const mobileToggle = document.getElementById('mobileToggle');
  const navLinks = document.querySelectorAll('.nav-menu .nav-link');

  const navSectionMap = [
    { id: 'hero', link: document.querySelector('.nav-menu a[href="#hero"]') },
    { id: 'ecosystem', link: document.querySelector('.nav-menu a[href="#ecosystem"]') },
    { id: 'verticals', link: document.querySelector('.nav-menu a[href="#verticals"]') },
    { id: 'foundation', link: document.querySelector('.nav-menu a[href="#foundation"]') },
    { id: 'impact', link: document.querySelector('.nav-menu a[href="#impact"]') },
    { id: 'connect', link: document.querySelector('.nav-menu a[href="#connect"]') }
  ];

  // Explicit click on any navbar link
  navLinks.forEach(link => {
    link.addEventListener('click', () => {
      navLinks.forEach(l => l.classList.remove('active'));
      link.classList.add('active');
    });
  });

  // Dynamic ScrollSpy
  function updateActiveNavOnScroll() {
    const scrollPosition = window.scrollY + 140; // Header offset
    let activeLink = null;

    for (let i = navSectionMap.length - 1; i >= 0; i--) {
      const targetEl = document.getElementById(navSectionMap[i].id);
      if (targetEl) {
        const top = targetEl.offsetTop;
        if (scrollPosition >= top) {
          activeLink = navSectionMap[i].link;
          break;
        }
      }
    }

    if (activeLink) {
      navLinks.forEach(l => l.classList.remove('active'));
      activeLink.classList.add('active');
    }
  }

  window.addEventListener('scroll', updateActiveNavOnScroll, { passive: true });
  updateActiveNavOnScroll();

  // Mobile menu toggle
  if (mobileToggle && navMenu) {
    mobileToggle.addEventListener('click', () => {
      navMenu.classList.toggle('show');
      const icon = mobileToggle.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-bars');
        icon.classList.toggle('fa-xmark');
      }
    });

    // Close menu when clicking a link
    navMenu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        navMenu.classList.remove('show');
        const icon = mobileToggle.querySelector('i');
        if (icon) {
          icon.classList.add('fa-bars');
          icon.classList.remove('fa-xmark');
        }
      });
    });
  }

  // --- 2. Interactive Umbrella Ribs Click -> Scroll to Vertical Card ---
  const ribs = document.querySelectorAll('.umbrella-canopy .rib');
  const filterChips = document.querySelectorAll('.legend-chips .chip');
  const verticalCards = document.querySelectorAll('.vertical-card');
  const verticalsSection = document.getElementById('verticals');

  ribs.forEach(rib => {
    rib.addEventListener('click', () => {
      const targetId = rib.getAttribute('data-target');
      if (targetId) {
        // Ensure card is visible even if previously filtered
        filterChips.forEach(c => c.classList.remove('active'));
        const allChip = document.querySelector('.legend-chips .chip[data-filter="all"]');
        if (allChip) allChip.classList.add('active');
        verticalCards.forEach(card => card.style.display = 'flex');

        const targetElement = document.getElementById(targetId);
        if (targetElement) {
          targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
          targetElement.classList.add('card-highlight');
          setTimeout(() => {
            targetElement.classList.remove('card-highlight');
          }, 1800);
        }
      }
    });
  });

  // --- 3. Filter Chips for 8 Verticals with Smooth Scroll ---
  filterChips.forEach(chip => {
    chip.addEventListener('click', () => {
      filterChips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');

      const filter = chip.getAttribute('data-filter');

      verticalCards.forEach(card => {
        const category = card.getAttribute('data-category');
        if (filter === 'all' || category === filter) {
          card.style.display = 'flex';
          card.style.animation = 'fadeIn 0.4s ease';
        } else {
          card.style.display = 'none';
        }
      });

      // Smoothly scroll down to the verticals section
      if (verticalsSection) {
        verticalsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // --- 4. Form Validation & Handling ---
  const form = document.getElementById('partnershipForm');
  const submitViaWhatsAppBtn = document.getElementById('submitViaWhatsApp');
  const successModal = document.getElementById('successModal');
  const closeModalBtn = document.getElementById('closeModalBtn');
  const modalMessage = document.getElementById('modalMessage');

  // Input elements
  const fullName = document.getElementById('fullName');
  const phoneNumber = document.getElementById('phoneNumber');
  const emailAddress = document.getElementById('emailAddress');
  const partnershipType = document.getElementById('partnershipType');
  const verticalInterest = document.getElementById('verticalInterest');
  const cityState = document.getElementById('cityState');
  const message = document.getElementById('message');

  // Error elements
  const nameError = document.getElementById('nameError');
  const phoneError = document.getElementById('phoneError');
  const emailError = document.getElementById('emailError');
  const typeError = document.getElementById('typeError');
  const verticalError = document.getElementById('verticalError');

  function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
  }

  function validatePhone(phone) {
    const cleaned = phone.replace(/[^0-9+]/g, '');
    return cleaned.length >= 10;
  }

  function clearErrors() {
    [nameError, phoneError, emailError, typeError, verticalError].forEach(el => {
      if (el) el.textContent = '';
    });
  }

  function validateForm() {
    clearErrors();
    let isValid = true;

    if (!fullName.value.trim()) {
      nameError.textContent = 'Please enter your name.';
      isValid = false;
    }

    if (!phoneNumber.value.trim()) {
      phoneError.textContent = 'Phone/WhatsApp number is required.';
      isValid = false;
    } else if (!validatePhone(phoneNumber.value.trim())) {
      phoneError.textContent = 'Please enter a valid phone number (at least 10 digits).';
      isValid = false;
    }

    if (!emailAddress.value.trim()) {
      emailError.textContent = 'Email address is required.';
      isValid = false;
    } else if (!validateEmail(emailAddress.value.trim())) {
      emailError.textContent = 'Please provide a valid email format.';
      isValid = false;
    }

    if (!partnershipType.value) {
      typeError.textContent = 'Please select your nature of interest.';
      isValid = false;
    }

    if (!verticalInterest.value) {
      verticalError.textContent = 'Please choose a vertical or all verticals.';
      isValid = false;
    }

    return isValid;
  }

  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      if (validateForm()) {
        const formData = {
          name: fullName.value.trim(),
          phone: phoneNumber.value.trim(),
          email: emailAddress.value.trim(),
          type: partnershipType.value,
          vertical: verticalInterest.value,
          location: cityState.value.trim() || 'Not specified',
          message: message.value.trim() || 'No message provided',
          timestamp: new Date().toISOString()
        };

        // Store submission in localStorage for persistence
        try {
          const submissions = JSON.parse(localStorage.getItem('la_inquiries') || '[]');
          submissions.push(formData);
          localStorage.setItem('la_inquiries', JSON.stringify(submissions));
        } catch (err) {
          console.warn('Storage not accessible', err);
        }

        // Show confirmation modal
        if (modalMessage) {
          modalMessage.innerHTML = `Thank you <strong>${formData.name}</strong>! Your inquiry regarding <strong>${formData.vertical}</strong> (${formData.type}) has been logged. Our leadership team will connect with you via <strong>${formData.phone}</strong> or <strong>${formData.email}</strong> shortly.`;
        }
        if (successModal) {
          successModal.classList.add('active');
        }

        form.reset();
      }
    });
  }

  // Close modal
  if (closeModalBtn && successModal) {
    closeModalBtn.addEventListener('click', () => {
      successModal.classList.remove('active');
    });

    successModal.addEventListener('click', (e) => {
      if (e.target === successModal) {
        successModal.classList.remove('active');
      }
    });
  }

  // --- 5. Submit Directly via WhatsApp ---
  if (submitViaWhatsAppBtn) {
    submitViaWhatsAppBtn.addEventListener('click', () => {
      const name = fullName.value.trim() || 'A Partner';
      const phone = phoneNumber.value.trim() || 'N/A';
      const email = emailAddress.value.trim() || 'N/A';
      const type = partnershipType.value || 'General Partnership';
      const vertical = verticalInterest.value || 'Entire Umbrella Ecosystem';
      const city = cityState.value.trim() || 'Not specified';
      const msg = message.value.trim() || 'Interested in discussing business partnership opportunities.';

      const waText = 
`*LA Solution Partnership Inquiry*
• *Name:* ${name}
• *Phone:* ${phone}
• *Email:* ${email}
• *Interest Type:* ${type}
• *Vertical:* ${vertical}
• *City/State:* ${city}
• *Note:* ${msg}`;

      const waUrl = `https://wa.me/916370793232?text=${encodeURIComponent(waText)}`;
      window.open(waUrl, '_blank');
    });
  }

  // Highlight keyframes
  const styleSheet = document.createElement('style');
  styleSheet.textContent = `
    @keyframes pulseCard {
      0% { box-shadow: 0 0 0 0 rgba(2, 132, 199, 0.6); }
      70% { box-shadow: 0 0 0 16px rgba(2, 132, 199, 0); }
      100% { box-shadow: 0 0 0 0 rgba(2, 132, 199, 0); }
    }
    .card-highlight {
      animation: pulseCard 1.5s ease-out;
      border-color: #0284c7 !important;
      transform: translateY(-8px) scale(1.02) !important;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  `;
  document.head.appendChild(styleSheet);
});

/**
 * Global helper to pre-fill the form with a clicked vertical
 * Scrolls directly down to connect form panel, pre-selects the vertical in the dropdown,
 * flashes a visual highlight, and focuses on the input.
 */
function preselectVertical(verticalName) {
  const formSection = document.getElementById('connect');
  const verticalDropdown = document.getElementById('verticalInterest');

  if (verticalDropdown) {
    let found = false;
    for (let i = 0; i < verticalDropdown.options.length; i++) {
      const optVal = verticalDropdown.options[i].value;
      const optText = verticalDropdown.options[i].text;
      if (optVal === verticalName || 
          optText.includes(verticalName) || 
          optVal.includes(verticalName) ||
          verticalName.includes(optVal)) {
        verticalDropdown.selectedIndex = i;
        found = true;
        break;
      }
    }
    if (!found) {
      verticalDropdown.value = verticalName;
    }

    // Visual glow animation on the dropdown
    verticalDropdown.classList.add('dropdown-pulse');
    setTimeout(() => {
      verticalDropdown.classList.remove('dropdown-pulse');
    }, 1500);
  }

  if (formSection) {
    formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    setTimeout(() => {
      const nameInput = document.getElementById('fullName');
      if (nameInput) nameInput.focus();
    }, 500);
  }
}
window.preselectVertical = preselectVertical;
