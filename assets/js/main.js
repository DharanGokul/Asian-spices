var currentYear = new Date().getFullYear();
document.getElementById('currentYear').textContent = currentYear;

// Hamburger menu toggle
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('nav');
hamburger.addEventListener('click', () => {
  navMenu.classList.toggle('nav-active');
  hamburger.classList.toggle('close');
});