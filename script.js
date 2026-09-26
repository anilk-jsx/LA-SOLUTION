/**
 * LA SOLUTION PARTNERSHIP - INTERACTIVE SCRIPT
 * Handles navigation, interactive umbrella rib clicks, vertical filtering,
 * form validation, direct WhatsApp message generation, and modal feedback.
 */

document.addEventListener('DOMContentLoaded', () => {

  // --- 1. Mobile Menu Toggle ---
  const mobileToggle = document.getElementById('mobileToggle');
  const navMenu = document.getElementById('navMenu');

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
  ribs.forEach(rib => {
    rib.addEventListener('click', () => {
      const targetId = rib.getAttribute('data-target');
      if (targetId) {
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

  // --- 3. Filter Chips for 8 Verticals ---
  const filterChips = document.querySelectorAll('.legend-chips .chip');
  const verticalCards = document.querySelectorAll('.vertical-card');

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

  // Highlight effect CSS keyframe in JS if needed
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
 */
function preselectVertical(verticalName) {
  const formSection = document.getElementById('connect');
  const verticalDropdown = document.getElementById('verticalInterest');

  if (verticalDropdown) {
    let found = false;
    for (let i = 0; i < verticalDropdown.options.length; i++) {
      if (verticalDropdown.options[i].text.includes(verticalName) || 
          verticalDropdown.options[i].value.includes(verticalName) ||
          verticalName.includes(verticalDropdown.options[i].value)) {
        verticalDropdown.selectedIndex = i;
        found = true;
        break;
      }
    }
    if (!found) {
      verticalDropdown.value = verticalName;
    }
  }

  if (formSection) {
    formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    setTimeout(() => {
      const nameInput = document.getElementById('fullName');
      if (nameInput) nameInput.focus();
    }, 600);
  }
}
window.preselectVertical = preselectVertical;
