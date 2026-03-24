<?php
$lang_array = [
    'es' => [
        'titulo' => 'Iniciar Sesión',
        'usuario' => 'Usuario',
        'password' => 'Contraseña',
        'boton' => 'Entrar',
        'error_vacios' => 'Por favor, llena todos los campos.',
        'error_login' => 'Usuario o contraseña incorrectos.',
        'exito' => '¡Bienvenido!',
        'placeholder_user' => 'Ingresa tu usuario',
        'placeholder_pass' => 'Ingresa tu contraseña'
    ],
    'en' => [
        'titulo' => 'Login',
        'usuario' => 'Username',
        'password' => 'Password',
        'boton' => 'Sign In',
        'error_vacios' => 'Please fill in all fields.',
        'error_login' => 'Invalid username or password.',
        'exito' => 'Welcome!',
        'placeholder_user' => 'Enter your username',
        'placeholder_pass' => 'Enter your password'
    ]
];

// Definir idioma por defecto
if (!isset($_SESSION['Idioma'])) {
    $_SESSION['Idioma'] = 'es';
}

$lang = $_SESSION['Idioma'];
$texts = $lang_array[$lang];
?>