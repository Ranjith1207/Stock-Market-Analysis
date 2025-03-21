// script.js
function toggleForm() {
  const loginForm = document.getElementById('login-form');
  const registerForm = document.getElementById('register-form');
  const formTitle = document.getElementById('form-title');
  const toggleLink = document.getElementById('toggle-link');

  if (loginForm.style.display === 'none') {
    loginForm.style.display = 'block';
    registerForm.style.display = 'none';
    formTitle.textContent = 'Login';
    toggleLink.innerHTML = "Don't have an account? <a href='javascript:void(0);' onclick='toggleForm()'>Register</a>";
  } else {
    loginForm.style.display = 'none';
    registerForm.style.display = 'block';
    formTitle.textContent = 'Register';
    toggleLink.innerHTML = "Already have an account? <a href='javascript:void(0);' onclick='toggleForm()'>Login</a>";
  }
}

document.getElementById('login-form').style.display = 'block';
document.getElementById('register-form').style.display = 'none';
