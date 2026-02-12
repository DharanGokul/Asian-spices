var currentYear = new Date().getFullYear();
document.getElementById('currentYear').textContent = currentYear;

// Hamburger menu toggle
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('nav');
hamburger.addEventListener('click', () => {
  navMenu.classList.toggle('nav-active');
  hamburger.classList.toggle('close');
});

const header = document.getElementById("mainHeader");

window.addEventListener("scroll", function () {
  if (window.scrollY > 0) {
    header.classList.add("sticky");
  } else {
    header.classList.remove("sticky");
  }
});

// Contact form validation
document.addEventListener('DOMContentLoaded', function() {
  const contactForm = document.getElementById('contactForm');
  if (contactForm) {
    // Real-time validation - clear errors as user types
    const fields = ['name', 'email', 'companyName', 'phone', 'country'];
    fields.forEach(fieldId => {
      const field = document.getElementById(fieldId);
      field.addEventListener('input', function() {
        clearFieldError(fieldId);
      });
    });
    
    // For select field
    const inquiryType = document.getElementById('inquiry-type');
    inquiryType.addEventListener('change', function() {
      clearFieldError('inquiry-type');
    });
    
    contactForm.addEventListener('submit', function(event) {
      event.preventDefault();
      
      // Clear previous errors and success message
      clearFieldErrors();
      hideSuccessMessage();
      
      // Get form fields
      const name = document.getElementById('name').value.trim();
      const email = document.getElementById('email').value.trim();
      const companyName = document.getElementById('companyName').value.trim();
      const phone = document.getElementById('phone').value.trim();
      const country = document.getElementById('country').value.trim();
      const inquiryTypeValue = document.getElementById('inquiry-type').value;
      const message = document.getElementById('message').value.trim();
      
      // Validation
      let isValid = true;
      
      // Required fields
      if (!name) {
        showError('name', 'Full Name is required');
        isValid = false;
      }
      
      if (!email) {
        showError('email', 'Email is required');
        isValid = false;
      } else if (!isValidEmail(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
      }
      
      if (!companyName) {
        showError('companyName', 'Company Name is required');
        isValid = false;
      }
      
      if (!country) {
        showError('country', 'Country is required');
        isValid = false;
      }
      
      if (!inquiryTypeValue) {
        showError('inquiry-type', 'Please select an Inquiry Type');
        isValid = false;
      }
      
      // Optional phone validation
      if (phone && !isValidPhone(phone)) {
        showError('phone', 'Please enter a valid phone number');
        isValid = false;
      }
      
      if (isValid) {
        // Submit form via AJAX
        submitFormViaAjax();
      }
    });
  }
});

// Function to show error message
function showError(fieldId, message) {
  const field = document.getElementById(fieldId);
  const errorDiv = document.getElementById(fieldId + '-error');
  field.classList.add('is-invalid');
  errorDiv.textContent = message;
}

// Function to clear error for a specific field
function clearFieldError(fieldId) {
  const field = document.getElementById(fieldId);
  const errorDiv = document.getElementById(fieldId + '-error');
  field.classList.remove('is-invalid');
  errorDiv.textContent = '';
}

// Function to show success message
function showSuccessMessage(message) {
  const successDiv = document.getElementById('success-message');
  successDiv.textContent = message;
  successDiv.style.display = 'block';
  // Scroll to the success message
  successDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// Function to hide success message
function hideSuccessMessage() {
  const successDiv = document.getElementById('success-message');
  successDiv.style.display = 'none';
  successDiv.textContent = '';
}

// Function to clear all field errors
function clearFieldErrors() {
  const fields = ['name', 'email', 'companyName', 'phone', 'country', 'inquiry-type'];
  fields.forEach(fieldId => {
    clearFieldError(fieldId);
  });
}

// Function to clear all form inputs
function clearFormInputs() {
  const fields = ['name', 'email', 'companyName', 'phone', 'country', 'message'];
  fields.forEach(fieldId => {
    document.getElementById(fieldId).value = '';
  });
  // Reset select field
  document.getElementById('inquiry-type').selectedIndex = 0;
}

// Function to submit form via AJAX
function submitFormViaAjax() {
  const form = document.getElementById('contactForm');
  const formData = new FormData(form);

  // Show loading state
  const submitButton = form.querySelector('button[type="submit"]');
  const originalText = submitButton.innerHTML;
  submitButton.disabled = true;
  submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';

  // Send AJAX request
  fetch('contact-form.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showSuccessMessage(data.message);
      clearFormInputs();
    } else {
      // Show server-side validation errors
      if (data.errors && data.errors.length > 0) {
        alert('Please correct the following errors:\n' + data.errors.join('\n'));
      } else {
        alert(data.message || 'An error occurred while submitting the form.');
      }
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred while submitting the form. Please try again.');
  })
  .finally(() => {
    // Reset button state
    submitButton.disabled = false;
    submitButton.innerHTML = originalText;
  });
}

// Email validation function
function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

// Basic phone validation (allows numbers, spaces, dashes, parentheses, plus)
function isValidPhone(phone) {
  const phoneRegex = /^[\+]?[1-9][\d\s\-\(\)]{7,}$/;
  return phoneRegex.test(phone);
}

// Count Animation for statistics
document.addEventListener('DOMContentLoaded', function() {
  const successCountRows = document.querySelectorAll('.success-count');
  let hasAnimated = false;

  const animateCounters = () => {
    if (hasAnimated) return;
    hasAnimated = true;

    const countElements = document.querySelectorAll('.success-count h1');
    countElements.forEach(element => {
      const target = parseInt(element.textContent.replace(/\D/g, ''));
      const suffix = element.textContent.replace(/\d/g, '');
      let current = 0;
      const increment = Math.ceil(target / 50);
      const duration = 2000; // 2 seconds
      const stepTime = duration / (target / increment);

      const counter = setInterval(() => {
        current += increment;
        if (current >= target) {
          current = target;
          clearInterval(counter);
        }
        element.textContent = current + suffix;
      }, stepTime);
    });
  };

  // Use Intersection Observer to trigger animation when section is in view
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting && !hasAnimated) {
        animateCounters();
      }
    });
  }, { threshold: 0.5 });

  successCountRows.forEach(row => {
    observer.observe(row);
  });
});