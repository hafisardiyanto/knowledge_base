<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KNOWLEDGE BASE GENERATOR</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>
    <div class="login-card">
        <div class="logo">🔐 KNOWLEDGE BASE Generator</div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            @if($errors->any())
                <div
                    style="background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="form-group">
                <label>Email / Username</label>
                <input type="text" name="email" value="{{ old('email', 'admin@example.com') }}" required autofocus
                    placeholder="admin@example.com">
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="password-container">
                    <input type="password" name="password" id="password" value="password" required>
                    <span class="toggle-password" onclick="togglePassword()">Show</span>
                </div>
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>

    <script>
        function togglePassword() {
            var pwd = document.getElementById("password");
            var toggle = document.querySelector(".toggle-password");
            if (pwd.type === "password") {
                pwd.type = "text";
                toggle.textContent = "Hide";
            } else {
                pwd.type = "password";
                toggle.textContent = "Show";
            }
        }
    </script>
</body>

</html>