const userInput = document.getElementById("username");
const passInput = document.getElementById("password");
const error = document.getElementById("errorMsg");

[userInput, passInput].forEach(input => {
  input.addEventListener("input", () => error.textContent = "");
});

document.getElementById("loginForm").addEventListener("submit", function(e) {
  e.preventDefault();

  const username = userInput.value.trim();
  const password = passInput.value.trim();

  error.textContent = "";

  if (!username || !password) {
    error.textContent = "Vui lòng nhập đầy đủ thông tin.";
    return;
  }

  const validUser = "Admin123@gmail.com";
  const validPass = "Admin@2025";

  if (username === validUser && password === validPass) {
    window.location.href = "../admin/dashboard.php";
  } else {
    error.textContent = "Sai tài khoản hoặc mật khẩu.";
  }
});
